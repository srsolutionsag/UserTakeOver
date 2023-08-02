<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\Settings\Settings;
use srag\Plugins\UserTakeOver\RequestHelper;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverAccessHandler
{
    use RequestHelper;

    protected IGroupRepository $group_repository;
    protected Settings $settings;
    protected RequestWrapper $get_request;
    protected Refinery $refinery;
    protected ilObjUser $current_user;
    protected ilRbacReview $review;
    protected ilRbacSystem $system;

    public function __construct(
        IGroupRepository $group_repository,
        Settings $settings,
        RequestWrapper $get_request,
        Refinery $refinery,
        ilObjUser $current_user,
        ilRbacReview $review,
        ilRbacSystem $system
    ) {
        $this->group_repository = $group_repository;
        $this->settings = $settings;
        $this->get_request = $get_request;
        $this->refinery = $refinery;
        $this->current_user = $current_user;
        $this->review = $review;
        $this->system = $system;
    }

    /**
     * Returns true if the current user is permitted to impersonate the target. This check
     * considers multiple factors:
     *
     *      - plugin-settings:
     *          - if the current user has access to the plugin.
     *          - if administrators can be impersonated.
     *     - group-settings (either user is a member of):
     *          - if the current user is restricted to group members.
     *          - if the current user is restricted to rbac roles.
     */
    public function canCurrentUserImpersonate(ilObjUser $target_user): bool
    {
        if (!$this->canCurrentUserUsePlugin()) {
            return false;
        }

        if (!$this->settings->isAllowImpersonationOfAdmins() && $this->isUserAdministrator($target_user->getId())) {
            return false;
        }

        if (!$this->isCurrentUserRestrictedToAnyGroup()) {
            return true;
        }

        $groups = $this->group_repository->getGroupsOfUser($this->current_user->getId(), true);

        $group_member_check = false;
        $group_role_check = false;

        foreach ($groups as $group) {
            // early return if earlier groups already totally allow the impersonation.
            if ($group_member_check && $group_role_check) {
                return true;
            }

            if ($group->isRestrictedToMembers()) {
                $group_member_check = $group_member_check || in_array($target_user->getId(), $group->getGroupMembers());
            }

            if ($group->isRestrictedToRoles()) {
                $group_role_check = $group_role_check || $this->review->isAssignedToAtLeastOneGivenRole(
                    $target_user->getId(),
                    $group->getAllowedRoles()
                );
            }
        }

        return ($group_member_check && $group_role_check);
    }

    public function canCurrentUserUsePlugin(): bool
    {
        if ($this->isCurrentUserAdministrator()) {
            return true;
        }

        if (empty($this->settings->getAllowedGlobalRoleIds())) {
            return false;
        }

        return $this->review->isAssignedToAtLeastOneGivenRole(
            $this->current_user->getId(),
            $this->settings->getAllowedGlobalRoleIds()
        );
    }

    public function isCurrentUserRestrictedToAnyGroup(): bool
    {
        $groups = $this->group_repository->getGroupsOfUser($this->current_user->getId(), true);

        foreach ($groups as $group) {
            if ($group->isRestrictedToMembers() || $group->isRestrictedToRoles()) {
                return true;
            }
        }

        return false;
    }

    public function isCurrentUserRestrictedToRoles(): bool
    {
        return false;
    }

    public function isCurrentUserAdministrator(): bool
    {
        return $this->isUserAdministrator($this->current_user->getId());
    }

    /**
     * Returns true if the current user has ILIAS write access, which can be used for e.g.
     * visivility of certain page elements like tabs.
     */
    public function hasCurrentUserWriteAccess(): bool
    {
        return $this->system->checkAccessOfUser(
            $this->current_user->getId(),
            'write',
            $this->getRequestedReferenceId($this->get_request) ?? 1
        );
    }

    protected function isUserAdministrator(int $user_id): bool
    {
        return $this->review->isAssigned($user_id, SYSTEM_ROLE_ID);
    }

    protected function getRefinery(): Refinery
    {
        return $this->refinery;
    }
}

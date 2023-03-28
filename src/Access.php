<?php

namespace srag\Plugins\UserTakeOver;

use Closure;
use ILIAS\GlobalScreen\Helper\BasicAccessCheckClosures;
use ilUserTakeOverARConfig;
use srag\Plugins\UserTakeOver\Group\IGroupMemberRepository;
use srag\Plugins\UserTakeOver\Group\IGroupRepository;

/**
 * Class Access
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Access
{
    /**
     * @var IGroupMemberRepository
     */
    protected $member_repository;
    /**
     * @var IGroupRepository
     */
    protected $group_repository;
    /**
     * @var int
     */
    protected $current_user_id;
    /**
     * @var int
     */
    protected $original_user_id;
    /**
     * @var BasicAccessCheckClosures
     */
    protected $basics;
    /**
     * @var \ILIAS\DI\RBACServices
     */
    protected $rbac;
    /**
     * @var ilUserTakeOverARConfig[]
     */
    protected $config;

    public function __construct(int $current_user_id, int $original_user_id)
    {
        global $DIC;

        $this->current_user_id = $current_user_id;
        $this->original_user_id = $original_user_id;
        $this->member_repository = new \ilUTOGroupMemberRepository($DIC->database());
        $this->group_repository = new \ilUTOGroupRepository($DIC->database());
        $this->basics = BasicAccessCheckClosures::getInstance();
        $this->config = \ilUserTakeOverARConfig::get();
        $this->rbac = $DIC->rbac();
    }

    public function isTakeOverRunning(?Closure $additional = null): Closure
    {
        return $this->getClosureWithOptinalClosure(function (): bool {
            $handler = new Handler();
            return $handler->isImpersonated();
        }, $additional);
    }

    public function isUserTakeOverAvailableForUser(?Closure $additional = null): Closure
    {
        $closure = function () use ($additional): bool {
            return ($this->hasUserAccessToUserSearch($additional)() || $this->isUserAssignedToGroup($additional)(
                ) || $this->isTakeOverRunning($additional)());
        };

        return $this->getClosureWithOptinalClosure(
            $this->getClosureWithOptinalClosure($closure, $additional),
            $this->basics->isUserLoggedIn()
        );
    }

    public function hasUserAccessToUserSearch(?Closure $additional = null): Closure
    {
        return $this->getClosureWithOptinalClosure(function () use ($additional): bool {
            return (($this->basics->hasAdministrationAccess($additional)() ||
                    $this->isUserAssignedToConfiguredRole($additional)()) &&
                !$this->isTakeOverRunning($additional)()
            );
        }, $additional);
    }

    public function isUserAllowedToImpersonate(?Closure $additional = null): Closure
    {
        $closure = function () use ($additional): bool {
            return ($this->hasUserAccessToUserSearch($additional)() || $this->isUserAssignedToGroup($additional)());
        };
        return $this->getClosureWithOptinalClosure(
            $this->getClosureWithOptinalClosure($this->basics->isUserLoggedIn(), $closure), $additional
        );
    }

    public function isUserAssignedToSameGroup(int $user_id_to_impersonate, ?Closure $additional = null): Closure
    {
        return $this->getClosureWithOptinalClosure(function () use ($user_id_to_impersonate): bool {
            static $is_in_group;
            if (isset($is_in_group)) {
                return $is_in_group;
            }

            $groups = $this->group_repository->getGroupsOfUser($user_id_to_impersonate);

            $is_in_group = false;
            foreach ($groups as $group) {
                $other_members = $this->member_repository->getGroupMembersOf($group);
                if (in_array($user_id_to_impersonate, $other_members, true)) {
                    $is_in_group = true;
                    break;
                }
            }

            return $is_in_group;
        }, $additional);
    }

    public function isUserToImpersonateAssignedToAllowedRole(
        int $user_id_to_impersonate,
        ?Closure $additional = null
    ): Closure {
        return $this->getClosureWithOptinalClosure(function () use ($user_id_to_impersonate): bool {
            $groups = $this->group_repository->getGroupsOfUser($this->current_user_id);
            foreach ($groups as $group) {
                if (!$group->isRestrictedToRoles()) {
                    continue;
                }
                if ($this->rbac->review()->isAssignedToAtLeastOneGivenRole(
                    $user_id_to_impersonate,
                    $group->getAllowedRoles()
                )) {
                    return true;
                }
            }

            return false;
        }, $additional);
    }

    public function isUserAssignedToGroup(?Closure $additional = null): Closure
    {
        return $this->getClosureWithOptinalClosure(function (): bool {
            static $is_in_group;
            if (isset($is_in_group)) {
                return $is_in_group;
            }

            $groups_of_user = $this->group_repository->getGroupsOfUser($this->current_user_id);

            $is_in_group = (0 < count($groups_of_user));

            return $is_in_group;
        }, $additional);
    }

    public function isUserAssignedToConfiguredRole(?Closure $additional = null): Closure
    {
        return $this->getClosureWithOptinalClosure(function (): bool {
            $identifier = ilUserTakeOverARConfig::CNF_ID_GLOBAL_ROLES;
            if (!isset($this->config[$identifier])) {
                return false;
            }

            return $this->rbac->review()->isAssignedToAtLeastOneGivenRole(
                $this->current_user_id,
                $this->config[$identifier]->getValue()
            );
        }, $additional);
    }

    public function canUserBeImpersonated(int $user_id_to_impersonate, ?Closure $additional = null): Closure
    {
        return $this->getClosureWithOptinalClosure(function () use ($user_id_to_impersonate): bool {
            $can = true;
            if ($this->isUserAssignedToGroup()()) {
                if ($this->isUserRestrictedToGroupMembers()()) {
                    $can = $this->isUserAssignedToSameGroup($user_id_to_impersonate)();
                }

                if ($this->isUserRestrictedToGlobalRoles()()) {
                    $can = $can && $this->isUserToImpersonateAssignedToAllowedRole($user_id_to_impersonate)();
                }
            }

            if (!$this->canAdminsBeImpersonated()) {
                $can = $can && !$this->rbac->review()->isAssignedToAtLeastOneGivenRole(
                    $user_id_to_impersonate,
                    [SYSTEM_ROLE_ID]
                );
            }

            return $can;
        }, $additional);
    }

    public function isUserRestrictedToGroupMembers(?Closure $additional = null): Closure
    {
        return $this->getClosureWithOptinalClosure(function (): bool {
            static $is_restricted;
            if (isset($is_restricted)) {
                return $is_restricted;
            }

            $is_restricted = false;
            foreach ($this->group_repository->getGroupsOfUser($this->current_user_id) as $group) {
                if ($group->isRestrictedToMembers()) {
                    $is_restricted = true;
                    break;
                }
            }

            return $is_restricted;
        }, $additional);
    }

    public function isUserRestrictedToGlobalRoles(?Closure $additional = null): Closure
    {
        return $this->getClosureWithOptinalClosure(function (): bool {
            static $is_restricted;
            if (isset($is_restricted)) {
                return $is_restricted;
            }

            $is_restricted = false;
            foreach ($this->group_repository->getGroupsOfUser($this->current_user_id) as $group) {
                if ($group->isRestrictedToRoles()) {
                    $is_restricted = true;
                    break;
                }
            }

            return $is_restricted;
        }, $additional);
    }

    private function checkClosureForBoolReturnValue(Closure $c): bool
    {
        try {
            $r = new \ReflectionFunction($c);
        } catch (\Throwable $e) {
            return false;
        }

        return $r->hasReturnType() && $r->getReturnType()->isBuiltin();
    }

    private function getClosureWithOptinalClosure(Closure $closure, ?Closure $additional = null): Closure
    {
        if ($additional instanceof Closure && $this->checkClosureForBoolReturnValue($additional)) {
            return static function () use ($closure, $additional): bool {
                return $additional() && $closure();
            };
        }

        return $closure;
    }

    private function canAdminsBeImpersonated(): bool
    {
        if (isset($this->config[ilUserTakeOverARConfig::CNF_ALLOW_IMPERSONATE_ADMINS])) {
            return '1' === $this->config[ilUserTakeOverARConfig::CNF_ALLOW_IMPERSONATE_ADMINS]->getValue();
        }

        return false;
    }
}

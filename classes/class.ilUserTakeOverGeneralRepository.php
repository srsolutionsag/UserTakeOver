<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\IGeneralRepository;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverGeneralRepository implements IGeneralRepository
{
    public function __construct(
        protected ilRbacReview $rbac,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAvailableGlobalRoles(): array
    {
        $role_options = [];
        foreach ($this->rbac->getRolesByFilter(ilRbacReview::FILTER_ALL_GLOBAL) as $role_data) {
            $role_id = (int) $role_data['obj_id'];
            // the administrator role can be ignored, as this
            // role should always be able to do everything.
            if ((int) SYSTEM_ROLE_ID !== $role_id) {
                $role_title = ilObjRole::_getTranslation($role_data['title']);

                // map the role-title to its role id associatively.
                $role_options[$role_id] = $role_title;
            }
        }

        return $role_options;
    }

    /**
     * @inheritDoc
     */
    public function findUsers(string $term): array
    {
        $query = new ilUserQuery();
        $query->setTextFilter($term);
        $results = $query->query();

        $users = [];
        foreach ($results['set'] as $user_data) {
            $beautified_name = "{$user_data['login']} ({$user_data['firstname']} {$user_data['lastname']})";
            $users[] = [
                'value' => $user_data['usr_id'],
                'searchBy' => $beautified_name,
                'display' => $beautified_name,
            ];
        }

        return $users;
    }

    /**
     * @inheritDoc
     */
    public function getUser(int $user_id): \ilObjUser
    {
        if (!ilObjUser::_exists($user_id)) {
            $user_id = ANONYMOUS_USER_ID;
        }

        return new ilObjUser($user_id);
    }
}

<?php

namespace srag\Plugins\UserTakeOver;

use srag\Plugins\UserTakeOver\UI\Form\AbstractFormBuilder;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IGeneralRepository
{
    /**
     * @return array<int, string> role-id => translation pairs
     */
    public function getAvailableGlobalRoles(): array;

    /**
     * Will return an array of users that match the search term, following the
     * format for @see AbstractFormBuilder::getTagInputAutoCompleteBinder()
     */
    public function findUsers(string $term): array;

    /**
     * Returns the anonymous user, if the user is not found.
     */
    public function getUser(int $user_id): \ilObjUser;
}

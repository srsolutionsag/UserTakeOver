<?php

declare(strict_types=1);

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
trait ilUserTakeOverDisplayName
{
    /**
     * Returns a name like "tfuhrer (Thibeau Fuhrer)".
     */
    protected function getDisplayName(ilObjUser $user): string
    {
        return "{$user->getLogin()} ({$user->getFirstname()} {$user->getLastname()})";
    }
}

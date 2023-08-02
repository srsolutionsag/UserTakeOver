<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\IRequestParameters;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
trait ilUserTakeOverImpersonationTarget
{
    /**
     * Returns a target url to impersonate the given user, which will be handled by
     * @see ilUserTakeOverUIHookGUI::gotoHook()
     */
    protected function getImpersonateTarget(?ilObjUser $target_user): string
    {
        return 'goto.php' .
            '?' . IRequestParameters::TARGET . '=' . ilUserTakeOverPlugin::PLUGIN_ID .
            '&' . IRequestParameters::USER_ID . '=' . $target_user?->getId();
    }
}

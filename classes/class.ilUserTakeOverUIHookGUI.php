<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . "/../vendor/autoload.php";

use srag\DIC\UserTakeOver\DICTrait;
use srag\Plugins\UserTakeOver\Handler;
use srag\Plugins\UserTakeOver\Redirect;

/**
 * Class ilUserTakeOverUIHookGUI
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id$
 * @ingroup ServicesUIComponent
 */
class ilUserTakeOverUIHookGUI extends ilUIHookPluginGUI
{

    use DICTrait;

    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

    /**
     * @var int
     */
    protected static $num = 0;

    /**
     * @param string $a_comp
     * @param string $a_part
     * @param array  $a_par
     * @return array
     */
    public function getHTML($a_comp, $a_part, $a_par = [])
    {
        return ['mode' => ilUIHookPluginGUI::KEEP, "html" => ''];
    }

    public function gotoHook()
    {
        if (preg_match("/usr_takeover_(.*)/uim", filter_input(INPUT_GET, 'target'), $matches)) {
            $handler = new Handler(self::dic()->ctrl());
            $handler->setOriginalUserId(self::dic()->user()->getId());
            $handler->setImpersonateUserId((int) $matches[1]);
            $handler->impersonateUser(new class implements Redirect {

                use DICTrait;

                const PLUGIN_CLASS_NAME = \ilUserTakeOverPlugin::class;

                public function performRedirect(int $user_id) : void
                {
                    $original_login     = self::dic()->user()->getLogin();
                    $impersonated_login = ilObjUser::_lookupLogin($user_id);
                    self::dic()->logger()->root()->log("UserTakeOver: {$original_login} has taken over the user view of {$impersonated_login}");
                    ilUtil::sendSuccess(self::plugin()->translate('user_taker_over_success', "", [$impersonated_login]), true);
                    ilUtil::redirect('ilias.php?baseClass=' . ilDashboardGUI::class . '&cmd=jumpToSelectedItems');
                }
            });
            ilUtil::sendSuccess(self::plugin()->translate('no_permission', ""), true);
            ilUtil::redirect('#');
        }
        if (preg_match("/usr_takeback/uim", filter_input(INPUT_GET, 'target'), $matches)) {
            $handler = new Handler();
            $handler->switchBack(new class implements Redirect {
                use DICTrait;

                const PLUGIN_CLASS_NAME = \ilUserTakeOverPlugin::class;

                public function performRedirect(int $user_id) : void
                {
                    $impersonated_login = self::dic()->user()->getLogin();
                    $original_login     = ilObjUser::_lookupLogin($user_id);
                    self::dic()->logger()->root()->log("UserTakeOver: {$impersonated_login} switched back to {$original_login}");
                    ilUtil::sendSuccess(self::plugin()->translate('user_taker_back_success', "", [$original_login]), true);
                    ilUtil::redirect('ilias.php?baseClass=' . ilDashboardGUI::class . '&cmd=jumpToSelectedItems');
                }
            });
        }
    }

}

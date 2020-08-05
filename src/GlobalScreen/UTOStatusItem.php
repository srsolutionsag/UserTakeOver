<?php

namespace srag\Plugins\UserTakeOver\GlobalScreen;

use ILIAS\UI\Component\Legacy\Legacy;
use srag\DIC\UserTakeOver\DICTrait;
use srag\Plugins\UserTakeOver\Handler;

/**
 * Class UTOStatusItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class UTOStatusItem extends LegacySubItem
{
    use DICTrait;

    const PLUGIN_CLASS_NAME = \ilUserTakeOverPlugin::class;

    public function getContent() : Legacy
    {
        global $DIC;

        $handler          = new Handler();
        $original_user_id = $handler->getLoadedOriginalId();

        $tpl = new \ilTemplate('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/templates/tpl.uto_status.html', true, true);
        $tpl->setVariable("ORIGINAL_USER", self::plugin()->translate('status_original_login'));
        $tpl->setVariable("IMPERSONATED_USER", self::plugin()->translate('status_impersonated_login'));
        $tpl->setVariable("ORIGINAL_USER_LOGIN", \ilObjUser::_lookupLogin($original_user_id));
        $tpl->setVariable("IMPERSONATED_USER_LOGIN", \ilObjUser::_lookupLogin($DIC->user()->getId()));

        return $DIC->ui()->factory()->legacy($tpl->get());
    }

}

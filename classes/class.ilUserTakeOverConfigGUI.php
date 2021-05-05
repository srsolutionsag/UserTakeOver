<?php
require_once __DIR__ . "/../vendor/autoload.php";

use srag\DIC\UserTakeOver\DICTrait;

/**
 * ilUserDefaultsConfigGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @author            Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @version           2.0.00
 *
 * @ilCtrl_IsCalledBy ilUserTakeOverConfigGUI: ilUIPluginRouterGUI,ilObjComponentSettingsGUI
 * @ilCtrl_Calls      ilUserTakeOverConfigGUI: ilUserTakeOverMainGUI
 */
class ilUserTakeOverConfigGUI extends ilPluginConfigGUI
{
    use DICTrait;

    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

    const CMD_CONFIGURE = 'configure';

    /**
     * @inheritDoc
     */
    public function performCommand($cmd)
    {
        $next_class = self::dic()->ctrl()->getNextClass($this);
        switch ($next_class) {
            case strtolower(ilUserTakeOverMainGUI::class):
                self::dic()->ctrl()->forwardCommand(new ilUserTakeOverMainGUI());
                break;
            default:
                self::dic()->ctrl()->redirectByClass(
                    [ilUserTakeOverMainGUI::class, ilUserTakeOverSettingsGUI::class]
                );
                break;
        }
    }
}


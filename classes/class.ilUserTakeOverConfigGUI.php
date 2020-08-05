<?php
require_once __DIR__ . "/../vendor/autoload.php";

use srag\DIC\UserTakeOver\DICTrait;

/**
 * ilUserDefaultsConfigGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           1.0.00
 * @ilCtrl_IsCalledBy ilUserTakeOverConfigGUI: ilUIPluginRouterGUI,ilObjComponentSettingsGUI
 * @ilCtrl_Calls      ilUserTakeOverConfigGUI: ilUserTakeOverGroupsGUI
 */
class ilUserTakeOverConfigGUI extends ilPluginConfigGUI
{

    use DICTrait;

    const CMD_CONFIGURE = 'configure';
    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

    public function executeCommand()
    {

        self::dic()->tabs()->clearTargets();
        $nextClass = self::dic()->ctrl()->getNextClass();
        switch ($nextClass) {
            case strtolower(ilUserTakeOverGroupsGUI::class):
                $ilUserTakeOverGroupsGUI = new ilUserTakeOverGroupsGUI();
                self::dic()->ctrl()->forwardCommand($ilUserTakeOverGroupsGUI);
                break;
            default;
                $this->performCommand(self::dic()->ctrl()->getCmdClass());
                break;
        }
    }

    public function performCommand($cmd)
    {
        switch (self::dic()->ctrl()->getCmd()) {
            case self::CMD_CONFIGURE:
                self::dic()->ctrl()->saveParameterByClass(ilUserTakeOverGroupsGUI::class, 'admin_mode');
                self::dic()->ctrl()->saveParameterByClass(ilUserTakeOverGroupsGUI::class, 'ctype');
                self::dic()->ctrl()->saveParameterByClass(ilUserTakeOverGroupsGUI::class, 'cname');
                self::dic()->ctrl()->saveParameterByClass(ilUserTakeOverGroupsGUI::class, 'slot_id');
                self::dic()->ctrl()->saveParameterByClass(ilUserTakeOverGroupsGUI::class, 'pname');
                self::dic()->ctrl()->redirectByClass(ilUserTakeOverGroupsGUI::class, ilUserTakeOverGroupsGUI::CMD_STANDARD);
                break;
            default:
                throw new ilException("command not defined.");
                break;
        }
    }
}


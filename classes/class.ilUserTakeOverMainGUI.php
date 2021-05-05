<?php

use srag\DIC\UserTakeOver\DICTrait;
use srag\CustomInputGUIs\UserTakeOver\MultiSelectSearchNewInputGUI\UsersAjaxAutoCompleteCtrl;

/**
 * Class ilUserTakeOverMainGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @author            Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilUserTakeOverMainGUI: ilUIPluginRouterGUI
 * @ilCtrl_IsCalledBy ilUserTakeOverMainGUI: ilUserTakeOverConfigGUI
 * @ilCtrl_Calls      ilUserTakeOverMainGUI: ilUserTakeOverGroupsGUI
 * @ilCtrl_Calls      ilUserTakeOverMainGUI: ilUserTakeOverMembersGUI
 * @ilCtrl_Calls      ilUserTakeOverMainGUI: ilUserTakeOverSettingsGUI
 */
class ilUserTakeOverMainGUI
{
    use DICTrait;

    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

    /**
     * commands
     */
    const CMD_SEARCH = 'search';
    const CMD_INDEX  = 'index';

    /**
     * tab id's (also used as lang vars)
     */
    const TAB_SETTINGS  = 'configuration';
    const TAB_GROUPS    = 'group';

    /**
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $this->initPage();
        $next_class = self::dic()->ctrl()->getNextClass($this);
        $cmd = self::dic()->ctrl()->getCmd(self::CMD_INDEX);

        switch ($next_class) {
            case strtolower(ilUserTakeOverSettingsGUI::class):
                self::dic()->tabs()->activateTab(self::TAB_SETTINGS);
                self::dic()->ctrl()->forwardCommand(new ilUserTakeOverSettingsGUI());
                break;
            case strtolower(ilUserTakeOverGroupsGUI::class):
                self::dic()->tabs()->activateTab(self::TAB_GROUPS);
                self::dic()->ctrl()->forwardCommand(new ilUserTakeOverGroupsGUI());
                break;
            case strtolower(ilUserTakeOverMembersGUI::class):
                self::dic()->tabs()->activateTab(self::TAB_GROUPS);
                self::dic()->ctrl()->forwardCommand(new ilUserTakeOverMembersGUI());
                break;
            case strtolower(self::class):
            default:
                $this->performCommand($cmd);
                break;
        }
    }

    public function performCommand(string $cmd)
    {
        switch ($cmd) {
            case self::CMD_SEARCH:
                $this->{$cmd}();
                break;
            default:
                throw new ilException('command not found');
                break;
        }
    }

    /**
     * sets up the configuration page.
     */
    private function initPage()
    {
        self::dic()->mainTemplate()->setDescription("");
        self::dic()->mainTemplate()->setTitle(
            self::dic()->language()->txt("cmps_plugin") . ": " . ilUserTakeOverPlugin::PLUGIN_CLASS_NAME
        );

        self::dic()->tabs()->addTab(
            self::TAB_GROUPS,
            self::plugin()->translate(self::TAB_GROUPS),
            self::dic()->ctrl()->getLinkTargetByClass(
                [ilUserTakeOverMainGUI::class, ilUserTakeOverGroupsGUI::class],
                ilUserTakeOverGroupsGUI::CMD_STANDARD
            )
        );

        self::dic()->tabs()->addTab(
            self::TAB_SETTINGS,
            self::plugin()->translate(self::TAB_SETTINGS),
            self::dic()->ctrl()->getLinkTargetByClass(
                [ilUserTakeOverMainGUI::class, ilUserTakeOverSettingsGUI::class],
                ilUserTakeOverSettingsGUI::CMD_STANDARD
            )
        );
    }

    private function search()
    {
        $term = "%" . self::dic()->http()->request()->getQueryParams()['q'] . "%";

        $q = "SELECT usr_id, firstname, lastname, login FROM usr_data 
                WHERE 
                firstname LIKE %s OR 
                lastname LIKE %s OR 
                email LIKE %s OR 
                login LIKE %s";

        $r    = self::dic()->database()->queryF($q, ['text', 'text', 'text', 'text'], [$term, $term, $term, $term]);
        $json = [];
        while ($d = self::dic()->database()->fetchObject($r)) {
//            $json[$d->usr_id] = "{$d->firstname} {$d->lastname}";
            $json[] = $d;
        }
        $result = [
            'title' => 'Results',
            'data'  => $json
        ];
        echo json_encode($json);
        exit;
    }
}

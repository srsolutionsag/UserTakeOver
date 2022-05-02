<?php

use srag\CustomInputGUIs\UserTakeOver\MultiSelectSearchNewInputGUI\UsersAjaxAutoCompleteCtrl;
use srag\Plugins\UserTakeOver\Access;

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
 * @ilCtrl_Calls      ilUserTakeOverMainGUI: UsersAjaxAutoCompleteCtrl
 */
class ilUserTakeOverMainGUI
{
    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;
    
    /**
     * commands
     */
    public const CMD_SEARCH = 'search';
    public const CMD_INDEX = 'index';
    
    /**
     * tab id's (also used as lang vars)
     */
    public const TAB_SETTINGS = 'configuration';
    public const TAB_GROUPS = 'group';
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var ilGlobalPageTemplate
     */
    protected $main_tpl;
    /**
     * @var ilDBInterface
     */
    protected $db;
    /**
     * @var \Psr\Http\Message\RequestInterface|\Psr\Http\Message\ServerRequestInterface
     */
    protected $request;
    /**
     * @var ilUserTakeOverPlugin
     */
    protected $plugin;
    /**
     * @var ilObjUserTakeOverAccess
     */
    protected $access;
    /**
     * @var int
     */
    protected $user_id;
    /**
     * @var Access
     */
    protected $access_checks;
    
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->db = $DIC->database();
        $this->request = $DIC->http()->request();
        $this->plugin = ilUserTakeOverPlugin::getInstance();
        $this->user_id = $DIC->user()->getId();
        $this->access_checks = new Access($this->user_id, $this->user_id);
    }
    
    /**
     * @throws ilCtrlException
     */
    public function executeCommand() : void
    {
        $this->initPage();
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd(self::CMD_INDEX);
        
        switch ($next_class) {
            case strtolower(ilUserTakeOverSettingsGUI::class):
                $this->tabs->activateTab(self::TAB_SETTINGS);
                $this->ctrl->forwardCommand(new ilUserTakeOverSettingsGUI());
                break;
            case strtolower(ilUserTakeOverGroupsGUI::class):
                $this->tabs->activateTab(self::TAB_GROUPS);
                $this->ctrl->forwardCommand(new ilUserTakeOverGroupsGUI());
                break;
            case strtolower(ilUserTakeOverMembersGUI::class):
                $this->tabs->activateTab(self::TAB_GROUPS);
                $this->ctrl->forwardCommand(new ilUserTakeOverMembersGUI());
                break;
            case strtolower(UsersAjaxAutoCompleteCtrl::class):
                $this->ctrl->forwardCommand(new UsersAjaxAutoCompleteCtrl());
                break;
            case strtolower(self::class):
            default:
                $this->performCommand($cmd);
                break;
        }
    }
    
    public function performCommand(string $cmd) : void
    {
        switch ($cmd) {
            case self::CMD_SEARCH:
                if ($this->access_checks->isUserAllowedToImpersonate()) {
                    $this->{$cmd}();
                }
                break;
            default:
                throw new ilException('command not found');
        }
    }
    
    /**
     * sets up the configuration page.
     */
    private function initPage() : void
    {
        $this->main_tpl->setDescription("");
        $this->main_tpl->setTitle(
            $this->plugin->txt("cmps_plugin") . ": " . ilUserTakeOverPlugin::PLUGIN_CLASS_NAME
        );
        
        $this->tabs->addTab(
            self::TAB_SETTINGS,
            $this->plugin->txt('tab_' . self::TAB_SETTINGS),
            $this->ctrl->getLinkTargetByClass(
                [ilUserTakeOverMainGUI::class, ilUserTakeOverSettingsGUI::class],
                ilUserTakeOverSettingsGUI::CMD_STANDARD
            )
        );
        
        $this->tabs->addTab(
            self::TAB_GROUPS,
            $this->plugin->txt('tab_' . self::TAB_GROUPS),
            $this->ctrl->getLinkTargetByClass(
                [ilUserTakeOverMainGUI::class, ilUserTakeOverGroupsGUI::class],
                ilUserTakeOverGroupsGUI::CMD_STANDARD
            )
        );
    }
    
    private function search()
    {
        $term = "%" . $this->request->getQueryParams()['q'] . "%";
        
        $q = "SELECT usr_id, firstname, lastname, login FROM usr_data
                WHERE
                firstname LIKE %s OR
                lastname LIKE %s OR
                email LIKE %s OR
                login LIKE %s";
        
        $r = $this->db->queryF($q, ['text', 'text', 'text', 'text'], [$term, $term, $term, $term]);
        $json = [];
        while ($d = $this->db->fetchObject($r)) {
            $json[] = $d;
        }
        echo json_encode($json);
        exit;
    }
}

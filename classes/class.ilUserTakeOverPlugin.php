<?php

require_once __DIR__ . "/../vendor/autoload.php";

use srag\RemovePluginDataConfirm\UserTakeOver\PluginUninstallTrait;

/**
 * ilUserTakeOverPlugin
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilUserTakeOverPlugin extends ilUserInterfaceHookPlugin
{

    use PluginUninstallTrait;
    const PLUGIN_ID = 'usrto';
    const PLUGIN_NAME = 'UserTakeOver';
    const PLUGIN_CLASS_NAME = self::class;
    const REMOVE_PLUGIN_DATA_CONFIRM_CLASS_NAME = ilUserTakeOverRemoveDataConfirm::class;
    /**
     * @var ilUserTakeOverPlugin
     */
    protected static $instance;


    /**
     * @return ilUserTakeOverPlugin
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * @return string
     */
    public function getPluginName()
    {
        return self::PLUGIN_NAME;
    }


    /**
     * @inheritdoc
     */
    protected function deleteData()/*: void*/
    {
        self::dic()->database()->dropTable(ilUserTakeOverConfig::TABLE_NAME, false);
        self::dic()->database()->dropTable('ui_uihk_usrto_config', false);
        self::dic()->database()->dropTable(usrtoGroup::TABLE_NAME, false);
        self::dic()->database()->dropTable(usrtoMember::TABLE_NAME, false);
    }
}

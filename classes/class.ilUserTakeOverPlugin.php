<?php

require_once __DIR__ . "/../vendor/autoload.php";

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationPluginProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use srag\Plugins\UserTakeOver\GlobalScreen\MetaBarProvider;
use srag\Plugins\UserTakeOver\UI\SlateLoaderDetector;
use srag\RemovePluginDataConfirm\UserTakeOver\PluginUninstallTrait;
use srag\Plugins\UserTakeOver\GlobalScreen\ModificationProvider;

/**
 * ilUserTakeOverPlugin
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id$
 */
class ilUserTakeOverPlugin extends ilUserInterfaceHookPlugin
{

    use PluginUninstallTrait;

    const PLUGIN_ID = 'usrto';
    const PLUGIN_NAME = 'UserTakeOver';
    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;
    const REMOVE_PLUGIN_DATA_CONFIRM_CLASS_NAME = ilUserTakeOverRemoveDataConfirm::class;
    const PLUGIN_BASE = './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver';
    /**
     * @var ilUserTakeOverPlugin
     */
    protected static $instance;

    public function __construct()
    {
        global $DIC;
        parent::__construct();

        // global screen service might not be available yet,
        // if this class is called from the setup CLI.
        if ($DIC->offsetExists('global_screen')) {
            $DIC->globalScreen()->layout()->meta()->addJs(self::PLUGIN_BASE . '/node_modules/@varvet/tiny-autocomplete/src/tiny-autocomplete.js', false, 3);
            $DIC->globalScreen()->layout()->meta()->addJs(self::PLUGIN_BASE . '/js/dist/main.js', false, 3);

            // provider also depend on global screen service.
            $this->provider_collection->setMetaBarProvider(new MetaBarProvider($DIC, $this));
            $this->provider_collection->setModificationProvider(new ModificationProvider($DIC, $this));
        }
    }

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

    public function exchangeUIRendererAfterInitialization(\ILIAS\DI\Container $dic) : Closure
    {
        return SlateLoaderDetector::exchange();
    }

    /**
     * @inheritdoc
     */
    protected function deleteData()/*: void*/
    {
        self::dic()->database()->dropTable(ilUserTakeOverARConfig::TABLE_NAME, false);
        self::dic()->database()->dropTable('ui_uihk_usrto_config', false);
        self::dic()->database()->dropTable(usrtoGroup::TABLE_NAME, false);
        self::dic()->database()->dropTable(usrtoMember::TABLE_NAME, false);
    }
}

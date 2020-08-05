<?php

require_once __DIR__ . "/../vendor/autoload.php";

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationPluginProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use srag\Plugins\UserTakeOver\GlobalScreen\MetaBarProvider;
use srag\Plugins\UserTakeOver\UI\SlateLoaderDetector;
use srag\RemovePluginDataConfirm\UserTakeOver\PluginUninstallTrait;

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
    const PLUGIN_CLASS_NAME = self::class;
    const REMOVE_PLUGIN_DATA_CONFIRM_CLASS_NAME = ilUserTakeOverRemoveDataConfirm::class;
    /**
     * @var ilUserTakeOverPlugin
     */
    protected static $instance;

    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $DIC->globalScreen()->layout()->meta()->addJs('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/node_modules/@varvet/tiny-autocomplete/src/tiny-autocomplete.js', false, 3);


//        $DIC->globalScreen()->layout()->meta()->addJs('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/node_modules/autocomplete-js/dist/autocomplete.js', false, 3);
        $DIC->globalScreen()->layout()->meta()->addJs('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/js/dist/main.js', false, 3);

        $this->provider_collection->setMetaBarProvider(new MetaBarProvider($DIC, $this));
        $this->provider_collection->setModificationProvider(new class($DIC, $this) extends AbstractModificationPluginProvider {
            public function isInterestedInContexts() : ContextCollection
            {
                return $this->context_collection->main()->internal();
            }
        });
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
        self::dic()->database()->dropTable(ilUserTakeOverConfig::TABLE_NAME, false);
        self::dic()->database()->dropTable('ui_uihk_usrto_config', false);
        self::dic()->database()->dropTable(usrtoGroup::TABLE_NAME, false);
        self::dic()->database()->dropTable(usrtoMember::TABLE_NAME, false);
    }
}

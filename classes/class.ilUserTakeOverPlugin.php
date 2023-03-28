<?php

require_once __DIR__ . "/../vendor/autoload.php";

use srag\Plugins\UserTakeOver\GlobalScreen\ModificationProvider;
use srag\Plugins\UserTakeOver\GlobalScreen\MetaBarProvider;
use srag\Plugins\UserTakeOver\ITranslator;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverPlugin extends ilUserInterfaceHookPlugin implements ITranslator
{
    public const PLUGIN_CLASS_NAME = self::class;
    public const PLUGIN_BASE = './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver';
    public const PLUGIN_NAME = 'UserTakeOver';
    public const PLUGIN_ID = 'usrto';
    /**
     * @var self|null
     */
    protected static $instance;

    /**
     * @var ilDBInterface
     */
    protected $database;

    public function __construct()
    {
        global $DIC;
        parent::__construct();

        // global screen service might not be available yet,
        // if this class is called from the setup CLI.
        if ($this->isActive() && $DIC->offsetExists('global_screen')) {
            $DIC->globalScreen()->layout()->meta()->addJs(
                self::PLUGIN_BASE . '/node_modules/@varvet/tiny-autocomplete/src/tiny-autocomplete.js',
                false,
                3
            );
            $DIC->globalScreen()->layout()->meta()->addJs(self::PLUGIN_BASE . '/js/dist/main.js', false, 3);

            // provider also depend on global screen service.
            $this->provider_collection->setMetaBarProvider(new MetaBarProvider($DIC, $this));
            $this->provider_collection->setModificationProvider(new ModificationProvider($DIC, $this));
        }

        if ($DIC->offsetExists('ilDB')) {
            $this->database = $DIC->database();
        }

        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }

    /**
     * @inheritDoc
     */
    protected function afterUninstall(): void
    {
        // normal groups
        $this->database->dropTable('ui_uihk_usrto_grp');
        $this->database->dropSequence('ui_uihk_usrto_grp');
        // role based groups
        $this->database->dropTable('ui_uihk_usrto_rb_grp');
        // group members
        $this->database->dropTable('ui_uihk_usrto_member');
        $this->database->dropSequence('ui_uihk_usrto_member');
        // config
        $this->database->dropTable('usrto_config');
    }
}

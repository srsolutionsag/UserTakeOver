<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use srag\Plugins\UserTakeOver\GlobalScreen\MetaBarProvider;
use srag\Plugins\UserTakeOver\UI\Component\Renderer;
use srag\Plugins\UserTakeOver\ITranslator;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverPlugin extends ilUserInterfaceHookPlugin implements ITranslator
{
    public const PLUGIN_BASE = './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver';
    public const PLUGIN_ID = 'usrto';

    public function __construct(
        ilDBInterface $db,
        ilComponentRepositoryWrite $component_repository,
        string $id
    ) {
        global $DIC;
        parent::__construct($db, $component_repository, $id);

        if ($DIC->offsetExists('global_screen')) {
            $meta_content = $DIC->globalScreen()->layout()->meta();
            $meta_content->addJs(self::PLUGIN_BASE . '/node_modules/@varvet/tiny-autocomplete/src/tiny-autocomplete.js', false, 3);
            $meta_content->addJs(self::PLUGIN_BASE . '/js/dist/main.js', false, 1);
            $meta_content->addOnloadCode("il.Plugins.UserTakeOver.init('.uto-search')");

            $this->provider_collection->setMetaBarProvider(new MetaBarProvider($DIC, $this));
        }
    }

    public function exchangeUIRendererAfterInitialization(\ILIAS\DI\Container $dic): Closure
    {
        $default_renderer = $dic->raw('ui.renderer');
        return static function () use ($dic, $default_renderer) {
            return new Renderer($default_renderer($dic));
        };
    }

    /**
     * @inheritDoc
     */
    protected function afterUninstall(): void
    {
        // groups
        $this->db->dropTable('ui_uihk_usrto_grp');
        $this->db->dropSequence('ui_uihk_usrto_grp');
        // group members
        $this->db->dropTable('ui_uihk_usrto_member');
        $this->db->dropSequence('ui_uihk_usrto_member');
        // config
        $this->db->dropTable('usrto_config');
    }
}

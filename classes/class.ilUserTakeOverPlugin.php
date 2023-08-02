<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use srag\Plugins\UserTakeOver\ITranslator;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverPlugin extends ilUserInterfaceHookPlugin implements ITranslator
{
    public const PLUGIN_ID = 'usrto';

    public function __construct(
        ilDBInterface $db,
        ilComponentRepositoryWrite $component_repository,
        string $id
    ) {
        global $DIC;
        parent::__construct($db, $component_repository, $id);

        if ($DIC->offsetExists('global_screen')) {
            $this->provider_collection->setMetaBarProvider(new ilUserTakeOverMetaBarProvider($DIC, $this));
        }
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

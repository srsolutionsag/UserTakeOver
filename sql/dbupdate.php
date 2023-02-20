<#1>
<?php
/**
 * This database step does not exist anymore. It created the table
 * ui_uihk_usrto_config, which is no longer required.
 */
?>
<#2>
<?php
/** @var $ilDB ilDBInterface */
if (!$ilDB->tableExists('ui_uihk_usrto_grp')) {
    $ilDB->createTable('ui_uihk_usrto_grp', [
        'id' => [
            'type' => 'integer',
            'notnull' => '1',
            'length' => '8',
        ],
        'title' => [
            'type' => 'text',
            'notnull' => '1',
            'length' => '254',
        ],
        'description' => [
            'type' => 'text',
            'notnull' => '1',
            'length' => '4000',
        ],
    ]);

    $ilDB->createSequence('ui_uihk_usrto_grp');

    $ilDB->addPrimaryKey('ui_uihk_usrto_grp', [
        'id',
    ]);
}
?>
<#3>
<?php
/** @var $ilDB ilDBInterface */
if (!$ilDB->tableExists('ui_uihk_usrto_member')) {
    $ilDB->createTable('ui_uihk_usrto_member', [
        'id' => [
            'type' => 'integer',
            'notnull' => '1',
            'length' => '8',
        ],
        'group_id' => [
            'type' => 'integer',
            'notnull' => '1',
            'length' => '8',
        ],
        'user_id' => [
            'type' => 'integer',
            'notnull' => '1',
            'length' => '8',
        ],
    ]);

    $ilDB->createSequence('ui_uihk_usrto_member');

    $ilDB->addPrimaryKey('ui_uihk_usrto_member', [
        'id',
    ]);
}
?>
<#4>
<?php
/** @var $ilDB ilDBInterface */
if ($ilDB->tableExists('ui_uihk_usrto_config')) {
    $results = $ilDB->fetchAll(
        $ilDB->query("SELECT * FROM ui_uihk_usrto_config")
    );

    $label = (new ilUserTakeOverPlugin())->txt('demo_group');

    foreach ($results as $query_result) {
        $group_id = (int) $ilDB->nextId('ui_uihk_usrto_grp');
        $ilDB->insert('ui_uihk_usrto_grp', [
            'id' => ['integer' => $group_id],
            'title' => ['text' => $label],
            'description' => ['text' => $label],
        ]);

        $user_ids = (array) json_decode($query_result['demo_group']);

        foreach ($user_ids as $user_id) {
            $ilDB->insert('ui_uihk_usrto_member', [
                'id' => ['integer' => (int) $ilDB->nextId('ui_uihk_usrto_member')],
                'group_id' => ['integer' => $group_id],
                'user_id' => ['integer' => $user_id],
            ]);
        }
    }
}
?>
<#5>
<?php
/**
 * This database step does not exist anymore. It created the table
 * ui_uihk_usrto_config_n, which is no longer required.
 */
?>
<#6>
<?php
/** @var $ilDB ilDBInterface */
if ($ilDB->tableExists('ui_uihk_usrto_config')) {
    $ilDB->dropTable('ui_uihk_usrto_config');
}
?>
<#7>
<?php
$fields = array();
if (!$ilDB->tableExists('usrto_config')) {
    $ilDB->createTable('usrto_config', [
        'identifier' => [
            'notnull' => '1',
            'type' => 'text',
            'length' => '250',
        ],
        'value' => [
            'notnull' => '1',
            'type' => 'text',
            'length' => '4000',
        ],
    ]);

    $ilDB->addPrimaryKey('usrto_config', [
        'identifier',
    ]);
}
?>
<#8>
<?php
if ($ilDB->tableExists('ui_uihk_usrto_grp')) {
    if (!$ilDB->tableColumnExists('ui_uihk_usrto_grp', 'restrict_to_members')) {
        $ilDB->addTableColumn('ui_uihk_usrto_grp', 'restrict_to_members', [
            'notnull' => '1',
            'length' => '1',
            'type' => 'integer',
        ]);

        $ilDB->manipulate("UPDATE ui_uihk_usrto_grp SET restrict_to_members = 1;");
    }

    if (!$ilDB->tableColumnExists('ui_uihk_usrto_grp', 'allowed_roles')) {
        $ilDB->addTableColumn('ui_uihk_usrto_grp', 'allowed_roles', [
            'notnull' => '0',
            'length' => '4000',
            'type' => 'text',
        ]);
    }
}
?>
<#9>
<?php
/** @var $ilDB ilDBInterface */
if ($ilDB->tableExists('ui_uihk_usrto_config_n')) {
    $ilDB->dropTable('ui_uihk_usrto_config_n');
}
?>

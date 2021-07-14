<#1>
<?php
//if(!\srag\DIC\UserTakeOver\DICStatic::dic()->database()->tableExists(ilUserTakeOverConfig::TABLE_NAME)) {
//	\ilUserTakeOverConfig::updateDB();
//}
?>
<#2>
<?php
if(!\srag\DIC\UserTakeOver\DICStatic::dic()->database()->tableExists(usrtoGroup::TABLE_NAME)) {
	usrtoGroup::updateDB();
}
?>
<#3>
<?php
if(!\srag\DIC\UserTakeOver\DICStatic::dic()->database()->tableExists(usrtoMember::TABLE_NAME)) {
	usrtoMember::updateDB();
}
?>
<#4>
<?php
/*
	migrates demo_group from config table to group table
*/
if (\srag\DIC\UserTakeOver\DICStatic::dic()->database()->tableExists('ui_uihk_usrto_config')) {

	$config_data_set = \srag\DIC\UserTakeOver\DICStatic::dic()->database()->query('SELECT * FROM ui_uihk_usrto_config');

	while ($data_rec = \srag\DIC\UserTakeOver\DICStatic::dic()->database()->fetchAssoc($config_data_set)) {
		$usrtoGroup = new usrtoGroup();
		$usrtoGroup->setTitle(\srag\DIC\UserTakeOver\DICStatic::plugin(\ilUserTakeOverPlugin::class)->translate("demo_group"));
		$usrtoGroup->setDescription(\srag\DIC\UserTakeOver\DICStatic::plugin(\ilUserTakeOverPlugin::class)->translate("demo_group"));
		$usrtoGroup->create();
		$usr_ids = (array)json_decode($data_rec['demo_group']);
		foreach ($usr_ids as $usr_id) {
			$usrtoMember = new usrtoMember();
			$usrtoMember->setGroupId($usrtoGroup->getId());
			$usrtoMember->setUserId($usr_id);
			$usrtoMember->create();
		}
	}
	\srag\DIC\UserTakeOver\DICStatic::dic()->database()->dropTable('ui_uihk_usrto_config');
}
?>
<#5>
<?php
//if(!\srag\DIC\UserTakeOver\DICStatic::dic()->database()->tableExists(ilUserTakeOverConfig::TABLE_NAME)) {
//	\ilUserTakeOverConfig::updateDB();
//}
?>
<#6>
<?php
/**
 * @var $ilDB ilDBInterface
 */
if ($ilDB->tableExists('ui_uihk_usrto_config')) {
    $ilDB->dropTable('ui_uihk_usrto_config');
}
?>
<#7>
<?php
$fields = array(
    'identifier' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '250',
    ),
    'value' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '4000',
    )
);
if (!$ilDB->tableExists('usrto_config')) {
    $ilDB->createTable('usrto_config', $fields);
    $ilDB->addPrimaryKey('usrto_config', array( 'identifier' ));
}
?>
<#1>
<?php
if(!\srag\DIC\DICStatic::dic()->database()->tableExists(ilUserTakeOverConfig::TABLE_NAME)) {
	\ilUserTakeOverConfig::updateDB();
}
?>
<#2>
<?php
if(!\srag\DIC\DICStatic::dic()->database()->tableExists(usrtoGroup::TABLE_NAME)) {
	usrtoGroup::updateDB();
}
?>
<#3>
<?php
if(!\srag\DIC\DICStatic::dic()->database()->tableExists(usrtoMember::TABLE_NAME)) {
	usrtoMember::updateDB();
}
?>
<#4>
<?php
/*
	migrates demo_group from config table to group table
*/
if (\srag\DIC\DICStatic::dic()->database()->tableExists(ilUserTakeOverConfigOld::TABLE_NAME)) {

	$config_data_set = \srag\DIC\DICStatic::dic()->database()->query('SELECT * FROM ' . \ilUserTakeOverConfigOld::TABLE_NAME);

	while ($data_rec = \srag\DIC\DICStatic::dic()->database()->fetchAssoc($config_data_set)) {
		$usrtoGroup = new usrtoGroup();
		$usrtoGroup->setTitle('demo_group');
		$usrtoGroup->setDescription(\srag\DIC\DICStatic::plugin(\ilUserTakeOverPlugin::class)->translate("demo_group"));
		$usrtoGroup->create();
		$usr_ids = (array)json_decode($data_rec['demo_group']);
		foreach ($usr_ids as $usr_id) {
			$usrtoMember = new usrtoMember();
			$usrtoMember->setGroupId($usrtoGroup->getId());
			$usrtoMember->setUserId($usr_id);
			$usrtoMember->create();
		}
	}
	\srag\DIC\DICStatic::dic()->database()->dropTable(ilUserTakeOverConfigOld::TABLE_NAME);
}
?>
<#5>
<?php
if(!\srag\DIC\DICStatic::dic()->database()->tableExists(ilUserTakeOverConfig::TABLE_NAME)) {
	\ilUserTakeOverConfig::updateDB();
}
?>

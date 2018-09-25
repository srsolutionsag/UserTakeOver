<#1>
<?php
if(!\srag\DIC\DICStatic::dic()->database()->tableExists(ilUserTakeOverConfig::TABLE_NAME)) {
	ilUserTakeOverConfig::updateDB();
	$config = new ilUserTakeOverConfig();
	$config->create();
}
?>
<#2>
<?php
if(!\srag\DIC\DICStatic::dic()->database()->tableExists(usrtoGroup::TABLE_NAME)) {
	usrtoGroup::updateDB();
	$usrtoGroup = new usrtoGroup();
	$usrtoGroup->create();
}
?>
<#3>
<?php
if(!\srag\DIC\DICStatic::dic()->database()->tableExists(usrtoMember::TABLE_NAME)) {
	usrtoMember::updateDB();
	$usrtoMember = new usrtoMember();
	$usrtoMember->create();
}
?>
<#4>
<?php
// migrates demo_group from config table to group table
//TODO evtl. user_id store in usrToMember table, check code before testing
if(\srag\DIC\DICStatic::dic()->database()->tableExists(ilUserTakeOverConfig::TABLE_NAME)) {

	$config_data_set = \srag\DIC\DICStatic::dic()->database()->query('SELECT * FROM ui_uihk_usrto_config');

	while($data_rec = \srag\DIC\DICStatic::dic()->database()->fetchAssoc($config_data_set)) {
		$usrtoGroup = new usrtoGroup();
		$usrtoGroup->setTitle('demo_group');
		$usrtoGroup->setDescription('Demo Gruppe');
		$usrtoGroup->create();
		foreach ($data_rec as $rec) {
			$usrtoMember = new usrtoMember();
			$usrtoMember->setId($rec);
			$usrtoMember->setGroupId($usrtoGroup->getId());
			$usrtoMember->setUserId($rec['user_id']);
			$usrtoMember->create();
		}
		$data[] = $data_rec;
	}

}
?>

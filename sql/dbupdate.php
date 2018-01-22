<#1>
<?php
require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/classes/class.ilUserTakeOverConfig.php");
if(!$ilDB->tableExists(ilUserTakeOverConfig::TABLE_NAME)) {
	ilUserTakeOverConfig::updateDB();
	$config = new ilUserTakeOverConfig();
	$config->create();
}

?>

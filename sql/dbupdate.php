<#1>
<?php
require_once "Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/vendor/autoload.php";
if(!$ilDB->tableExists(ilUserTakeOverConfig::TABLE_NAME)) {
	ilUserTakeOverConfig::updateDB();
	$config = new ilUserTakeOverConfig();
	$config->create();
}

?>

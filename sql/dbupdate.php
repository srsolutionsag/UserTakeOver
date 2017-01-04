<#1>
<?php
require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/classes/class.ilUserTakeOverConfig.php");
if(!$ilDB->tableExists("ui_uihk_usrto_config")) {
	ilUserTakeOverConfig::installDB();
	$config = new ilUserTakeOverConfig();
	$config->create();
}

?>

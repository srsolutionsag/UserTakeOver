<#1>
<?php
if(!\srag\DIC\DICStatic::dic()->database()->tableExists(ilUserTakeOverConfig::TABLE_NAME)) {
	ilUserTakeOverConfig::updateDB();
	$config = new ilUserTakeOverConfig();
	$config->create();
}

?>

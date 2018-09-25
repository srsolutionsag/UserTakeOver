<?php

use srag\ActiveRecordConfig\ActiveRecordConfig;

/**
 * Class ilUserTakeOverConfig
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilUserTakeOverConfig extends ActiveRecordConfig {

	const TABLE_NAME = 'ui_uihk_usrto_config';
	const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;
	const DELETE_PLUGIN_DATA = 'delete_plugin_data';
	const DEFAULT_DELETE_PLUGIN_DATA = 0;

	/**
	 * @return bool|null
	 */
	public static function getUninstallRemovesData()/*: ?bool*/ {
		return self::getXValue(ilUserTakeOverRemoveDataConfirm::KEY_UNINSTALL_REMOVES_DATA, ilUserTakeOverRemoveDataConfirm::DEFAULT_UNINSTALL_REMOVES_DATA);
	}


	/**
	 * @param bool $uninstall_removes_data
	 */
	public static function setUninstallRemovesData(/*bool*/$uninstall_removes_data)/*: void*/ {
		self::setBooleanValue(ilUserTakeOverRemoveDataConfirm::KEY_UNINSTALL_REMOVES_DATA, $uninstall_removes_data);
	}


	public static function removeUninstallRemovesData()/*: void*/ {
		self::removeName(ilUserTakeOverRemoveDataConfirm::KEY_UNINSTALL_REMOVES_DATA);
	}


}

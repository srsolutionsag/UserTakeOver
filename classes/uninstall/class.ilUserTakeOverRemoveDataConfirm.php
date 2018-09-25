<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use srag\RemovePluginDataConfirm\AbstractRemovePluginDataConfirm;

/**
 * Class ilUserTakeOverRemoveDataConfirm
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 * @ilCtrl_isCalledBy ilUserTakeOverRemoveDataConfirm: ilUIPluginRouterGUI
 */

class ilUserTakeOverRemoveDataConfirm extends AbstractRemovePluginDataConfirm {

	const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

	/**
	 * @inheritdoc
	 */
	public function getUninstallRemovesData()/*: ?bool*/ {
		return ilUserTakeOverConfig::getUninstallRemovesData();
	}


	/**
	 * @inheritdoc
	 */
	public function setUninstallRemovesData(/*bool*/$uninstall_removes_data)/*: void*/ {
		ilUserTakeOverConfig::setUninstallRemovesData($uninstall_removes_data);
	}


	/**
	 * @inheritdoc
	 */
	public function removeUninstallRemovesData()/*: void*/ {
		ilUserTakeOverConfig::removeUninstallRemovesData();
	}
}
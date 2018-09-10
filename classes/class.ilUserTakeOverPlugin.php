<?php

require_once __DIR__ . "/../vendor/autoload.php";

use srag\DIC\DICTrait;

/**
 * ilUserTakeOverPlugin
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilUserTakeOverPlugin extends ilUserInterfaceHookPlugin {

	use DICTrait;

	const PLUGIN_ID = 'usrto';
	const PLUGIN_NAME = 'UserTakeOver';
	const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

	/**
	 * @var ilUserTakeOverPlugin
	 */
	protected static $instance;


	/**
	 * @return ilUserTakeOverPlugin
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * @return string
	 */
	public function getPluginName() {
		return self::PLUGIN_NAME;
	}


	/**
	 * @return bool
	 */
	protected function beforeUninstall() {
		self::dic()->database()->dropTable(ilUserTakeOverConfig::TABLE_NAME, false);

		return true;
	}
}

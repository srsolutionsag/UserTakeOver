<?php

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * ilUserTakeOverPlugin
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilUserTakeOverPlugin extends ilUserInterfaceHookPlugin {

	const PLUGIN_ID = 'usrto';
	const PLUGIN_NAME = 'UserTakeOver';
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
	 * @var ilDB
	 */
	protected $db;


	public function __construct() {
		parent::__construct();

		global $DIC;

		$this->db = $DIC->database();
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
		$this->db->dropTable(ilUserTakeOverConfig::TABLE_NAME, false);

		return true;
	}
}

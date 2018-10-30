<?php

namespace srag\plugins\UserTakeOver;

require_once __DIR__ . "/../../vendor/autoload.php";

use ilUserTakeOverPlugin;
use srag\CustomInputGUIs\MultiSelectSearchInputGUI\MultiSelectSearchInput2GUI;

/**
 * Class ilMultiSelectSearchInput2GUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilusrtoMultiSelectSearchInput2GUI extends MultiSelectSearchInput2GUI {

	const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

	/**
	 * @param string $title
	 * @param string $post_var
	 */
	public function __construct($title, $post_var) {
		parent::__construct($title, $post_var);
		$this->setInputTemplate(self::plugin()->template('tpl.multiple_select.html'));
		$this->setWidth('300px');
	}


	protected function getValueAsJson() {
		$query = "SELECT firstname, lastname, login, usr_id FROM usr_data WHERE " . self::dic()->database()->in("usr_id", $this->getValue(), false, "integer");
		$res = self::dic()->database()->query($query);
		while ($user = self::dic()->database()->fetchAssoc($res)) {
			$result[] = [
				"id" => $user['usr_id'],
				"text" => $user['firstname'] . " " . $user['lastname'] . " (" . $user['login'] . ")"
			];
		}
		return json_encode($result);
	}

}
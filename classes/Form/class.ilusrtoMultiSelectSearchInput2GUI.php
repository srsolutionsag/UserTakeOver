<?php

namespace srag\plugins\UserTakeOver;

require_once __DIR__ . "/../../vendor/autoload.php";

use ilTemplate;
use ilUserTakeOverPlugin;
use ilUtil;
use srag\CustomInputGUIs\MultiSelectSearchInput2GUI;

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


	/**
	 * @return string
	 */
	protected function getValueAsJsonNew() {
		/*//TODO: change hardcoded values for ids
		$query = "SELECT firstname, lastname, login, usr_id FROM usr_data WHERE " . self::dic()->database()->in("usr_id", [6, 13, 196, 200], false, "integer");
		$res = self::dic()->database()->query($query);
		while ($user = self::dic()->database()->fetchAssoc($res)) {
			$result[] = [
				"id" => $user['usr_id'],
				"text" => $user['firstname'] . " " . $user['lastname'] . " (" . $user['login'] . ")"
			];
		}
		return json_encode($result);*/
		return json_encode(parent::getValue());

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
		//return something like $result[0][id] = 281
		//return something like $result[0][text] = Test User 1 (tuser1)
		return json_encode($result);
	}

}
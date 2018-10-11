<?php

namespace srag\Plugins\UserTakeOver\Factories\Members;

require_once __DIR__ . "/../../../vendor/autoload.php";

use srag\Plugins\UserTakeOver\interfaces\Members\UserTakeOverMemberFactoryInterface;
use srag\DIC\DICTrait;

/**
 * Class UserTakeOverMemberFactory
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class UserTakeOverMemberFactory implements UserTakeOverMemberFactoryInterface {

	use DICTrait;
	const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;


	public function getMembersByGroupId($grp_id) {
		$result = [];
		$res = self::dic()->database()->query("SELECT * FROM ui_uihk_usrto_member WHERE ".
			" group_id = ".self::dic()->database()->quote($grp_id, "integer")
		);
		while ($member = self::dic()->database()->fetchAssoc($res)) {
			$result[] = $member;
		}
		return $result;
	}


	public function getUsersForMembers($members) {
		$user_ids = $this->getUserIdsByMembersArray($members);

		$query = "SELECT firstname, lastname, login, usr_id FROM usr_data WHERE " . self::dic()->database()->in("usr_id", $user_ids, false, "integer");
		$result = [];
		$res = self::dic()->database()->query($query);
		while ($user = self::dic()->database()->fetchAssoc($res)) {
			$result[] = [
				"id" => $user['usr_id'],
				"text" => $user['firstname'] . " " . $user['lastname'] . " (" . $user['login'] . ")"
			];
		}

		return $result;
	}

	public function getUserIdsByMembersArray($members) {
		$user_ids = [];
		foreach ($members as $member) {
			$user_ids[] = $member['user_id'];
		}
		return $user_ids;
	}
}
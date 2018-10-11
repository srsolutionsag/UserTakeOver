<?php

namespace srag\Plugins\UserTakeOver\interfaces\Groups;

/**
 * Class UserTakeOverGroupFactoryInterface
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface UserTakeOverGroupFactoryInterface {

	/**
	 * @return array
	 */
	public function getAllGroups();

	/**
	 * @param int $grp_id
	 *
	 * @return \usrtoGroup | null
	 */
	//public function getGroupById($grp_id);

}
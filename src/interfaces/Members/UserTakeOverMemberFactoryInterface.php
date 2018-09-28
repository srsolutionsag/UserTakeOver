<?php

namespace srag\Plugins\UserTakeOver\interfaces\Members;

/**
 * Class UserTakeOverMemberFactoryInterface
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface UserTakeOverMemberFactoryInterface {

	/**
	 * @param integer $grp_id
	 *
	 * @return array
	 */
	public function getMembersByGroupId($grp_id);

}
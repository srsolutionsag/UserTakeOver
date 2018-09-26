<?php
/**
 * Class ilUserTakeOverMemberFactoryInterface
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilUserTakeOverMemberFactoryInterface {

	/**
	 * @param integer $grp_id
	 *
	 * @return array
	 */
	public function getMembersByGroupId($grp_id);

}
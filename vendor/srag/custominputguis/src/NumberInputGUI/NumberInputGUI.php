<?php

namespace srag\CustomInputGUIs\UserTakeOver\NumberInputGUI;

use ilNumberInputGUI;
use ilTableFilterItem;
use srag\DIC\UserTakeOver\DICTrait;

/**
 * Class NumberInputGUI
 *
 * @package srag\CustomInputGUIs\UserTakeOver\NumberInputGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class NumberInputGUI extends ilNumberInputGUI implements ilTableFilterItem {

	use DICTrait;


	/**
	 * Get input item HTML to be inserted into table filters
	 *
	 * @return string
	 */
	public function getTableFilterHTML()/*: string*/ {
		return $this->render();
	}
}

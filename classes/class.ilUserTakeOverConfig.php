<?php

/**
 * Class ilUserTakeOverConfig
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilUserTakeOverConfig extends ActiveRecord {

	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_sequence   true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $id = 0;

	/**
	 * @var int[]
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     4000
	 */
	protected $demo_group = '';

	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 * @deprecated
	 */
	static function returnDbTableName() {
		return 'ui_uihk_usrto_config';
	}

	/**
	 * @param $field_name
	 * @param $field_value
	 *
	 * @return mixed|null
	 */
	public function wakeUp($field_name, $field_value) {
		switch ($field_name) {
			case 'demo_group':
				return json_decode($field_value);
				break;
		}

		return null;
	}

	/**
	 * @param $field_name
	 *
	 * @return mixed|null|string
	 */
	public function sleep($field_name) {
		switch ($field_name) {
			case 'demo_group':
				return json_encode($this->{$field_name});
				break;
		}

		return null;
	}

	/**
	 * @return int[]
	 */
	public function getDemoGroup() {
		return $this->demo_group;
	}

	/**
	 * @param int[] $demo_group
	 */
	public function setDemoGroup($demo_group) {
		$this->demo_group = $demo_group;
	}
}
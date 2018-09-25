<?php

/**
 * Class ilUserTakeOverConfigOld
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @deprecated
 */
class ilUserTakeOverConfigOld extends ActiveRecord {
	/*
	 * @deprecated
	 */
	const TABLE_NAME = 'ui_uihk_usrto_config';


	/**
	 * @return string
	 * @deprecated
	 */
	public function getConnectorContainerName() {
		return self::TABLE_NAME;
	}


	/**
	 * @return string
	 * @deprecated
	 */
	public static function returnDbTableName() {
		return self::TABLE_NAME;
	}


	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_sequence   true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 * @deprecated
	 */
	protected $id = 0;
	/**
	 * @var int[]
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     4000
	 * @deprecated
	 */
	protected $demo_group = array();


	/**
	 * @param string $field_name
	 * @param string $field_value
	 *
	 * @return mixed|null
	 * @deprecated
	 */
	public function wakeUp($field_name, $field_value) {
		switch ($field_name) {
			case 'demo_group':
				return (array)json_decode($field_value);
				break;
		}

		return NULL;
	}


	/**
	 * @param string $field_name
	 *
	 * @return mixed|null|string
	 * @deprecated
	 */
	public function sleep($field_name) {
		switch ($field_name) {
			case 'demo_group':
				return json_encode($this->{$field_name});
				break;
		}

		return NULL;
	}


	/**
	 * @return int[]
	 * @deprecated
	 */
	public function getDemoGroup() {
		return $this->demo_group;
	}


	/**
	 * @param int[] $demo_group
	 * @deprecated
	 */
	public function setDemoGroup($demo_group) {
		$this->demo_group = $demo_group;
	}
}

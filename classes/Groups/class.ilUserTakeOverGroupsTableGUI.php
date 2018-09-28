<?php
require_once __DIR__ . "/../../vendor/autoload.php";

use srag\DIC\DICTrait;

/**
 * Class ilUserTakeOverGroupsTableGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilUserTakeOverGroupsTableGUI extends ilTable2GUI {

	use DICTrait;

	const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

	const TBL_ID = 'tbl_usrto_grps';

	/**
	 * @var ilUserTakeOverGroupsGUI
	 */
	protected $parent_obj;
	/**
	 * @var array
	 */
	protected $filter = array();


	/**
	 * @param ilUserTakeOverGroupsGUI $a_parent_obj
	 * @param string          $a_parent_cmd
	 */
	public function  __construct(ilUserTakeOverGroupsGUI $a_parent_obj, $a_parent_cmd) {

		$this->parent_obj = $a_parent_obj;

		$this->setId(self::TBL_ID);
		$this->setPrefix(self::TBL_ID);
		$this->setFormName(self::TBL_ID);
		self::dic()->ctrl()->saveParameter($a_parent_obj, $this->getNavParameter());

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->parent_obj = $a_parent_obj;
		$this->setRowTemplate('tpl.groups.html', 'Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver');
		$this->setFormAction(self::dic()->ctrl()->getFormAction($a_parent_obj));
		$this->setExternalSorting(true);
		$this->initColums();
		$this->addFilterItems();
		$this->parseData();
	}

	protected function addFilterItems() {
		$title = new ilTextInputGUI(self::plugin()->translate('title'), 'title');
		$this->addAndReadFilterItem($title);

		$number_of_members = new ilTextInputGUI(self::plugin()->translate('number_of_members'), 'number_of_members');
		$this->addAndReadFilterItem($number_of_members);
	}

	/**
	 * @param $item
	 */
	protected function addAndReadFilterItem(ilFormPropertyGUI $item) {
		$this->addFilterItem($item);
		$item->readFromSession();
		if ($item instanceof ilCheckboxInputGUI) {
			$this->filter[$item->getPostVar()] = $item->getChecked();
		} else {
			$this->filter[$item->getPostVar()] = $item->getValue();
		}
	}

	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		/**
		 * @var usrtoGroup $usrtoGroup
		 */
		$usrtoGroup = usrtoGroup::find($a_set['id']);
		$this->tpl->setCurrentBlock('tbl_content');
		$this->tpl->setVariable('TITLE', $usrtoGroup->getTitle());
		//TODO replace static number of members
		$this->tpl->setVariable('NUMBER_OF_MEMBERS', 8);
		$this->addActionMenu($usrtoGroup);
		$this->tpl->parseCurrentBlock();
	}

	protected function initColums() {
		$this->addColumn(self::plugin()->translate('name_grp'), 'title');
		$this->addColumn(self::plugin()->translate('number_of_members'), 'number_of_members');
		$this->addColumn(self::plugin()->translate('common_actions'), '', '150px');
	}

	/**
	 * @param usrtoGroup $usrtoGroup
	 */
	protected function addActionMenu(usrtoGroup $usrtoGroup) {
		$access = new ilObjUserTakeOverAccess();

		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle(self::plugin()->translate('common_actions'));
		$current_selection_list->setId('grp_actions_' . $usrtoGroup->getId());
		$current_selection_list->setUseImages(false);

		self::dic()->ctrl()->setParameter($this->parent_obj, ilUserTakeOverGroupsGUI::IDENTIFIER, $usrtoGroup->getId());
		if ($access->hasWriteAccess()) {
			$current_selection_list->addItem(self::plugin()->translate('edit_members'), ilUserTakeOverMembersGUI::CMD_CONFIGURE, self::dic()->ctrl()->getLinkTargetByClass(ilUserTakeOverMembersGUI::class, ilUserTakeOverMembersGUI::CMD_CONFIGURE));
			$current_selection_list->addItem(self::plugin()->translate('edit_grp'), ilUserTakeOverGroupsGUI::CMD_EDIT, self::dic()->ctrl()->getLinkTarget($this->parent_obj, ilUserTakeOverGroupsGUI::CMD_EDIT));
		}
		if ($access->hasDeleteAccess()) {
			$current_selection_list->addItem(self::plugin()->translate('delete'), ilUserTakeOverGroupsGUI::CMD_DELETE, self::dic()->ctrl()->getLinkTarget($this->parent_obj, ilUserTakeOverGroupsGUI::CMD_CONFIRM));
		}
		$current_selection_list->getHTML();
		$this->tpl->setVariable('ACTIONS', $current_selection_list->getHTML());
	}

	protected function parseData() {

		$this->determineOffsetAndOrder();
		$this->determineLimit();

		$collection = usrtoGroup::getCollection();
		//$collection->where(array( 'my_geolocations_id' => $this->parent_obj->getMyGeolocationId() ));

		$sorting_column = $this->getOrderField() ? $this->getOrderField() : 'title';
		$offset = $this->getOffset() ? $this->getOffset() : 0;

		$sorting_direction = $this->getOrderDirection();
		$num = $this->getLimit();

		$collection->orderBy($sorting_column, $sorting_direction);
		$collection->limit($offset, $num);

		foreach ($this->filter as $filter_key => $filter_value) {
			switch ($filter_key) {
				case 'title':
					$collection->where(array( $filter_key => '%' . $filter_value . '%' ), 'LIKE');
					break;
					//TODO implement filter
/*				case 'number_of_members':
					$collection->
					$collection->leftjoin(usrtoMember::TABLE_NAME,  usrToMember::TABLE_NAME . '.group_id')->;
					break;*/
			}
		}

		//$collection->debug();
		$this->setData($collection->getArray());
	}

}
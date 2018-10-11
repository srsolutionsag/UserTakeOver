<?php
require_once __DIR__ . "/../../vendor/autoload.php";

use srag\plugins\UserTakeOver\ilusrtoMultiSelectSearchInput2GUI;

use srag\DIC\DICTrait;

/**
 * GUI class ilUserTakeOverMembersGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilUserTakeOverMembersGUI: ilUIPluginRouterGUI
 */

class ilUserTakeOverMembersGUI {

	use DICTrait;

	const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;
	const CMD_CONFIGURE = 'configure';
	const CMD_SAVE = 'save';
	const CMD_SEARCH_USERS = 'searchUsers';
	const CMD_CANCEL = 'cancel';

	public function executeCommand() {

		self::dic()->tabs()->clearTargets();
		$nextClass = self::dic()->ctrl()->getNextClass();
		switch ($nextClass) {
			default;
				$this->performCommand(self::dic()->ctrl()->getCmdClass());
				break;
		}
	}


	public function performCommand($cmd) {
		$cmd = self::dic()->ctrl()->getCmd();
		switch ($cmd) {
			case self::CMD_CONFIGURE:
			case self::CMD_SEARCH_USERS:
			case self::CMD_SAVE:
			case self::CMD_CANCEL:
				$this->$cmd();
				break;
			default:
				throw new ilException("command not defined.");
				break;
		}
	}


	public function configure() {
		$form = $this->getForm();
		$this->fillForm($form);
		self::dic()->ui()->mainTemplate()->setContent($form->getHTML());
	}


	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getForm() {
		$form = new ilPropertyFormGUI();
		$form->setTitle(self::plugin()->translate("configuration"));

		/**
		 * @var usrtoGroup $group
		 */
		$group = usrtoGroup::find(filter_input(INPUT_GET, "usrtoGrp"));
		if(is_object($group)) {
			$title = $group->getTitle();
		} else {
			$title = self::plugin()->translate("group");
		}
		$input = new ilusrtoMultiSelectSearchInput2GUI($title, $group->getTitle());
		$input->setInfo(self::plugin()->translate("group_info"));
		$input->setAjaxLink(self::dic()->ctrl()->getLinkTarget($this, self::CMD_SEARCH_USERS));
		$form->addItem($input);

		//$form->addCommandButton(self::CMD_SAVE, sel   f::plugin()->translate("save"));
		$this->initButtons($form);

		$form->setFormAction(self::dic()->ctrl()->getFormAction($this, self::CMD_SAVE));

		return $form;
	}


	/**
	 * @param ilPropertyFormGUI &$form
	 */
	protected function initButtons(&$form) {
		$form->addCommandButton(self::CMD_SAVE, self::plugin()->translate("save"));
		$form->addCommandButton(self::CMD_CANCEL, self::plugin()->translate("cancel"));
	}

	protected function cancel() {
		self::dic()->ctrl()->redirectByClass(ilUserTakeOverGroupsGUI::class, ilUserTakeOverGroupsGUI::CMD_STANDARD);
	}


	protected function save() {
		$form = $this->getForm();
		$form->setValuesByPost();
		if ($form->checkInput()) {
			$group = explode(",", $form->getInput("group")[0]);
			$config = ilUserTakeOverConfig::first();
			$config->setDemoGroup($group);
			$config->save();
			ilUtil::sendSuccess(self::plugin()->translate("success"), true);
			self::dic()->ctrl()->redirect($this, self::CMD_CONFIGURE);
		} else {
			ilUtil::sendFailure(self::plugin()->translate("something_went_wrong"), true);
			self::dic()->ui()->mainTemplate()->setContent($form->getHTML());
		}
	}

	//TODO delete method
	protected function fillFormOld(&$form) {
		$config = ilUserTakeOverConfig::first();
		$demo_group = $config->getDemoGroup();

		$values = [
			"demo_group" => implode(',', $demo_group)
		];

		$form->setValuesByArray($values);
	}


	/**
	 * @param ilPropertyFormGUI $form
	 */
	protected function fillForm(&$form) {
		//$group = usrtoGroup::find(filter_input(INPUT_GET, "usrtoGrp"));
		//$values = usrtoGroup::getArray('id', filter_input(INPUT_GET, "usrtoGrp"));

		//$values =(array) $group;
		$userTakeOverMemberFactory = new srag\Plugins\UserTakeOver\Factories\Members\UserTakeOverMemberFactory();
		$members = $userTakeOverMemberFactory->getMembersByGroupId(filter_input(INPUT_GET, "usrtoGrp"));
		$users = $userTakeOverMemberFactory->getUsersForMembers($members);

		/** @var usrtoGroup $group */
		$group = usrtoGroup::find(filter_input(INPUT_GET, "usrtoGrp"));

		$values = [
			$group->getTitle() =>  $users
		];

		$form->setValuesByArray($values);
	}


	protected function searchUsers() {
		//TODO here it should be possible to add all users to the group
		// Only Administrators
		if (!in_array(2, self::dic()->rbacreview()->assignedGlobalRoles(self::dic()->user()->getId()))) {
			echo json_encode([]);
			exit;
		}

		$term = filter_input(INPUT_GET, "term");
		/** @var ilObjUser[] $users */
		//$users = ilObjUser::searchUsers($term);
		$result = [];

/*		foreach ($users as $user) {
			$result[] = [
				"id" => $user['usr_id'],
				"text" => $user['firstname'] . " " . $user['lastname'] . " (" . $user['login'] . ")"
			];
		}*/

		$result[] = [
			"id" => "6",
			"text" => "root"
		];

		echo json_encode($result);
		exit;
	}

}
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
			case self::CMD_SAVE:
				self::dic()->tabs()->setBackTarget(self::plugin()->translate('back'), self::dic()->ctrl()->getLinkTargetByClass(ilUserTakeOverGroupsGUI::class, ilUserTakeOverGroupsGUI::CMD_STANDARD));
				$this->$cmd();
				break;
			case self::CMD_SEARCH_USERS:
			case self::CMD_CANCEL:
				$this->$cmd();
				break;
			default:
				throw new ilException("command not defined.");
				break;
		}
	}


	public function configure() {
		self::dic()->ctrl()->saveParameterByClass(self::class, "usrtoGrp");
		$form = $this->getForm();
		$this->fillForm($form);
		self::plugin()->output($form);
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
		$input = new ilusrtoMultiSelectSearchInput2GUI($title, 'grp[' . $group->getId() . ']');
		$input->setInfo(self::plugin()->translate("group_info"));
		$input->setAjaxLink(self::dic()->ctrl()->getLinkTarget($this, self::CMD_SEARCH_USERS));

		$members_data = \usrtoMember::innerjoin('usr_data','user_id','usr_id')
			->where(["group_id" => filter_input(INPUT_GET, "usrtoGrp")], "=")->getArray(null, ["usr_id", "firstname", "lastname", "login"]);
		$options = [];
		foreach($members_data as $member_data) {
			$options[$member_data['usr_id']] = $member_data['firstname'] . " " . $member_data['lastname'] . " (" . $member_data['login'] . ")";
		}
		$input->setOptions($options);
		$form->addItem($input);

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
			$group_id = filter_input(INPUT_GET, "usrtoGrp");
			$grp_user_array = filter_var(filter_input(INPUT_POST, "grp", FILTER_DEFAULT, FILTER_FORCE_ARRAY)[$group_id], FILTER_DEFAULT, FILTER_FORCE_ARRAY);
			foreach($grp_user_array as $key => $user_id) {
				$usrtoMember = usrtoMember::where([ 'group_id' => $group_id, 'user_id' => $user_id], '=' )->first();
				if(!empty($usrtoMember)) {
					continue;
				} else {
					$usrtoMember = new usrtoMember();
					$usrtoMember->setGroupId($group_id);
					$usrtoMember->setUserId($user_id);
					$usrtoMember->store();
				}
			}
			$user_ids = \usrtoMember::where(["group_id" => filter_input(INPUT_GET, "usrtoGrp")], "=")->getArray(null, "user_id");
			//get ids of users who are not longer in the group
			$usr_ids_not_anymore_members = array_diff($user_ids, $grp_user_array);
			foreach($usr_ids_not_anymore_members as $key => $usr_id) {
				$usrtoMember = usrtoMember::where(["user_id"=>$usr_id])->first();
				$usrtoMember->delete();
			}
			ilUtil::sendSuccess(self::plugin()->translate("success"), true);
			self::dic()->ctrl()->saveParameterByClass(self::class, "usrtoGrp");
			self::dic()->ctrl()->redirect($this, self::CMD_CONFIGURE);
		} else {
			ilUtil::sendFailure(self::plugin()->translate("something_went_wrong"), true);
			self::plugin()->output($form);
		}
	}


	/**
	 * @param ilPropertyFormGUI $form
	 */
	protected function fillForm(&$form) {
		$user_ids = \usrtoMember::where(["group_id" => filter_input(INPUT_GET, "usrtoGrp")], "=")->getArray(null, "user_id");

		/** @var usrtoGroup $group */
		$group = usrtoGroup::find(filter_input(INPUT_GET, "usrtoGrp"));

		$values = [
			'grp[' . $group->getId() . ']' =>  implode(',', $user_ids)
		];

		$form->setValuesByArray($values);
	}


	protected function searchUsers() {
		// Only Administrators
		if (!in_array(2, self::dic()->rbacreview()->assignedGlobalRoles(self::dic()->user()->getId()))) {
			self::plugin()->output([]);
			return;
		}

		//when the search was done via select2 input field the term will be send as array. In the search field it won't be send as array.
		if (is_array($_GET['term'])) {
			$filtered_term = filter_input(INPUT_GET, "term", FILTER_DEFAULT, FILTER_FORCE_ARRAY)["term"];
		} else {
			$filtered_term = filter_input(INPUT_GET, "term", FILTER_DEFAULT);
		}
		$filtered_term = isset($filtered_term )? $filtered_term : "";

		/** @var ilObjUser[] $users */
		$users = ilObjUser::searchUsers($filtered_term);
		$result = [];

		foreach ($users as $user) {
			$result[] = [
				"id" => $user['usr_id'],
				"text" => $user['firstname'] . " " . $user['lastname'] . " (" . $user['login'] . ")"
			];
		}

		self::plugin()->output($result);
	}

}
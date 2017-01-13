<?php

use srag\plugins\UserTakeOver\ilusrtoMultiSelectSearchInput2GUI;

require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/classes/Form/class.ilusrtoMultiSelectSearchInput2GUI.php");
require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/classes/class.ilUserTakeOverConfig.php");
require_once("./Services/Exceptions/classes/class.ilException.php");
/**
 * ilUserDefaultsConfigGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 1.0.00
 *
 * @ilCtrl_IsCalledBy ilUserTakeOverConfigGUI: ilUIPluginRouterGUI,ilObjComponentSettingsGUI
 */
class ilUserTakeOverConfigGUI extends ilPluginConfigGUI {

	/** @var ilCtrl ilCtrl */
	protected $ctrl;
	/** @var  tabs ilTabsGUI */
	protected $tabs;
	/** @var tpl ilTemplate */
	protected $tpl;
	/** @var lng ilLanguage */
	protected $lng;
	/** @var ilUserTakeOverPlugin ilUserTakeOverPlugin */
	protected $pl;

	public function __construct() {
		global $ilCtrl, $ilTabs, $tpl, $lng, $pl;

		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->pl = new ilUserTakeOverPlugin();
	}

	public function executeCommand() {

		$this->tabs->clearTargets();
		$nextClass = $this->ctrl->getNextClass();
		switch ($nextClass) {

			default;
				$this->performCommand($this->ctrl->getCmdClass());
				break;
		}
	}


	public function performCommand($cmd) {
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case "configure":
			case "searchUsers":
			case "save":
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
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getForm() {
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->pl->txt("configuration"));

		$input = new ilusrtoMultiSelectSearchInput2GUI($this->pl->txt("demo_group"), "demo_group");
		$input->setInfo($this->pl->txt("demo_group_info"));
		$input->setAjaxLink($this->ctrl->getLinkTarget($this, "searchUsers"));
		$form->addItem($input);

		$form->addCommandButton("save", $this->pl->txt("save"));

		$form->setFormAction($this->ctrl->getFormAction($this, "save"));

		return $form;

	}

	protected function save() {
		$form = $this->getForm();
		$form->setValuesByPost();
		if($form->checkInput()) {
			$demo_group = explode(",",$form->getInput("demo_group")[0]);
			$config = ilUserTakeOverConfig::first();
			$config->setDemoGroup($demo_group);
			$config->save();
			ilUtil::sendSuccess($this->pl->txt("success"), true);
			$this->ctrl->redirect($this, "configure");
		} else {
			ilUtil::sendFailure($this->pl->txt("something_went_wrong"), true);
			$this->tpl->setContent($form->getHTML());
		}
	}

	/**
	 * @param $form ilPropertyFormGUI
	 */
	protected function fillForm(&$form) {
		$config = ilUserTakeOverConfig::first();
		$demo_group = $config->getDemoGroup();

		$values = [
			"demo_group" => implode(',',$demo_group)
		];

		$form->setValuesByArray($values);
	}

	protected function searchUsers() {
		global $rbacreview, $ilUser;
		// Only Administrators
		if (!in_array(2, $rbacreview->assignedGlobalRoles($ilUser->getId()))) {
			echo json_encode([]);exit;
		}

		$term = $_GET['term'];
		/** @var ilObjUser[] $users */
		$users = ilObjUser::searchUsers($term);
		$result = [];

		foreach ($users as $user) {
			$result[] = [
				"id" => $user['usr_id'],
				"text" => $user['firstname']." ".$user['lastname']." (".$user['login'].")"
			];
		}

		echo json_encode($result);
		exit;
	}
}


<?php

use srag\plugins\UserTakeOver\ilusrtoMultiSelectSearchInput2GUI;

require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/classes/Form/class.ilusrtoMultiSelectSearchInput2GUI.php");
require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/classes/class.ilUserTakeOverConfig.php");
require_once("./Services/Exceptions/classes/class.ilException.php");
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

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
	const CMD_CONFIGURE = 'configure';
	const CMD_SAVE = 'save';
	const CMD_SEARCH_USERS = 'searchUsers';
	/** @var ilCtrl */
	protected $ctrl;
	/** @var ilTabsGUI */
	protected $tabs;
	/** @var  ilTemplate */
	protected $tpl;
	/** @var ilLanguage */
	protected $lng;
	/** @var ilUserTakeOverPlugin */
	protected $pl;
	/**
	 * @var ilObjUser
	 */
	protected $usr;
	/**
	 * @var ilRbacReview
	 */
	protected $rbacview;

	public function __construct() {
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->tabs = $DIC->tabs();
		$this->tpl = $DIC->ui()->mainTemplate();
		$this->lng = $DIC->language();
		$this->pl = new ilUserTakeOverPlugin();
		$this->usr = $DIC->user();
		$this->rbacview = $DIC->rbac()->review();
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
			case self::CMD_CONFIGURE:
			case self::CMD_SEARCH_USERS:
			case self::CMD_SAVE:
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
		$input->setAjaxLink($this->ctrl->getLinkTarget($this, self::CMD_SEARCH_USERS));
		$form->addItem($input);

		$form->addCommandButton(self::CMD_SAVE, $this->pl->txt("save"));

		$form->setFormAction($this->ctrl->getFormAction($this, self::CMD_SAVE));

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
			$this->ctrl->redirect($this, self::CMD_CONFIGURE);
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
		global $DIC;
		// Only Administrators
		if (!in_array(2, $this->rbacview->assignedGlobalRoles($this->usr->getId()))) {
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


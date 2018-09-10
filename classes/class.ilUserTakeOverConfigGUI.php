<?php
require_once __DIR__ . "/../vendor/autoload.php";

use srag\plugins\UserTakeOver\ilusrtoMultiSelectSearchInput2GUI;

use srag\DIC\DICTrait;

/**
 * ilUserDefaultsConfigGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version           1.0.00
 *
 * @ilCtrl_IsCalledBy ilUserTakeOverConfigGUI: ilUIPluginRouterGUI,ilObjComponentSettingsGUI
 */
class ilUserTakeOverConfigGUI extends ilPluginConfigGUI {

	use DICTrait;

	const CMD_CONFIGURE = 'configure';
	const CMD_SAVE = 'save';
	const CMD_SEARCH_USERS = 'searchUsers';
	const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

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

		$input = new ilusrtoMultiSelectSearchInput2GUI(self::plugin()->translate("demo_group"), "demo_group");
		$input->setInfo(self::plugin()->translate("demo_group_info"));
		$input->setAjaxLink(self::dic()->ctrl()->getLinkTarget($this, self::CMD_SEARCH_USERS));
		$form->addItem($input);

		$form->addCommandButton(self::CMD_SAVE, self::plugin()->translate("save"));

		$form->setFormAction(self::dic()->ctrl()->getFormAction($this, self::CMD_SAVE));

		return $form;
	}


	protected function save() {
		$form = $this->getForm();
		$form->setValuesByPost();
		if ($form->checkInput()) {
			$demo_group = explode(",", $form->getInput("demo_group")[0]);
			$config = ilUserTakeOverConfig::first();
			$config->setDemoGroup($demo_group);
			$config->save();
			ilUtil::sendSuccess(self::plugin()->translate("success"), true);
			self::dic()->ctrl()->redirect($this, self::CMD_CONFIGURE);
		} else {
			ilUtil::sendFailure(self::plugin()->translate("something_went_wrong"), true);
			self::dic()->ui()->mainTemplate()->setContent($form->getHTML());
		}
	}


	/**
	 * @param ilPropertyFormGUI $form
	 */
	protected function fillForm(&$form) {
		$config = ilUserTakeOverConfig::first();
		$demo_group = $config->getDemoGroup();

		$values = [
			"demo_group" => implode(',', $demo_group)
		];

		$form->setValuesByArray($values);
	}


	protected function searchUsers() {
		// Only Administrators
		if (!in_array(2, self::dic()->rbacreview()->assignedGlobalRoles(self::dic()->user()->getId()))) {
			echo json_encode([]);
			exit;
		}

		$term = $_GET['term'];
		/** @var ilObjUser[] $users */
		$users = ilObjUser::searchUsers($term);
		$result = [];

		foreach ($users as $user) {
			$result[] = [
				"id" => $user['usr_id'],
				"text" => $user['firstname'] . " " . $user['lastname'] . " (" . $user['login'] . ")"
			];
		}

		echo json_encode($result);
		exit;
	}
}


<?php
require_once __DIR__ . "/../../vendor/autoload.php";

use srag\DIC\DICTrait;

/**
 * Class ilUserTakeOverGroupsGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilUserTakeOverGroupsGUI: usrtoGroupFormGUI, ilUserTakeOverGroupsTableGUI, ilUserTakeOverMembersGUI
 */

class ilUserTakeOverGroupsGUI {

	use DICTrait;

	const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

	const CMD_STANDARD = 'content';
	const CMD_ADD = 'add';
	const CMD_SAVE = 'save';
	const CMD_CREATE = 'create';
	const CMD_EDIT = 'edit';
	const CMD_UPDATE = 'update';
	const CMD_CONFIRM = 'confirmDelete';
	const CMD_DELETE = 'delete';
	const CMD_CANCEL = 'cancel';
	const CMD_VIEW = 'view';
	const IDENTIFIER = 'usrtoGrp';

	public function executeCommand() {
		$this->initTabs();
		$nextClass = self::dic()->ctrl()->getNextClass();
		switch ($nextClass) {
			case strtolower(ilUserTakeOverMembersGUI::class):
				$ilUserTakeOverMembersGUI = new ilUserTakeOverMembersGUI();
				self::dic()->ctrl()->forwardCommand($ilUserTakeOverMembersGUI);
				break;
			default:
				$cmd = self::dic()->ctrl()->getCmd(self::CMD_STANDARD);
				$this->{$cmd}();
				break;
		}
	}

	public function performCommand($cmd) {
		$cmd = self::dic()->ctrl()->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
				if(ilObjUserTakeOverAccess::hasWriteAccess()) {
					$this->{$cmd};
					break;
				} else {
					ilUtil::sendFailure(self::plugin()->translate('permission_denied'), true);
					self::dic()->ctrl()->redirectByClass(ilObjComponentSettingsGUI::class, 'listPlugins');
					break;
				}
			default:
				throw new ilException("command not defined.");
				break;
		}
	}

	protected function initTabs() {
		self::dic()->tabs()->addTab(self::CMD_STANDARD, self::dic()->language(''), self::dic()->ctrl()->getLinkTargetByClass(ilUserTakeOverGroupsTableGUI::class));
	}

	protected function content() {
		$f = self::dic()->ui()->factory();
		self::dic()->toolbar()->addComponent($f->button()->standard(self::plugin()->translate("add_grp"), self::dic()->ctrl()->getLinkTargetByClass(ilUserTakeOverGroupsGUI::class, ilUserTakeOverGroupsGUI::CMD_ADD)));

		$ilUserTakeOverGroupsTableGUI = new ilUserTakeOverGroupsTableGUI($this, self::CMD_STANDARD);
		self::plugin()->output($ilUserTakeOverGroupsTableGUI->getHTML());

	}

	protected function add() {
		$usrtoGroupFormGUI = new usrtoGroupFormGUI($this, new usrtoGroup());
		self::dic()->ui()->mainTemplate()->setContent($usrtoGroupFormGUI->getHTML());
	}

	protected function create() {
		$usrtoGroupFormGUI = new usrtoGroupFormGUI($this, new usrtoGroup());
		$usrtoGroupFormGUI->setValuesByPost();
		if ($usrtoGroupFormGUI->saveObject()) {
			ilUtil::sendSuccess(self::plugin()->translate('create_grp_msg_success'), true);
			self::dic()->ctrl()->redirect($this);
		}
		self::dic()->ui()->mainTemplate()->setContent($usrtoGroupFormGUI->getHTML());

	}

	protected function edit() {
		$usrtoGroupFormGUI = new usrtoGroupFormGUI($this, usrtoGroup::find(filter_input(INPUT_GET, self::IDENTIFIER)));
		$usrtoGroupFormGUI->fillForm();
		self::dic()->ui()->mainTemplate()->setContent($usrtoGroupFormGUI->getHTML());
	}

	protected function update() {
		$usrtoGroupFormGUI = new usrtoGroupFormGUI($this, usrtoGroup::find(filter_input(INPUT_GET, self::IDENTIFIER)));
		$usrtoGroupFormGUI->setValuesByPost();
		if ($usrtoGroupFormGUI->saveObject()) {
			ilUtil::sendSuccess(self::plugin()->translate('update_grp_msg_success'), true);
			self::dic()->ctrl()->redirect($this);
		}
		self::dic()->ui()->mainTemplate()->setContent($usrtoGroupFormGUI->getHTML());
	}

	protected function confirmDelete() {
		/**
		 * @var usrToGroup $usrToGroup
		 */
		$usrToGroup = usrtoGroup::find(filter_input(INPUT_GET, self::IDENTIFIER));

		ilUtil::sendQuestion(self::plugin()->translate('confirm_delete_grp'), true);
		$confirm = new ilConfirmationGUI();
		$confirm->addItem(self::IDENTIFIER, $usrToGroup->getId(), $usrToGroup->getTitle());
		$confirm->setFormAction(self::dic()->ctrl()->getFormAction($this));
		$confirm->setCancel(self::plugin()->translate('cancel'), self::CMD_CANCEL);
		$confirm->setConfirm(self::plugin()->translate('delete'), self::CMD_DELETE);

		self::dic()->ui()->mainTemplate()->setContent($confirm->getHTML());
	}

	protected function delete() {
		/**
		 * @var usrtoGroup $usrtoGroup
		 */
		$usrtoGroup = usrtoGroup::find(filter_input(INPUT_POST, self::IDENTIFIER));
		$members = usrtoMember::where(["group_id" => $usrtoGroup->getId()])->get();
		/**
		 * @var usrtoMember $member
		 */
		foreach($members as $member) {
			$usrtoMember = usrtoMember::find($member->getId());
			$usrtoMember->delete();
		}
		$usrtoGroup->delete();
		$this->cancel();
	}

	protected function cancel() {
		self::dic()->ctrl()->redirectByClass(self::class, self::CMD_STANDARD);
	}

	protected function applyFilter() {
		$ilUserTakeOverGroupsTableGUI = new ilUserTakeOverGroupsTableGUI($this, self::CMD_STANDARD);
		$ilUserTakeOverGroupsTableGUI->writeFilterToSession();
		self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	protected function resetFilter() {
		$ilUserTakeOverGroupsTableGUI = new ilUserTakeOverGroupsTableGUI($this, self::CMD_STANDARD);
		$ilUserTakeOverGroupsTableGUI->resetFilter();
		$ilUserTakeOverGroupsTableGUI->resetOffset();
		self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
	}

}
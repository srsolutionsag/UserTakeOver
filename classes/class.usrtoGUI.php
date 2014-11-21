<?php
require_once './Services/User/classes/class.ilObjUser.php';

/**
 * Class usrtoGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @version 1.0.0
 *
 * @ilCtrl_IsCalledBy usrtoGUI: ilRouterGUI
 */
class usrtoGUI {

	const CMD_STD = 'performUserTakeOver';

	public function __construct() {
		global $ilCtrl, $tpl, $lng, $ilTabs, $rbacreview, $ilUser, $ilLog;
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $tpl ilTemplate
		 * @var $lng ilLanguage
		 * @var $ilTabs ilTabsGUI
		 * @var $rbacreview ilRbacReview
		 * @var $ilUser ilObjUser
		 * @var $ilLog ilLog
		 */
		$this->ilCtrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->tabs = $ilTabs;
		$this->rbacreview = $rbacreview;
		$this->ilUser = $ilUser;
		$this->ilLog = $ilLog;
		$this->pl = new ilUserTakeOverPlugin();


	}

	public function executeCommand() {
		$cmd = $this->ilCtrl->getCmd(self::CMD_STD);

		switch ($cmd) {
			case 'performUserTakeOver':
				$this->performUserTakeOver();
				break;
			case 'performUserTakeBack':
				$this->performUserTakeBack();
				break;
		}

	}

	public function performUserTakeOver() {
		//Permission Check: Only Administrators
		if(!in_array(2,$this->rbacreview->assignedGlobalRoles($this->ilUser->getId()))) {
			ilUtil::sendFailure($this->pl->txt("no_permission"), true);
			ilUtil::redirect('');
			return false;
		}

		if($_GET['usr_id']) {
			$_SESSION['AccountId'] = $_GET['usr_id'];
			$_SESSION['usrtoOriginalAccountId'] = $this->ilUser->getId();

			$ilObjUser = new ilObjUser($_SESSION['AccountId']);

			$this->ilLog->write('Plugin usrto: '.$this->ilUser->getLogin().' has taken over the user view of '.$ilObjUser->getLogin());

			ilUtil::sendSuccess(sprintf($this->pl->txt('user_taker_over_success'),$ilObjUser->getLogin()), true);
			ilUtil::redirect('');
		} else {
			ilUtil::sendFailure($this->pl->txt('user_taker_over_failure'), true);
		}
	}

	public function performUserTakeBack() {
		//No Permission Check. The existing Session usrtoOriginalAccountId is the permission check
		if($_SESSION['usrtoOriginalAccountId']) {
			$_SESSION['AccountId'] = $_SESSION['usrtoOriginalAccountId'];

			unset($_SESSION['usrtoOriginalAccountId']);

			$ilObjUser = new ilObjUser($_SESSION['AccountId']);

			$this->ilLog->write('Plugin usrto: '.$ilObjUser->getLogin().' has left the user view of '.$this->ilUser->getLogin());

			ilUtil::sendSuccess(sprintf($this->pl->txt('user_taker_back_success'),$ilObjUser->getLogin()), true);
			ilUtil::redirect('');
		} else {
			ilUtil::sendFailure($this->pl->txt('user_taker_over_failure'), true);
		}
	}
}
?>
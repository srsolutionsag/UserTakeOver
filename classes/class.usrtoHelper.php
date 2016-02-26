<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/classes/class.ilUserTakeOverPlugin.php');

/**
 * Class usrtoHelper
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class usrtoHelper {

	const USR_ID_GLOBAL = 'AccountId';
	const USR_ID_BACKUP = 'usrtoOriginalAccountId';
	const USR_ID = 'usr_id';
	/**
	 * @var usrtoHelper
	 */
	protected static $instance;


	/**
	 * @return usrtoHelper
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * @var int
	 */
	protected $original_usr_id = 6;
	/**
	 * @var int
	 */
	protected $temporary_usr_id = 6;


	/**
	 * @return int
	 */
	public function getOriginalUsrId() {
		return $this->original_usr_id;
	}


	/**
	 * @param int $original_usr_id
	 */
	public function setOriginalUsrId($original_usr_id) {
		$this->original_usr_id = $original_usr_id;
	}


	/**
	 * @return int
	 */
	public function getTemporaryUsrId() {
		return $this->temporary_usr_id;
	}


	/**
	 * @param int $temporary_usr_id
	 */
	public function setTemporaryUsrId($temporary_usr_id) {
		$this->temporary_usr_id = $temporary_usr_id;
	}


	/**
	 * @return bool
	 */
	public function isTakenOver() {
		return (isset($_SESSION[self::USR_ID_BACKUP]));
	}


	/**
	 * @param $usr_id
	 */
	public function takeOver($usr_id) {
		global $ilUser, $ilLog;
		$this->checkAccess($ilUser->getId());
		$this->setTemporaryUsrId($usr_id);
		$this->setOriginalUsrId($ilUser->getId());
		$pl = ilUserTakeOverPlugin::getInstance();
		$_SESSION[self::USR_ID_GLOBAL] = $this->getTemporaryUsrId();
		$_SESSION[self::USR_ID_BACKUP] = $this->getOriginalUsrId();

		$ilObjUser = new ilObjUser($this->getTemporaryUsrId());

		$ilLog->write('Plugin usrto: ' . $ilUser->getLogin() . ' has taken over the user view of ' . $ilObjUser->getLogin());

		ilUtil::sendSuccess(sprintf($pl->txt('user_taker_over_success'), $ilObjUser->getLogin()), true);
		ilUtil::redirect('login.php');
	}


	public function switchBack() {
		if ($_SESSION[self::USR_ID_BACKUP]) {
			$_SESSION[self::USR_ID_GLOBAL] = $_SESSION[self::USR_ID_BACKUP];

			$ilObjUser = new ilObjUser($_SESSION[self::USR_ID_BACKUP]);
			unset($_SESSION[self::USR_ID_BACKUP]);
			global $ilUser, $ilLog;
			$pl = ilUserTakeOverPlugin::getInstance();

			$ilLog->write('Plugin usrto: ' . $ilObjUser->getLogin() . ' has left the user view of ' . $ilUser->getLogin());

			ilUtil::sendSuccess(sprintf($pl->txt('user_taker_back_success'), $ilObjUser->getLogin()), true);
			ilUtil::redirect('login.php');
		}
	}


	/**
	 * @param $usr_id
	 * @return bool
	 */
	protected function checkAccess($usr_id) {
		global $rbacreview;
		$pl = ilUserTakeOverPlugin::getInstance();
		if (!isset($usr_id) || !in_array(2, $rbacreview->assignedGlobalRoles($usr_id))) {
			ilUtil::sendFailure($pl->txt('no_permission'), true);
			ilUtil::redirect('login.php');

			return false;
		}
	}
}
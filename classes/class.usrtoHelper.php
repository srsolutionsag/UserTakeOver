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
	const USR_ID_AUTHSESSION = '_authsession_user_id';
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
	protected $original_usr_id = 0;
	/**
	 * @var int
	 */
	protected $temporary_usr_id = 0;


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
	public function takeOver($usr_id, $track = true) {
		global $ilUser, $ilLog;
		$this->checkAccess($ilUser->getId(), $usr_id);
		$this->setTemporaryUsrId($usr_id);
		$this->setOriginalUsrId($ilUser->getId());
		$pl = ilUserTakeOverPlugin::getInstance();
		$_SESSION[self::USR_ID_GLOBAL] = $this->getTemporaryUsrId();
		$_SESSION[self::USR_ID_AUTHSESSION] = $this->getTemporaryUsrId();
		if($track == true)
			$_SESSION[self::USR_ID_BACKUP] = $this->getOriginalUsrId();

		$ilObjUser = new ilObjUser($this->getTemporaryUsrId());

		$ilLog->write('Plugin usrto: ' . $ilUser->getLogin() . ' has taken over the user view of ' . $ilObjUser->getLogin());

		ilUtil::sendSuccess(sprintf($pl->txt('user_taker_over_success'), $ilObjUser->getLogin()), true);
		ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSelectedItems');
	}

	/**
	 * swiches the user-session back
	 */
	public function switchBack() {
		if ($_SESSION[self::USR_ID_BACKUP]) {
			$_SESSION[self::USR_ID_GLOBAL] = $_SESSION[self::USR_ID_BACKUP];
			$_SESSION[self::USR_ID_AUTHSESSION] = $_SESSION[self::USR_ID_BACKUP];

			$pl = ilUserTakeOverPlugin::getInstance();
			ilUtil::sendSuccess(sprintf($pl->txt('user_taker_back_success'), ilObjUser::_lookupLogin($_SESSION[self::USR_ID_BACKUP])), true);
			unset($_SESSION[self::USR_ID_BACKUP]);
		}
		ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSelectedItems');
	}


	/**
	 * @param $usr_id
	 * @param $take_over_id
	 * @return bool
	 */
	protected function checkAccess($usr_id, $take_over_id) {
		global $rbacreview;
		$pl = ilUserTakeOverPlugin::getInstance();

		// If they are both in the Demo Group then it's fine.
		/** @var ilUserTakeOverConfig $config */
		$config = ilUserTakeOverConfig::first();
		$demo_group = $config->getDemoGroup();
		if(in_array($usr_id, $demo_group) && in_array($take_over_id, $demo_group))
			return true;

        $portal_admin = false;
        foreach($rbacreview->assignedGlobalRoles($usr_id) as $role){
            $title = ilObject::_lookupTitle($role);
            if($title === 'Portal Administration' || $title === 'Portal Administrator'){
                $portal_admin = true;
            }
        }
		// If the user taking over is of id 13? or is not in the admin role he does not have permission.
		if (!isset($usr_id) || $usr_id == 13 || !in_array(2, $rbacreview->assignedGlobalRoles($usr_id)) && ! $portal_admin) {
			ilUtil::sendFailure($pl->txt('no_permission'), true);
			ilUtil::redirect('login.php');

			return false;
		}
	}
}
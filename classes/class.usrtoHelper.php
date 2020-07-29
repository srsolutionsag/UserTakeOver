<?php

require_once __DIR__ . "/../vendor/autoload.php";

use srag\DIC\UserTakeOver\DICTrait;

/**
 * Class usrtoHelper
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class usrtoHelper
{

    use DICTrait;

    const USR_ID_GLOBAL = 'AccountId';
    const USR_ID_AUTHSESSION = '_authsession_user_id';
    const USR_ID_BACKUP = 'usrtoOriginalAccountId';
    const USR_ID = 'usr_id';
    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;
    /**
     * @var usrtoHelper
     */
    protected static $instance;

    /**
     * @return usrtoHelper
     */
    public static function getInstance()
    {
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
    public function getOriginalUsrId()
    {
        return $this->original_usr_id;
    }

    /**
     * @param int $original_usr_id
     */
    public function setOriginalUsrId($original_usr_id)
    {
        $this->original_usr_id = $original_usr_id;
    }

    /**
     * @return int
     */
    public function getTemporaryUsrId()
    {
        return $this->temporary_usr_id;
    }

    /**
     * @param int $temporary_usr_id
     */
    public function setTemporaryUsrId($temporary_usr_id)
    {
        $this->temporary_usr_id = $temporary_usr_id;
    }

    /**
     * @return bool
     */
    public function isTakenOver()
    {
        return (isset($_SESSION[self::USR_ID_BACKUP]));
    }

    /**
     * @param int     $usr_id
     * @param boolean $track
     * @param int     $group_id
     */
    public function takeOver($usr_id, $track = true, $group_id = 0)
    {
        $this->checkAccess(self::dic()->user()->getId(), $usr_id, $group_id);
        $this->setTemporaryUsrId($usr_id);
        $this->setOriginalUsrId(self::dic()->user()->getId());
        $_SESSION[self::USR_ID_GLOBAL]      = $this->getTemporaryUsrId();
        $_SESSION[self::USR_ID_AUTHSESSION] = $this->getTemporaryUsrId();
        if ($track == true) {
            /*
            This condition makes sure that if a user is in a group
             and he switches between multiple group members that he is logged in as originally
            logged in user again if he leaves user view
            */
            if (!isset($_SESSION[self::USR_ID_BACKUP])) {
                $_SESSION[self::USR_ID_BACKUP] = $this->getOriginalUsrId();
            }
        }

        $ilObjUser = new ilObjUser($this->getTemporaryUsrId());

        self::dic()->log()->write('Plugin usrto: ' . self::dic()->user()->getLogin() . ' has taken over the user view of ' . $ilObjUser->getLogin());

        ilUtil::sendSuccess(self::plugin()->translate('user_taker_over_success', "", [$ilObjUser->getLogin()]), true);
        ilUtil::redirect('ilias.php?baseClass=' . ilPersonalDesktopGUI::class . '&cmd=jumpToSelectedItems');
    }

    /**
     * swiches the user-session back
     */
    public function switchBack()
    {
        if ($_SESSION[self::USR_ID_BACKUP]) {
            $_SESSION[self::USR_ID_GLOBAL]      = $_SESSION[self::USR_ID_BACKUP];
            $_SESSION[self::USR_ID_AUTHSESSION] = $_SESSION[self::USR_ID_BACKUP];

            ilUtil::sendSuccess(self::plugin()
                                    ->translate('user_taker_back_success', "", [ilObjUser::_lookupLogin($_SESSION[self::USR_ID_BACKUP])]), true);
            unset($_SESSION[self::USR_ID_BACKUP]);
        }
        ilUtil::redirect('ilias.php?baseClass=' . ilPersonalDesktopGUI::class . '&cmd=jumpToSelectedItems');
    }

    /**
     * @param int $usr_id
     * @param int $take_over_id
     * @param int $group_id
     * @return bool
     */
    protected function checkAccess($usr_id, $take_over_id, $group_id)
    {
        // If they are both in the same group then it's fine.
        $user_ids = \usrtoMember::where(["group_id" => $group_id], "=")->getArray(null, "user_id");
        if (in_array($usr_id, $user_ids) && in_array($take_over_id, $user_ids)) {
            return true;
        }

        // If the user taking over is of id 13? or is not in the admin role he does not have permission.
        if (!isset($usr_id) || $usr_id == 13 || !in_array(2, self::dic()->rbacreview()->assignedGlobalRoles($usr_id))) {
            ilUtil::sendFailure(self::plugin()->translate('no_permission'), true);
            ilUtil::redirect('login.php');

            return false;
        }
    }
}

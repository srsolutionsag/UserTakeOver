<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/classes/class.usrtoGUI.php';

/**
 * Class ilUserTakeOverUIHookGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id$
 * @ingroup ServicesUIComponent
 */
class ilUserTakeOverUIHookGUI extends ilUIHookPluginGUI {

	/**
	 * @var array
	 */
	protected static $loaded = array();


	/**
	 * @param $key
	 *
	 * @return bool
	 */
	protected static function isLoaded($key) {
		return self::$loaded[$key] == 1;
	}


	/**
	 * @param $key
	 */
	protected static function setLoaded($key) {
		self::$loaded[$key] = 1;
	}


	/**
	 * @var int
	 */
	protected static $num = 0;


	/**
	 * @param       $a_comp
	 * @param       $a_part
	 * @param array $a_par
	 *
	 * @return array
	 */
	public function getHTML($a_comp, $a_part, $a_par = array()) {
		/**
		 * @var $ilCtrl     ilCtrl
		 * @var $tpl        ilTemplate
		 * @var $ilToolbar  ilToolbarGUI
		 * @var $rbacreview ilRbacReview
		 * @var $ilUser     ilObjUser
		 */
		global $ilCtrl;

		if ($a_comp == 'Services/MainMenu') {
			if ($_SESSION['usrtoOriginalAccountId']) {
				$ilToolbar = new ilToolbarGUI();
				if (!self::isLoaded('user_take_back')) {
					if ($ilToolbar instanceof ilToolbarGUI) {
						$ilUserTakeOverPlugin = ilUserTakeOverPlugin::getInstance();
						$ilCtrl->setCmd(usrtoGUI::CMD_PERFORM_USER_TAKE_BACK);
						$ilCtrl->setParameterByClass('usrtoGUI', 'cmd', 'performUserTakeBack');
						$link = $ilCtrl->getLinkTargetByClass(array( 'ilAdministrationGUI', 'ilRouterGUI', 'usrtoGUI' ));

						$html = '<a class="MMInactive" id="leave_user_view" href="' . $link . '">' . $ilUserTakeOverPlugin->txt("leave_user_view")
							. '</a>';
						self::setLoaded('user_take_back');

						return array( "mode" => ilUIHookPluginGUI::APPEND, "html" => $html );
					}
				}
			}
		}

		if (!self::isLoaded('user_take_over')) {
			if ($_GET['cmdClass'] == 'ilobjusergui' AND ($_GET['cmd'] == 'view' OR $_GET['cmd'] == 'edit')) {
				global $rbacreview, $ilUser;
				// Only Administrators
				if (!in_array(2, $rbacreview->assignedGlobalRoles($ilUser->getId()))) {
					self::setLoaded('user_take_over');

					return false;
				}

				global $ilToolbar;
				if ($ilToolbar instanceof ilToolbarGUI) {
					$ilUserTakeOverPlugin = ilUserTakeOverPlugin::getInstance();
					$ilCtrl->setParameterByClass('usrtoGUI', usrtoGUI::USR_ID, $_GET['obj_id']);
					$link = $ilCtrl->getLinkTargetByClass(array( 'ilAdministrationGUI', 'ilRouterGUI', 'usrtoGUI' ));
					// TODO: Refactor in ILIAS 5.0: ilLinkButton::getInstance(); and $ilToolbar->addButtonInstance();
					$ilToolbar->addButton($ilUserTakeOverPlugin->txt('take_over_user_view'), $link, '', '', 'take_over_user_view');
					self::setLoaded('user_take_over');
				}
			}
		}
	}
}

?>
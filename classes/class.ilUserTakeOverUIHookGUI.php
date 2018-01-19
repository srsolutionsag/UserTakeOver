<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/classes/class.usrtoHelper.php');
require_once("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/classes/class.ilUserTakeOverConfig.php");

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
	 * @var ilObjUser
	 */
	protected $user;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	public function __construct() {
		global $DIC;
		$this->user = $DIC->user();
		$this->ctrl = $DIC->ctrl();
	}


	/**
	 * @param       $a_comp
	 * @param       $a_part
	 * @param array $a_par
	 *
	 * @return array
	 */
	public function getHTML($a_comp, $a_part, $a_par = array()) {
		global $DIC;
		if ($a_comp == 'Services/MainMenu' && $a_part=='main_menu_search') {
			if (!self::isLoaded('user_take_over')) {
				$html = '';
				/** @var ilUserTakeOverConfig $config */
				$config = ilUserTakeOverConfig::first();
				/////////////////// FOR EXITING THE VIEW ///////////////////////
				if ($_SESSION[usrtoHelper::USR_ID_BACKUP]) {
						$html .= $this->takeBackHtml();
				}

				/////////// For the Demo Group //////////////////
				if (in_array($this->user->getId(), $config->getDemoGroup())) {
					$html .= $this->getDemoGroupHtml($config, $this->user);
				}
				
				$rbacreview = $DIC->rbac()->review();
				// If we are admin
				/** Some Async requests wont instanciate rbacreview. Thus we just terminate. */
				if (($rbacreview instanceof ilRbacReview) && in_array(2, $rbacreview->assignedGlobalRoles($this->user->getId()))) {
					///////////////// IN THE USER ADMINISTRATION /////////////////
					$this->initTakeOverToolbar($DIC->toolbar());

					if(!in_array($this->user->getId(), $config->getDemoGroup()))
						//////////////TOP BAR /////////////
						$html .= $this->getTopBarHtml();
				}

				self::setLoaded('user_take_over'); // Main Menu gets called multiple times so we statically save that we already did all that is needed.
				return array("mode" => ilUIHookPluginGUI::PREPEND, "html" => $html);
			} else {
				return array('mode' => ilUIHookPluginGUI::KEEP, "html" => '');
			}

		}
	}


	public function gotoHook() {
		if (preg_match("/usr_takeover_(.*)/uim", $_GET['target'], $matches)) {
			$track = (int)$_GET['track'];
			usrtoHelper::getInstance()->takeOver((int)$matches[1], $track === 1);
		}
		if (preg_match("/usr_takeback/uim", $_GET['target'], $matches)) {
			usrtoHelper::getInstance()->switchBack();
		}
	}

	/**
	 * @return array
	 * @internal param $a_comp
	 */
	protected function getTopBarHtml() {
		$plugin = new ilUserTakeOverPlugin();
		$template = $plugin->getTemplate("tpl.MMUserTakeOver.html", false, false);
		$template->setVariable("SEARCHUSERLINK", $this->ctrl->getLinkTargetByClass(array("ilUIPluginRouterGUI", "ilUserTakeOverConfigGUI"), "searchUsers"));
		// If we already switched user we want to set the backup id to the new takeover but keep the one to the original user.
		if (!$_SESSION[usrtoHelper::USR_ID_BACKUP]) {
			$track = 1;
		} else {
			$track = 0;
		}
		$template->setVariable("TAKEOVERPREFIX", "goto.php?track=$track&target=usr_takeover_");
		$template->setVariable("LOADING_TEXT", $plugin->txt("loading"));
		$template->setVariable("NO_RESULTS", $plugin->txt("no_results"));
		self::setLoaded('user_take_over');
		$html = $template->get();

		$html = '<li>' . $html . '</li>';

		return $html;

	}

	/**
	 * @param $config
	 * @param $ilUser
	 * @return string
	 */
	protected function getDemoGroupHtml($config, $ilUser) {
		$inner_html = "";
		foreach ($config->getDemoGroup() as $userId) {
			$user = new ilObjUser($userId);
			$b = "";
			if ($userId == $ilUser->getId())
				$b = " style='font-weight: bold;'";
			$inner_html .= "<li>
								<a href=\"goto.php?track=0&target=usr_takeover_$userId\"$b>{$user->getPresentationTitle()}</a>
							</li>";
		}
		$tmpHtml = "<a href='#' class='dropdown-toggle' data-toggle='dropdown'>
								<b>{$ilUser->getPresentationTitle()}</b>
							</a>
							<ul class=\"dropdown-menu pull-right\" role=\"menu\">
							$inner_html
						</ul>";

		$tmpHtml = '<li>' . $tmpHtml . '</li>';
		return $tmpHtml;
	}

	/**
	 * @param $ilToolbar
	 * @return mixed
	 */
	protected function initTakeOverToolbar($ilToolbar) {
		if ($_GET['cmdClass'] == 'ilobjusergui' AND ($_GET['cmd'] == 'view' OR $_GET['cmd'] == 'edit')) {
			if ($ilToolbar instanceof ilToolbarGUI) {
				$ilUserTakeOverPlugin = ilUserTakeOverPlugin::getInstance();
				$link = 'goto.php?track=1&target=usr_takeover_' . $_GET['obj_id'];
				$button = ilLinkButton::getInstance();
				$button->setCaption($ilUserTakeOverPlugin->txt('take_over_user_view'),false);
				$button->setUrl($link);
				$ilToolbar->addButtonInstance($button);
				return $ilToolbar;
			}
			return $ilToolbar;
		}
		return $ilToolbar;
	}

	private function takeBackHtml() {

		$ilToolbar = new ilToolbarGUI();

		/**
		 * @var $ilPluginAdmin ilPluginAdmin
		 */
		if ($ilToolbar instanceof ilToolbarGUI) {

			$ilUserTakeOverPlugin = ilUserTakeOverPlugin::getInstance();
			$link = 'goto.php?target=usr_takeback';

			$tmpHtml = '<a class="MMInactive" id="leave_user_view" target="" href="' . $link . '">' . $ilUserTakeOverPlugin->txt("leave_user_view")
				. '</a>';

			$tmpHtml = '<li>' . $tmpHtml . '</li>';
			return $tmpHtml;
		}
		return '';
	}
}
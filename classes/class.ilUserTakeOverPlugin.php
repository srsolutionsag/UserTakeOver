<?php
include_once('./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php');

/**
 * ilUserTakeOverPlugin
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilUserTakeOverPlugin extends ilUserInterfaceHookPlugin {

	/**
	 * @var ilUserTakeOverPlugin
	 */
	protected static $instance;


	/**
	 * @return ilUserTakeOverPlugin
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * @return string
	 */
	public function getPluginName() {
		return 'UserTakeOver';
	}


	/**
	 * @param int $id
	 *
	 * @return ctrlmmEntryCtrl[]
	 */
	public static function getMenuEntries($id = 0) {
		if (!$_SESSION[usrtoHelper::USR_ID_BACKUP]) {
			return array();
		}

		$entries[$id] = array();
		$entries[0] = array();
		if (is_file('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/EntryTypes/Link/class.ctrlmmEntryLink.php')) {
			$ilUserTakeOverPlugin = ilUserTakeOverPlugin::getInstance();
			$hub_menu = new ctrlmmEntryLink();
			$hub_menu->setUrl('goto.php?target=usr_takeback');
			$hub_menu->setTitle($ilUserTakeOverPlugin->txt("leave_user_view"));
			$hub_menu->setPermissionType(ctrlmmMenu::PERM_NONE);
			$hub_menu->setTarget('');
			$hub_menu->setPlugin(true);

			$entries[0][] = $hub_menu;
		}

		return $entries[$id];
	}
}

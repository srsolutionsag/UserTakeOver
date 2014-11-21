<?php
include_once('./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php');

/**
 * ilUserTakeOverPlugin
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilUserTakeOverPlugin extends ilUserInterfaceHookPlugin {

	/**
	 * @return string
	 */
	public function getPluginName() {
		return 'UserTakeOver';
	}
}
?>

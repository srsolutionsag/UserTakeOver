<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . "/../vendor/autoload.php";

use srag\DIC\UserTakeOver\DICTrait;

/**
 * Class ilUserTakeOverUIHookGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id$
 * @ingroup ServicesUIComponent
 */
class ilUserTakeOverUIHookGUI extends ilUIHookPluginGUI
{

    use DICTrait;
    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;
    /**
     * @var array
     */
    protected static $loaded = [];


    /**
     * @param string $key
     *
     * @return bool
     *
     */
    protected static function isLoaded($key)
    {
        return self::$loaded[$key] == 1;
    }


    /**
     * @param string $key
     */
    protected static function setLoaded($key)
    {
        self::$loaded[$key] = 1;
    }


    /**
     * @var int
     */
    protected static $num = 0;


    /**
     * @param string $a_comp
     * @param string $a_part
     * @param array  $a_par
     *
     * @return array
     */
    public function getHTML($a_comp, $a_part, $a_par = [])
    {
        if ($a_comp == 'Services/MainMenu' && $a_part == 'main_menu_search') {
            if (!self::isLoaded('user_take_over')) {
                $html = '';
                /////////////////// FOR EXITING THE VIEW ///////////////////////
                if ($_SESSION[usrtoHelper::USR_ID_BACKUP]) {
                    $html .= $this->takeBackHtml();
                }

                // If we are admin
                /** Some Async requests wont instanciate rbacreview. Thus we just terminate. */
                if ((self::dic()->rbacreview() instanceof ilRbacReview)
                    && in_array(2, self::dic()->rbacreview()->assignedGlobalRoles(self::dic()->user()->getId()))
                ) {
                    ///////////////// IN THE USER ADMINISTRATION /////////////////
                    $this->initTakeOverToolbar(self::dic()->toolbar());
                }
                $html .= $this->getTopBarHtml();

                self::setLoaded('user_take_over'); // Main Menu gets called multiple times so we statically save that we already did all that is needed.

                return ["mode" => ilUIHookPluginGUI::PREPEND, "html" => $html];
            } else {
                return ['mode' => ilUIHookPluginGUI::KEEP, "html" => ''];
            }
        }
    }


    public function gotoHook()
    {
        if (preg_match("/usr_takeover_(.*)/uim", filter_input(INPUT_GET, 'target'), $matches)) {
            $track = (int) filter_input(INPUT_GET, 'track');
            $group_id = (int) filter_input(INPUT_GET, 'group_id');
            usrtoHelper::getInstance()->takeOver((int) $matches[1], $track === 1, $group_id);
        }
        if (preg_match("/usr_takeback/uim", filter_input(INPUT_GET, 'target'), $matches)) {
            usrtoHelper::getInstance()->switchBack();
        }
    }


    /**
     * @return array
     * @internal param $a_comp
     */
    protected function getTopBarHtml()
    {
        $template = self::plugin()->getPluginObject()->getTemplate("tpl.MMUserTakeOver.html", false, false);
        if (in_array(2, self::dic()->rbacreview()->assignedGlobalRoles(self::dic()->user()->getId()))) {
            $template->setVariable("SEARCHUSERLINK", self::dic()->ctrl()->getLinkTargetByClass([
                ilUIPluginRouterGUI::class,
                //ilUserTakeOverConfigGUI::class,
                ilUserTakeOverMembersGUI::class,
            ], ilUserTakeOverMembersGUI::CMD_SEARCH_USERS));
            // If we already switched user we want to set the backup id to the new takeover but keep the one to the original user.
            if (!$_SESSION[usrtoHelper::USR_ID_BACKUP]) {
                $track = 1;
            } else {
                $track = 0;
            }
            $template->setVariable("TAKEOVERPREFIX", "goto.php?track=$track&target=usr_takeover_");
            $template->setVariable("LOADING_TEXT", self::plugin()->translate("loading"));
            $template->setVariable("NO_RESULTS", self::plugin()->translate("no_results"));
            $template->setCurrentBlock("search");
            $template->setVariable("TXT_TAKE_OVER_USER", self::plugin()->translate("take_over_user"));
            $template->setVariable("SEARCH_INPUT", "<input class=\"srag-seach-input\" style='color: black;' type='text'/>");
            $template->parseCurrentBlock("search");
        }

        /////////// For the Groups //////////////////
        $group_ids = usrtoMember::where(["user_id" => self::dic()->user()->getId()], "=")->getArray(null, "group_id");

        //if the current user is member of at least one group render the groups html
        if (!empty($group_ids)) {
            $groups_html = $this->getGroupsHtml($group_ids, self::dic()->user());
        }
        //only group members or user with admin role can use search
        if (in_array(2, self::dic()->rbacreview()->assignedGlobalRoles(self::dic()->user()->getId())) || !empty($group_ids)) {
            $template->setCurrentBlock("DROPDOWN_TOGGLE");
            $template->setVariable("TOGGLE", "<a id=\"srag-toggle\" class=\"dropdown-toggle\"><span class=\"glyphicon glyphicon-eye-open\"><span class=\"caret\"></span></span></a>");
            $template->parseCurrentBlock();
        }
        $template->setVariable("GROUPS", $groups_html);
        self::setLoaded('user_take_over');
        $html = $template->get();

        $html = '<li>' . $html . '</li>';

        return $html;
    }


    /**
     * @param array     $group_ids
     * @param ilObjUser $ilUser
     *
     * @return string
     */
    protected function getGroupsHtml($group_ids, $ilUser)
    {
        $inner_html = "";
        foreach ($group_ids as $group_id) {
            $user_ids = \usrtoMember::where(["group_id" => $group_id], "=")->getArray(null, "user_id");
            $group = usrtoGroup::find($group_id);
            $inner_html .= "<li>
								<span style=\"font-weight: bold; padding-left: 10px\">{$group->getTitle()}</span>
							</li>";
            $group_id = $group->getId();
            foreach ($user_ids as $userId) {
                $user = new ilObjUser($userId);
                if ($userId == $ilUser->getId()) {
                    continue;
                }
                $inner_html .= "<li>
								<a href=\"goto.php?track=1&target=usr_takeover_$userId&group_id=$group_id\">{$user->getPresentationTitle()}</a>
							</li>";
            }
        }

        return $inner_html;
    }


    /**
     * @param ilToolbarGUI $ilToolbar
     *
     * @return mixed
     */
    protected function initTakeOverToolbar($ilToolbar)
    {
        if (strcasecmp(filter_input(INPUT_GET, 'cmdClass'), ilObjUserGUI::class) == 0 AND (filter_input(INPUT_GET, 'cmd') == 'view'
                OR filter_input(INPUT_GET, 'cmd') == 'edit')
        ) {
            if ($ilToolbar instanceof ilToolbarGUI) {
                $link = 'goto.php?track=1&target=usr_takeover_' . filter_input(INPUT_GET, 'obj_id');
                $button = ilLinkButton::getInstance();
                $button->setCaption(self::plugin()->translate('take_over_user_view'), false);
                $button->setUrl($link);
                $ilToolbar->addButtonInstance($button);

                return $ilToolbar;
            }

            return $ilToolbar;
        }

        return $ilToolbar;
    }


    private function takeBackHtml()
    {

        $ilToolbar = new ilToolbarGUI();

        /**
         * @var ilPluginAdmin $ilPluginAdmin
         */
        if ($ilToolbar instanceof ilToolbarGUI) {

            $link = 'goto.php?target=usr_takeback';

            /**
             * @author Jean-Luc Braun <braun@qualitus.de>
             */
            $tmpHtml = '<a class="dropdown-toggle" id="leave_user_view" target="" href="' . $link
                . '"><span class="glyphicon glyphicon-eye-close"></span></a>';

            $tmpHtml = '<li>' . $tmpHtml . '</li>';

            return $tmpHtml;
        }

        return '';
    }
}
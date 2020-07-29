<?php

namespace srag\Plugins\UserTakeOver\GlobalScreen;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarPluginProvider;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use srag\Plugins\UserTakeOver\Access;
use srag\Plugins\UserTakeOver\Handler;

class MetaBarProvider extends AbstractStaticMetaBarPluginProvider
{

    protected const ICON_BASE = './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/templates/';

    public function getMetaBarItems() : array
    {
        $helper  = new Handler();
        $access  = new Access($this->dic->user()->getId(), $helper->getLoadedOriginalId());
        $running = $access->isTakeOverRunning()();
        $txt     = function (string $id) : string {
            return $this->plugin->txt($id);
        };

        $if   = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };
        $icon = function (string $type, string $size = 'small') : Icon {
            return $this->dic->ui()->factory()->symbol()->icon()->custom(self::ICON_BASE . 'uto_' . $type . '.svg', 'Leave', $size);
        };

        $ui_f     = $this->dic->ui()->factory();
        $symbol_f = $ui_f->symbol();

        $children = [];

        // Leave
        if ($running) {
            $status     = new UTOStatusItem($if('uto_status'), $this->dic);
            $status     = $status->withTitle($txt('status'));
            $status     = $status->withSymbol($icon('info'));
            $status     = $status->withVisibilityCallable($access->isTakeOverRunning());
            $children[] = $status;

            $children[] = $this->meta_bar->linkItem($if('uto_leave'))
                                         ->withTitle($txt('leave_user_view'))
                                         ->withAction('goto.php?target=usr_takeback')
                                         ->withSymbol($icon('leave'));

        }

        if ($access->hasUserAccessToUserSearch()()) {
            // Search Field
            $search = new UTOSearchItem($if('uto_search'), $this->dic);
            $search = $search->withSymbol($icon('search'));
            $search = $search->withUrl($this->dic->ctrl()->getLinkTargetByClass([\ilUIPluginRouterGUI::class, \ilUserTakeOverGUI::class], \ilUserTakeOverGUI::CMD_SEARCH));
            $search = $search->withTitle($txt('take_over_user_view'));
            $search = $search->withVisibilityCallable($access->hasUserAccessToUserSearch());

            $children[] = $search;
        }

        // Groups
        if ($access->isUserAssignedToAGroup()()) {
            foreach (\usrtoGroup::getArray('id', 'title') as $id => $group) {

                $group_members = [];
                foreach (\usrtoMember::where(["group_id" => $id], "=")->leftjoin('usr_data', 'user_id', 'usr_id', ['login', 'firstname', 'lastname'])->getArray(null) as $user_data) {
                    $usr_id = $user_data['user_id'];

                    if ((int) $usr_id === (int) $this->dic->user()->getId()) {
                        continue;
                    }

                    $group_members[] = $this->meta_bar->linkItem($if('uto_group_' . $id . '_m_' . $usr_id))
                                                      ->withAction("goto.php?track=1&target=usr_takeover_{$usr_id}")
                                                      ->withSymbol($symbol_f->glyph()->user())
                                                      ->withTitle($user_data['usr_data_firstname'] . ' ' . $user_data['usr_data_lastname'] . ' (' . $user_data['usr_data_login'] . ')');
                }

                $children[] = $this->meta_bar->topParentItem($if('uto_group_' . $id))
                                             ->withChildren($group_members)
                                             ->withSymbol($icon('group'))
                                             ->withTitle($group);

            }
        }

        return [
            $this->meta_bar->topParentItem($if('uto'))
                           ->withChildren($children)
                           ->withTitle($txt('take_over_user_view'))
                           ->withSymbol($icon($running ? 'leave' : 'index', 'medium'))
                           ->withAvailableCallable($access->isUserTakeOverAvailableForUser())

        ];
    }

}

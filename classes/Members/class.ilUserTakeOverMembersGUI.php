<?php
require_once __DIR__ . "/../../vendor/autoload.php";

use srag\CustomInputGUIs\UserTakeOver\MultiSelectSearchNewInputGUI\UsersAjaxAutoCompleteCtrl;
use srag\DIC\UserTakeOver\DICTrait;
use srag\plugins\UserTakeOver\ilusrtoMultiSelectSearchInput2GUI;

/**
 * GUI class ilUserTakeOverMembersGUI
 * @author            : Benjamin Seglias   <bs@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilUserTakeOverMembersGUI: ilUIPluginRouterGUI
 * @ilCtrl_isCalledBy srag\CustomInputGUIs\UserTakeOver\MultiSelectSearchNewInputGUI\UsersAjaxAutoCompleteCtrl: ilUserTakeOverMembersGUI
 */
class ilUserTakeOverMembersGUI
{

    use DICTrait;

    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;
    const CMD_CONFIGURE = 'configure';
    const CMD_SAVE = 'save';
    const CMD_SEARCH_USERS = 'searchUsers';
    const CMD_CANCEL = 'cancel';

    public function executeCommand()
    {

        self::dic()->tabs()->clearTargets();
        $nextClass = self::dic()->ctrl()->getNextClass();
        switch ($nextClass) {
            case strtolower(UsersAjaxAutoCompleteCtrl::class):
                self::dic()->ctrl()->forwardCommand(new UsersAjaxAutoCompleteCtrl());
                break;
            default;
                $this->performCommand(self::dic()->ctrl()->getCmd());
                break;
        }
    }

    public function performCommand($cmd)
    {
        switch ($cmd) {
            case self::CMD_CONFIGURE:
            case self::CMD_SAVE:
                self::dic()->tabs()->setBackTarget(self::plugin()->translate('back'), self::dic()->ctrl()
                                                                                          ->getLinkTargetByClass(ilUserTakeOverGroupsGUI::class, ilUserTakeOverGroupsGUI::CMD_STANDARD));
                $this->$cmd();
                break;
            case self::CMD_SEARCH_USERS:
            case self::CMD_CANCEL:
                $this->$cmd();
                break;
            default:
                throw new ilException("command not defined.");
                break;
        }
    }

    public function configure()
    {
        self::dic()->ctrl()->saveParameterByClass(self::class, "usrtoGrp");
        $form = $this->getForm();
        $this->fillForm($form);
        self::output()->output($form);
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle(self::plugin()->translate("configuration"));

        /**
         * @var usrtoGroup $group
         */
        $group = usrtoGroup::find(filter_input(INPUT_GET, "usrtoGrp"));
        if (is_object($group)) {
            $title = $group->getTitle();
        } else {
            $title = self::plugin()->translate("group");
        }
        $input = new srag\CustomInputGUIs\UserTakeOver\MultiSelectSearchNewInputGUI\MultiSelectSearchNewInputGUI($title, 'grp[' . $group->getId() . ']');
        $input->setInfo(self::plugin()->translate("group_info"));
        $input->setAjaxAutoCompleteCtrl(new UsersAjaxAutoCompleteCtrl());

        $members_data = \usrtoMember::innerjoin('usr_data', 'user_id', 'usr_id')->where(["group_id" => filter_input(INPUT_GET, "usrtoGrp")], "=")
                                    ->getArray(null, ["usr_id", "firstname", "lastname", "login"]);
        $options      = [];
        foreach ($members_data as $member_data) {
            $options[$member_data['usr_id']] = $member_data['firstname'] . " " . $member_data['lastname'] . " (" . $member_data['login'] . ")";
        }
        $input->setOptions($options);
        $form->addItem($input);

        $this->initButtons($form);

        $form->setFormAction(self::dic()->ctrl()->getFormAction($this, self::CMD_SAVE));

        return $form;
    }

    /**
     * @param ilPropertyFormGUI &$form
     */
    protected function initButtons(&$form)
    {
        $form->addCommandButton(self::CMD_SAVE, self::plugin()->translate("save"));
        $form->addCommandButton(self::CMD_CANCEL, self::plugin()->translate("cancel"));
    }

    protected function cancel()
    {
        self::dic()->ctrl()->redirectByClass(ilUserTakeOverGroupsGUI::class, ilUserTakeOverGroupsGUI::CMD_STANDARD);
    }

    protected function save()
    {
        $form = $this->getForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $group_id       = filter_input(INPUT_GET, "usrtoGrp");
            $grp_user_array = filter_var(filter_input(INPUT_POST, "grp", FILTER_DEFAULT, FILTER_FORCE_ARRAY)[$group_id], FILTER_DEFAULT, FILTER_FORCE_ARRAY);
            foreach ($grp_user_array as $key => $user_id) {
                $usrtoMember = usrtoMember::where(['group_id' => $group_id, 'user_id' => $user_id], '=')->first();
                if (!empty($usrtoMember)) {
                    continue;
                } else {
                    $usrtoMember = new usrtoMember();
                    $usrtoMember->setGroupId($group_id);
                    $usrtoMember->setUserId($user_id);
                    $usrtoMember->store();
                }
            }
            $user_ids = \usrtoMember::where(["group_id" => filter_input(INPUT_GET, "usrtoGrp")], "=")->getArray(null, "user_id");
            //get ids of users who are not longer in the group
            $usr_ids_not_anymore_members = array_diff($user_ids, $grp_user_array);
            foreach ($usr_ids_not_anymore_members as $key => $usr_id) {
                $usrtoMember = usrtoMember::where(["user_id" => $usr_id])->first();
                $usrtoMember->delete();
            }
            ilUtil::sendSuccess(self::plugin()->translate("success"), true);
            self::dic()->ctrl()->saveParameterByClass(self::class, "usrtoGrp");
            self::dic()->ctrl()->redirect($this, self::CMD_CONFIGURE);
        } else {
            ilUtil::sendFailure(self::plugin()->translate("something_went_wrong"), true);
            self::output()->output($form);
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    protected function fillForm(&$form)
    {
        $user_ids = \usrtoMember::where(["group_id" => filter_input(INPUT_GET, "usrtoGrp")], "=")->getArray(null, "user_id");

        /** @var usrtoGroup $group */
        $group = usrtoGroup::find(filter_input(INPUT_GET, "usrtoGrp"));

        $values = [
            'grp[' . $group->getId() . ']' => implode(',', $user_ids),
        ];

        $form->setValuesByArray($values);
    }
}

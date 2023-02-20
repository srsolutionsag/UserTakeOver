<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use srag\plugins\UserTakeOver\ilusrtoMultiSelectSearchInput2GUI;
use srag\Plugins\UserTakeOver\Group\IGroupMemberRepository;
use srag\Plugins\UserTakeOver\Group\GroupRequestHelper;
use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\ArrayBasedRequestWrapper;
use srag\DIC\UserTakeOver\DICTrait;
use ILIAS\Refinery\Factory;

/**
 * @author       Benjamin Seglias <bs@studer-raimann.ch>
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverMembersGUI
{
    use GroupRequestHelper;
    use DICTrait;

    public const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

    public const CMD_CONFIGURE = 'configure';
    public const CMD_SAVE = 'save';
    public const CMD_SEARCH_USERS = 'searchUsers';
    public const CMD_CANCEL = 'cancel';

    /**
     * @var IGroupMemberRepository
     */
    protected $member_repository;
    /**
     * @var ArrayBasedRequestWrapper
     */
    protected $post_request;
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->member_repository = new ilUTOGroupMemberRepository($DIC->database());
        $this->group_repository = new ilUTOGroupRepository($DIC->database());

        $this->post_request = new ArrayBasedRequestWrapper(
            $DIC->http()->request()->getParsedBody()
        );

        $this->get_request = new ArrayBasedRequestWrapper(
            $DIC->http()->request()->getQueryParams()
        );

        $this->refinery = $DIC->refinery();
        $this->ctrl = $DIC->ctrl();
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_CONFIGURE:
            case self::CMD_SAVE:
                self::dic()->tabs()->setBackTarget(
                    self::plugin()->translate('back'),
                    $this->ctrl->getLinkTargetByClass(
                        [ilUserTakeOverMainGUI::class, ilUserTakeOverGroupsGUI::class],
                        ilUserTakeOverGroupsGUI::CMD_STANDARD
                    )
                );
            // no break
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
        $this->ctrl->saveParameterByClass(self::class, "usrtoGrp");
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

        $group = $this->getRequestedGroup($this->get_request, ilUserTakeOverGroupsGUI::PARAM_GROUP_ID);
        if (null !== $group) {
            $title = $group->getTitle();
        } else {
            $title = self::plugin()->translate("group");
        }

        $input = new ilusrtoMultiSelectSearchInput2GUI($title, 'grp[' . $group->getId() . ']');
        $input->setInfo(self::plugin()->translate("group_info"));
        $input->setAjaxLink(
            $this->ctrl->getLinkTargetByClass(
                [ilUserTakeOverMainGUI::class, self::class],
                self::CMD_SEARCH_USERS
            )
        );

        $members = (null !== $group) ? $this->member_repository->getGroupMembersOf($group) : [];

        $options = [];
        foreach ($members as $user_id) {
            $user = new ilObjUser(
                (ilObjUser::_exists($user_id)) ? $user_id : ANONYMOUS_USER_ID
            );

            $options[$user_id] = "{$user->getFirstname()} {$user->getLastname()} ({$user->getLogin()})";
        }

        $input->setOptions($options);
        $form->addItem($input);

        $this->initButtons($form);

        $form->setFormAction(
            $this->ctrl->getFormActionByClass(
                [ilUserTakeOverMainGUI::class, self::class],
                self::CMD_SAVE
            )
        );

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
        $this->ctrl->redirectByClass(
            [ilUserTakeOverMainGUI::class, ilUserTakeOverGroupsGUI::class],
            ilUserTakeOverGroupsGUI::CMD_STANDARD
        );
    }

    protected function save()
    {
        $form = $this->getForm();
        $form->setValuesByPost();

        $group = $this->getRequestedGroup($this->get_request, ilUserTakeOverGroupsGUI::PARAM_GROUP_ID);

        if (null === $group || null === ($group_id = $group->getId()) || !$form->checkInput()) {
            ilUtil::sendFailure(self::plugin()->translate("something_went_wrong"), true);
            self::output()->output($form);
        }

        /** @var $new_user_ids int[] */
        $new_user_ids = array_map(
            'intval',
            filter_var(
                filter_input(INPUT_POST, "grp", FILTER_DEFAULT, FILTER_FORCE_ARRAY)[$group_id],
                FILTER_DEFAULT,
                FILTER_FORCE_ARRAY
            )
        );

        $existing_members = $this->member_repository->getGroupMembersOf($group);

        foreach ($this->member_repository->getGroupMembersOf($group) as $existing_user_id) {
            $this->member_repository->removeGroupMemberFrom($existing_user_id, $group);
        }

        $this->member_repository->addGroupMembersTo($new_user_ids, $group);

        ilUtil::sendSuccess(self::plugin()->translate("success"), true);
        $this->ctrl->saveParameterByClass(self::class, "usrtoGrp");
        $this->ctrl->redirectByClass([ilUserTakeOverMainGUI::class, self::class], self::CMD_CONFIGURE);
    }

    /**
     * @param ilPropertyFormGUI $form
     */
    protected function fillForm(&$form)
    {
        $group = $this->getRequestedGroup($this->get_request, ilUserTakeOverGroupsGUI::PARAM_GROUP_ID);
        if (null === $group) {
            return;
        }

        $user_ids = $this->member_repository->getGroupMembersOf($group);

        $values = [
            'grp[' . $group->getId() . ']' => implode(',', $user_ids),
        ];

        $form->setValuesByArray($values);
    }

    protected function searchUsers()
    {
        $user_id = self::dic()->user()->getId();
        if (ilObjUserTakeOverAccess::isUserAssignedToConfiguredRole($user_id) ||
            ilObjUserTakeOverAccess::isUserAdministrator($user_id)
        ) {
            //when the search was done via select2 input field the term will be sent as array. In the search field it won't be sent as array.
            if (is_array($_GET['term'])) {
                $filtered_term = filter_input(INPUT_GET, "term", FILTER_DEFAULT, FILTER_FORCE_ARRAY)["term"];
            } else {
                $filtered_term = filter_input(INPUT_GET, "term", FILTER_DEFAULT);
            }
            $filtered_term = isset($filtered_term) ? $filtered_term : "";

            /** @var ilObjUser[] $users */
            $users = ilObjUser::searchUsers($filtered_term);
            $result = [];

            foreach ($users as $user) {
                $result[] = [
                    "id" => $user['usr_id'],
                    "text" => $user['firstname'] . " " . $user['lastname'] . " (" . $user['login'] . ")",
                ];
            }

            //self::plugin()->output($result, false);
            echo json_encode($result);
            exit;
        }

        echo json_encode([]);
        exit;
    }
}

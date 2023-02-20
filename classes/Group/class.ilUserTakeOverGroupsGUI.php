<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use srag\CustomInputGUIs\UserTakeOver\MultiSelectSearchNewInputGUI\UsersAjaxAutoCompleteCtrl;
use srag\Plugins\UserTakeOver\Group\Form\GroupFormProcessor;
use srag\Plugins\UserTakeOver\Group\Form\GroupFormBuilder;
use srag\Plugins\UserTakeOver\Group\IGroupMemberRepository;
use srag\Plugins\UserTakeOver\Group\GroupRequestHelper;
use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\Group\Group;
use srag\Plugins\UserTakeOver\ArrayBasedRequestWrapper;
use srag\Plugins\UserTakeOver\ITranslator;
use srag\DIC\UserTakeOver\DICTrait;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\Refinery\Factory;
use ILIAS\DI\RBACServices;

/**
 * @author            Benjamin Seglias <bs@studer-raimann.ch>
 * @author            Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy srag\CustomInputGUIs\UserTakeOver\MultiSelectSearchNewInputGUI\UsersAjaxAutoCompleteCtrl: ilUserTakeOverGroupsGUI
 *
 * @noinspection      AutoloadingIssuesInspection
 */
class ilUserTakeOverGroupsGUI
{
    use GroupRequestHelper;
    use DICTrait;

    public const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;
    public const PARAM_GROUP_ID = 'usrtoGrp';

    public const CMD_STANDARD = 'content';
    public const CMD_SAVE = 'save';
    public const CMD_EDIT = 'edit';
    public const CMD_CONFIRM = 'confirmDelete';
    public const CMD_DELETE = 'delete';
    public const CMD_CANCEL = 'cancel';
    public const CMD_APPLY_FILTER = 'applyFilter';
    public const CMD_RESET_FILTER = 'resetFilter';

    /**
     * @var IGroupMemberRepository
     */
    protected $member_repository;
    /**
     * @var ArrayBasedRequestWrapper
     */
    protected $post_request;
    /**
     * @var ServerRequestInterface
     */
    protected $request;
    /**
     * @var ITranslator
     */
    protected $translator;
    /**
     * @var \ILIAS\UI\Factory
     */
    protected $components;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var array<string, ilUserTakeOverARConfig>
     */
    protected $config;
    /**
     * @var RBACServices
     */
    protected $rbac;
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->group_repository = new ilUTOGroupRepository($DIC->database());
        $this->member_repository = new ilUTOGroupMemberRepository($DIC->database());

        $this->post_request = new ArrayBasedRequestWrapper(
            $DIC->http()->request()->getParsedBody()
        );

        $this->get_request = new ArrayBasedRequestWrapper(
            $DIC->http()->request()->getQueryParams()
        );

        $this->translator = ilUserTakeOverPlugin::getInstance();
        $this->components = $DIC->ui()->factory();
        $this->request = $DIC->http()->request();
        $this->refinery = $DIC->refinery();
        $this->toolbar = $DIC->toolbar();
        $this->config = ilUserTakeOverARConfig::get();
        $this->rbac = $DIC->rbac();
        $this->ctrl = $DIC->ctrl();
    }

    /**
     * dispatches the current command from ilCtrl.
     */
    public function executeCommand(): void
    {
        if (!ilObjUserTakeOverAccess::hasWriteAccess()) {
            ilUtil::sendFailure($this->translator->txt('permission_denied'), true);
            $this->ctrl->redirectByClass(ilObjComponentSettingsGUI::class, 'listPlugins');
        }

        $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
        switch ($cmd) {
            case self::CMD_STANDARD:
            case self::CMD_SAVE:
            case self::CMD_EDIT:
            case self::CMD_CONFIRM:
            case self::CMD_CANCEL:
            case self::CMD_DELETE:
            case self::CMD_APPLY_FILTER:
            case self::CMD_RESET_FILTER:
                $this->{$cmd}();
                break;
            default:
                throw new ilException("command not defined.");
                break;
        }
    }

    protected function content(): void
    {
        $this->toolbar->addComponent(
            $this->components->button()->standard(
                $this->translator->txt('add_grp'),
                $this->ctrl
                    ->getLinkTargetByClass(
                        self::class,
                        self::CMD_EDIT
                    )
            )
        );

        $ilUserTakeOverGroupsTableGUI = new ilUserTakeOverGroupsTableGUI(
            $this->group_repository,
            $this,
            self::CMD_STANDARD
        );

        self::output()->output($ilUserTakeOverGroupsTableGUI->getHTML());
    }

    protected function edit(): void
    {
        self::output()->output($this->getGroupForm());
    }

    protected function save(): void
    {
        $processor = $this->getGroupFormProcessor();
        if (!$processor->process()) {
            self::output()->output($processor->getProcessedForm());
            return;
        }

        ilUtil::sendSuccess($this->translator->txt('update_grp_msg_success'), true);
        $this->ctrl->redirectByClass(self::class, self::CMD_STANDARD);
    }

    protected function confirmDelete(): void
    {
        $group = $this->getRequestedGroup($this->get_request, self::PARAM_GROUP_ID);
        if (null === $group) {
            $this->cancel();
        }

        ilUtil::sendQuestion($this->translator->txt('confirm_delete_grp'), true);

        $confirm = new ilConfirmationGUI();
        $confirm->addItem(self::PARAM_GROUP_ID, $group->getId(), $group->getTitle());
        $confirm->setFormAction($this->ctrl->getFormActionByClass([ilUserTakeOverMainGUI::class, self::class]));
        $confirm->setCancel($this->translator->txt('cancel'), self::CMD_CANCEL);
        $confirm->setConfirm($this->translator->txt('delete'), self::CMD_DELETE);

        self::output()->output($confirm);
    }

    protected function delete(): void
    {
        $group = $this->getRequestedGroup($this->post_request, self::PARAM_GROUP_ID);
        if (null === $group) {
            $this->cancel();
        }

        foreach ($this->member_repository->getGroupMembersOf($group) as $user) {
            $this->member_repository->removeGroupMemberFrom($user, $group);
        }

        $this->group_repository->deleteGroup($group);

        $this->cancel();
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableGlobalRoles(): array
    {
        $global_roles = $this->rbac->review()->getRolesByFilter(ilRbacReview::FILTER_ALL_GLOBAL);
        $role_options = [];

        foreach ($global_roles as $role_data) {
            $role_id = (int) $role_data['obj_id'];

            if ((int) SYSTEM_ROLE_ID === $role_id) {
                continue;
            }

            $role_title = ilObjRole::_getTranslation($role_data['title']);
            $role_options[$role_id] = $role_title;
        }

        return $role_options;
    }

    protected function getGroupFormBuilder(): GroupFormBuilder
    {
        return new GroupFormBuilder(
            $this->components,
            $this->translator,
            $this->refinery,
            $this->getAvailableGlobalRoles(),
            $this->getRequestedGroup($this->get_request, self::PARAM_GROUP_ID) ?? $this->getBlankGroup()
        );
    }

    protected function getGroupFormProcessor(): GroupFormProcessor
    {
        return new GroupFormProcessor(
            $this->group_repository,
            $this->getRequestedGroup($this->get_request, self::PARAM_GROUP_ID) ?? $this->getBlankGroup(),
            $this->request,
            $this->getGroupForm()
        );
    }

    protected function getGroupForm(): Form
    {
        if (null !== ($group = $this->getRequestedGroup($this->get_request, self::PARAM_GROUP_ID))) {
            $this->ctrl->setParameterByClass(self::class, self::PARAM_GROUP_ID, $group->getId());
        }

        return $this->getGroupFormBuilder()->getForm(
            $this->ctrl->getFormActionByClass(self::class, self::CMD_SAVE)
        );
    }

    protected function getBlankGroup(): Group
    {
        return new Group(null, '');
    }

    protected function cancel(): void
    {
        $this->ctrl->redirectByClass([ilUserTakeOverMainGUI::class, self::class], self::CMD_STANDARD);
    }
}

<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\UI\Table\Group\GroupPresentationTableBuilder;
use srag\Plugins\UserTakeOver\UI\Form\Group\GroupFormBuilder;
use srag\Plugins\UserTakeOver\UI\Form\Group\GroupFormProcessor;
use srag\Plugins\UserTakeOver\UI\Form\TagInputAutoCompleteBinder;
use srag\Plugins\UserTakeOver\UI\Form\AbstractFormBuilder;
use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\Group\Group;
use srag\Plugins\UserTakeOver\IRequestParameters;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\UI\Component\Input\Container\Filter\Standard;
use ILIAS\UI\Component\Table\Presentation;
use ILIAS\UI\Component\Table\PresentationRow;
use ILIAS\UI\Factory as Components;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\GlobalHttpState;

/**
 * @author            Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @ilCtrl_IsCalledBy ilUserTakeOverGroupGUI: ilObjPluginDispatchGUI
 *
 * @noinspection      AutoloadingIssuesInspection
 */
class ilUserTakeOverGroupGUI extends ilUserTakeOverAbstractGUI
{
    use ilUserTakeOverGroupRequestHelper;

    public const CMD_SHOW = 'show';
    public const CMD_EDIT = 'edit';
    public const CMD_SAVE = 'save';
    public const CMD_DELETE = 'delete';
    public const CMD_FIND_MEMBERS = 'findMembers';
    public const CMD_FIND_TARGETS = 'findTargets';

    protected IGroupRepository $group_repository;
    protected ilUIFilterService $filter_service;
    protected GlobalHttpState $http;
    protected ilObjUser $current_user;

    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->http = $DIC->http();
        $this->current_user = $DIC->user();
        $this->group_repository = new ilUserTakeOverGroupRepository($DIC->database());
        $this->filter_service = (new ilUIService(
            $DIC->http()->request(),
            $DIC->ui(),
        ))->filter();
    }

    /**
     * @inheritDoc
     */
    protected function checkAccess(ilUserTakeOverAccessHandler $handler, string $command): bool
    {
        // metabar search autocomplete should be accessible for users permitted to
        // use the plugin.
        if ($command === self::CMD_FIND_TARGETS) {
            return $handler->canCurrentUserUsePlugin();
        }

        return $handler->hasCurrentUserWriteAccess();
    }

    /**
     * @inheritDoc
     */
    protected function setupPage(ilUserTakeOverTabManager $manager, ilToolbarGUI $toolbar, string $command): void
    {
        $manager->addAdministrationTabGroup();
        $manager->setGroupTab();

        if ($command === self::CMD_EDIT || $command === self::CMD_SAVE) {
            $manager->setBackTarget(
                $this->ctrl->getLinkTargetByClass(self::class, self::CMD_SHOW),
            );
        }

        if (self::CMD_SHOW === $command) {
            $toolbar->addComponent(
                $this->components->button()->primary(
                    $this->translator->txt(ITranslator::GROUP_ACTION_ADD),
                    $this->ctrl->getLinkTargetByClass(self::class, self::CMD_EDIT)
                )
            );
        }
    }

    /**
     * This method is the entrypoint of this controller, which shows lists all created
     * groups with an according filter to browse them quickly.
     */
    protected function show(): void
    {
        $filter = $this->getGroupTableFilter();

        $this->render([
            $filter,
            $this->getGroupPresentationTableBuilder()->getTable(
                $this->getVisibleGroups($filter)
            ),
        ]);
    }

    /**
     * This method will process the submitted form from edit() and save the group
     * in the database. If the form is invalid, it will be displayed with according
     * errors, otherwise this will redirect back to show().
     */
    protected function save(): void
    {
        $form_processor = $this->getGroupFormProcessor();
        if ($form_processor->processForm()) {
            $this->sendSuccess($this->translator->txt(ITranslator::MSG_GROUP_SUCCESS));
            $this->ctrl->redirectByClass(self::class, self::CMD_SHOW);
        }

        $this->render($form_processor->getProcessedForm());
    }

    /**
     * This method will show the form to edit or create a group, depending on the
     * request, which will eventually submit to save(). This endpoint will be called
     * by table- or toolbar-actions of show().
     */
    protected function edit(): void
    {
        $this->render($this->getGroupFormBuilder()->getForm());
    }

    /**
     * This method will try to delete the requested group and redirect back to show().
     * An according on-screen message will appear afterwards.
     */
    protected function delete(): void
    {
        $group = $this->getRequestedGroup($this->get_request);
        if (null !== $group) {
            $this->group_repository->deleteGroup($group);
            $this->sendSuccess($this->translator->txt(ITranslator::MSG_GROUP_DELETED));
        } else {
            $this->sendFailure($this->translator->txt(ITranslator::MSG_GROUP_NOT_FOUND));
        }

        $this->ctrl->redirectByClass(self::class, self::CMD_SHOW);
    }

    /**
     * This method is the endpoint for @see TagInputAutoCompleteBinder::getTagInputAutoCompleteBinder(),
     * which must return possible users for the given term.
     */
    protected function findMembers(): void
    {
        $term = $this->getRequestedString($this->get_request, IRequestParameters::SEARCH_TERM);

        if (null !== $term) {
            $body = Streams::ofString(json_encode($this->general_repository->findUsers($term)));
        } else {
            $body = Streams::ofString(json_encode([]));
        }

        $this->http->saveResponse(
            $this->http
                ->response()
                ->withBody($body)
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
        );

        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * This method is the endpoint for @see TagInputAutoCompleteBinder::getTagInputAutoCompleteBinder(),
     * which must return possible targets to impersonate for the given term.
     */
    protected function findTargets(): void
    {
        $term = $this->getRequestedString($this->get_request, IRequestParameters::SEARCH_TERM);

        $possible_targets = [];
        foreach ($this->general_repository->findUsers($term) as $user) {
            $user_id = (int) $user['value'];
            if ($user_id !== $this->current_user->getId() &&
                $this->access_handler->canCurrentUserImpersonate(
                    $this->general_repository->getUser($user_id)
                )
            ) {
                $possible_targets[] = $user;
            }
        }

        if (null !== $term) {
            $body = Streams::ofString(json_encode($possible_targets));
        } else {
            $body = Streams::ofString(json_encode([]));
        }

        $this->http->saveResponse(
            $this->http
                ->response()
                ->withBody($body)
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
        );

        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * @return Group[]
     */
    protected function getVisibleGroups(Standard $filter): array
    {
        $filter_data = $this->filter_service->getData($filter);
        if (null === $filter_data) {
            return $this->group_repository->getAllGroups();
        }

        return array_filter(
            $this->group_repository->getAllGroups(),
            function (Group $group) use ($filter_data): bool {
                $matches_title = (
                    empty($filter_data[ITranslator::GROUP_TITLE]) ||
                    false !== stripos($group->getTitle(), $filter_data[ITranslator::GROUP_TITLE])
                );

                $matches_amount = (
                    empty($filter_data[ITranslator::GROUP_FILTER_MIN_MEMBER_AMOUNT]) ||
                    (int) $filter_data[ITranslator::GROUP_FILTER_MIN_MEMBER_AMOUNT] < count(
                        $group->getGroupMembers()
                    )
                );

                return ($matches_title && $matches_amount);
            }
        );
    }

    protected function getGroupTableFilter(): Standard
    {
        return $this->filter_service->standard(
            self::class,
            $this->ctrl->getLinkTargetByClass(self::class, self::CMD_SHOW),
            [
                ITranslator::GROUP_TITLE => $this->components->input()->field()->text(
                    $this->translator->txt(ITranslator::GROUP_TITLE),
                ),

                ITranslator::GROUP_FILTER_MIN_MEMBER_AMOUNT => $this->components->input()->field()->numeric(
                    $this->translator->txt(ITranslator::GROUP_FILTER_MIN_MEMBER_AMOUNT),
                ),
            ],
            [true, true],
            true,
            true
        );
    }

    protected function getGroupPresentationTableBuilder(): GroupPresentationTableBuilder
    {
        return new GroupPresentationTableBuilder(
            $this->general_repository,
            $this->group_repository,
            $this->translator,
            $this->components,
            $this->ctrl,
        );
    }

    protected function getGroupFormBuilder(): GroupFormBuilder
    {
        $group = $this->getRequestedGroup($this->get_request) ?? new Group(null, '');
        $this->ctrl->setParameterByClass(self::class, IRequestParameters::GROUP_ID, $group->getId());

        return new GroupFormBuilder(
            $group,
            $this->general_repository->getAvailableGlobalRoles(),
            $this->translator,
            $this->components->input()->container()->form(),
            $this->components->input()->field(),
            $this->getRefinery(),
            $this->ctrl->getFormActionByClass(self::class, self::CMD_SAVE),
            $this->ctrl->getLinkTargetByClass(self::class, self::CMD_FIND_MEMBERS, null, true),
        );
    }

    protected function getGroupFormProcessor(): GroupFormProcessor
    {
        return new GroupFormProcessor(
            $this->group_repository,
            $this->getRequestedGroup($this->get_request) ?? new Group(null, ''),
            $this->request,
            $this->getGroupFormBuilder()->getForm()
        );
    }

    protected function getGroupRepository(): IGroupRepository
    {
        return $this->group_repository;
    }
}

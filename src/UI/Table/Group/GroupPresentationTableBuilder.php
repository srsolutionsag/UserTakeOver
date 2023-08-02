<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\UI\Table\Group;

use srag\Plugins\UserTakeOver\UI\Table\IPresentationTableBuilder;
use srag\Plugins\UserTakeOver\Group\IGroupMemberRepository;
use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\Group\Group;
use srag\Plugins\UserTakeOver\IGeneralRepository;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Table\PresentationRow;
use ILIAS\UI\Component\Table\Presentation;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Factory as Components;
use ILIAS\UI\Component\Listing\Descriptive;
use srag\Plugins\UserTakeOver\IRequestParameters;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupPresentationTableBuilder implements IPresentationTableBuilder
{
    use ComponentHelper;

    public function __construct(
        protected IGeneralRepository $general_repository,
        protected IGroupRepository $group_repository,
        protected ITranslator $translator,
        protected Components $components,
        protected \ilCtrlInterface $ctrl,
    ) {
    }

    /**
     * @param Group[] $visible_records
     */
    public function getTable(array $visible_records): Presentation
    {
        $this->checkArgListElements('visible_records', $visible_records, [Group::class]);

        $row_mapping = fn (
            PresentationRow $row,
            Group $record,
            Components $factory,
            mixed $environment
        ): PresentationRow => $row
            ->withHeadline($record->getTitle())
            ->withSubheadline($record->getDescription())
            ->withImportantFields([
                $this->translator->txt(ITranslator::GROUP_TABLE_MEMBER_AMOUNT) => count(
                    $record->getGroupMembers()
                ),
                $this->translator->txt(ITranslator::GROUP_TABLE_RESTRICTION) => $this->getGroupRestrictionStatus($record)
            ])->withContent(
                $this->getGroupMemberList($factory, $record)
            )->withAction(
                $this->getRowActions($factory, $record)
            );

        return $this->components->table()->presentation(
            $this->translator->txt('groups'),
            [],
            $row_mapping
        )->withData($visible_records);
    }

    protected function getRowActions(Components $factory, Group $record): Dropdown
    {
        $this->ctrl->setParameterByClass(
            \ilUserTakeOverGroupGUI::class,
            IRequestParameters::GROUP_ID,
            $record->getId()
        );

        $dropdown = $factory->dropdown()->standard(
            [
                $factory->button()->shy(
                    $this->translator->txt(ITranslator::GROUP_ACTION_EDIT),
                    $this->ctrl->getLinkTargetByClass(
                        \ilUserTakeOverGroupGUI::class,
                        \ilUserTakeOverGroupGUI::CMD_EDIT
                    )
                ),
                $factory->button()->shy(
                    $this->translator->txt(ITranslator::GROUP_ACTION_DELETE),
                    $this->ctrl->getLinkTargetByClass(
                        \ilUserTakeOverGroupGUI::class,
                        \ilUserTakeOverGroupGUI::CMD_DELETE
                    )
                ),
            ]
        );

        $this->ctrl->setParameterByClass(
            \ilUserTakeOverGroupGUI::class,
            IRequestParameters::GROUP_ID,
            null
        );

        return $dropdown;
    }

    protected function getGroupRestrictionStatus(Group $record): string
    {
        if (!$record->isRestrictedToMembers() && !$record->isRestrictedToRoles()) {
            return $this->translator->txt(ITranslator::GROUP_TABLE_RESTRICTION_STATUS_NONE);
        }

        $status = '';
        if ($record->isRestrictedToMembers()) {
            $status .= $this->translator->txt(ITranslator::GROUP_TABLE_RESTRICTION_STATUS_MEMBERS);
        }

        if ($record->isRestrictedToRoles()) {
            $status .= ('' === $status) ? '' : ' & ';
            $status .= $this->translator->txt(ITranslator::GROUP_TABLE_RESTRICTION_STATUS_ROLES);
        }

        return $status;
    }

    /**
     * Returns an unordered list titled with 'group members' listing all members like:
     * tfuhrer (Thibeau Fuhrer).
     */
    protected function getGroupMemberList(Components $factory, Group $record): Descriptive
    {
        $user_names = [];
        foreach ($record->getGroupMembers() as $user_id) {
            $user = $this->general_repository->getUser($user_id);
            $user_names[] = "{$user->getLogin()} ({$user->getFirstname()} {$user->getLastname()})";
        }

        if (empty($user_names)) {
            $user_names[] = $this->translator->txt(ITranslator::GROUP_TABLE_NO_MEMBERS);
        }

        return $factory->listing()->descriptive([
            $this->translator->txt(ITranslator::GROUP_MEMBERS) => $this->components->listing()->unordered($user_names),
        ]);
    }
}

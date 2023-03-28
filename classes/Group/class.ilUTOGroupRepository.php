<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\Group\Group;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUTOGroupRepository implements IGroupRepository
{
    protected const SEPARATOR = ',';

    /**
     * @var ilDBInterface
     */
    protected $database;

    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    public function storeGroup(Group $group): IGroupRepository
    {
        if (null === ($group_id = $group->getId()) || null === $this->getGroup($group_id)) {
            $this->insertGroup($group);
        } else {
            $this->updateGroup($group);
        }

        return $this;
    }

    public function getGroup(int $group_id): ?Group
    {
        $results = $this->database->fetchAll(
            $this->database->queryF(
                "SELECT id, title, description, restrict_to_members, allowed_roles FROM ui_uihk_usrto_grp WHERE id = %s;",
                [ilDBConstants::T_INTEGER],
                [$group_id]
            )
        );

        if (empty($results)) {
            return null;
        }

        return $this->queryResultToGroup($results[0]);
    }

    /**
     * @inheritDoc
     */
    public function getGroupsOfUser(int $user_id): array
    {
        $results = $this->database->fetchAll(
            $this->database->queryF(
                "SELECT grp.id, grp.title, grp.description, grp.restrict_to_members, grp.allowed_roles FROM ui_uihk_usrto_grp AS grp
                    JOIN ui_uihk_usrto_member AS mem ON mem.group_id = grp.id
                    WHERE mem.user_id = %s;",
                [ilDBConstants::T_INTEGER],
                [$user_id]
            )
        );

        $groups = [];
        foreach ($results as $query_result) {
            $groups[] = $this->queryResultToGroup($query_result);
        }

        return $groups;
    }

    /**
     * @inheritDoc
     */
    public function getAllGroups(bool $array_data = false): array
    {
        $results = $this->database->fetchAll(
            $this->database->query(
                "SELECT id, title, description, restrict_to_members, allowed_roles FROM ui_uihk_usrto_grp;"
            )
        );

        if ($array_data) {
            return $results;
        }

        $groups = [];
        foreach ($results as $query_result) {
            $groups[] = $this->queryResultToGroup($query_result);
        }

        return $groups;
    }

    public function deleteGroup(Group $group): IGroupRepository
    {
        if (null === $group->getId()) {
            return $this;
        }

        $this->database->manipulateF(
            "DELETE grp, members FROM (SELECT %s AS group_id) AS deletable 
                LEFT OUTER JOIN ui_uihk_usrto_member AS members ON members.group_id = deletable.group_id
                LEFT OUTER JOIN ui_uihk_usrto_grp AS grp ON grp.id = deletable.group_id
            ;",
            [ilDBConstants::T_INTEGER],
            [$group->getId()]
        );

        return $this;
    }

    public function arrayToString(array $data): string
    {
        return implode(self::SEPARATOR, $data);
    }

    public function stringToArray(string $data): array
    {
        return explode(self::SEPARATOR, $data);
    }

    protected function insertGroup(Group $group): int
    {
        $next_id = (int) $this->database->nextId('ui_uihk_usrto_grp');

        $this->database->insert(
            'ui_uihk_usrto_grp',
            [
                Group::F_ID => [ilDBConstants::T_INTEGER, $next_id],
                Group::F_TITLE => [ilDBConstants::T_TEXT, $group->getTitle()],
                Group::F_DESCRIPTION => [ilDBConstants::T_TEXT, $group->getDescription()],
                Group::F_RESTRICT_TO_MEMBERS => [ilDBConstants::T_TEXT, $group->isRestrictedToMembers()],
                Group::F_ALLOWED_ROLES => [
                    ilDBConstants::T_TEXT,
                    ($group->isRestrictedToRoles()) ? $this->arrayToString($group->getAllowedRoles()) : null
                ],
            ]
        );

        return $next_id;
    }

    protected function updateGroup(Group $group): void
    {
        $this->database->update(
            'ui_uihk_usrto_grp',
            [
                Group::F_TITLE => [ilDBConstants::T_TEXT, $group->getTitle()],
                Group::F_DESCRIPTION => [ilDBConstants::T_TEXT, $group->getDescription()],
                Group::F_RESTRICT_TO_MEMBERS => [ilDBConstants::T_TEXT, $group->isRestrictedToMembers()],
                Group::F_ALLOWED_ROLES => [
                    ilDBConstants::T_TEXT,
                    ($group->isRestrictedToRoles()) ? $this->arrayToString($group->getAllowedRoles()) : null
                ],
            ],
            [
                Group::F_ID => [ilDBConstants::T_INTEGER, $group->getId()],
            ]
        );
    }

    protected function queryResultToGroup(array $query_result): Group
    {
        $allowed_roles = (null !== $query_result[Group::F_ALLOWED_ROLES]) ?
            array_map('intval', $this->stringToArray($query_result[Group::F_ALLOWED_ROLES])) :
            [];

        return new Group(
            (int) $query_result[Group::F_ID],
            (string) $query_result[Group::F_TITLE],
            (string) $query_result[Group::F_DESCRIPTION],
            $allowed_roles,
            (bool) $query_result[Group::F_RESTRICT_TO_MEMBERS],
        );
    }
}

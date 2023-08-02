<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\Group\Group;
use srag\Plugins\UserTakeOver\ArrayFieldHelper;
use srag\Plugins\UserTakeOver\Group\IGroupMemberRepository;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverGroupRepository implements IGroupRepository
{
    use ArrayFieldHelper;

    protected const F_ID = 'id';
    protected const F_USER_ID = 'user_id';
    protected const F_GROUP_ID = 'group_id';
    protected const F_TITLE = 'title';
    protected const F_DESCRIPTION = 'description';
    protected const F_RESTRICT_TO_MEMBERS = 'restrict_to_members';
    protected const F_ALLOWED_ROLES = 'allowed_roles';

    /**
     * @var array<int, Group[]>
     */
    protected static $user_group_cache = [];

    public function __construct(
        protected ilDBInterface $database,
    ) {
    }

    public function storeGroup(Group $group): self
    {
        if (null === ($group_id = $group->getId()) || null === $this->getGroup($group_id)) {
            $this->insertGroup($group);
        } else {
            $this->updateGroup($group);
        }

        return $this->setGroupMembersOf($group);
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
    public function getGroupsOfUser(int $user_id, bool $use_cache = false): array
    {
        if ($use_cache && isset(self::$user_group_cache[$user_id])) {
            return self::$user_group_cache[$user_id];
        }

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

        self::$user_group_cache[$user_id] = $groups;
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

    public function deleteGroup(Group $group): self
    {
        if (null === $group->getId()) {
            return $this;
        }

        $this->deleteGroupMembersOf($group);

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

    public function removeGroupMemberFrom(int $user_id, Group $group): self
    {
        if (null === ($group_id = $group->getId())) {
            return $this;
        }

        $this->database->manipulateF(
            "DELETE FROM ui_uihk_usrto_member WHERE user_id = %s AND group_id = %s;",
            [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
            [$user_id, $group_id]
        );

        return $this;
    }

    public function deleteGroupMembersOf(Group $group): IGroupRepository
    {
        return $this->setGroupMembersOf($group->setGroupMembers([]));
    }

    protected function getGroupMembersOf(int $group_id): array
    {
        $results = $this->database->fetchAll(
            $this->database->queryF(
                "SELECT user_id FROM ui_uihk_usrto_member WHERE group_id = %s;",
                [ilDBConstants::T_INTEGER],
                [$group_id]
            )
        );

        $user_ids = [];
        foreach ($results as $query_result) {
            $user_ids[] = (int) $query_result['user_id'];
        }

        return $user_ids;
    }

    protected function setGroupMembersOf(Group $group): self
    {
        if (null === $group->getId()) {
            return $this;
        }

        $this->database->queryF(
            "DELETE FROM ui_uihk_usrto_member WHERE group_id = %s;",
            [ilDBConstants::T_INTEGER],
            [$group->getId()]
        );

        $this->addGroupMembersTo($group);

        return $this;
    }

    protected function addGroupMembersTo(Group $group): self
    {
        if (null === ($group_id = $group->getId())) {
            return $this;
        }

        foreach ($group->getGroupMembers() as $user_id) {
            $next_id = $this->database->nextId('ui_uihk_usrto_member');

            $this->database->insert(
                'ui_uihk_usrto_member',
                [
                    self::F_ID => [ilDBConstants::T_INTEGER, $next_id],
                    self::F_USER_ID => [ilDBConstants::T_INTEGER, $user_id],
                    self::F_GROUP_ID => [ilDBConstants::T_INTEGER, $group_id],
                ]
            );
        }

        return $this;
    }

    protected function insertGroup(Group $group): int
    {
        $next_id = $this->database->nextId('ui_uihk_usrto_grp');

        $this->database->insert(
            'ui_uihk_usrto_grp',
            [
                self::F_ID => [ilDBConstants::T_INTEGER, $next_id],
                self::F_TITLE => [ilDBConstants::T_TEXT, $group->getTitle()],
                self::F_DESCRIPTION => [ilDBConstants::T_TEXT, $group->getDescription()],
                self::F_RESTRICT_TO_MEMBERS => [ilDBConstants::T_INTEGER, (int) $group->isRestrictedToMembers()],
                self::F_ALLOWED_ROLES => [
                    ilDBConstants::T_TEXT,
                    ($group->isRestrictedToRoles()) ? $this->arrayToString($group->getAllowedRoles()) : null
                ],
            ]
        );

        $group->setId($next_id);

        return $next_id;
    }

    protected function updateGroup(Group $group): void
    {
        $this->database->update(
            'ui_uihk_usrto_grp',
            [
                self::F_TITLE => [ilDBConstants::T_TEXT, $group->getTitle()],
                self::F_DESCRIPTION => [ilDBConstants::T_TEXT, $group->getDescription()],
                self::F_RESTRICT_TO_MEMBERS => [ilDBConstants::T_INTEGER, (int) $group->isRestrictedToMembers()],
                self::F_ALLOWED_ROLES => [
                    ilDBConstants::T_TEXT,
                    ($group->isRestrictedToRoles()) ? $this->arrayToString($group->getAllowedRoles()) : null
                ],
            ],
            [
                self::F_ID => [ilDBConstants::T_INTEGER, $group->getId()],
            ]
        );
    }

    protected function queryResultToGroup(array $query_result): Group
    {
        $allowed_roles = (null !== $query_result[self::F_ALLOWED_ROLES]) ?
            array_map('intval', $this->stringToArray($query_result[self::F_ALLOWED_ROLES])) :
            [];

        return new Group(
            (int) $query_result[self::F_ID],
            (string) $query_result[self::F_TITLE],
            (string) $query_result[self::F_DESCRIPTION],
            $allowed_roles,
            (bool) $query_result[self::F_RESTRICT_TO_MEMBERS],
            $this->getGroupMembersOf((int) $query_result[self::F_ID])
        );
    }

    protected function getSeparator(): string
    {
        return ',';
    }
}

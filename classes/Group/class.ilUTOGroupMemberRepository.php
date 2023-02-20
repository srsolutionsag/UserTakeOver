<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\Group\IGroupMemberRepository;
use srag\Plugins\UserTakeOver\Group\Group;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUTOGroupMemberRepository implements IGroupMemberRepository
{
    protected const F_ID = 'id';
    protected const F_USER_ID = 'user_id';
    protected const F_GROUP_ID = 'group_id';

    /**
     * @var ilDBInterface
     */
    protected $database;

    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @inheritDoc
     */
    public function getGroupMembersOf(Group $group): array
    {
        if (null === ($group_id = $group->getId())) {
            return [];
        }

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

    /**
     * @inheritDoc
     */
    public function addGroupMembersTo(array $user_ids, Group $group): IGroupMemberRepository
    {
        if (null === ($group_id = $group->getId())) {
            return $this;
        }

        foreach ($user_ids as $user_id) {
            $next_id = (int) $this->database->nextId('ui_uihk_usrto_member');

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

    public function removeGroupMemberFrom(int $user_id, Group $group): IGroupMemberRepository
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
}

<?php

namespace srag\Plugins\UserTakeOver\Group;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IGroupMemberRepository
{
    /**
     * @return int[]
     */
    public function getGroupMembersOf(Group $group): array;

    /**
     * @param int[] $user_ids
     */
    public function addGroupMembersTo(array $user_ids, Group $group): self;

    public function removeGroupMemberFrom(int $user_id, Group $group): self;
}

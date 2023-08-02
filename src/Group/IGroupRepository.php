<?php

namespace srag\Plugins\UserTakeOver\Group;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IGroupRepository
{
    public function getGroup(int $group_id): ?Group;

    public function storeGroup(Group $group): self;

    /**
     * @return Group[]
     */
    public function getGroupsOfUser(int $user_id, bool $use_cache = false): array;

    /**
     * @return Group[]
     */
    public function getAllGroups(bool $array_data = false): array;

    public function deleteGroup(Group $group): self;

    public function removeGroupMemberFrom(int $user_id, Group $group): self;

    public function deleteGroupMembersOf(Group $group): self;
}

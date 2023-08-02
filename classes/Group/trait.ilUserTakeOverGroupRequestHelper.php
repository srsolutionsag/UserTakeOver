<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\Group\Group;
use srag\Plugins\UserTakeOver\RequestHelper;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use srag\Plugins\UserTakeOver\IRequestParameters;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
trait ilUserTakeOverGroupRequestHelper
{
    use RequestHelper;

    protected function getRequestedGroup(ArrayBasedRequestWrapper $request): ?Group
    {
        $group_id = $this->getRequestedInteger($request, IRequestParameters::GROUP_ID);
        if (null !== $group_id) {
            return $this->getGroupRepository()->getGroup($group_id);
        }

        return null;
    }

    abstract protected function getGroupRepository(): IGroupRepository;
}

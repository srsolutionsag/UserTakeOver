<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\Group;

use srag\Plugins\UserTakeOver\ArrayBasedRequestWrapper;
use ILIAS\Refinery\Factory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait GroupRequestHelper
{
    /**
     * @var IGroupRepository
     */
    protected $group_repository;
    /**
     * @var ArrayBasedRequestWrapper
     */
    protected $get_request;
    /**
     * @var Factory
     */
    protected $refinery;

    protected function getRequestedGroup(ArrayBasedRequestWrapper $request, string $parameter): ?Group
    {
        if (!$request->has($parameter)) {
            return null;
        }

        $group_id = $request->retrieve($parameter, $this->refinery->kindlyTo()->int());

        return $this->group_repository->getGroup($group_id);
    }
}

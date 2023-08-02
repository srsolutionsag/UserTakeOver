<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\UI\Form\Group;

use srag\Plugins\UserTakeOver\UI\Form\AbstractFormProcessor;
use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\Group\Group;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Form;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupFormProcessor extends AbstractFormProcessor
{
    public function __construct(
        protected IGroupRepository $group_repository,
        protected Group $group,
        ServerRequestInterface $request,
        Form $form
    ) {
        parent::__construct($request, $form);
    }

    /**
     * @inheritDoc
     */
    protected function isValid(array $post_data): bool
    {
        return isset($post_data[ITranslator::GROUP]);
    }

    /**
     * @inheritDoc
     */
    protected function processData(array $post_data): void
    {
        $group = $post_data[ITranslator::GROUP];

        $this->group
            ->setTitle($group[ITranslator::GROUP_TITLE])
            ->setDescription($group[ITranslator::GROUP_DESCRIPTION])
            ->setRestrictToMembers($group[ITranslator::GROUP_RESTRICTION_MEMBERS])
            ->setGroupMembers(array_map('intval', $group[ITranslator::GROUP_MEMBERS]));

        if (null !== $group[ITranslator::GROUP_RESTRICTION_ROLES] &&
            !empty($group[ITranslator::GROUP_RESTRICTION_ROLES][ITranslator::GROUP_ALLOWED_ROLES])
        ) {
            $this->group->setAllowedRoles(
                array_map('intval', $group[ITranslator::GROUP_RESTRICTION_ROLES][ITranslator::GROUP_ALLOWED_ROLES])
            );
        } else {
            // flush the allowed roles if the input has been disabled.
            $this->group->setAllowedRoles([]);
        }

        $this->group_repository->storeGroup($this->group);
    }
}

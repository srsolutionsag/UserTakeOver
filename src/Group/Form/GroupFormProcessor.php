<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\Group\Form;

use srag\Plugins\UserTakeOver\Group\IGroupRepository;
use srag\Plugins\UserTakeOver\Group\Group;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Form;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupFormProcessor
{
    /**
     * @var IGroupRepository
     */
    protected $group_repository;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var Form
     */
    protected $form;

    public function __construct(
        IGroupRepository $group_repository,
        Group $group,
        ServerRequestInterface $request,
        Form $form
    ) {
        $this->group_repository = $group_repository;
        $this->group = $group;
        $this->form = $form->withRequest($request);
    }

    public function process(): bool
    {
        $form_data = $this->form->getData();
        if (null === $form_data) {
            return false;
        }

        $this->group
            ->setTitle($form_data[Group::F_TITLE])
            ->setDescription($form_data[Group::F_DESCRIPTION])
            ->setRestrictToMembers($form_data[Group::F_RESTRICT_TO_MEMBERS]);

        if (null !== $form_data[GroupFormBuilder::I_OPTIONAL] &&
            !empty($form_data[GroupFormBuilder::I_OPTIONAL][Group::F_ALLOWED_ROLES])
        ) {
            $this->group->setAllowedRoles(
                array_map('intval', $form_data[GroupFormBuilder::I_OPTIONAL][Group::F_ALLOWED_ROLES])
            );
        } else {
            // flush the allowed roles if the input has been disabled.
            $this->group->setAllowedRoles([]);
        }

        $this->group_repository->storeGroup($this->group);

        return true;
    }

    public function getProcessedForm(): Form
    {
        return $this->form;
    }
}

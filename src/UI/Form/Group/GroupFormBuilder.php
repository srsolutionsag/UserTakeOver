<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\UI\Form\Group;

use srag\Plugins\UserTakeOver\UI\Form\AbstractFormBuilder;
use srag\Plugins\UserTakeOver\Group\Group;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupFormBuilder extends AbstractFormBuilder
{
    protected Group $group;
    protected array $global_roles;
    protected string $ajax_action;

    /**
     * @var array<int, string> $global_roles
     */
    public function __construct(
        Group $group,
        array $global_roles,
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Factory $refinery,
        string $form_action,
        string $ajax_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
        $this->group = $group;
        $this->global_roles = $global_roles;
        $this->ajax_action = $ajax_action;
    }

    public function getForm(): Form
    {
        $inputs = [];
        $inputs[ITranslator::GROUP_TITLE] = $this->fields->text(
            $this->translator->txt(ITranslator::GROUP_TITLE)
        )->withRequired(true)->withAdditionalTransformation(
            $this->getTextLengthValidationConstraint(1, Group::MAX_TITLE_LENGTH)
        )->withValue($this->group->getTitle());

        $inputs[ITranslator::GROUP_DESCRIPTION] = $this->fields->textarea(
            $this->translator->txt(ITranslator::GROUP_DESCRIPTION)
        )->withValue($this->group->getDescription());

        $inputs[ITranslator::GROUP_RESTRICTION_MEMBERS] = $this->fields->checkbox(
            $this->translator->txt(ITranslator::GROUP_RESTRICTION_MEMBERS),
            $this->translator->txt(ITranslator::GROUP_RESTRICTION_MEMBERS_INFO)
        )->withValue($this->group->isRestrictedToMembers());

        $inputs[ITranslator::GROUP_RESTRICTION_ROLES] = $this->fields->optionalGroup(
            [
                ITranslator::GROUP_ALLOWED_ROLES => $this->fields->multiSelect(
                    $this->translator->txt(ITranslator::GROUP_ALLOWED_ROLES),
                    $this->global_roles
                ),
            ],
            $this->translator->txt(ITranslator::GROUP_RESTRICTION_ROLES),
            $this->translator->txt(ITranslator::GROUP_RESTRICTION_ROLES_INFO)
        )->withValue(
            $this->group->isRestrictedToRoles() ? [
                ITranslator::GROUP_ALLOWED_ROLES => $this->group->getAllowedRoles(),
            ] : null
        );

        $inputs[ITranslator::GROUP_MEMBERS] = $this->fields->tag(
            $this->translator->txt(ITranslator::GROUP_MEMBERS),
            []
        )->withAdditionalOnLoadCode(
            $this->getTagInputAutoCompleteBinder($this->ajax_action)
        )->withValue(array_map('strval', $this->group->getGroupMembers()));

        return $this->forms->standard($this->form_action, [
            ITranslator::GROUP => $this->fields->section($inputs, $this->translator->txt(ITranslator::GROUP)),
        ]);
    }
}

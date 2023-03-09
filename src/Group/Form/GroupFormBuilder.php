<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\Group\Form;

use srag\Plugins\UserTakeOver\Group\Group;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class GroupFormBuilder
{
    public const I_OPTIONAL = 'restrict_to_roles';

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $components;
    /**
     * @var ITranslator
     */
    protected $translator;
    /**
     * @var Factory
     */
    protected $refinery;
    /**
     * @var array<int, string>
     */
    protected $global_roles;
    /**
     * @var Group
     */
    protected $group;

    /**
     * @param array<int, string> $global_roles
     */
    public function __construct(
        \ILIAS\UI\Factory $components,
        ITranslator $translator,
        Factory $refinery,
        array $global_roles,
        Group $group
    ) {
        $this->components = $components;
        $this->translator = $translator;
        $this->refinery = $refinery;
        $this->global_roles = $global_roles;
        $this->group = $group;
    }

    public function getForm(string $form_action): Form
    {
        $inputs[Group::F_TITLE] = $this->components->input()->field()->text(
            $this->translator->txt(Group::F_TITLE)
        )->withRequired(true)->withValue($this->group->getTitle());

        $inputs[Group::F_DESCRIPTION] = $this->components->input()->field()->textarea(
            $this->translator->txt(Group::F_DESCRIPTION)
        )->withValue($this->group->getDescription());

        $inputs[Group::F_RESTRICT_TO_MEMBERS] = $this->components->input()->field()->checkbox(
            $this->translator->txt(Group::F_RESTRICT_TO_MEMBERS),
            $this->translator->txt(Group::F_RESTRICT_TO_MEMBERS . '_info')
        )->withValue($this->group->isRestrictedToMembers());

        $inputs[self::I_OPTIONAL] = $this->components->input()->field()->optionalGroup([
            Group::F_ALLOWED_ROLES => $this->components->input()->field()->multiSelect(
                $this->translator->txt(Group::F_ALLOWED_ROLES),
                $this->global_roles
            ),
        ],
            $this->translator->txt(self::I_OPTIONAL),
            $this->translator->txt(self::I_OPTIONAL . '_info'),
        )->withValue(
            $this->group->isRestrictedToRoles() ? [
                Group::F_ALLOWED_ROLES => $this->group->getAllowedRoles(),
            ] : null
        );

        return $this->components->input()->container()->form()->standard($form_action, $inputs);
    }
}

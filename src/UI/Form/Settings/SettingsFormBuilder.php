<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\UI\Form\Settings;

use srag\Plugins\UserTakeOver\UI\Form\AbstractFormBuilder;
use srag\Plugins\UserTakeOver\Settings\Settings;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\Factory as FormFactory;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class SettingsFormBuilder extends AbstractFormBuilder
{
    protected Settings $settings;
    protected array $available_global_roles;

    /**
     * @param array<int, string> $available_global_roles
     */
    public function __construct(
        Settings $settings,
        array $available_global_roles,
        ITranslator $translator,
        FormFactory $forms,
        FieldFactory $fields,
        Refinery $refinery,
        string $form_action
    ) {
        parent::__construct($translator, $forms, $fields, $refinery, $form_action);
        $this->settings = $settings;
        $this->available_global_roles = $available_global_roles;
    }

    public function getForm(): UIForm
    {
        $inputs[ITranslator::SETTINGS_ALLOWED_GLOBAL_ROLES] = $this->fields->multiSelect(
            $this->translator->txt(ITranslator::SETTINGS_ALLOWED_GLOBAL_ROLES),
            $this->available_global_roles,
            $this->translator->txt(ITranslator::SETTINGS_ALLOWED_GLOBAL_ROLES_INFO),
        )->withValue($this->settings->getAllowedGlobalRoleIds());

        $inputs[ITranslator::SETTINGS_ALLOW_ADMIN_IMPERSONATION] = $this->fields->checkbox(
            $this->translator->txt(ITranslator::SETTINGS_ALLOW_ADMIN_IMPERSONATION),
            $this->translator->txt(ITranslator::SETTINGS_ALLOW_ADMIN_IMPERSONATION_INFO),
        )->withValue($this->settings->isAllowImpersonationOfAdmins());

        return $this->forms->standard($this->form_action, [
            ITranslator::SETTINGS => $this->fields->section($inputs, $this->translator->txt(ITranslator::SETTINGS)),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\UI\Form\Settings;

use srag\Plugins\UserTakeOver\UI\Form\AbstractFormProcessor;
use srag\Plugins\UserTakeOver\Settings\ISettingsRepository;
use srag\Plugins\UserTakeOver\Settings\Settings;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class SettingsFormProcessor extends AbstractFormProcessor
{
    protected ISettingsRepository $repository;
protected Settings $settings;

    public function __construct(
        ISettingsRepository $repository,
        Settings $settings,
        ServerRequestInterface $request,
        UIForm $form
    ) {
        parent::__construct($request, $form);
        $this->repository = $repository;
        $this->settings = $settings;
    }

    protected function isValid(array $post_data): bool
    {
        return isset($post_data[ITranslator::SETTINGS]);
    }

    protected function processData(array $post_data): void
    {
        $settings = $post_data[ITranslator::SETTINGS];

        if (null !== $settings[ITranslator::SETTINGS_ALLOWED_GLOBAL_ROLES]) {
            $this->settings->setAllowedGlobalRoleIds($settings[ITranslator::SETTINGS_ALLOWED_GLOBAL_ROLES]);
        } else {
            // flush the allowed global role ids.
            $this->settings->setAllowedGlobalRoleIds([]);
        }

        $this->settings->setAllowImpersonationOfAdmins(
            (bool) $settings[ITranslator::SETTINGS_ALLOW_ADMIN_IMPERSONATION]
        );

        $this->repository->store($this->settings);
    }
}

<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\UI\Form\Settings\SettingsFormProcessor;
use srag\Plugins\UserTakeOver\UI\Form\Settings\SettingsFormBuilder;
use srag\Plugins\UserTakeOver\ITranslator;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverSettingsGUI extends ilUserTakeOverAbstractGUI
{
    public const CMD_EDIT = 'edit';
    public const CMD_SAVE = 'save';

    /**
     * @inheritDoc
     */
    protected function checkAccess(ilUserTakeOverAccessHandler $handler, string $command): bool
    {
        return $handler->hasCurrentUserWriteAccess();
    }

    /**
     * @inheritDoc
     */
    protected function setupPage(ilUserTakeOverTabManager $manager, ilToolbarGUI $toolbar, string $command): void
    {
        $manager->addAdministrationTabGroup();
        $manager->setSettingsTab();
    }

    /**
     * This method is the endpoint of the edit() method and processes the settings
     * form. It either shows the form again with errors or redirects back to edit().
     */
    protected function save(): void
    {
        $processor = $this->getSettingsFormProcessor();
        if ($processor->processForm()) {
            $this->sendSuccess($this->translator->txt(ITranslator::MSG_SETTINGS_SUCCESS));
            $this->cancel();
        }

        $this->render($processor->getProcessedForm());
    }

    /**
     * This method is the entrypoint of this controller, which shows the settings
     * form on the current page. It submits to save(), which processes the form.
     */
    protected function edit(): void
    {
        $this->render($this->getSettingsFormBuilder()->getForm());
    }

    protected function getSettingsFormBuilder(): SettingsFormBuilder
    {
        return new SettingsFormBuilder(
            $this->settings_repository->get(),
            $this->general_repository->getAvailableGlobalRoles(),
            $this->translator,
            $this->components->input()->container()->form(),
            $this->components->input()->field(),
            $this->refinery,
            $this->ctrl->getFormActionByClass(self::class, self::CMD_SAVE),
        );
    }

    protected function getSettingsFormProcessor(): SettingsFormProcessor
    {
        return new SettingsFormProcessor(
            $this->settings_repository,
            $this->settings_repository->get(),
            $this->request,
            $this->getSettingsFormBuilder()->getForm()
        );
    }

    protected function cancel(): void
    {
        $this->ctrl->redirectByClass(self::class, self::CMD_EDIT);
    }
}

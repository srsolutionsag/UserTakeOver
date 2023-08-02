<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\ITranslator;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverTabManager
{
    public function __construct(
        protected ITranslator $translator,
        protected ilUserTakeOverAccessHandler $access_handler,
        protected ilCtrlInterface $ctrl,
        protected ilTabsGUI $tabs,
    ) {
    }

    public function addAdministrationTabGroup(): void
    {
        $this->addSettingsTab();
        $this->addGroupTab();
    }

    public function addSettingsTab(): void
    {
        if (!$this->access_handler->hasCurrentUserWriteAccess()) {
            return;
        }

        $this->tabs->addTab(
            ITranslator::SETTINGS,
            $this->translator->txt('settings'),
            $this->ctrl->getLinkTargetByClass(
                [
                    ilAdministrationGUI::class,
                    ilObjComponentSettingsGUI::class,
                    ilUserTakeOverConfigGUI::class,
                    ilUserTakeOverSettingsGUI::class
                ],
                ilUserTakeOverSettingsGUI::CMD_EDIT
            )
        );
    }

    public function setSettingsTab(): void
    {
        $this->tabs->activateTab(ITranslator::SETTINGS);
    }

    public function addGroupTab(): void
    {
        if (!$this->access_handler->hasCurrentUserWriteAccess()) {
            return;
        }

        $this->tabs->addTab(
            ITranslator::GROUP,
            $this->translator->txt('group'),
            $this->ctrl->getLinkTargetByClass(
                [
                    ilAdministrationGUI::class,
                    ilObjComponentSettingsGUI::class,
                    ilUserTakeOverConfigGUI::class,
                    ilUserTakeOverGroupGUI::class,
                ],
                ilUserTakeOverGroupGUI::CMD_SHOW
            )
        );
    }

    public function setGroupTab(): void
    {
        $this->tabs->activateTab(ITranslator::GROUP);
    }

    public function setBackTarget(string $target): void
    {
        $this->tabs->setBackTarget($this->translator->txt(ITranslator::GENERAL_ACTION_BACK), $target);
    }
}

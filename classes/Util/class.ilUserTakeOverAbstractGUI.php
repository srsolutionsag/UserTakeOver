<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\Settings\ISettingsRepository;
use srag\Plugins\UserTakeOver\IGeneralRepository;
use srag\Plugins\UserTakeOver\RequestHelper;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as Components;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
abstract class ilUserTakeOverAbstractGUI
{
    use ilUserTakeOverOnScreenMessages;
    use ilUserTakeOverTemplateHelper;
    use ilUserTakeOverPluginInstance;
    use RequestHelper;

    protected ISettingsRepository $settings_repository;
    protected IGeneralRepository $general_repository;
    protected ITranslator $translator;
    protected ServerRequestInterface $request;
    protected ArrayBasedRequestWrapper $get_request;
    protected Refinery $refinery;
    protected Components $components;
    protected Renderer $renderer;
    protected ilGlobalTemplateInterface $template;
    protected ilCtrlInterface $ctrl;
    protected ilUserTakeOverAccessHandler $access_handler;

    private ilUserTakeOverTabManager $tab_manager;
    private ilToolbarGUI $toolbar;

    public function __construct()
    {
        global $DIC;

        $plugin = $this->getPlugin($DIC);

        $this->settings_repository = new ilUserTakeOverSettingsRepository($DIC->database());
        $this->general_repository = new ilUserTakeOverGeneralRepository($DIC->rbac()->review());
        $this->access_handler = new ilUserTakeOverAccessHandler(
            new ilUserTakeOverGroupRepository($DIC->database()),
            $this->settings_repository->get(),
            $DIC->http()->wrapper()->query(),
            $DIC->refinery(),
            $DIC->user(),
            $DIC->rbac()->review(),
            $DIC->rbac()->system()
        );

        $this->tab_manager = new ilUserTakeOverTabManager(
            $plugin,
            $this->access_handler,
            $DIC->ctrl(),
            $DIC->tabs()
        );

        $this->translator = $plugin;
        $this->get_request = $DIC->http()->wrapper()->query();
        $this->request = $DIC->http()->request();
        $this->refinery = $DIC->refinery();
        $this->components = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->template = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->ctrl = $DIC->ctrl();
    }

    /**
     * Dispatches the requested command in the derived class. It also
     *      - checks if the current user is permitted to do so, and
     *      - sets up the page
     */
    public function executeCommand(): void
    {
        $command = $this->ctrl->getCmd() ?? '';
        if (!method_exists($this, $command)) {
            throw new LogicException(static::class . " does not implement $command().");
        }

        if (!$this->checkAccess($this->access_handler, $command)) {
            $this->permissionDenied();
        }

        $this->setupPage($this->tab_manager, $this->toolbar, $command);
        $this->{$command}();
    }

    /**
     * Redirects to the repository-root if the current user is not permitted to run the
     * requested command.
     */
    protected function permissionDenied(): void
    {
        $this->ctrl->redirectToURL(ilLink::_getStaticLink(1));
    }

    protected function getRefinery(): Refinery
    {
        return $this->refinery;
    }

    protected function getTemplate(): ilGlobalTemplateInterface
    {
        return $this->template;
    }

    protected function getRenderer(): Renderer
    {
        return $this->renderer;
    }

    /**
     * Must return if the current user is permitted to execute the given command.
     *
     * Please use the access-handler to to so, if this does not suffice you can
     * always extend it.
     */
    abstract protected function checkAccess(ilUserTakeOverAccessHandler $handler, string $command): bool;

    /**
     * Must setup the visible tabs of the current page by using the tab-manager.
     * This process differ from one command to another, which is why it's passed
     * as an argument too.
     */
    abstract protected function setupPage(
        ilUserTakeOverTabManager $manager,
        ilToolbarGUI $toolbar,
        string $command
    ): void;
}

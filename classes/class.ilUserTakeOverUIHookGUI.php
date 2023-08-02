<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\IGeneralRepository;
use srag\Plugins\UserTakeOver\IRequestParameters;
use srag\Plugins\UserTakeOver\RequestHelper;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverUIHookGUI extends ilUIHookPluginGUI
{
    use ilUserTakeOverOnScreenMessages;
    use ilUserTakeOverPluginInstance;
    use RequestHelper;

    protected ilUserTakeOverImpersonationHandler $impersonation_handler;
    protected IGeneralRepository $repository;
    protected ITranslator $translator;

    protected ilGlobalTemplateInterface $template;
    protected ilCtrlInterface $ctrl;
    protected ServerRequestInterface $request;
    protected RequestWrapper $get_request;
    protected ilObjUser $current_user;
    protected Refinery $refinery;

    protected bool $is_initialized = false;

    /**
     * This method is called in order to initialize this class. We cannot use the
     * constructor here because this class will be loaded by ILIAS automatically in
     * an early stage of the bootup process, where some dependencies are not yet
     * available.
     */
    public function init(): void
    {
        global $DIC;

        if ($this->is_initialized) {
            return;
        }

        $plugin = $this->getPlugin($DIC);

        $this->repository = new ilUserTakeOverGeneralRepository($DIC->rbac()->review());
        $this->impersonation_handler = new ilUserTakeOverImpersonationHandler(
            $this->repository,
            $plugin,
            new ilUserTakeOverAccessHandler(
                new ilUserTakeOverGroupRepository($DIC->database()),
                (new ilUserTakeOverSettingsRepository($DIC->database()))->get(),
                $DIC->http()->wrapper()->query(),
                $DIC->refinery(),
                $DIC->user(),
                $DIC->rbac()->review(),
                $DIC->rbac()->system()
            ),
            new ilUserTakeOverSessionWrapper(),
            $DIC->refinery(),
            $DIC->ui()->mainTemplate(),
            $DIC->ctrl(),
            $DIC->user()
        );

        $this->template = $DIC->ui()->mainTemplate();
        $this->request = $DIC->http()->request();
        $this->get_request = $DIC->http()->wrapper()->query();
        $this->refinery = $DIC->refinery();
        $this->current_user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->translator = $plugin;

        $this->is_initialized = true;
    }

    /**
     * @inheritDoc
     */
    public function gotoHook(): void
    {
        $this->init();

        // check if the current request should be handled by UserTakeOver.
        if (null === ($target = $this->getRequestedString($this->get_request, IRequestParameters::TARGET)) ||
            ilUserTakeOverPlugin::PLUGIN_ID !== $target
        ) {
            $this->redirectToPreviousUrl();
            return;
        }

        if (null === ($user_id = $this->getRequestedInteger($this->get_request, IRequestParameters::USER_ID))) {
            $this->sendFailure($this->translator->txt(ITranslator::MSG_USER_NOT_FOUND));
            $this->redirectToPreviousUrl();
            return;
        }

        $user = $this->repository->getUser($user_id);
        if ($user->getId() === $this->current_user->getId()) {
            $this->redirectToPreviousUrl();
            return;
        }

        if (ANONYMOUS_USER_ID === $user->getId()) {
            $this->sendFailure($this->translator->txt(ITranslator::MSG_USER_NOT_FOUND));
            $this->redirectToPreviousUrl();
            return;
        }

        $this->impersonation_handler->impersonate($user);
    }

    protected function redirectToPreviousUrl(): void
    {
        $referer = $this->request->getHeader('referer')[0] ?? null;
        if (null === $referer) {
            $this->redirectToDashboard();
        }

        $this->ctrl->redirectToURL($referer);
    }

    protected function redirectToDashboard(): void
    {
        $this->ctrl->redirectByClass(ilDashboardGUI::class, 'jumpToSelectedItems');
    }

    protected function getTemplate(): ilGlobalTemplateInterface
    {
        return $this->template;
    }

    protected function getRefinery(): Refinery
    {
        return $this->refinery;
    }
}

<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\IGeneralRepository;
use srag\Plugins\UserTakeOver\ITranslator;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverImpersonationHandler
{
    use ilUserTakeOverOnScreenMessages;
    use ilUserTakeOverDisplayName;

    /**
     * These two keys are used by ILIAS to store the current user-id in the session.
     * By changing these values, we can impersonate another user.
     */
    protected const ILIAS_SESSION_USER_ID = 'AccountId';
    protected const ILIAS_AUTH_USER_ID = '_authsession_user_id';

    /**
     * These two keys are used by UserTakeOver to store the original and impersonated
     * user-ids in the session, to keep track of impersonations.
     */
    protected const ORIGINAL_USER_ID = 'user_take_over_original_user_id';

    protected IGeneralRepository $general_repository;
    protected ITranslator $translator;
    protected ilUserTakeOverAccessHandler $access_handler;
    protected ilUserTakeOverSessionWrapper $session;
    protected Refinery $refinery;
    protected ilGlobalTemplateInterface $template;
    protected ilCtrlInterface $ctrl;
    protected ilObjUser $current_user;

    public function __construct(
        IGeneralRepository $general_repository,
        ITranslator $translator,
        ilUserTakeOverAccessHandler $access_handler,
        ilUserTakeOverSessionWrapper $session,
        Refinery $refinery,
        ilGlobalTemplateInterface $template,
        ilCtrlInterface $ctrl,
        ilObjUser $current_user
    ) {
        $this->general_repository = $general_repository;
        $this->translator = $translator;
        $this->access_handler = $access_handler;
        $this->session = $session;
        $this->refinery = $refinery;
        $this->template = $template;
        $this->ctrl = $ctrl;
        $this->current_user = $current_user;
    }

    /**
     * Returns true, if an impersonation is active. This means, the current user is not the
     * user who originally logged into ILIAS.
     */
    public function isImpersonationActive(): bool
    {
        return $this->session->has(self::ORIGINAL_USER_ID);
    }

    /**
     * Tries to impersonate the targetted user. This may start or stop an imersonation,
     * depending on the current state.
     */
    public function impersonate(ilObjUser $target_user): void
    {
        if ($this->isImpersonationActive()) {
            $this->stopImpersonation($target_user);
        } else {
            $this->startImpersonation($target_user);
        }
    }

    public function getOriginalUser(): ilObjUser
    {
        $user_id = ($this->session->has(self::ORIGINAL_USER_ID)) ? $this->session->retrieve(
            self::ORIGINAL_USER_ID,
            $this->refinery->kindlyTo()->int()
        ) : -1;

        return $this->general_repository->getUser($user_id);
    }

    protected function stopImpersonation(ilObjUser $target_user): void
    {
        $original_user = $this->general_repository->getUser(
            $this->session->retrieve(
                self::ORIGINAL_USER_ID,
                $this->refinery->kindlyTo()->int()
            )
        );

        if ($original_user->getId() !== $target_user->getId()) {
            $this->sendFailure(
                sprintf(
                    $this->translator->txt(ITranslator::MSG_INVALID_ORIGINAL_USER),
                    $this->getDisplayName($target_user)
                )
            );

            $this->redirectToDashboard();
        }

        $this->session->set(self::ORIGINAL_USER_ID, null);
        $this->session->set(self::ILIAS_SESSION_USER_ID, $original_user->getId());
        $this->session->set(self::ILIAS_AUTH_USER_ID, $original_user->getId());
        $this->session->save();

        $this->redirectToDashboardOfUser($target_user);
    }

    protected function startImpersonation(ilObjUser $target_user): void
    {
        if (!$this->access_handler->canCurrentUserImpersonate($target_user)) {
            $this->sendFailure(
                sprintf(
                    $this->translator->txt(ITranslator::MSG_INVALID_PERMISSIONS),
                    $this->getDisplayName($target_user)
                )
            );

            $this->redirectToDashboard();
        }

        $this->session->set(self::ORIGINAL_USER_ID, $this->current_user->getId());
        $this->session->set(self::ILIAS_SESSION_USER_ID, $target_user->getId());
        $this->session->set(self::ILIAS_AUTH_USER_ID, $target_user->getId());
        $this->session->save();

        $this->redirectToDashboardOfUser($target_user);
    }

    protected function redirectToDashboardOfUser(ilObjUser $target_user): void
    {
        $this->sendSuccess(
            sprintf(
                $this->translator->txt(ITranslator::MSG_IMPERSONATION_SUCCESS),
                $this->getDisplayName($target_user)
            )
        );

        $this->redirectToDashboard();
    }

    protected function redirectToDashboard(): void
    {
        $this->ctrl->redirectByClass(ilDashboardGUI::class, 'jumpToSelectedItems');
    }

    protected function getTemplate(): ilGlobalTemplateInterface
    {
        return $this->template;
    }
}

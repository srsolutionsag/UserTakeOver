<?php

namespace srag\Plugins\UserTakeOver;

/**
 * Class Handler
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Handler
{
    private const USR_ID_GLOBAL = 'AccountId';
    private const USR_ID_AUTHSESSION = '_authsession_user_id';
    private const ORIGINAL_USER_ID = 'original_user_id';
    private const IMPERSONATE_USER_ID = 'impersonate_user_id';

    /**
     * @var int
     */
    protected $original_user_id;
    /**
     * @var int
     */
    protected $impersonate_user_id;

    /**
     * @param int $original_user_id
     */
    public function setOriginalUserId(int $original_user_id) : void
    {
        $this->original_user_id = $original_user_id;
    }

    /**
     * @param int $impersonate_user_id
     */
    public function setImpersonateUserId(int $impersonate_user_id) : void
    {
        $this->impersonate_user_id = $impersonate_user_id;
    }

    protected function save() : void
    {
        $_SESSION[self::ORIGINAL_USER_ID]    = $_SESSION[self::ORIGINAL_USER_ID] ?? $this->original_user_id;
        $_SESSION[self::IMPERSONATE_USER_ID] = $this->impersonate_user_id;
    }

    protected function load() : void
    {
        $this->original_user_id    = (int) ($_SESSION[self::ORIGINAL_USER_ID]);
        $this->impersonate_user_id = (int) ($_SESSION[self::IMPERSONATE_USER_ID]);
    }

    public function getLoadedOriginalId() : int
    {
        $this->load();
        return $this->original_user_id;
    }

    public function isImpersonated() : bool
    {
        $this->load();
        return isset($this->impersonate_user_id) && $this->impersonate_user_id > 0;
    }

    public function impersonateUser(Redirect $redirect) : void
    {
        $access = new Access($this->original_user_id, $this->impersonate_user_id);
        if ($access->isUserAllowedToImpersonate()()) {
            $this->save();
            $_SESSION[self::USR_ID_GLOBAL]      = $this->impersonate_user_id;
            $_SESSION[self::USR_ID_AUTHSESSION] = $this->impersonate_user_id;

            $redirect->performRedirect($this->impersonate_user_id);
        }
    }

    public function switchBack(Redirect $redirect) : void
    {
        $this->load();
        if ($this->original_user_id) {
            $_SESSION[self::USR_ID_GLOBAL]      = $this->original_user_id;
            $_SESSION[self::USR_ID_AUTHSESSION] = $this->original_user_id;

            unset($_SESSION[self::ORIGINAL_USER_ID]);
            unset($_SESSION[self::IMPERSONATE_USER_ID]);
        }
        $redirect->performRedirect($this->original_user_id);
    }
}

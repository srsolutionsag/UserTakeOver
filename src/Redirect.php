<?php

namespace srag\Plugins\UserTakeOver;

/**
 * Interface Redirect
 * @package srag\Plugins\UserTakeOver
 */
interface Redirect
{
    public function performRedirect(int $user_id) : void;
}

<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\Settings;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ISettingsRepository
{
    public function get(): Settings;

    public function store(Settings $object): Settings;
}

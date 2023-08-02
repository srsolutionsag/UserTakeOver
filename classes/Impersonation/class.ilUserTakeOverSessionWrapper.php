<?php

declare(strict_types=1);

use ILIAS\Refinery\Transformation;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverSessionWrapper
{
    public function has(string $key): bool
    {
        return ilSession::has($key);
    }

    /**
     * Returns a transformed value from the current session. Please use has()
     * beforehand to check if the key exists.
     */
    public function retrieve(string $key, Transformation $transformation): mixed
    {
        return $transformation->transform(ilSession::get($key));
    }

    /**
     * Sets a key => value pair in the current session. If the values need to be
     * persisdent, call store() afterwards.
     *
     * If the value is null, the key will be removed from the session.
     */
    public function set(string $key, mixed $value): void
    {
        if (null === $value) {
            ilSession::clear($key);
        } else {
            ilSession::set($key, $value);
        }
    }

    /**
     * Writes the current session into the database.
     */
    public function save(): void
    {
        ilSession::_writeData(session_id(), ilSession::dumpToString());
    }
}

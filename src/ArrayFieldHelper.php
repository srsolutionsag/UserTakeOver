<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait ArrayFieldHelper
{
    public function arrayToString(array $data): string
    {
        return implode($this->getSeparator(), $data);
    }

    public function stringToArray(string $data): array
    {
        if ('' !== $data) {
            return explode($this->getSeparator(), $data);
        }

        return [];
    }

    abstract protected function getSeparator(): string;
}

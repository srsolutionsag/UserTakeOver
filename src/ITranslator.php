<?php

namespace srag\Plugins\UserTakeOver;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ITranslator
{
    public function txt(string $lang_var): string;
}

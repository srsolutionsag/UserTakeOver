<?php

namespace srag\Plugins\UserTakeOver\UI\Table;

use ILIAS\UI\Component\Table\Presentation;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IPresentationTableBuilder
{
    public function getTable(array $visible_records): Presentation;
}

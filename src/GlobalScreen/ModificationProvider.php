<?php

namespace srag\Plugins\UserTakeOver\GlobalScreen;

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationPluginProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

class ModificationProvider extends AbstractModificationPluginProvider
{
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->internal();
    }
}

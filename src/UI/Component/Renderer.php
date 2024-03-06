<?php

namespace srag\Plugins\UserTakeOver\UI\Component;

use ILIAS\UI\Implementation\Render\DecoratedRenderer;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Renderer extends DecoratedRenderer
{
    protected function manipulateRendering($component, \ILIAS\UI\Renderer $root): ?string
    {
        if ($component instanceof LegacySubSlate) {
            return $root->render($component->getContent());
        }

        return null;
    }
}

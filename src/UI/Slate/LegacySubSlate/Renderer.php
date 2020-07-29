<?php

namespace ILIAS\UI\Implementation\Component\MainControls\Slate\LegacySubSlate;

use ILIAS\UI\Component;
use ILIAS\UI\Renderer as RendererInterface;

/**
 * Class LegacySubSlate
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Renderer extends \ILIAS\UI\Implementation\Component\MainControls\Slate\Renderer
{
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        return $default_renderer->render($component->getContent());
    }

}

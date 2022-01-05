<?php declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\UI;

use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\DefaultRenderer;

class AccessGetRendererForWrapperRenderer extends DefaultRenderer
{
    /**
     * @var DefaultRenderer
     */
    private $renderer;

    public function __construct(DefaultRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function accessGetRendererFor(Component $component)
    {
        return $this->renderer->getRendererFor($component);
    }
}

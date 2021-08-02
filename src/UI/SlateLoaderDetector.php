<?php

namespace srag\Plugins\UserTakeOver\UI;

use Closure;
use ilImagePathResolver;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\MainControls\Slate\LegacySubSlate\LegacySubSlate;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Renderer;
use Pimple\Container;

/**
 * Class SlateLoaderDetector
 * @package srag\Plugins\SrUI\UI\Loader
 */
class SlateLoaderDetector extends AbstractLoaderDetector
{

    /**
     * @return Closure
     */
    public static function exchange() : Closure
    {
        global $DIC;
        $previous_renderer = Closure::bind(function () {
            return $this->raw("ui.renderer");
        }, $DIC, Container::class)();

        return function () use ($previous_renderer, $DIC) : Renderer {
            $previous_renderer = $previous_renderer($DIC);

            if ($previous_renderer instanceof DefaultRenderer) {
                $previous_renderer_loader = Closure::bind(function () {
                    return $this->component_renderer_loader;
                }, $previous_renderer, DefaultRenderer::class)();
            } else {
                $previous_renderer_loader = null;
            }

            return new DefaultRenderer(new self($previous_renderer_loader));
        };
    }

    /**
     * @inheritDoc
     */
    public function getRendererFor(Component $component, array $contexts) : ComponentRenderer
    {
        if ($component instanceof LegacySubSlate) {
            return $this->getRendererForIliasVersion();
        }

        return parent::getRendererFor($component, $contexts);
    }

    /**
     * Instantiates the custom renderer according to the ILIAS version.
     *
     * @return ComponentRenderer
     */
    private function getRendererForIliasVersion() : ComponentRenderer
    {
        global $DIC;

        // ILIAS 7 Renderer is instantiated differently than in ILIAS 6, as it
        // needs an additional ImagePathResolver which is not yet known in V6.
        if (version_compare(ILIAS_VERSION_NUMERIC, '7.0') >= 0) {
            return new \ILIAS\UI\Implementation\Component\MainControls\Slate\LegacySubSlate\Renderer(
                $DIC["ui.factory"],
                $DIC["ui.template_factory"],
                $DIC["lng"],
                $DIC["ui.javascript_binding"],
                $DIC['refinery'],
                new ilImagePathResolver()
            );
        }

        return new \ILIAS\UI\Implementation\Component\MainControls\Slate\LegacySubSlate\Renderer(
            $DIC["ui.factory"],
            $DIC["ui.template_factory"],
            $DIC["lng"],
            $DIC["ui.javascript_binding"],
            $DIC['refinery']
        );
    }
}

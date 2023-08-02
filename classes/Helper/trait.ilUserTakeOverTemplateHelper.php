<?php

declare(strict_types=1);

use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
trait ilUserTakeOverTemplateHelper
{
    /**
     * Please use $force_print with caution, it may be possible that the entire page
     * will be printed out twice, which would only be visible in the source-code and
     * can be recognized by UI signals not working anymore (because of duplicate ids).
     *
     * @param Component|Component[] $components
     */
    protected function render($components, bool $force_print = false): void
    {
        $this->getTemplate()->setContent(
            $this->getRenderer()->render($components)
        );

        if ($force_print) {
            $this->getTemplate()->printToStdout();
        }
    }

    /**
     * @deprecated please use render() whenever possible.
     */
    protected function renderLegacy(string $html, bool $force_print = false): void
    {
        $this->getTemplate()->setContent($html);

        if ($force_print) {
            $this->getTemplate()->printToStdout();
        }
    }

    abstract protected function getTemplate(): \ilGlobalTemplateInterface;

    abstract protected function getRenderer(): Renderer;
}

<?php

namespace srag\Plugins\UserTakeOver\GlobalScreen;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer\AbstractMetaBarItemRenderer;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer\MetaBarItemRenderer;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Factory;
use Closure;
use ILIAS\GlobalScreen\isGlobalScreenItem;
use srag\Plugins\UserTakeOver\UI\Component\LegacySubSlate;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class LegacySubItem extends AbstractChildItem implements hasSymbol, hasTitle
{
    protected Factory $ui_factory;
    protected ?Closure $symbol_decorator = null;
    protected ?Legacy $content = null;
    protected ?Symbol $symbol = null;
    protected string $title = "";

    public function __construct(
        IdentificationInterface $provider_identification,
        Factory $ui_factory
    ) {
        parent::__construct($provider_identification);
        $this->ui_factory = $ui_factory;
    }

    public function getRenderer(): MetaBarItemRenderer
    {
        return new class () extends AbstractMetaBarItemRenderer {
            protected function getSpecificComponentForItem(isItem $item): Component
            {
                return new LegacySubSlate(
                    $GLOBALS['DIC']['ui.signal_generator'],
                    $item->getTitle(),
                    $item->getSymbol(),
                    $item->getContent()
                );
            }
        };
    }

    public function addSymbolDecorator(Closure $symbol_decorator): isGlobalScreenItem
    {
        $this->symbol_decorator = $symbol_decorator;
        return $this;
    }

    public function getSymbolDecorator(): ?Closure
    {
        return $this->symbol_decorator;
    }

    public function getContent(): Legacy
    {
        return $this->content;
    }

    public function withContent(Legacy $content): self
    {
        $clone = clone $this;
        $clone->content = $content;

        return $clone;
    }

    public function withSymbol(Symbol $symbol): hasSymbol
    {
        $clone = clone($this);
        $clone->symbol = $symbol;

        return $clone;
    }

    public function getSymbol(): \ILIAS\UI\Component\Symbol\Symbol
    {
        return $this->symbol;
    }

    public function hasSymbol(): bool
    {
        return ($this->symbol instanceof Symbol);
    }

    public function withTitle(string $title): hasTitle
    {
        $clone = clone($this);
        $clone->title = $title;

        return $clone;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}

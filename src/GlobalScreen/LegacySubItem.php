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
use ILIAS\UI\Implementation\Component\MainControls\Slate\LegacySubSlate\LegacySubSlate;

/**
 * Class LegacySubItem
 * @package srag\Plugins\UserTakeOver\GlobalScreen
 */
abstract class LegacySubItem extends AbstractChildItem implements hasSymbol, hasTitle
{

    /**
     * @var Factory
     */
    private $ui;
    /**
     * @var Legacy
     */
    protected $content;

    /**
     * @var Symbol
     */
    protected $symbol;

    /**
     * @var string
     */
    protected $title = "";

    public function __construct(IdentificationInterface $provider_identification, Container $DIC)
    {
        parent::__construct($provider_identification);
        $this->ui = $DIC->ui()->factory();
    }

    public function getRenderer() : MetaBarItemRenderer
    {
        return new class() extends AbstractMetaBarItemRenderer {
            protected function getSpecificComponentForItem(isItem $item) : Component
            {
                global $DIC;
                $name    = $item->getTitle();
                $symbol  = $this->getStandardSymbol($item);
                $content = $item->getContent();

                return new LegacySubSlate($DIC['ui.signal_generator'], $name, $symbol, $content);
            }
        };
    }

    /**
     * @return Legacy
     */
    public function getContent() : Legacy
    {
        return $this->content;
    }

    /**
     * @param Legacy $content
     * @return $this
     */
    public function withContent(Legacy $content) : self
    {
        $clone          = clone $this;
        $clone->content = $content;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        $clone         = clone($this);
        $clone->symbol = $symbol;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getSymbol() : \ILIAS\UI\Component\Symbol\Symbol
    {
        return $this->symbol;
    }

    /**
     * @inheritDoc
     */
    public function hasSymbol() : bool
    {
        return ($this->symbol instanceof Symbol);
    }

    /**
     * @inheritDoc
     */
    public function withTitle(string $title) : hasTitle
    {
        $clone        = clone($this);
        $clone->title = $title;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }
}

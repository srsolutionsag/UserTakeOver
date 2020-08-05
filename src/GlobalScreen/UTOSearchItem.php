<?php

namespace srag\Plugins\UserTakeOver\GlobalScreen;

use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Class UTOSearchItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class UTOSearchItem extends LegacySubItem
{
    /**
     * @var string
     */
    private $url = '';
    /**
     * @var string
     */
    private $placeholder = '';

    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function withUrl(string $url) : self
    {
        $clone      = clone $this;
        $clone->url = $url;

        return $clone;
    }

    public function withPlaceholder(string $placeholder) : self
    {
        $clone              = clone $this;
        $clone->placeholder = $placeholder;

        return $clone;
    }

    public function getPlaceholder() : string
    {
        return $this->placeholder;
    }

    public function getContent() : Legacy
    {
        global $DIC;

        $tpl = new \ilTemplate('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/templates/tpl.uto_search.html', true, true);
        $tpl->setVariable('PLACEHOLDER', $this->getPlaceholder());
        $tpl->setVariable('SEARCH_URL', $this->getUrl());

        return $DIC->ui()->factory()->legacy($tpl->get());
    }

}

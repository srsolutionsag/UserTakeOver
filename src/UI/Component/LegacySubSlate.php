<?php

namespace srag\Plugins\UserTakeOver\UI\Component;

use ILIAS\UI\Component\Legacy\Legacy as LegacyContent;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class LegacySubSlate extends Legacy
{
    /**
     * @var LegacyContent
     */
    protected $content;

    /**
     * @return LegacyContent
     */
    public function getContent(): LegacyContent
    {
        return $this->content ?? reset($this->contents);
    }

    /**
     * @param LegacyContent $content
     * @return $this
     */
    public function withContent(LegacyContent $content): self
    {
        $clone          = clone $this;
        $clone->content = $content;

        return $clone;
    }
}

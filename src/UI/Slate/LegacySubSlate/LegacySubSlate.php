<?php

namespace ILIAS\UI\Implementation\Component\MainControls\Slate\LegacySubSlate;

use ILIAS\UI\Component\Legacy\Legacy as LegacyContent;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy;

/**
 * Class LegacySubSlate
 * @author Fabian Schmid <fs@studer-raimann.ch>
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
    public function getContent() : LegacyContent
    {
        return $this->content ?? reset($this->contents);
    }

    /**
     * @param LegacyContent $content
     * @return $this
     */
    public function withContent(LegacyContent $content) : self
    {
        $clone          = clone $this;
        $clone->content = $content;

        return $clone;
    }
}

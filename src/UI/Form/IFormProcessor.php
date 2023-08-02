<?php

namespace srag\Plugins\UserTakeOver\UI\Form;

use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IFormProcessor
{
    /**
     * @return bool
     */
    public function processForm(): bool;

    /**
     * @return UIForm
     */
    public function getProcessedForm(): UIForm;
}

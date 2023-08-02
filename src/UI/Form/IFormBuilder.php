<?php

namespace srag\Plugins\UserTakeOver\UI\Form;

use ILIAS\UI\Component\Input\Container\Form\Form as UIForm;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IFormBuilder
{
    /**
     * @return UIForm
     */
    public function getForm(): UIForm;
}

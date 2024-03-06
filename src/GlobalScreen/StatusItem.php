<?php

namespace srag\Plugins\UserTakeOver\GlobalScreen;

use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\UI\Factory;
use srag\Plugins\UserTakeOver\ITranslator;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class StatusItem extends LegacySubItem
{
    private int $original_user_id;
    private ITranslator $translator;

    public function __construct(
        IdentificationInterface $provider_identification,
        Factory $ui_factory,
        int $original_user_id,
        ITranslator $translator
    ) {
        parent::__construct($provider_identification, $ui_factory);
        $this->original_user_id = $original_user_id;
        $this->translator = $translator;
    }

    public function getContent(): Legacy
    {
        global $DIC;
        $tpl = new \ilTemplate(
            './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/templates/tpl.uto_status.html',
            true,
            true
        );
        $tpl->setVariable("ORIGINAL_USER", $this->translator->txt('status_original_login'));
        $tpl->setVariable("IMPERSONATED_USER", $this->translator->txt('status_impersonated_login'));
        $tpl->setVariable("ORIGINAL_USER_LOGIN", \ilObjUser::_lookupLogin($this->original_user_id));
        $tpl->setVariable("IMPERSONATED_USER_LOGIN", \ilObjUser::_lookupLogin($DIC->user()->getId()));

        return $DIC->ui()->factory()->legacy($tpl->get());
    }

}

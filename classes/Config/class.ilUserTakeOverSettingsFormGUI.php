<?php

use srag\DIC\UserTakeOver\DICTrait;

/**
 * Class ilUserTakeOverSettingsForm
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilUserTakeOverSettingsFormGUI extends ilPropertyFormGUI
{
    use DICTrait;

    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;

    /**
     * lang vars
     */
    private const CMD_TRANSLATION_SAVE     = 'save';
    private const CMD_TRANSLATION_CANCEL   = 'cancel';

    /**
     * ilUserTakeOverSettingsFormGUI constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->initForm();
        $this->fillFormData();
    }

    /**
     * initializes the (legacy) form.
     */
    private function initForm() : void
    {
        $global_roles_input = new ilMultiSelectInputGUI(
            self::plugin()->translate(ilUserTakeOverARConfig::CNF_ID_GLOBAL_ROLES),
            ilUserTakeOverARConfig::CNF_ID_GLOBAL_ROLES);
        $global_roles_input->setOptions($this->getAvailableGlobalRoles());
        $global_roles_input->setInfo(self::plugin()->translate(ilUserTakeOverARConfig::CNF_ID_GLOBAL_ROLES . '_info'));
        $this->addItem($global_roles_input);

        $this->setFormAction(self::dic()->ctrl()->getFormActionByClass(
            [ilUserTakeOverMainGUI::class, ilUserTakeOverSettingsGUI::class],
            ilUserTakeOverSettingsGUI::CMD_STANDARD
        ));

        $this->addCommandButton(
            ilUserTakeOverSettingsGUI::CMD_CONFIG_SAVE,
            self::plugin()->translate(self::CMD_TRANSLATION_SAVE)
        );

        $this->addCommandButton(
            ilUserTakeOverSettingsGUI::CMD_CANCEL,
            self::plugin()->translate(self::CMD_TRANSLATION_CANCEL)
        );
    }

    /**
     * fills the form data depending on the form state.
     */
    private function fillFormData() : void
    {
        if (empty($_POST)) {
            $this->setValuesByArray($this->getConfiguration());
        } else {
            $this->setValuesByPost();
        }
    }

    /**
     * returns the current configurations
     *
     * @return array
     */
    private function getConfiguration() : array
    {
        $configurations = ilUserTakeOverARConfig::get();
        if (empty($configurations)) return [];

        $data = [];
        foreach ($configurations as $config) {
            $data[$config->getIdentifier()] = $config->getValue();
        }

        return $data;
    }

    /**
     * returns all available global roles as 'obj_id' => 'title' array.
     *
     * @return array
     */
    private function getAvailableGlobalRoles() : array
    {
        $roles = [];
        foreach (self::dic()->rbac()->review()->getRolesByFilter(ilRbacReview::FILTER_ALL_GLOBAL) as $role_data) {
            $roles[$role_data['rol_id']] = ilObjRole::_getTranslation($role_data['title']);
        }

        // remove admin-role from entries
        unset($roles[SYSTEM_ROLE_ID]);

        return $roles;
    }

    /**
     * stores a value for given identifier into the database.
     *
     * @param string $identifier
     * @param mixed  $value
     * @throws arException
     */
    private function storeConfig(string $identifier, $value) : void
    {
        $config = ilUserTakeOverARConfig::find($identifier);
        if (null === $config) {
            $config = new ilUserTakeOverARConfig();
            $config->setIdentifier($identifier);
        }

        $config->setValue($value);
        $config->store();
    }

    /**
     * saves this forms input field values.
     *
     * @throws arException
     */
    public function save() : void
    {
        /**
         * @var $input ilCheckboxGroupInputGUI (for now)
         */
        foreach ($this->getInputItemsRecursive() as $input) {
            $this->storeConfig($input->getPostVar(), $input->getValue());
        }
    }
}

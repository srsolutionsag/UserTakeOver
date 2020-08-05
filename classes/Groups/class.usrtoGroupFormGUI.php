<?php
require_once __DIR__ . "/../../vendor/autoload.php";

use srag\DIC\UserTakeOver\DICTrait;

/**
 * Class usrtoGroupFormGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
class usrtoGroupFormGUI extends ilPropertyFormGUI
{

    use DICTrait;

    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;
    /**
     * @var  usrtoGroup
     */
    protected $object;
    /**
     * @var ilUserTakeOverGroupsGUI
     */
    protected $parent_gui;
    /**
     * @var boolean
     */
    protected $is_new;

    /**
     * @param ilUserTakeOverGroupsGUI $parent_gui
     * @param usrtoGroup              $usrtoGroup
     */
    public function __construct(ilUserTakeOverGroupsGUI $parent_gui, ActiveRecord $usrtoGroup)
    {
        parent::__construct();
        $this->object     = $usrtoGroup;
        $this->parent_gui = $parent_gui;
        $this->ctrl->saveParameter($parent_gui, ilUserTakeOverGroupsGUI::IDENTIFIER);
        $this->is_new = ($this->object->getId() == '');
        $this->initForm();
    }

    protected function initForm()
    {
        $this->setTarget('_top');
        $this->setFormAction(self::dic()->ctrl()->getFormAction($this->parent_gui));
        $this->initButtons();

        $te = new ilTextInputGUI(self::plugin()->translate('title'), 'title');
        $te->setRequired(true);
        $this->addItem($te);

        $ta = new ilTextAreaInputGUI(self::plugin()->translate('description'), 'desc');
        $this->addItem($ta);
    }

    public function fillForm()
    {
        $array = array(
            'title' => $this->object->getTitle(),
            'desc'  => $this->object->getDescription(),
        );
        $this->setValuesByArray($array);
    }

    /**
     * returns whether checkinput was successful or not.
     * @return bool
     */
    public function fillObject()
    {
        if (!$this->checkInput()) {
            return false;
        }

        $this->object->setTitle($this->getInput('title'));
        $this->object->setDescription($this->getInput('desc'));

        return true;
    }

    /**
     * @return bool|string
     */
    public function saveObject()
    {
        if (!$this->fillObject()) {
            return false;
        }
        if (!usrtoGroup::where(array('id' => $this->object->getId()))->hasSets()) {
            $this->object->create();
        } else {
            $this->object->update();
        }

        return true;
    }

    protected function initButtons()
    {
        if ($this->is_new) {
            $this->setTitle(self::plugin()->translate('create_group'));
            $this->addCommandButton(ilUserTakeOverGroupsGUI::CMD_CREATE, self::plugin()->translate(ilUserTakeOverGroupsGUI::CMD_CREATE));
        } else {
            $this->setTitle(self::plugin()->translate('edit_group'));
            $this->addCommandButton(ilUserTakeOverGroupsGUI::CMD_UPDATE, self::plugin()->translate(ilUserTakeOverGroupsGUI::CMD_UPDATE));
        }

        $this->addCommandButton(ilUserTakeOverGroupsGUI::CMD_CANCEL, self::plugin()->translate(ilUserTakeOverGroupsGUI::CMD_CANCEL));
    }
}

<?php

require_once __DIR__ . "/../../vendor/autoload.php";

/**
 * Class usrtoGroup
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
class usrtoGroup extends ActiveRecord
{

    const TABLE_NAME = "ui_uihk_usrto_grp";

    /**
     * @return string
     */
    public function getConnectorContainerName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @var string
     * @db_has_field        true
     * @con_is_unique       true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_is_primary       true
     * @con_sequence        true
     */
    protected $id;
    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected $title = '';
    /**
     * @var string
     * @con_has_field  true
     * @con_fieldtype  text
     */
    protected $description = '';

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}

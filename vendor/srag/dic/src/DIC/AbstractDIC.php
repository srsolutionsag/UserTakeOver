<?php

namespace srag\DIC\UserTakeOver\DIC;

use ILIAS\DI\Container;
use srag\DIC\UserTakeOver\Database\DatabaseDetector;
use srag\DIC\UserTakeOver\Database\DatabaseInterface;

/**
 * Class AbstractDIC
 *
 * @package srag\DIC\UserTakeOver\DIC
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractDIC implements DICInterface
{

    /**
     * @var Container
     */
    protected $dic;


    /**
     * @inheritDoc
     */
    public function __construct(Container &$dic)
    {
        $this->dic = &$dic;
    }


    /**
     * @inheritDoc
     */
    public function database() : DatabaseInterface
    {
        return DatabaseDetector::getInstance($this->databaseCore());
    }
}

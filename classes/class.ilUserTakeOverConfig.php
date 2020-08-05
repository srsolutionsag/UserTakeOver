<?php

use srag\ActiveRecordConfig\UserTakeOver\ActiveRecordConfig;

/**
 * Class ilUserTakeOverConfig
 * @author Benjamin Seglias <bs@studer-raimann.ch>
 */
class ilUserTakeOverConfig extends ActiveRecordConfig
{

    const TABLE_NAME = 'ui_uihk_usrto_config_n';
    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;
    /**
     * @var array
     */
    protected static $fields
        = [

        ];
}

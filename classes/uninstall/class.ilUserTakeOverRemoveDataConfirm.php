<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use srag\RemovePluginDataConfirm\UserTakeOver\AbstractRemovePluginDataConfirm;

/**
 * Class ilUserTakeOverRemoveDataConfirm
 * @author            Benjamin Seglias   <bs@studer-raimann.ch>
 * @ilCtrl_isCalledBy ilUserTakeOverRemoveDataConfirm: ilUIPluginRouterGUI
 */
class ilUserTakeOverRemoveDataConfirm extends AbstractRemovePluginDataConfirm
{

    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;
}

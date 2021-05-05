<?php
require_once __DIR__ . "/../../vendor/autoload.php";

use srag\DIC\UserTakeOver\DICTrait;

/**
 * Class ilObjUserTakeOverAccess
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
class ilObjUserTakeOverAccess extends ilObjectPluginAccess
{

    use DICTrait;
    const PLUGIN_CLASS_NAME = ilUserTakeOverPlugin::class;


    /**
     * @param null $ref_id
     * @param null $user_id
     *
     * @return bool
     */
    public static function hasReadAccess($ref_id = null, $user_id = null)
    {

        return (new self)->hasAccess('read', $ref_id, $user_id);
    }


    /**
     * @param null $ref_id
     * @param null $user_id
     *
     * @return bool
     */
    public static function hasWriteAccess($ref_id = null, $user_id = null)
    {

        return (new self)->hasAccess('write', $ref_id, $user_id);
    }


    /**
     * @param null $ref_id
     * @param null $user_id
     *
     * @return bool
     */
    public static function hasDeleteAccess($ref_id = null, $user_id = null)
    {
        return (new self)->hasAccess('delete', $ref_id, $user_id);
    }


    /**
     * @param      $permission
     * @param null $ref_id
     * @param null $user_id
     *
     * @return bool
     */
    protected function hasAccess($permission, $ref_id = null, $user_id = null)
    {
        $ref_id = $ref_id ? $ref_id : filter_input(INPUT_GET, "ref_id");
        $user_id = $user_id ? $user_id : self::dic()->user()->getId();

        return (bool) self::dic()->access()->checkAccessOfUser($user_id, $permission, '', $ref_id);
    }

    /**
     * @param int $user_id
     * @return bool
     */
    public static function isUserAdministrator(int $user_id)
    {
        return self::dic()->rbacreview()->isAssigned($user_id, 2);
    }


    /**
     * @param int $user_id
     * @return bool
     */
    public static function isUserAssignedToConfiguredRole(int $user_id)
    {
        $config = ilUserTakeOverARConfig::get();
        $identifier = ilUserTakeOverARConfig::CNF_ID_GLOBAL_ROLES;
        if (!isset($config[$identifier])) {
            return false;
        }

        return self::dic()->rbacreview()->isAssignedToAtLeastOneGivenRole(
            $user_id,
            $config[$identifier]->getValue()
        );
    }
}
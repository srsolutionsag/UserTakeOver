<?php

namespace srag\Plugins\UserTakeOver;

/**
 * Note that this class can be transformed into an Enum once ILIAS
 * versions support PHP >= 8.1.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface IRequestParameters
{
    // ILIAS request parameters:
    public const USER_ID = 'usr_id';
    public const OBJ_ID = 'obj_id';
    public const REF_ID = 'ref_id';
    public const TARGET = 'target';

    // Plugin request parameters:
    public const SEARCH_TERM = 'usrto_search_term';
    public const GROUP_ID = 'usrto_group_id';
}

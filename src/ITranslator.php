<?php

namespace srag\Plugins\UserTakeOver;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ITranslator
{
    public const SETTINGS = 'settings';
    public const SETTINGS_ALLOWED_GLOBAL_ROLES = 'settings_allowed_global_roles';
    public const SETTINGS_ALLOWED_GLOBAL_ROLES_INFO = 'settings_allowed_global_roles_info';
    public const SETTINGS_ALLOW_ADMIN_IMPERSONATION = 'settings_allow_admin_impersonation';
    public const SETTINGS_ALLOW_ADMIN_IMPERSONATION_INFO = 'settings_allow_admin_impersonation_info';

    public const GROUP = 'group';
    public const GROUPS = 'groups';
    public const GROUP_TITLE = 'group_title';
    public const GROUP_DESCRIPTION = 'group_description';
    public const GROUP_RESTRICTION_MEMBERS = 'group_restriction_members';
    public const GROUP_RESTRICTION_MEMBERS_INFO = 'group_restriction_members_info';
    public const GROUP_RESTRICTION_ROLES = 'group_restriction_roles';
    public const GROUP_RESTRICTION_ROLES_INFO = 'group_restriction_roles_info';
    public const GROUP_ALLOWED_ROLES = 'group_allowed_roles';
    public const GROUP_MEMBERS = 'group_members';
    public const GROUP_FILTER_MIN_MEMBER_AMOUNT = 'group_filter_min_member_amount';
    public const GROUP_TABLE_MEMBER_AMOUNT = 'group_table_member_amount';
    public const GROUP_TABLE_NO_MEMBERS = 'group_table_no_members';
    public const GROUP_TABLE_RESTRICTION = 'group_table_restriction';
    public const GROUP_TABLE_RESTRICTION_STATUS_MEMBERS = 'group_table_restriction_status_members';
    public const GROUP_TABLE_RESTRICTION_STATUS_ROLES = 'group_table_restriction_status_roles';
    public const GROUP_TABLE_RESTRICTION_STATUS_NONE = 'group_table_restriction_status_none';
    public const GROUP_ACTION_ADD = 'group_action_add';
    public const GROUP_ACTION_EDIT = 'group_action_edit';
    public const GROUP_ACTION_DELETE = 'group_action_delete';
    public const GROUP_ACTION_EDIT_MEMBERS = 'group_action_edit_members';

    public const TOOL_TITLE = 'tool_title';
    public const TOOL_TITLE_LEAVE = 'tool_title_leave';
    public const TOOL_TITLE_SEARCH = 'tool_title_search';

    public const MSG_INVALID_ORIGINAL_USER = 'msg_invalid_original_user';
    public const MSG_USER_NOT_FOUND = 'msg_user_not_found';
    public const MSG_INVALID_PERMISSIONS = 'msg_invalid_permissions';
    public const MSG_INVALID_REF_IDS = 'msg_invalid_ref_ids';
    public const MSG_INVALID_REF_ID = 'msg_invalid_ref_id';
    public const MSG_INVALID_EMAIL = 'msg_invalid_email';
    public const MSG_NUMBER_OUT_OF_RANGE = 'msg_invalid_range_number';
    public const MSG_TEXT_OUT_OF_RANGE = 'msg_invalid_range_text';
    public const MSG_IMPERSONATION_SUCCESS = 'msg_impersonation_success';
    public const MSG_SETTINGS_SUCCESS = 'msg_settings_success';
    public const MSG_GROUP_SUCCESS = 'msg_group_success';
    public const MSG_GROUP_DELETED = 'msg_group_deleted';
    public const MSG_GROUP_NOT_FOUND = 'msg_group_not_found';

    public const GENERAL_ACTION_IMPERSONATE = 'general_action_impersonate';
    public const GENERAL_ACTION_BACK = 'general_action_back';

    /**
     * Please only use language variables known to this interface.
     */
    public function txt(string $variable): string;
}

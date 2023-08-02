<?php

declare(strict_types=1);

use srag\Plugins\UserTakeOver\Settings\ISettingsRepository;
use srag\Plugins\UserTakeOver\Settings\Settings;
use srag\Plugins\UserTakeOver\ArrayFieldHelper;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilUserTakeOverSettingsRepository implements ISettingsRepository
{
    use ArrayFieldHelper;

    /**
     * Internal identifiers setting values, each constant corresponds to a
     * property of the Settings class.
     */
    protected const ALLOW_ADMIN_IMPERSONATION = 'cnf_allow_impersonate_admins';
    protected const ALLOWED_GLOBAL_ROLES = 'cnf_global_role_ids';

    /**
     * Database field names.
     */
    protected const F_IDENTIFIER = 'identifier';
    protected const F_VALUE = 'value';

    protected ilDBInterface $database;

    public function __construct(
        ilDBInterface $database
    ) {
        $this->database = $database;
    }

    public function get(): Settings
    {
        $query = "SELECT identifier, `value` FROM usrto_config;";
        $results = $this->database->fetchAll(
            $this->database->query($query)
        );

        $settings = new Settings();
        foreach ($results as $query_result) {
            switch ($query_result[self::F_IDENTIFIER]) {
                case self::ALLOWED_GLOBAL_ROLES:
                    $settings->setAllowedGlobalRoleIds($this->stringToArray($query_result[self::F_VALUE]));
                    break;

                case self::ALLOW_ADMIN_IMPERSONATION:
                    $settings->setAllowImpersonationOfAdmins((bool) $query_result[self::F_VALUE]);
                    break;
            }
        }

        return $settings;
    }

    public function store(Settings $object): Settings
    {
        $this->updateSetting(self::ALLOWED_GLOBAL_ROLES, $this->arrayToString($object->getAllowedGlobalRoleIds()));
        $this->updateSetting(self::ALLOW_ADMIN_IMPERSONATION, (string) $object->isAllowImpersonationOfAdmins());

        return $object;
    }

    protected function updateSetting(string $identifier, string $value): void
    {
        $query = "UPDATE usrto_config SET `value` = %s WHERE identifier = %s;";

        $this->database->manipulateF(
            $query,
            ['text', 'text'],
            [
                $value,
                $identifier,
            ]
        );
    }

    protected function getSeparator(): string
    {
        return ',';
    }
}

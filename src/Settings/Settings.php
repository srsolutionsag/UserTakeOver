<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\Settings;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Settings
{
    /**
     * @param int[] $allow_impersonation_of_admins
     */
    public function __construct(
        protected array $allowed_global_role_ids = [],
        protected bool $allow_impersonation_of_admins = false,
    ) {
    }

    /**
     * @return int[]
     */
    public function getAllowedGlobalRoleIds(): array
    {
        return $this->allowed_global_role_ids;
    }

    /**
     * @param int[] $allowed_global_role_ids
     */
    public function setAllowedGlobalRoleIds(array $allowed_global_role_ids): void
    {
        $this->allowed_global_role_ids = $allowed_global_role_ids;
    }

    public function isAllowImpersonationOfAdmins(): bool
    {
        return $this->allow_impersonation_of_admins;
    }

    public function setAllowImpersonationOfAdmins(bool $allow_impersonation_of_admins): void
    {
        $this->allow_impersonation_of_admins = $allow_impersonation_of_admins;
    }
}

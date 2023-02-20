# UserTakeOver Changelog

## v4.1.0

- Added new feature to restrict the impersonation of users by global roles.
- Replaced the legacy form implementation of groups by UI components.
- Fixed database update-script which no longer uses `ActiveRecord` instances that might produce unexpected database
  states.
- Replaced ActiveRecord implementation of groups by DTOs and repositories.
- Added new configuration which must be enabled in order for administrators to be impersonated. Configuration will be
  taken into account when searching for users as well.
- Removed unnecessary root-folder files.
- Added CLI support.

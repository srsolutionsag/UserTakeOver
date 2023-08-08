# UserTakeOver Changelog

## 5.0.1

- Fixed an issue where other ILIAS goto-links no longer worked and e.g. the repository was could not be accessed
  anymore.

## 5.0.0

- Improved over-complicated access-check collection and removed the need for `Closure`'s.
- Improved the impersonation mechanism and removed the need for `Redirect` handlers.
- Replaced all usages of the legacy package `DICTrait` by using proper dependency injection (whenever possible).
- Replaced `ActiveRecord` implementation by using the repository pattern.
- Replaced usages of `filter_input` by using using the ILIAS request wrappers.
- Replaced legacy UI input for the user search by a UI component.
- Replaces all legacy tables by new UI components.
- Replaced all legacy forms by new UI components.
- Removed the `node_modules` directory from the repository along with its config files.
- Added default values for the UserTakeOver plugin settings.
- Added ILIAS 8 compatibility.

## 4.1.1

- Groups which are only restricted to roles are no longer shown in the metabar slate.
- Added a small description about the two kinds of groups to the documentation.
- Fixed missing translation `restricted_to_members` and added bylines to the group-form.
- Added gitlab CI config for creating review-apps.

## 4.1.0

- Added new feature to restrict the impersonation of users by global roles.
- Replaced the legacy form implementation of groups by UI components.
- Fixed database update-script which no longer uses `ActiveRecord` instances that might produce unexpected database
  states.
- Replaced ActiveRecord implementation of groups by DTOs and repositories.
- Added new configuration which must be enabled in order for administrators to be impersonated. Configuration will be
  taken into account when searching for users as well.
- Removed unnecessary root-folder files.
- Added CLI support.

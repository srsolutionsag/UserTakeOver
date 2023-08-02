# UserTakeOver Roadmap

This document holds ideas and improvements that could be implemented for this plugin in the future.
(If one of the following points has been implemented mark it checked)

- [x] Replace the over-complicated access-check collection that consists of nested `Closure`'s by a more simple and
  explicit approach. This really does cause some headaches.

- [x] Remove the `DICTrait` legacy package by fluxlabs and replace any usages by using proper DI (where possible) and
  using the ILIAS DI container `$DIC`.

- [x] Replace the legacy forms (`ilPropertyFormGUI`) by new UI-Components and get rid of any special input magic.

- [ ] Implement the "impersonate search" as custom UI component to get rid of the node modules folder which is currently
  being used for ajax autocomplete.

- [ ] Replace the custom multi-select input by an existing or custom UI-Component.

- [ ] Introduce a local dependency-injection-container to centralize internal dependency management.

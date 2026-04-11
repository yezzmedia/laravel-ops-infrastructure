# Ops Infrastructure Testing Pattern

- Keep registration expectations in `RegistrationTest`.
- Keep manager, resolver, and summary behavior in targeted feature or unit tests.
- Keep page rendering in `OpsInfrastructurePageTest`.
- Keep doctor checks and refresh flow in their dedicated feature tests.
- Run `composer test:ops-infrastructure` from `/home/yezz/Developement/packages/1-dev-test` when available in the shared runner.

# Install And Doctor Rules

Declared install steps:

- `PublishOpsInfrastructureConfigInstallStep`
- `ConfigureOpsInfrastructureAuditInstallStep`

Declared doctor checks:

- `QueueConfiguredCheck`
- `CacheConfiguredCheck`
- `DatabaseReachableCheck`
- `StorageReadyCheck`
- `RuntimeMetricsSupportedCheck`

Keep the current runtime scoped to diagnostics posture and config publication; do not assume migrations or persistence exist in V1.

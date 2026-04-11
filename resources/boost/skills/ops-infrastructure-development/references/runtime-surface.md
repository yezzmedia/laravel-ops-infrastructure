# Approved V1 Ops Infrastructure Surface

- permissions:
  - `ops.infrastructure.view`
  - `ops.infrastructure.manage`
- features:
  - `infrastructure.queue`
  - `infrastructure.cache`
  - `infrastructure.database`
  - `infrastructure.storage`
  - `infrastructure.runtime`
- audit event:
  - `ops.infrastructure.snapshot_refreshed`
- ops module:
  - `infrastructure.overview`

Core public runtime types include:

- `OpsInfrastructurePlatformPackage`
- `OpsInfrastructureServiceProvider`
- `OpsInfrastructureManager`
- `QueuePostureResolver`
- `CachePostureResolver`
- `DatabasePostureResolver`
- `StoragePostureResolver`
- `RuntimeResourcePostureResolver`
- `OpsInfrastructureSummaryBuilder`
- `RefreshInfrastructureSnapshotAction`

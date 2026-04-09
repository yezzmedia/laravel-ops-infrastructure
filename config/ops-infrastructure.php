<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Controls caching of the computed infrastructure posture summary.
    | When enabled, the summary is cached for the configured TTL to avoid
    | redundant live probes on every request.
    |
    */

    'cache' => [
        'enabled' => env('OPS_INFRASTRUCTURE_CACHE_ENABLED', true),
        'store' => env('OPS_INFRASTRUCTURE_CACHE_STORE'),
        'ttl' => env('OPS_INFRASTRUCTURE_CACHE_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    |
    | Audit persistence for infrastructure snapshot refresh events.
    | Supported drivers: null, 'activitylog'.
    | Set to null to disable audit persistence entirely.
    |
    */

    'audit' => [
        'driver' => env('OPS_INFRASTRUCTURE_AUDIT_DRIVER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Additional queue connections to probe beyond the default.
    | The default connection is always probed automatically.
    |
    */

    'queue' => [
        'connections' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Additional cache stores to probe beyond the default.
    | The default store is always probed automatically.
    | Note: this is different from the posture cache above.
    |
    */

    'cache_stores' => [
        'stores' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Additional database connections to probe beyond the default.
    | The default connection is always probed automatically.
    |
    */

    'database' => [
        'connections' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Disks
    |--------------------------------------------------------------------------
    |
    | Additional storage disks to probe beyond the default.
    | The default disk is always probed automatically.
    |
    */

    'storage' => [
        'disks' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Probe Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in milliseconds to wait for a single infrastructure probe.
    |
    */

    'probe_timeout_ms' => env('OPS_INFRASTRUCTURE_PROBE_TIMEOUT_MS', 5000),

    /*
    |--------------------------------------------------------------------------
    | Runtime Resource Thresholds
    |--------------------------------------------------------------------------
    |
    | Thresholds for runtime resource monitoring.
    | memory_warning_percent triggers a warning status.
    | memory_failed_percent triggers a failed status.
    |
    */

    'runtime' => [
        'thresholds' => [
            'memory_warning_percent' => env('OPS_INFRASTRUCTURE_MEMORY_WARNING_PERCENT', 80.0),
            'memory_failed_percent' => env('OPS_INFRASTRUCTURE_MEMORY_FAILED_PERCENT', 95.0),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Integration
    |--------------------------------------------------------------------------
    |
    | When enabled and spatie/laravel-health is installed, the package
    | will include health check results in the infrastructure summary.
    |
    */

    'health' => [
        'enabled' => env('OPS_INFRASTRUCTURE_HEALTH_ENABLED', false),
    ],

];

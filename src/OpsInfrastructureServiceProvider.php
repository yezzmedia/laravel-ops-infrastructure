<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Database\DatabaseManager;
use InvalidArgumentException;
use Spatie\Activitylog\Support\ActivityLogger;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;
use YezzMedia\OpsInfrastructure\Actions\RefreshInfrastructureSnapshotAction;
use YezzMedia\OpsInfrastructure\Contracts\OpsInfrastructureAuditWriter;
use YezzMedia\OpsInfrastructure\Doctor\CacheConfiguredCheck;
use YezzMedia\OpsInfrastructure\Doctor\DatabaseReachableCheck;
use YezzMedia\OpsInfrastructure\Doctor\QueueConfiguredCheck;
use YezzMedia\OpsInfrastructure\Doctor\RuntimeMetricsSupportedCheck;
use YezzMedia\OpsInfrastructure\Doctor\StorageReadyCheck;
use YezzMedia\OpsInfrastructure\Events\InfrastructureSnapshotRefreshed;
use YezzMedia\OpsInfrastructure\Install\ConfigureOpsInfrastructureAuditInstallStep;
use YezzMedia\OpsInfrastructure\Install\PublishOpsInfrastructureConfigInstallStep;
use YezzMedia\OpsInfrastructure\Listeners\OpsInfrastructureAuditListener;
use YezzMedia\OpsInfrastructure\Support\ActivityLogOpsInfrastructureAuditWriter;
use YezzMedia\OpsInfrastructure\Support\CachePostureResolver;
use YezzMedia\OpsInfrastructure\Support\DatabasePostureResolver;
use YezzMedia\OpsInfrastructure\Support\NullOpsInfrastructureAuditWriter;
use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureManager;
use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureSummaryBuilder;
use YezzMedia\OpsInfrastructure\Support\QueuePostureResolver;
use YezzMedia\OpsInfrastructure\Support\RuntimeResourcePostureResolver;
use YezzMedia\OpsInfrastructure\Support\StoragePostureResolver;

/**
 * Bootstrap root for the ops-infrastructure package.
 *
 * Bindings belong in packageRegistered().
 * Foundation registration belongs in packageBooted().
 */
class OpsInfrastructureServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-ops-infrastructure')
            ->hasConfigFile('ops-infrastructure');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(OpsInfrastructureAuditWriter::class, fn (): OpsInfrastructureAuditWriter => $this->makeAuditWriter());

        // Doctor checks
        $this->app->singleton(QueueConfiguredCheck::class);
        $this->app->singleton(CacheConfiguredCheck::class);
        $this->app->singleton(DatabaseReachableCheck::class, function (): DatabaseReachableCheck {
            return new DatabaseReachableCheck($this->app->make(DatabaseManager::class));
        });
        $this->app->singleton(StorageReadyCheck::class, function (): StorageReadyCheck {
            return new StorageReadyCheck($this->app->make(FilesystemFactory::class));
        });
        $this->app->singleton(RuntimeMetricsSupportedCheck::class);

        // Install steps
        $this->app->singleton(PublishOpsInfrastructureConfigInstallStep::class);
        $this->app->singleton(ConfigureOpsInfrastructureAuditInstallStep::class);

        // Resolvers
        $this->app->singleton(QueuePostureResolver::class, function (): QueuePostureResolver {
            return new QueuePostureResolver($this->app->make(QueueFactory::class));
        });
        $this->app->singleton(CachePostureResolver::class, function (): CachePostureResolver {
            return new CachePostureResolver($this->app->make(CacheFactory::class));
        });
        $this->app->singleton(DatabasePostureResolver::class, function (): DatabasePostureResolver {
            return new DatabasePostureResolver($this->app->make(DatabaseManager::class));
        });
        $this->app->singleton(StoragePostureResolver::class, function (): StoragePostureResolver {
            return new StoragePostureResolver($this->app->make(FilesystemFactory::class));
        });
        $this->app->singleton(RuntimeResourcePostureResolver::class);
        $this->app->singleton(OpsInfrastructureSummaryBuilder::class);

        // Manager
        $this->app->singleton(OpsInfrastructureManager::class, function (): OpsInfrastructureManager {
            return new OpsInfrastructureManager(
                queueResolver: $this->app->make(QueuePostureResolver::class),
                cacheResolver: $this->app->make(CachePostureResolver::class),
                databaseResolver: $this->app->make(DatabasePostureResolver::class),
                storageResolver: $this->app->make(StoragePostureResolver::class),
                runtimeResolver: $this->app->make(RuntimeResourcePostureResolver::class),
                summaryBuilder: $this->app->make(OpsInfrastructureSummaryBuilder::class),
                cacheFactory: $this->app->make(CacheFactory::class),
                cacheEnabled: (bool) config('ops-infrastructure.cache.enabled', true),
                cacheStore: config('ops-infrastructure.cache.store'),
                cacheTtl: (int) config('ops-infrastructure.cache.ttl', 300),
            );
        });

        // Action
        $this->app->singleton(RefreshInfrastructureSnapshotAction::class, function (): RefreshInfrastructureSnapshotAction {
            return new RefreshInfrastructureSnapshotAction(
                manager: $this->app->make(OpsInfrastructureManager::class),
                events: $this->app->make(Dispatcher::class),
            );
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(PlatformPackageRegistrar::class)->register(new OpsInfrastructurePlatformPackage);
        $this->registerAuditListeners($this->app->make(Dispatcher::class));
    }

    private function registerAuditListeners(Dispatcher $events): void
    {
        $events->listen(InfrastructureSnapshotRefreshed::class, [OpsInfrastructureAuditListener::class, 'handleSnapshotRefreshed']);
    }

    private function makeAuditWriter(): OpsInfrastructureAuditWriter
    {
        $driver = config('ops-infrastructure.audit.driver');

        if ($driver === null) {
            return new NullOpsInfrastructureAuditWriter;
        }

        if ($driver !== 'activitylog') {
            throw new InvalidArgumentException(sprintf('Unsupported ops infrastructure audit driver [%s].', $driver));
        }

        if (! class_exists('Spatie\\Activitylog\\ActivitylogServiceProvider') || ! class_exists(ActivityLogger::class)) {
            throw new InvalidArgumentException('Ops infrastructure audit driver [activitylog] requires spatie/laravel-activitylog.');
        }

        return new ActivityLogOpsInfrastructureAuditWriter($this->app->make(ActivityLogger::class));
    }
}

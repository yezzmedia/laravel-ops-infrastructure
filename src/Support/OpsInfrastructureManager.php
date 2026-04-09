<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Central read API for infrastructure posture diagnostics.
 *
 * Orchestrates resolvers, caches the computed summary, and provides
 * domain-level and component-level accessors. No mutations — read-only.
 */
class OpsInfrastructureManager
{
    private CacheRepository $cacheRepository;

    private ?InfrastructurePostureSummary $memo = null;

    public function __construct(
        private readonly QueuePostureResolver $queueResolver,
        private readonly CachePostureResolver $cacheResolver,
        private readonly DatabasePostureResolver $databaseResolver,
        private readonly StoragePostureResolver $storageResolver,
        private readonly RuntimeResourcePostureResolver $runtimeResolver,
        private readonly OpsInfrastructureSummaryBuilder $summaryBuilder,
        private readonly CacheFactory $cacheFactory,
        private readonly bool $cacheEnabled,
        private readonly ?string $cacheStore,
        private readonly int $cacheTtl,
    ) {
        $this->cacheRepository = $this->cacheFactory->store($this->cacheStore);
    }

    public function summary(): InfrastructurePostureSummary
    {
        if ($this->memo !== null) {
            return $this->memo;
        }

        if ($this->cacheEnabled) {
            $cached = $this->cacheRepository->get($this->cacheKey());

            if ($cached instanceof InfrastructurePostureSummary) {
                $this->memo = $cached;

                return $cached;
            }
        }

        $summary = $this->computeSummary();
        $this->memo = $summary;

        if ($this->cacheEnabled) {
            $this->cacheRepository->put($this->cacheKey(), $summary, $this->cacheTtl);
        }

        return $summary;
    }

    public function queue(): QueuePosture
    {
        return $this->summary()->queue;
    }

    public function cache(): CachePosture
    {
        return $this->summary()->cache;
    }

    public function database(): DatabasePosture
    {
        return $this->summary()->database;
    }

    public function storage(): StoragePosture
    {
        return $this->summary()->storage;
    }

    public function runtimeResources(): RuntimeResourcePosture
    {
        return $this->summary()->runtimeResources;
    }

    public function overallStatus(): OpsInfrastructureStatus
    {
        return $this->summary()->overallStatus;
    }

    /**
     * @return array<int, InfrastructureComponentStatus>
     */
    public function failingComponents(): array
    {
        return $this->summary()->failingComponents;
    }

    /**
     * @return array<int, InfrastructureComponentStatus>
     */
    public function warningComponents(): array
    {
        return $this->summary()->warningComponents;
    }

    /**
     * @return array<int, InfrastructureComponentStatus>
     */
    public function unsupportedComponents(): array
    {
        return $this->summary()->unsupportedComponents;
    }

    /**
     * Forces a fresh summary computation, bypassing and replacing the cache.
     */
    public function refresh(): InfrastructurePostureSummary
    {
        $this->memo = null;
        $this->cacheRepository->forget($this->cacheKey());

        return $this->summary();
    }

    private function computeSummary(): InfrastructurePostureSummary
    {
        $defaultQueueConnection = (string) config('queue.default', 'sync');
        $additionalQueueConnections = (array) config('ops-infrastructure.queue.connections', []);
        $queueTimeout = (int) config('ops-infrastructure.probe_timeout_ms', 5000);

        $defaultCacheStore = (string) config('cache.default', 'file');
        $additionalCacheStores = (array) config('ops-infrastructure.cache.stores', []);
        $cacheTimeout = (int) config('ops-infrastructure.probe_timeout_ms', 5000);

        $defaultDbConnection = (string) config('database.default', 'mysql');
        $additionalDbConnections = (array) config('ops-infrastructure.database.connections', []);
        $dbTimeout = (int) config('ops-infrastructure.probe_timeout_ms', 5000);

        $defaultDisk = (string) config('filesystems.default', 'local');
        $additionalDisks = (array) config('ops-infrastructure.storage.disks', []);
        $storageTimeout = (int) config('ops-infrastructure.probe_timeout_ms', 5000);

        $runtimeThresholds = (array) config('ops-infrastructure.runtime.thresholds', []);

        return $this->summaryBuilder->build(
            queue: $this->queueResolver->resolve($defaultQueueConnection, $additionalQueueConnections, $queueTimeout),
            cache: $this->cacheResolver->resolve($defaultCacheStore, $additionalCacheStores, $cacheTimeout),
            database: $this->databaseResolver->resolve($defaultDbConnection, $additionalDbConnections, $dbTimeout),
            storage: $this->storageResolver->resolve($defaultDisk, $additionalDisks, $storageTimeout),
            runtime: $this->runtimeResolver->resolve($runtimeThresholds),
        );
    }

    private function cacheKey(): string
    {
        return 'ops_infrastructure.summary';
    }
}

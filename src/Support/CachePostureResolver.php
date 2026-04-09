<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Throwable;

/**
 * Probes cache stores and reports posture.
 *
 * Checks the default cache store and any additional stores
 * listed in the package configuration. Never exposes credentials.
 */
final readonly class CachePostureResolver
{
    public function __construct(
        private CacheFactory $cacheFactory,
    ) {}

    /**
     * @param  array<int, string>  $stores
     */
    public function resolve(string $defaultStore, array $stores = [], int $timeoutMs = 5000): CachePosture
    {
        $componentStatuses = [];
        $allStores = array_unique([$defaultStore, ...$stores]);

        foreach ($allStores as $store) {
            $componentStatuses[] = $this->probeStore($store, $timeoutMs);
        }

        $statuses = array_map(
            static fn (InfrastructureComponentStatus $c): OpsInfrastructureStatus => $c->status,
            $componentStatuses,
        );

        $overallStatus = OpsInfrastructureStatus::worst($statuses);
        $driver = $this->resolveDriver($defaultStore);

        return new CachePosture(
            status: $overallStatus,
            driver: $driver,
            store: $defaultStore,
            stores: $componentStatuses,
            message: sprintf('Cache posture: %s (%s via %s).', $overallStatus->label(), $defaultStore, $driver),
        );
    }

    private function probeStore(string $store, int $timeoutMs): InfrastructureComponentStatus
    {
        try {
            $cache = $this->cacheFactory->store($store);
            $driver = $this->resolveDriver($store);

            $testKey = 'ops_infrastructure_probe_'.md5($store);
            $cache->put($testKey, true, 10);
            $result = $cache->get($testKey);
            $cache->forget($testKey);

            if ($result !== true) {
                return new InfrastructureComponentStatus(
                    domain: 'cache',
                    component: $store,
                    status: OpsInfrastructureStatus::Warning,
                    message: sprintf('Cache store [%s] accepted write but returned unexpected read result.', $store),
                    context: ['driver' => $driver],
                );
            }

            return new InfrastructureComponentStatus(
                domain: 'cache',
                component: $store,
                status: OpsInfrastructureStatus::Healthy,
                message: sprintf('Cache store [%s] is reachable and functional.', $store),
                context: ['driver' => $driver],
            );
        } catch (Throwable $e) {
            return new InfrastructureComponentStatus(
                domain: 'cache',
                component: $store,
                status: OpsInfrastructureStatus::Failed,
                message: sprintf('Cache store [%s] is unreachable: %s', $store, $e->getMessage()),
                context: ['error' => $e->getMessage()],
            );
        }
    }

    private function resolveDriver(string $store): string
    {
        return (string) config(sprintf('cache.stores.%s.driver', $store), 'unknown');
    }
}

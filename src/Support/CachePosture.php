<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

/**
 * Posture snapshot for cache infrastructure.
 *
 * @phpstan-type CacheContext array<string, mixed>
 */
final readonly class CachePosture
{
    /**
     * @param  array<int, InfrastructureComponentStatus>  $stores
     * @param  CacheContext  $context
     */
    public function __construct(
        public OpsInfrastructureStatus $status,
        public string $driver,
        public string $store,
        public array $stores = [],
        public string $message = '',
        public array $context = [],
    ) {}
}

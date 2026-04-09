<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

/**
 * Posture snapshot for runtime resource metrics.
 *
 * @phpstan-type RuntimeContext array<string, mixed>
 */
final readonly class RuntimeResourcePosture
{
    /**
     * @param  RuntimeContext  $context
     */
    public function __construct(
        public OpsInfrastructureStatus $status,
        public int $memoryUsageMb,
        public int $memoryLimitMb,
        public float $memoryUsagePercent,
        public string $phpVersion,
        public string $laravelVersion,
        public string $message = '',
        public array $context = [],
    ) {}
}

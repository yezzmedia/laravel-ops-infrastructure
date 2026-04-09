<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

use DateTimeImmutable;

/**
 * Complete infrastructure posture summary across all domains.
 *
 * @phpstan-type SummaryContext array<string, mixed>
 */
final readonly class InfrastructurePostureSummary
{
    /**
     * @param  array<int, InfrastructureComponentStatus>  $failingComponents
     * @param  array<int, InfrastructureComponentStatus>  $warningComponents
     * @param  array<int, InfrastructureComponentStatus>  $unsupportedComponents
     * @param  SummaryContext  $context
     */
    public function __construct(
        public OpsInfrastructureStatus $overallStatus,
        public QueuePosture $queue,
        public CachePosture $cache,
        public DatabasePosture $database,
        public StoragePosture $storage,
        public RuntimeResourcePosture $runtimeResources,
        public array $failingComponents,
        public array $warningComponents,
        public array $unsupportedComponents,
        public int $healthyCount,
        public int $failingCount,
        public int $warningCount,
        public int $unsupportedCount,
        public DateTimeImmutable $completedAt,
        public array $context = [],
    ) {}
}

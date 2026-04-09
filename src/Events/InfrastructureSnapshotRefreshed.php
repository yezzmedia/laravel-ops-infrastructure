<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Events;

use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureStatus;

/**
 * Runtime event emitted after a successful infrastructure snapshot refresh.
 *
 * Emitted only after the summary has been computed and optionally cached.
 * Use as the package-owned signal for audit bridging.
 */
final class InfrastructureSnapshotRefreshed
{
    /**
     * @param  array<int, string>  $failingComponents
     * @param  array<int, string>  $warningComponents
     */
    public function __construct(
        public readonly OpsInfrastructureStatus $overallStatus,
        public readonly int $healthyCount,
        public readonly int $failingCount,
        public readonly int $warningCount,
        public readonly int $unsupportedCount,
        public readonly array $failingComponents,
        public readonly array $warningComponents,
        public readonly int|string|null $actorId,
        public readonly string $completedAt,
        public readonly ?string $source,
    ) {}
}

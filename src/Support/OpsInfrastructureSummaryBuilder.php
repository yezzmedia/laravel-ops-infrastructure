<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

use DateTimeImmutable;

/**
 * Builds a complete InfrastructurePostureSummary from individual domain postures.
 *
 * Aggregates all component statuses, computes counts and overall status.
 * Stateless — call build() with the five domain postures.
 */
final readonly class OpsInfrastructureSummaryBuilder
{
    public function build(
        QueuePosture $queue,
        CachePosture $cache,
        DatabasePosture $database,
        StoragePosture $storage,
        RuntimeResourcePosture $runtime,
    ): InfrastructurePostureSummary {
        $allComponents = [
            ...$queue->connections,
            ...$cache->stores,
            ...$database->connections,
            ...$storage->disks,
        ];

        $failing = array_values(array_filter(
            $allComponents,
            static fn (InfrastructureComponentStatus $c): bool => $c->status === OpsInfrastructureStatus::Failed,
        ));

        $warning = array_values(array_filter(
            $allComponents,
            static fn (InfrastructureComponentStatus $c): bool => $c->status === OpsInfrastructureStatus::Warning,
        ));

        $unsupported = array_values(array_filter(
            $allComponents,
            static fn (InfrastructureComponentStatus $c): bool => $c->status === OpsInfrastructureStatus::Unsupported,
        ));

        $healthy = array_values(array_filter(
            $allComponents,
            static fn (InfrastructureComponentStatus $c): bool => $c->status === OpsInfrastructureStatus::Healthy,
        ));

        $domainStatuses = [
            $queue->status,
            $cache->status,
            $database->status,
            $storage->status,
            $runtime->status,
        ];

        $overallStatus = OpsInfrastructureStatus::worst($domainStatuses);

        return new InfrastructurePostureSummary(
            overallStatus: $overallStatus,
            queue: $queue,
            cache: $cache,
            database: $database,
            storage: $storage,
            runtimeResources: $runtime,
            failingComponents: $failing,
            warningComponents: $warning,
            unsupportedComponents: $unsupported,
            healthyCount: count($healthy),
            failingCount: count($failing),
            warningCount: count($warning),
            unsupportedCount: count($unsupported),
            completedAt: new DateTimeImmutable,
        );
    }
}

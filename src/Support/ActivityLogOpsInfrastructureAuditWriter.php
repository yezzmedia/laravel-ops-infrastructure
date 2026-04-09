<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

use Spatie\Activitylog\Support\ActivityLogger;
use YezzMedia\OpsInfrastructure\Contracts\OpsInfrastructureAuditWriter;
use YezzMedia\OpsInfrastructure\Events\InfrastructureSnapshotRefreshed;

final class ActivityLogOpsInfrastructureAuditWriter implements OpsInfrastructureAuditWriter
{
    public function __construct(
        private readonly ActivityLogger $logger,
    ) {}

    public function record(InfrastructureSnapshotRefreshed $event): void
    {
        $this->logger
            ->useLog('ops_infrastructure')
            ->event('ops.infrastructure.snapshot_refreshed')
            ->withProperties([
                'overall_status' => $event->overallStatus->value,
                'healthy_count' => $event->healthyCount,
                'failing_count' => $event->failingCount,
                'warning_count' => $event->warningCount,
                'unsupported_count' => $event->unsupportedCount,
                'failing_components' => $event->failingComponents,
                'warning_components' => $event->warningComponents,
                'actor_id' => $event->actorId,
                'completed_at' => $event->completedAt,
                'source' => $event->source,
            ])
            ->log('ops.infrastructure.snapshot_refreshed');
    }
}

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use YezzMedia\OpsInfrastructure\Actions\RefreshInfrastructureSnapshotAction;
use YezzMedia\OpsInfrastructure\Events\InfrastructureSnapshotRefreshed;
use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureStatus;

it('dispatches InfrastructureSnapshotRefreshed event on refresh', function (): void {
    Event::fake([InfrastructureSnapshotRefreshed::class]);

    $action = app(RefreshInfrastructureSnapshotAction::class);
    $summary = $action->execute('test');

    expect($summary)->not->toBeNull()
        ->and($summary->overallStatus)->toBeInstanceOf(OpsInfrastructureStatus::class);

    Event::assertDispatched(InfrastructureSnapshotRefreshed::class, function (InfrastructureSnapshotRefreshed $event): bool {
        return $event->source === 'test'
            && $event->overallStatus instanceof OpsInfrastructureStatus
            && is_string($event->completedAt);
    });
});

it('includes component counts in the event', function (): void {
    Event::fake([InfrastructureSnapshotRefreshed::class]);

    app(RefreshInfrastructureSnapshotAction::class)->execute('test');

    Event::assertDispatched(InfrastructureSnapshotRefreshed::class, function (InfrastructureSnapshotRefreshed $event): bool {
        return $event->healthyCount >= 0
            && $event->failingCount >= 0
            && $event->warningCount >= 0
            && $event->unsupportedCount >= 0;
    });
});

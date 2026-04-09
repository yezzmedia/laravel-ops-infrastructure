<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Actions;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Auth;
use YezzMedia\OpsInfrastructure\Events\InfrastructureSnapshotRefreshed;
use YezzMedia\OpsInfrastructure\Support\InfrastructureComponentStatus;
use YezzMedia\OpsInfrastructure\Support\InfrastructurePostureSummary;
use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureManager;

/**
 * Forces a fresh infrastructure snapshot and emits the refresh event.
 *
 * Called from the admin UI or programmatically. Always produces
 * a fresh summary (bypasses cache) and dispatches the domain event.
 */
final readonly class RefreshInfrastructureSnapshotAction
{
    public function __construct(
        private OpsInfrastructureManager $manager,
        private Dispatcher $events,
    ) {}

    public function execute(?string $source = null): InfrastructurePostureSummary
    {
        $summary = $this->manager->refresh();

        $this->events->dispatch(new InfrastructureSnapshotRefreshed(
            overallStatus: $summary->overallStatus,
            healthyCount: $summary->healthyCount,
            failingCount: $summary->failingCount,
            warningCount: $summary->warningCount,
            unsupportedCount: $summary->unsupportedCount,
            failingComponents: array_map(
                static fn (InfrastructureComponentStatus $c): string => sprintf('%s.%s', $c->domain, $c->component),
                $summary->failingComponents,
            ),
            warningComponents: array_map(
                static fn (InfrastructureComponentStatus $c): string => sprintf('%s.%s', $c->domain, $c->component),
                $summary->warningComponents,
            ),
            actorId: Auth::id(),
            completedAt: $summary->completedAt->format('c'),
            source: $source,
        ));

        return $summary;
    }
}

<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Listeners;

use YezzMedia\OpsInfrastructure\Contracts\OpsInfrastructureAuditWriter;
use YezzMedia\OpsInfrastructure\Events\InfrastructureSnapshotRefreshed;

final readonly class OpsInfrastructureAuditListener
{
    public function __construct(
        private OpsInfrastructureAuditWriter $writer,
    ) {}

    public function handleSnapshotRefreshed(InfrastructureSnapshotRefreshed $event): void
    {
        $this->writer->record($event);
    }
}

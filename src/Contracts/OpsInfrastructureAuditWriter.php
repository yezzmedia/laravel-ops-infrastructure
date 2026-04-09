<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Contracts;

use YezzMedia\OpsInfrastructure\Events\InfrastructureSnapshotRefreshed;

interface OpsInfrastructureAuditWriter
{
    public function record(InfrastructureSnapshotRefreshed $event): void;
}

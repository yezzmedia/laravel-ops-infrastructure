<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

use YezzMedia\OpsInfrastructure\Contracts\OpsInfrastructureAuditWriter;
use YezzMedia\OpsInfrastructure\Events\InfrastructureSnapshotRefreshed;

final class NullOpsInfrastructureAuditWriter implements OpsInfrastructureAuditWriter
{
    public function record(InfrastructureSnapshotRefreshed $event): void {}
}

<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Install;

use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Install\AuditInstallStep;
use YezzMedia\Foundation\Install\OptionalInstallStep;

final readonly class ConfigureOpsInfrastructureAuditInstallStep implements AuditInstallStep, OptionalInstallStep
{
    public function key(): string
    {
        return 'configure_ops_infrastructure_audit';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-ops-infrastructure';
    }

    public function priority(): int
    {
        return 25;
    }

    public function shouldRun(InstallContext $context): bool
    {
        return $context->shouldConfigureAuditFor($this->package());
    }

    public function handle(InstallContext $context): void
    {
        $configPath = config_path('ops-infrastructure.php');

        if (! file_exists($configPath)) {
            return;
        }

        $contents = file_get_contents($configPath);

        if ($contents === false) {
            return;
        }

        $updated = str_replace(
            "'driver' => env('OPS_INFRASTRUCTURE_AUDIT_DRIVER'),",
            "'driver' => env('OPS_INFRASTRUCTURE_AUDIT_DRIVER', 'activitylog'),",
            $contents,
        );

        file_put_contents($configPath, $updated);
    }

    public function isOptional(): bool
    {
        return true;
    }
}

<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Install;

use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Install\InstallStep;

final readonly class PublishOpsInfrastructureConfigInstallStep implements InstallStep
{
    public function key(): string
    {
        return 'publish_ops_infrastructure_config';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-ops-infrastructure';
    }

    public function priority(): int
    {
        return 20;
    }

    public function shouldRun(InstallContext $context): bool
    {
        return $context->refreshPublishedResources || ! file_exists(config_path('ops-infrastructure.php'));
    }

    public function handle(InstallContext $context): void
    {
        $source = dirname(__DIR__, 2).'/config/ops-infrastructure.php';

        if (! file_exists($source)) {
            return;
        }

        copy($source, config_path('ops-infrastructure.php'));
    }
}

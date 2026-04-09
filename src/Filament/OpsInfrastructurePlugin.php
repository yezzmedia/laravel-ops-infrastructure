<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use YezzMedia\OpsInfrastructure\Filament\Pages\OpsInfrastructurePage;

/**
 * Registers the Ops Infrastructure UI pages into a Filament panel.
 */
final class OpsInfrastructurePlugin implements Plugin
{
    public static function make(): static
    {
        return new self;
    }

    public function getId(): string
    {
        return 'ops-infrastructure';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            OpsInfrastructurePage::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}

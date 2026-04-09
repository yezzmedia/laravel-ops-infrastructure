<?php

declare(strict_types=1);

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;
use YezzMedia\OpsInfrastructure\Filament\Pages\OpsInfrastructurePage;

it('builds the infrastructure page schema without invalid schema component calls', function (): void {
    Gate::define('ops.infrastructure.view', fn ($user = null) => true);
    Gate::define('ops.infrastructure.manage', fn ($user = null) => true);

    $page = app(OpsInfrastructurePage::class);

    $schema = $page->content(Schema::make($page));
    $components = $schema->getComponents(withActions: false, withHidden: true);

    expect($components)
        ->toHaveCount(3)
        ->and($components[0])->toBeInstanceOf(Section::class)
        ->and($components[0]->getHeading())->toBe('Overview');
});

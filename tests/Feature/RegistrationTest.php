<?php

declare(strict_types=1);

use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\OpsInfrastructure\OpsInfrastructurePlatformPackage;
use YezzMedia\OpsInfrastructure\OpsInfrastructureServiceProvider;

it('boots the ops-infrastructure service provider', function (): void {
    expect(app()->providerIsLoaded(OpsInfrastructureServiceProvider::class))->toBeTrue();
});

it('registers the platform package with foundation', function (): void {
    $registry = app(PackageRegistry::class);

    expect($registry->has('yezzmedia/laravel-ops-infrastructure'))->toBeTrue();
});

it('registers the correct package metadata', function (): void {
    $package = new OpsInfrastructurePlatformPackage;
    $metadata = $package->metadata();

    expect($metadata->name)->toBe('yezzmedia/laravel-ops-infrastructure')
        ->and($metadata->vendor)->toBe('yezzmedia')
        ->and($metadata->description)->not->toBeEmpty();
});

it('declares two permissions', function (): void {
    $package = new OpsInfrastructurePlatformPackage;

    $permissions = $package->permissionDefinitions();
    $viewPermission = collect($permissions)->firstWhere('name', 'ops.infrastructure.view');
    $managePermission = collect($permissions)->firstWhere('name', 'ops.infrastructure.manage');

    expect($permissions)
        ->toHaveCount(2)
        ->and(array_map(fn ($p) => $p->name, $permissions))
        ->toContain('ops.infrastructure.view', 'ops.infrastructure.manage')
        ->and($viewPermission?->defaultRoleHints)->toBe(['super-admin'])
        ->and($managePermission?->defaultRoleHints)->toBe(['super-admin']);
});

it('declares five features', function (): void {
    $package = new OpsInfrastructurePlatformPackage;

    expect($package->featureDefinitions())->toHaveCount(5);
});

it('declares one audit event', function (): void {
    $package = new OpsInfrastructurePlatformPackage;

    $events = $package->auditEventDefinitions();

    expect($events)->toHaveCount(1)
        ->and($events[0]->key)->toBe('ops.infrastructure.snapshot_refreshed');
});

it('declares five doctor checks', function (): void {
    $package = new OpsInfrastructurePlatformPackage;

    expect($package->doctorChecks())->toHaveCount(5);
});

it('declares two install steps', function (): void {
    $package = new OpsInfrastructurePlatformPackage;

    expect($package->installSteps())->toHaveCount(2);
});

it('declares one ops module', function (): void {
    $package = new OpsInfrastructurePlatformPackage;

    $modules = $package->opsModuleDefinitions();

    expect($modules)->toHaveCount(1)
        ->and($modules[0]->key)->toBe('infrastructure.overview');
});

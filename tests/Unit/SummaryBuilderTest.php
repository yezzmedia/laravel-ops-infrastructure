<?php

declare(strict_types=1);

use YezzMedia\OpsInfrastructure\Support\CachePosture;
use YezzMedia\OpsInfrastructure\Support\DatabasePosture;
use YezzMedia\OpsInfrastructure\Support\InfrastructureComponentStatus;
use YezzMedia\OpsInfrastructure\Support\InfrastructurePostureSummary;
use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureStatus;
use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureSummaryBuilder;
use YezzMedia\OpsInfrastructure\Support\QueuePosture;
use YezzMedia\OpsInfrastructure\Support\RuntimeResourcePosture;
use YezzMedia\OpsInfrastructure\Support\StoragePosture;

it('builds a summary from healthy postures', function (): void {
    $builder = new OpsInfrastructureSummaryBuilder;

    $healthyComponent = new InfrastructureComponentStatus(
        domain: 'test',
        component: 'default',
        status: OpsInfrastructureStatus::Healthy,
        message: 'OK',
    );

    $summary = $builder->build(
        queue: new QueuePosture(
            status: OpsInfrastructureStatus::Healthy,
            driver: 'sync',
            connection: 'sync',
            connections: [$healthyComponent],
        ),
        cache: new CachePosture(
            status: OpsInfrastructureStatus::Healthy,
            driver: 'array',
            store: 'array',
            stores: [$healthyComponent],
        ),
        database: new DatabasePosture(
            status: OpsInfrastructureStatus::Healthy,
            driver: 'sqlite',
            connection: 'testing',
            connections: [$healthyComponent],
        ),
        storage: new StoragePosture(
            status: OpsInfrastructureStatus::Healthy,
            defaultDisk: 'local',
            disks: [$healthyComponent],
        ),
        runtime: new RuntimeResourcePosture(
            status: OpsInfrastructureStatus::Healthy,
            memoryUsageMb: 64,
            memoryLimitMb: 256,
            memoryUsagePercent: 25.0,
            phpVersion: PHP_VERSION,
            laravelVersion: '13.0.0',
        ),
    );

    expect($summary)->toBeInstanceOf(InfrastructurePostureSummary::class)
        ->and($summary->overallStatus)->toBe(OpsInfrastructureStatus::Healthy)
        ->and($summary->healthyCount)->toBe(4)
        ->and($summary->failingCount)->toBe(0)
        ->and($summary->warningCount)->toBe(0)
        ->and($summary->unsupportedCount)->toBe(0);
});

it('computes overall failed when any domain fails', function (): void {
    $builder = new OpsInfrastructureSummaryBuilder;

    $failedComponent = new InfrastructureComponentStatus(
        domain: 'database',
        component: 'mysql',
        status: OpsInfrastructureStatus::Failed,
        message: 'Unreachable',
    );

    $healthyComponent = new InfrastructureComponentStatus(
        domain: 'test',
        component: 'default',
        status: OpsInfrastructureStatus::Healthy,
        message: 'OK',
    );

    $summary = $builder->build(
        queue: new QueuePosture(
            status: OpsInfrastructureStatus::Healthy,
            driver: 'sync',
            connection: 'sync',
            connections: [$healthyComponent],
        ),
        cache: new CachePosture(
            status: OpsInfrastructureStatus::Healthy,
            driver: 'array',
            store: 'array',
            stores: [$healthyComponent],
        ),
        database: new DatabasePosture(
            status: OpsInfrastructureStatus::Failed,
            driver: 'mysql',
            connection: 'mysql',
            connections: [$failedComponent],
        ),
        storage: new StoragePosture(
            status: OpsInfrastructureStatus::Healthy,
            defaultDisk: 'local',
            disks: [$healthyComponent],
        ),
        runtime: new RuntimeResourcePosture(
            status: OpsInfrastructureStatus::Healthy,
            memoryUsageMb: 64,
            memoryLimitMb: 256,
            memoryUsagePercent: 25.0,
            phpVersion: PHP_VERSION,
            laravelVersion: '13.0.0',
        ),
    );

    expect($summary->overallStatus)->toBe(OpsInfrastructureStatus::Failed)
        ->and($summary->failingCount)->toBe(1)
        ->and($summary->failingComponents)->toHaveCount(1);
});

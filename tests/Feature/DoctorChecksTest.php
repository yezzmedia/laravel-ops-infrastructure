<?php

declare(strict_types=1);

use YezzMedia\OpsInfrastructure\Doctor\CacheConfiguredCheck;
use YezzMedia\OpsInfrastructure\Doctor\DatabaseReachableCheck;
use YezzMedia\OpsInfrastructure\Doctor\QueueConfiguredCheck;
use YezzMedia\OpsInfrastructure\Doctor\RuntimeMetricsSupportedCheck;
use YezzMedia\OpsInfrastructure\Doctor\StorageReadyCheck;

it('runs the queue configured check', function (): void {
    $check = app(QueueConfiguredCheck::class);
    $result = $check->run();

    expect($result->key)->toBe('queue_configured')
        ->and($result->package)->toBe('yezzmedia/laravel-ops-infrastructure')
        ->and($result->status)->toBeIn(['passed', 'warning', 'failed', 'skipped']);
});

it('runs the cache configured check', function (): void {
    $check = app(CacheConfiguredCheck::class);
    $result = $check->run();

    expect($result->key)->toBe('cache_configured')
        ->and($result->package)->toBe('yezzmedia/laravel-ops-infrastructure')
        ->and($result->status)->toBeIn(['passed', 'warning', 'failed', 'skipped']);
});

it('runs the database reachable check', function (): void {
    $check = app(DatabaseReachableCheck::class);
    $result = $check->run();

    expect($result->key)->toBe('database_reachable')
        ->and($result->package)->toBe('yezzmedia/laravel-ops-infrastructure')
        ->and($result->status)->toBeIn(['passed', 'warning', 'failed', 'skipped']);
});

it('runs the storage ready check', function (): void {
    $check = app(StorageReadyCheck::class);
    $result = $check->run();

    expect($result->key)->toBe('storage_ready')
        ->and($result->package)->toBe('yezzmedia/laravel-ops-infrastructure')
        ->and($result->status)->toBeIn(['passed', 'warning', 'failed', 'skipped']);
});

it('runs the runtime metrics supported check', function (): void {
    $check = app(RuntimeMetricsSupportedCheck::class);
    $result = $check->run();

    expect($result->key)->toBe('runtime_metrics_supported')
        ->and($result->package)->toBe('yezzmedia/laravel-ops-infrastructure')
        ->and($result->status)->toBeIn(['passed', 'warning', 'failed', 'skipped']);
});

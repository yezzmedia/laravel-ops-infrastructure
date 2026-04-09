<?php

declare(strict_types=1);

use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureManager;
use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureStatus;

it('produces an infrastructure posture summary', function (): void {
    $manager = app(OpsInfrastructureManager::class);
    $summary = $manager->summary();

    expect($summary)->not->toBeNull()
        ->and($summary->overallStatus)->toBeInstanceOf(OpsInfrastructureStatus::class)
        ->and($summary->completedAt)->toBeInstanceOf(DateTimeImmutable::class);
});

it('resolves queue posture', function (): void {
    $manager = app(OpsInfrastructureManager::class);
    $queue = $manager->queue();

    expect($queue)->not->toBeNull()
        ->and($queue->status)->toBeInstanceOf(OpsInfrastructureStatus::class)
        ->and($queue->driver)->toBeString()
        ->and($queue->connection)->toBeString();
});

it('resolves cache posture', function (): void {
    $manager = app(OpsInfrastructureManager::class);
    $cache = $manager->cache();

    expect($cache)->not->toBeNull()
        ->and($cache->status)->toBeInstanceOf(OpsInfrastructureStatus::class)
        ->and($cache->driver)->toBeString()
        ->and($cache->store)->toBeString();
});

it('resolves database posture', function (): void {
    $manager = app(OpsInfrastructureManager::class);
    $database = $manager->database();

    expect($database)->not->toBeNull()
        ->and($database->status)->toBeInstanceOf(OpsInfrastructureStatus::class)
        ->and($database->driver)->toBeString()
        ->and($database->connection)->toBeString();
});

it('resolves storage posture', function (): void {
    $manager = app(OpsInfrastructureManager::class);
    $storage = $manager->storage();

    expect($storage)->not->toBeNull()
        ->and($storage->status)->toBeInstanceOf(OpsInfrastructureStatus::class)
        ->and($storage->defaultDisk)->toBeString();
});

it('resolves runtime resource posture', function (): void {
    $manager = app(OpsInfrastructureManager::class);
    $runtime = $manager->runtimeResources();

    expect($runtime)->not->toBeNull()
        ->and($runtime->status)->toBeInstanceOf(OpsInfrastructureStatus::class)
        ->and($runtime->phpVersion)->toBe(PHP_VERSION)
        ->and($runtime->memoryUsageMb)->toBeGreaterThan(0)
        ->and($runtime->memoryLimitMb)->toBeGreaterThan(0);
});

it('computes an overall status', function (): void {
    $manager = app(OpsInfrastructureManager::class);
    $status = $manager->overallStatus();

    expect($status)->toBeInstanceOf(OpsInfrastructureStatus::class);
});

it('refreshes the summary and returns a fresh result', function (): void {
    $manager = app(OpsInfrastructureManager::class);

    $first = $manager->summary();
    $refreshed = $manager->refresh();

    expect($refreshed)->not->toBeNull()
        ->and($refreshed->completedAt)->not->toBe($first->completedAt);
});

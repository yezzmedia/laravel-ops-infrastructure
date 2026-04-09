<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use YezzMedia\OpsInfrastructure\Contracts\OpsInfrastructureAuditWriter;
use YezzMedia\OpsInfrastructure\Events\InfrastructureSnapshotRefreshed;
use YezzMedia\OpsInfrastructure\Support\ActivityLogOpsInfrastructureAuditWriter;
use YezzMedia\OpsInfrastructure\Support\NullOpsInfrastructureAuditWriter;
use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureStatus;

it('binds the null audit writer by default', function (): void {
    $writer = app(OpsInfrastructureAuditWriter::class);

    expect($writer)->toBeInstanceOf(NullOpsInfrastructureAuditWriter::class);
});

it('null audit writer accepts events without error', function (): void {
    $writer = new NullOpsInfrastructureAuditWriter;

    $event = new InfrastructureSnapshotRefreshed(
        overallStatus: OpsInfrastructureStatus::Healthy,
        healthyCount: 5,
        failingCount: 0,
        warningCount: 0,
        unsupportedCount: 0,
        failingComponents: [],
        warningComponents: [],
        actorId: null,
        completedAt: now()->toIso8601String(),
        source: 'test',
    );

    $writer->record($event);

    expect(true)->toBeTrue();
});

it('binds the activitylog audit writer when the driver is enabled', function (): void {
    if (! class_exists(Activity::class)) {
        $this->markTestSkipped('spatie/laravel-activitylog is not installed in the package environment.');
    }

    if (! Schema::hasTable('activity_log')) {
        Schema::create('activity_log', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->string('event')->nullable();
            $table->json('attribute_changes')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    config()->set('ops-infrastructure.audit.driver', 'activitylog');
    app()->forgetInstance(OpsInfrastructureAuditWriter::class);

    $writer = app(OpsInfrastructureAuditWriter::class);

    expect($writer)->toBeInstanceOf(ActivityLogOpsInfrastructureAuditWriter::class);

    $writer->record(new InfrastructureSnapshotRefreshed(
        overallStatus: OpsInfrastructureStatus::Warning,
        healthyCount: 3,
        failingCount: 0,
        warningCount: 2,
        unsupportedCount: 0,
        failingComponents: [],
        warningComponents: ['queue.redis'],
        actorId: 7,
        completedAt: '2026-04-07T12:45:00+00:00',
        source: 'ops_panel',
    ));

    $activity = Activity::query()->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity?->log_name)->toBe('ops_infrastructure')
        ->and($activity?->event)->toBe('ops.infrastructure.snapshot_refreshed')
        ->and($activity?->description)->toBe('ops.infrastructure.snapshot_refreshed')
        ->and($activity?->getProperty('overall_status'))->toBe('warning')
        ->and($activity?->getProperty('warning_components'))->toBe(['queue.redis'])
        ->and($activity?->getProperty('actor_id'))->toBe(7)
        ->and($activity?->getProperty('source'))->toBe('ops_panel');
});

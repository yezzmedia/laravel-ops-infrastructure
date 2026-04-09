<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure;

use YezzMedia\Foundation\Contracts\DefinesAuditEvents;
use YezzMedia\Foundation\Contracts\DefinesInstallSteps;
use YezzMedia\Foundation\Contracts\DefinesPermissions;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Contracts\ProvidesDoctorChecks;
use YezzMedia\Foundation\Contracts\ProvidesOpsModules;
use YezzMedia\Foundation\Contracts\RegistersFeatures;
use YezzMedia\Foundation\Data\AuditEventDefinition;
use YezzMedia\Foundation\Data\FeatureDefinition;
use YezzMedia\Foundation\Data\OpsModuleDefinition;
use YezzMedia\Foundation\Data\PackageMetadata;
use YezzMedia\Foundation\Data\PermissionDefinition;
use YezzMedia\Foundation\Doctor\DoctorCheck;
use YezzMedia\Foundation\Install\InstallStep;
use YezzMedia\OpsInfrastructure\Doctor\CacheConfiguredCheck;
use YezzMedia\OpsInfrastructure\Doctor\DatabaseReachableCheck;
use YezzMedia\OpsInfrastructure\Doctor\QueueConfiguredCheck;
use YezzMedia\OpsInfrastructure\Doctor\RuntimeMetricsSupportedCheck;
use YezzMedia\OpsInfrastructure\Doctor\StorageReadyCheck;
use YezzMedia\OpsInfrastructure\Install\ConfigureOpsInfrastructureAuditInstallStep;
use YezzMedia\OpsInfrastructure\Install\PublishOpsInfrastructureConfigInstallStep;

/**
 * Describes the ops-infrastructure package surface that foundation should register.
 */
final class OpsInfrastructurePlatformPackage implements DefinesAuditEvents, DefinesInstallSteps, DefinesPermissions, PlatformPackage, ProvidesDoctorChecks, ProvidesOpsModules, RegistersFeatures
{
    public function metadata(): PackageMetadata
    {
        return new PackageMetadata(
            name: 'yezzmedia/laravel-ops-infrastructure',
            vendor: 'yezzmedia',
            description: 'Live infrastructure posture diagnostics for the Yezz Media Laravel website platform.',
            packageClass: self::class,
        );
    }

    /**
     * @return array<int, PermissionDefinition>
     */
    public function permissionDefinitions(): array
    {
        return [
            new PermissionDefinition(
                name: 'ops.infrastructure.view',
                package: 'yezzmedia/laravel-ops-infrastructure',
                label: 'View infrastructure posture',
                description: 'Allows viewing live infrastructure posture diagnostics.',
                defaultRoleHints: ['super-admin'],
            ),
            new PermissionDefinition(
                name: 'ops.infrastructure.manage',
                package: 'yezzmedia/laravel-ops-infrastructure',
                label: 'Manage infrastructure posture',
                description: 'Allows triggering infrastructure snapshot refreshes.',
                defaultRoleHints: ['super-admin'],
            ),
        ];
    }

    /**
     * @return array<int, FeatureDefinition>
     */
    public function featureDefinitions(): array
    {
        return [
            new FeatureDefinition(
                'infrastructure.queue',
                'yezzmedia/laravel-ops-infrastructure',
                'Queue posture',
                'Reports operational posture of configured queue connections.',
            ),
            new FeatureDefinition(
                'infrastructure.cache',
                'yezzmedia/laravel-ops-infrastructure',
                'Cache posture',
                'Reports operational posture of configured cache stores.',
            ),
            new FeatureDefinition(
                'infrastructure.database',
                'yezzmedia/laravel-ops-infrastructure',
                'Database posture',
                'Reports operational posture of configured database connections.',
            ),
            new FeatureDefinition(
                'infrastructure.storage',
                'yezzmedia/laravel-ops-infrastructure',
                'Storage posture',
                'Reports operational posture of configured filesystem disks.',
            ),
            new FeatureDefinition(
                'infrastructure.runtime',
                'yezzmedia/laravel-ops-infrastructure',
                'Runtime resources',
                'Reports PHP runtime resource metrics and thresholds.',
            ),
        ];
    }

    /**
     * @return array<int, AuditEventDefinition>
     */
    public function auditEventDefinitions(): array
    {
        return [
            new AuditEventDefinition(
                key: 'ops.infrastructure.snapshot_refreshed',
                package: 'yezzmedia/laravel-ops-infrastructure',
                action: 'refreshed',
                subjectType: 'infrastructure_snapshot',
                description: 'Infrastructure posture snapshot was refreshed.',
                severity: 'info',
                contextKeys: ['overall_status', 'healthy_count', 'failing_count', 'warning_count', 'unsupported_count', 'failing_components', 'warning_components', 'actor_id', 'completed_at', 'source'],
            ),
        ];
    }

    /**
     * @return array<int, InstallStep>
     */
    public function installSteps(): array
    {
        return [
            app(PublishOpsInfrastructureConfigInstallStep::class),
            app(ConfigureOpsInfrastructureAuditInstallStep::class),
        ];
    }

    /**
     * @return array<int, DoctorCheck>
     */
    public function doctorChecks(): array
    {
        return [
            app(QueueConfiguredCheck::class),
            app(CacheConfiguredCheck::class),
            app(DatabaseReachableCheck::class),
            app(StorageReadyCheck::class),
            app(RuntimeMetricsSupportedCheck::class),
        ];
    }

    /**
     * @return array<int, OpsModuleDefinition>
     */
    public function opsModuleDefinitions(): array
    {
        return [
            new OpsModuleDefinition(
                'infrastructure.overview',
                'yezzmedia/laravel-ops-infrastructure',
                'Infrastructure',
                'page',
                'ops.infrastructure.view',
            ),
        ];
    }
}

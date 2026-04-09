<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

use function data_get;

/**
 * Optional bridge to spatie/laravel-health check results.
 *
 * When spatie/laravel-health is installed and enabled in the config,
 * this resolver aggregates health check results into infrastructure
 * component statuses. When not available, returns unsupported status.
 */
final readonly class HealthIntegrationResolver
{
    private const RESULT_STORE_CLASS = 'Spatie\\Health\\ResultStores\\ResultStore';

    /**
     * @return array<int, InfrastructureComponentStatus>
     */
    public function resolve(): array
    {
        if (! $this->isAvailable()) {
            return [];
        }

        try {
            $resultStore = app(self::RESULT_STORE_CLASS);

            if (! is_object($resultStore) || ! method_exists($resultStore, 'latestReport')) {
                return [];
            }

            $report = $resultStore->latestReport();

            if ($report === null) {
                return [
                    new InfrastructureComponentStatus(
                        domain: 'health',
                        component: 'spatie_health',
                        status: OpsInfrastructureStatus::Warning,
                        message: 'No health check report available yet.',
                    ),
                ];
            }

            $components = [];

            foreach (data_get($report, 'checkResults', []) as $result) {
                $status = match ((string) data_get($result, 'status.value', 'unsupported')) {
                    'ok' => OpsInfrastructureStatus::Healthy,
                    'warning' => OpsInfrastructureStatus::Warning,
                    'failed', 'crashed' => OpsInfrastructureStatus::Failed,
                    default => OpsInfrastructureStatus::Unsupported,
                };

                $components[] = new InfrastructureComponentStatus(
                    domain: 'health',
                    component: (string) data_get($result, 'check', 'unknown'),
                    status: $status,
                    message: (string) data_get($result, 'shortSummary', data_get($result, 'check', 'Unknown health check')),
                    context: [
                        'notification_message' => (string) data_get($result, 'notificationMessage', ''),
                    ],
                );
            }

            return $components;
        } catch (\Throwable) {
            return [
                new InfrastructureComponentStatus(
                    domain: 'health',
                    component: 'spatie_health',
                    status: OpsInfrastructureStatus::Failed,
                    message: 'Failed to read health check results.',
                ),
            ];
        }
    }

    public function isAvailable(): bool
    {
        return (bool) config('ops-infrastructure.health.enabled', false)
            && class_exists(self::RESULT_STORE_CLASS);
    }
}

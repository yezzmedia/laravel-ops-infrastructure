<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

use Illuminate\Foundation\Application;

/**
 * Probes PHP and Laravel runtime resource metrics.
 *
 * Reports memory usage, PHP version, Laravel version, and evaluates
 * runtime health against configurable thresholds. No credentials exposed.
 */
final readonly class RuntimeResourcePostureResolver
{
    /**
     * @param  array<string, mixed>  $thresholds
     */
    public function resolve(array $thresholds = []): RuntimeResourcePosture
    {
        $memoryUsageBytes = memory_get_usage(true);
        $memoryLimitBytes = $this->parseMemoryLimit();

        $memoryUsageMb = (int) round($memoryUsageBytes / 1024 / 1024);
        $memoryLimitMb = (int) round($memoryLimitBytes / 1024 / 1024);
        $memoryUsagePercent = $memoryLimitBytes > 0
            ? round(($memoryUsageBytes / $memoryLimitBytes) * 100, 2)
            : 0.0;

        $phpVersion = PHP_VERSION;
        $laravelVersion = Application::VERSION;

        $warningThreshold = (float) ($thresholds['memory_warning_percent'] ?? 80.0);
        $failedThreshold = (float) ($thresholds['memory_failed_percent'] ?? 95.0);

        $status = match (true) {
            $memoryUsagePercent >= $failedThreshold => OpsInfrastructureStatus::Failed,
            $memoryUsagePercent >= $warningThreshold => OpsInfrastructureStatus::Warning,
            default => OpsInfrastructureStatus::Healthy,
        };

        return new RuntimeResourcePosture(
            status: $status,
            memoryUsageMb: $memoryUsageMb,
            memoryLimitMb: $memoryLimitMb,
            memoryUsagePercent: $memoryUsagePercent,
            phpVersion: $phpVersion,
            laravelVersion: $laravelVersion,
            message: sprintf(
                'Runtime: PHP %s, Laravel %s, Memory %dMB/%dMB (%.1f%%).',
                $phpVersion,
                $laravelVersion,
                $memoryUsageMb,
                $memoryLimitMb,
                $memoryUsagePercent,
            ),
        );
    }

    private function parseMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');

        if ($limit === false || $limit === '' || $limit === '-1') {
            return PHP_INT_MAX;
        }

        $value = (int) $limit;
        $unit = strtolower(substr($limit, -1));

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}

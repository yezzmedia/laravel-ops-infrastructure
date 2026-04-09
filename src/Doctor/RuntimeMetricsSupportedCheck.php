<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Doctor;

use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Doctor\DoctorCheck;

final readonly class RuntimeMetricsSupportedCheck implements DoctorCheck
{
    private const KEY = 'runtime_metrics_supported';

    private const PACKAGE = 'yezzmedia/laravel-ops-infrastructure';

    public function key(): string
    {
        return self::KEY;
    }

    public function package(): string
    {
        return self::PACKAGE;
    }

    public function run(): DoctorResult
    {
        $memoryLimit = ini_get('memory_limit');

        if ($memoryLimit === false || $memoryLimit === '') {
            return $this->result(
                status: 'warning',
                message: 'PHP memory_limit could not be read.',
            );
        }

        if ($memoryLimit === '-1') {
            return $this->result(
                status: 'warning',
                message: 'PHP memory_limit is set to unlimited (-1). Memory threshold checks will be skipped.',
                context: ['memory_limit' => '-1'],
            );
        }

        return $this->result(
            status: 'passed',
            message: sprintf('Runtime metrics are supported. memory_limit=%s.', $memoryLimit),
            context: [
                'memory_limit' => $memoryLimit,
                'php_version' => PHP_VERSION,
            ],
        );
    }

    /**
     * @param  array<string, mixed>|null  $context
     */
    private function result(string $status, string $message, bool $isBlocking = false, ?array $context = null): DoctorResult
    {
        return new DoctorResult(
            key: $this->key(),
            package: $this->package(),
            status: $status,
            message: $message,
            isBlocking: $isBlocking,
            context: $context,
        );
    }
}

<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Doctor;

use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Doctor\DoctorCheck;

final readonly class CacheConfiguredCheck implements DoctorCheck
{
    private const KEY = 'cache_configured';

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
        $default = config('cache.default');

        if (! is_string($default) || $default === '') {
            return $this->result(
                status: 'failed',
                message: 'No default cache store is configured.',
                isBlocking: true,
            );
        }

        $driver = config(sprintf('cache.stores.%s.driver', $default));

        if (! is_string($driver) || $driver === '') {
            return $this->result(
                status: 'failed',
                message: sprintf('Default cache store [%s] has no driver configured.', $default),
                isBlocking: true,
                context: ['store' => $default],
            );
        }

        return $this->result(
            status: 'passed',
            message: sprintf('Default cache store [%s] is configured with driver [%s].', $default, $driver),
            context: ['store' => $default, 'driver' => $driver],
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

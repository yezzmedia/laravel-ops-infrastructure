<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Doctor;

use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Doctor\DoctorCheck;

final readonly class QueueConfiguredCheck implements DoctorCheck
{
    private const KEY = 'queue_configured';

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
        $default = config('queue.default');

        if (! is_string($default) || $default === '') {
            return $this->result(
                status: 'failed',
                message: 'No default queue connection is configured.',
                isBlocking: true,
            );
        }

        $driver = config(sprintf('queue.connections.%s.driver', $default));

        if ($driver === 'sync') {
            return $this->result(
                status: 'warning',
                message: sprintf('Default queue connection [%s] uses the sync driver.', $default),
                context: ['connection' => $default, 'driver' => 'sync'],
            );
        }

        if (! is_string($driver) || $driver === '') {
            return $this->result(
                status: 'failed',
                message: sprintf('Default queue connection [%s] has no driver configured.', $default),
                isBlocking: true,
                context: ['connection' => $default],
            );
        }

        return $this->result(
            status: 'passed',
            message: sprintf('Default queue connection [%s] is configured with driver [%s].', $default, $driver),
            context: ['connection' => $default, 'driver' => $driver],
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

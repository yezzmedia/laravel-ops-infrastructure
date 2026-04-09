<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Doctor;

use Illuminate\Database\DatabaseManager;
use Throwable;
use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Doctor\DoctorCheck;

final readonly class DatabaseReachableCheck implements DoctorCheck
{
    private const KEY = 'database_reachable';

    private const PACKAGE = 'yezzmedia/laravel-ops-infrastructure';

    public function __construct(
        private DatabaseManager $databaseManager,
    ) {}

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
        $default = config('database.default');

        if (! is_string($default) || $default === '') {
            return $this->result(
                status: 'failed',
                message: 'No default database connection is configured.',
                isBlocking: true,
            );
        }

        try {
            $this->databaseManager->connection($default)->getPdo()->query('SELECT 1');

            $driver = (string) config(sprintf('database.connections.%s.driver', $default), 'unknown');

            return $this->result(
                status: 'passed',
                message: sprintf('Default database connection [%s] is reachable via [%s].', $default, $driver),
                context: ['connection' => $default, 'driver' => $driver],
            );
        } catch (Throwable $e) {
            return $this->result(
                status: 'failed',
                message: sprintf('Default database connection [%s] is unreachable: %s', $default, $e->getMessage()),
                isBlocking: true,
                context: ['connection' => $default, 'error' => $e->getMessage()],
            );
        }
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

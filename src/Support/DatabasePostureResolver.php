<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

use Illuminate\Database\DatabaseManager;
use Throwable;

/**
 * Probes database connections and reports posture.
 *
 * Checks the default database connection and any additional connections
 * listed in the package configuration. Never exposes credentials or DSNs.
 */
final readonly class DatabasePostureResolver
{
    public function __construct(
        private DatabaseManager $databaseManager,
    ) {}

    /**
     * @param  array<int, string>  $connections
     */
    public function resolve(string $defaultConnection, array $connections = [], int $timeoutMs = 5000): DatabasePosture
    {
        $componentStatuses = [];
        $allConnections = array_unique([$defaultConnection, ...$connections]);

        foreach ($allConnections as $connection) {
            $componentStatuses[] = $this->probeConnection($connection, $timeoutMs);
        }

        $statuses = array_map(
            static fn (InfrastructureComponentStatus $c): OpsInfrastructureStatus => $c->status,
            $componentStatuses,
        );

        $overallStatus = OpsInfrastructureStatus::worst($statuses);
        $driver = $this->resolveDriver($defaultConnection);

        return new DatabasePosture(
            status: $overallStatus,
            driver: $driver,
            connection: $defaultConnection,
            connections: $componentStatuses,
            message: sprintf('Database posture: %s (%s via %s).', $overallStatus->label(), $defaultConnection, $driver),
        );
    }

    private function probeConnection(string $connection, int $timeoutMs): InfrastructureComponentStatus
    {
        try {
            $pdo = $this->databaseManager->connection($connection)->getPdo();
            $driver = $this->resolveDriver($connection);

            // Simple SELECT 1 to verify connectivity
            $pdo->query('SELECT 1');

            return new InfrastructureComponentStatus(
                domain: 'database',
                component: $connection,
                status: OpsInfrastructureStatus::Healthy,
                message: sprintf('Database connection [%s] is reachable.', $connection),
                context: ['driver' => $driver],
            );
        } catch (Throwable $e) {
            return new InfrastructureComponentStatus(
                domain: 'database',
                component: $connection,
                status: OpsInfrastructureStatus::Failed,
                message: sprintf('Database connection [%s] is unreachable: %s', $connection, $e->getMessage()),
                context: ['error' => $e->getMessage()],
            );
        }
    }

    private function resolveDriver(string $connection): string
    {
        return (string) config(sprintf('database.connections.%s.driver', $connection), 'unknown');
    }
}

<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Throwable;

/**
 * Probes queue connections and reports posture.
 *
 * Checks the default queue connection and any additional connections
 * listed in the package configuration. Never exposes credentials.
 */
final readonly class QueuePostureResolver
{
    public function __construct(
        private QueueFactory $queueFactory,
    ) {}

    /**
     * @param  array<int, string>  $connections
     */
    public function resolve(string $defaultConnection, array $connections = [], int $timeoutMs = 5000): QueuePosture
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

        return new QueuePosture(
            status: $overallStatus,
            driver: $driver,
            connection: $defaultConnection,
            connections: $componentStatuses,
            message: sprintf('Queue posture: %s (%s via %s).', $overallStatus->label(), $defaultConnection, $driver),
        );
    }

    private function probeConnection(string $connection, int $timeoutMs): InfrastructureComponentStatus
    {
        try {
            $queue = $this->queueFactory->connection($connection);
            $driver = $this->resolveDriver($connection);

            if ($driver === 'sync') {
                return new InfrastructureComponentStatus(
                    domain: 'queue',
                    component: $connection,
                    status: OpsInfrastructureStatus::Warning,
                    message: sprintf('Queue connection [%s] uses sync driver (not recommended for production).', $connection),
                    context: ['driver' => $driver],
                );
            }

            // Attempt to get queue size as a basic reachability probe
            $queue->size();

            return new InfrastructureComponentStatus(
                domain: 'queue',
                component: $connection,
                status: OpsInfrastructureStatus::Healthy,
                message: sprintf('Queue connection [%s] is reachable.', $connection),
                context: ['driver' => $driver],
            );
        } catch (Throwable $e) {
            return new InfrastructureComponentStatus(
                domain: 'queue',
                component: $connection,
                status: OpsInfrastructureStatus::Failed,
                message: sprintf('Queue connection [%s] is unreachable: %s', $connection, $e->getMessage()),
                context: ['error' => $e->getMessage()],
            );
        }
    }

    private function resolveDriver(string $connection): string
    {
        return (string) config(sprintf('queue.connections.%s.driver', $connection), 'unknown');
    }
}

<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

/**
 * Posture snapshot for queue infrastructure.
 *
 * @phpstan-type QueueContext array<string, mixed>
 */
final readonly class QueuePosture
{
    /**
     * @param  array<int, InfrastructureComponentStatus>  $connections
     * @param  QueueContext  $context
     */
    public function __construct(
        public OpsInfrastructureStatus $status,
        public string $driver,
        public string $connection,
        public array $connections = [],
        public string $message = '',
        public array $context = [],
    ) {}
}

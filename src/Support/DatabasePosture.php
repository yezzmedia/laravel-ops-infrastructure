<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

/**
 * Posture snapshot for database infrastructure.
 *
 * @phpstan-type DatabaseContext array<string, mixed>
 */
final readonly class DatabasePosture
{
    /**
     * @param  array<int, InfrastructureComponentStatus>  $connections
     * @param  DatabaseContext  $context
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

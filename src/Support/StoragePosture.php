<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

/**
 * Posture snapshot for filesystem/storage infrastructure.
 *
 * @phpstan-type StorageContext array<string, mixed>
 */
final readonly class StoragePosture
{
    /**
     * @param  array<int, InfrastructureComponentStatus>  $disks
     * @param  StorageContext  $context
     */
    public function __construct(
        public OpsInfrastructureStatus $status,
        public string $defaultDisk,
        public array $disks = [],
        public string $message = '',
        public array $context = [],
    ) {}
}

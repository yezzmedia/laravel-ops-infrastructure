<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

/**
 * Immutable posture snapshot for a single infrastructure component.
 *
 * @phpstan-type ComponentContext array<string, mixed>
 */
final readonly class InfrastructureComponentStatus
{
    /**
     * @param  ComponentContext  $context
     */
    public function __construct(
        public string $domain,
        public string $component,
        public OpsInfrastructureStatus $status,
        public string $message,
        public array $context = [],
    ) {}
}

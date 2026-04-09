<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

/**
 * Describes the operational posture of an infrastructure component.
 *
 * healthy      — component is reachable, configured, and within thresholds.
 * warning      — component is reachable but a threshold is breached or degraded.
 * failed       — component is unreachable or misconfigured.
 * unsupported  — component is intentionally excluded or not available.
 */
enum OpsInfrastructureStatus: string
{
    case Healthy = 'healthy';
    case Warning = 'warning';
    case Failed = 'failed';
    case Unsupported = 'unsupported';

    public function label(): string
    {
        return match ($this) {
            self::Healthy => 'Healthy',
            self::Warning => 'Warning',
            self::Failed => 'Failed',
            self::Unsupported => 'Unsupported',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Healthy => 'heroicon-o-check-circle',
            self::Warning => 'heroicon-o-exclamation-triangle',
            self::Failed => 'heroicon-o-x-circle',
            self::Unsupported => 'heroicon-o-minus-circle',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Healthy => 'success',
            self::Warning => 'warning',
            self::Failed => 'danger',
            self::Unsupported => 'gray',
        };
    }

    /**
     * Determines the worst status from a collection of statuses.
     * Unsupported is excluded from aggregation by default.
     *
     * @param  array<int, self>  $statuses
     */
    public static function worst(array $statuses, bool $excludeUnsupported = true): self
    {
        $candidates = $excludeUnsupported
            ? array_filter($statuses, static fn (self $s): bool => $s !== self::Unsupported)
            : $statuses;

        if ($candidates === []) {
            return self::Unsupported;
        }

        $priority = [self::Failed, self::Warning, self::Healthy, self::Unsupported];

        foreach ($priority as $status) {
            if (in_array($status, $candidates, true)) {
                return $status;
            }
        }

        return self::Healthy;
    }
}

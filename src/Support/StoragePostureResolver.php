<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Support;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Throwable;

/**
 * Probes filesystem disks and reports posture.
 *
 * Checks the default disk and any additional disks listed in the
 * package configuration. Never exposes credentials or paths.
 */
final readonly class StoragePostureResolver
{
    public function __construct(
        private FilesystemFactory $filesystemFactory,
    ) {}

    /**
     * @param  array<int, string>  $disks
     */
    public function resolve(string $defaultDisk, array $disks = [], int $timeoutMs = 5000): StoragePosture
    {
        $componentStatuses = [];
        $allDisks = array_unique([$defaultDisk, ...$disks]);

        foreach ($allDisks as $disk) {
            $componentStatuses[] = $this->probeDisk($disk, $timeoutMs);
        }

        $statuses = array_map(
            static fn (InfrastructureComponentStatus $c): OpsInfrastructureStatus => $c->status,
            $componentStatuses,
        );

        $overallStatus = OpsInfrastructureStatus::worst($statuses);

        return new StoragePosture(
            status: $overallStatus,
            defaultDisk: $defaultDisk,
            disks: $componentStatuses,
            message: sprintf('Storage posture: %s (%s).', $overallStatus->label(), $defaultDisk),
        );
    }

    private function probeDisk(string $disk, int $timeoutMs): InfrastructureComponentStatus
    {
        try {
            $filesystem = $this->filesystemFactory->disk($disk);
            $driver = (string) config(sprintf('filesystems.disks.%s.driver', $disk), 'unknown');

            $testPath = '.ops_infrastructure_probe_'.md5($disk);
            $filesystem->put($testPath, 'probe');
            $exists = $filesystem->exists($testPath);
            $filesystem->delete($testPath);

            if (! $exists) {
                return new InfrastructureComponentStatus(
                    domain: 'storage',
                    component: $disk,
                    status: OpsInfrastructureStatus::Warning,
                    message: sprintf('Storage disk [%s] accepted write but file was not found after write.', $disk),
                    context: ['driver' => $driver],
                );
            }

            return new InfrastructureComponentStatus(
                domain: 'storage',
                component: $disk,
                status: OpsInfrastructureStatus::Healthy,
                message: sprintf('Storage disk [%s] is writable and functional.', $disk),
                context: ['driver' => $driver],
            );
        } catch (Throwable $e) {
            return new InfrastructureComponentStatus(
                domain: 'storage',
                component: $disk,
                status: OpsInfrastructureStatus::Failed,
                message: sprintf('Storage disk [%s] is not accessible: %s', $disk, $e->getMessage()),
                context: ['error' => $e->getMessage()],
            );
        }
    }
}

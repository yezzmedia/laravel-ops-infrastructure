<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Doctor;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Throwable;
use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Doctor\DoctorCheck;

final readonly class StorageReadyCheck implements DoctorCheck
{
    private const KEY = 'storage_ready';

    private const PACKAGE = 'yezzmedia/laravel-ops-infrastructure';

    public function __construct(
        private FilesystemFactory $filesystemFactory,
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
        $default = config('filesystems.default');

        if (! is_string($default) || $default === '') {
            return $this->result(
                status: 'failed',
                message: 'No default filesystem disk is configured.',
                isBlocking: true,
            );
        }

        try {
            $disk = $this->filesystemFactory->disk($default);
            $testPath = '.ops_infrastructure_doctor_probe';
            $disk->put($testPath, 'probe');
            $disk->delete($testPath);

            $driver = (string) config(sprintf('filesystems.disks.%s.driver', $default), 'unknown');

            return $this->result(
                status: 'passed',
                message: sprintf('Default storage disk [%s] is writable via [%s].', $default, $driver),
                context: ['disk' => $default, 'driver' => $driver],
            );
        } catch (Throwable $e) {
            return $this->result(
                status: 'failed',
                message: sprintf('Default storage disk [%s] is not accessible: %s', $default, $e->getMessage()),
                isBlocking: true,
                context: ['disk' => $default, 'error' => $e->getMessage()],
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

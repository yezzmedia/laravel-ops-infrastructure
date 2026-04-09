<?php

declare(strict_types=1);

use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureStatus;

it('has the four expected status cases', function (): void {
    expect(OpsInfrastructureStatus::cases())->toHaveCount(4);
});

it('returns correct labels', function (OpsInfrastructureStatus $status, string $expectedLabel): void {
    expect($status->label())->toBe($expectedLabel);
})->with([
    [OpsInfrastructureStatus::Healthy, 'Healthy'],
    [OpsInfrastructureStatus::Warning, 'Warning'],
    [OpsInfrastructureStatus::Failed, 'Failed'],
    [OpsInfrastructureStatus::Unsupported, 'Unsupported'],
]);

it('returns correct colors', function (OpsInfrastructureStatus $status, string $expectedColor): void {
    expect($status->color())->toBe($expectedColor);
})->with([
    [OpsInfrastructureStatus::Healthy, 'success'],
    [OpsInfrastructureStatus::Warning, 'warning'],
    [OpsInfrastructureStatus::Failed, 'danger'],
    [OpsInfrastructureStatus::Unsupported, 'gray'],
]);

it('computes worst status correctly', function (): void {
    expect(OpsInfrastructureStatus::worst([
        OpsInfrastructureStatus::Healthy,
        OpsInfrastructureStatus::Warning,
    ]))->toBe(OpsInfrastructureStatus::Warning);

    expect(OpsInfrastructureStatus::worst([
        OpsInfrastructureStatus::Healthy,
        OpsInfrastructureStatus::Failed,
    ]))->toBe(OpsInfrastructureStatus::Failed);

    expect(OpsInfrastructureStatus::worst([
        OpsInfrastructureStatus::Warning,
        OpsInfrastructureStatus::Failed,
    ]))->toBe(OpsInfrastructureStatus::Failed);
});

it('excludes unsupported from worst by default', function (): void {
    $result = OpsInfrastructureStatus::worst([
        OpsInfrastructureStatus::Healthy,
        OpsInfrastructureStatus::Unsupported,
    ]);

    expect($result)->toBe(OpsInfrastructureStatus::Healthy);
});

it('returns unsupported when all are unsupported', function (): void {
    $result = OpsInfrastructureStatus::worst([
        OpsInfrastructureStatus::Unsupported,
        OpsInfrastructureStatus::Unsupported,
    ]);

    expect($result)->toBe(OpsInfrastructureStatus::Unsupported);
});

it('returns unsupported for empty input', function (): void {
    expect(OpsInfrastructureStatus::worst([]))->toBe(OpsInfrastructureStatus::Unsupported);
});

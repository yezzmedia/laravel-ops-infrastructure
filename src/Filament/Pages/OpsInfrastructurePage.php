<?php

declare(strict_types=1);

namespace YezzMedia\OpsInfrastructure\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;
use UnitEnum;
use YezzMedia\OpsInfrastructure\Actions\RefreshInfrastructureSnapshotAction;
use YezzMedia\OpsInfrastructure\Support\InfrastructureComponentStatus;
use YezzMedia\OpsInfrastructure\Support\InfrastructurePostureSummary;
use YezzMedia\OpsInfrastructure\Support\OpsInfrastructureManager;

class OpsInfrastructurePage extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-server-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Infrastructure';

    protected static ?string $navigationLabel = 'Infrastructure';

    protected static ?int $navigationSort = 60;

    protected static ?string $title = 'Infrastructure Posture';

    protected static ?string $slug = 'ops-infrastructure';

    public static function canAccess(): bool
    {
        return Gate::check('ops.infrastructure.view');
    }

    public static function getNavigationBadge(): ?string
    {
        $manager = app(OpsInfrastructureManager::class);
        $status = $manager->overallStatus();

        return $status->label();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return app(OpsInfrastructureManager::class)->overallStatus()->color();
    }

    public function content(Schema $schema): Schema
    {
        $summary = app(OpsInfrastructureManager::class)->summary();

        return $schema->components([
            $this->overviewSection($summary),
            $this->domainTabs($summary),
            $this->actionsSection(),
        ]);
    }

    private function overviewSection(InfrastructurePostureSummary $summary): Section
    {
        return Section::make('Overview')
            ->schema([
                Grid::make(4)->schema([
                    ...$this->labeledText(
                        'Overall Status',
                        $summary->overallStatus->label(),
                        color: $summary->overallStatus->color(),
                        icon: $summary->overallStatus->icon(),
                        badge: true,
                    ),
                    ...$this->labeledText(
                        'Healthy',
                        (string) $summary->healthyCount,
                        color: 'success',
                        icon: 'heroicon-o-check-circle',
                        badge: true,
                    ),
                    ...$this->labeledText(
                        'Warnings',
                        (string) $summary->warningCount,
                        color: $summary->warningCount > 0 ? 'warning' : 'gray',
                        icon: 'heroicon-o-exclamation-triangle',
                        badge: true,
                    ),
                    ...$this->labeledText(
                        'Failures',
                        (string) $summary->failingCount,
                        color: $summary->failingCount > 0 ? 'danger' : 'gray',
                        icon: 'heroicon-o-x-circle',
                        badge: true,
                    ),
                ]),
                ...$this->labeledText(
                    'Last checked',
                    $summary->completedAt->format('Y-m-d H:i:s T'),
                    color: 'gray',
                ),
            ]);
    }

    private function domainTabs(InfrastructurePostureSummary $summary): Tabs
    {
        return Tabs::make('domains')
            ->tabs([
                $this->queueTab($summary),
                $this->cacheTab($summary),
                $this->databaseTab($summary),
                $this->storageTab($summary),
                $this->runtimeTab($summary),
            ]);
    }

    private function queueTab(InfrastructurePostureSummary $summary): Tab
    {
        $queue = $summary->queue;

        return Tab::make('Queue')
            ->icon($queue->status->icon())
            ->badgeColor($queue->status->color())
            ->schema([
                Grid::make(3)->schema([
                    ...$this->labeledText('Status', $queue->status->label(), color: $queue->status->color(), icon: $queue->status->icon(), badge: true),
                    ...$this->labeledText('Driver', $queue->driver),
                    ...$this->labeledText('Connection', $queue->connection),
                ]),
                ...$this->componentList($queue->connections),
            ]);
    }

    private function cacheTab(InfrastructurePostureSummary $summary): Tab
    {
        $cache = $summary->cache;

        return Tab::make('Cache')
            ->icon($cache->status->icon())
            ->badgeColor($cache->status->color())
            ->schema([
                Grid::make(3)->schema([
                    ...$this->labeledText('Status', $cache->status->label(), color: $cache->status->color(), icon: $cache->status->icon(), badge: true),
                    ...$this->labeledText('Driver', $cache->driver),
                    ...$this->labeledText('Store', $cache->store),
                ]),
                ...$this->componentList($cache->stores),
            ]);
    }

    private function databaseTab(InfrastructurePostureSummary $summary): Tab
    {
        $db = $summary->database;

        return Tab::make('Database')
            ->icon($db->status->icon())
            ->badgeColor($db->status->color())
            ->schema([
                Grid::make(3)->schema([
                    ...$this->labeledText('Status', $db->status->label(), color: $db->status->color(), icon: $db->status->icon(), badge: true),
                    ...$this->labeledText('Driver', $db->driver),
                    ...$this->labeledText('Connection', $db->connection),
                ]),
                ...$this->componentList($db->connections),
            ]);
    }

    private function storageTab(InfrastructurePostureSummary $summary): Tab
    {
        $storage = $summary->storage;

        return Tab::make('Storage')
            ->icon($storage->status->icon())
            ->badgeColor($storage->status->color())
            ->schema([
                Grid::make(2)->schema([
                    ...$this->labeledText('Status', $storage->status->label(), color: $storage->status->color(), icon: $storage->status->icon(), badge: true),
                    ...$this->labeledText('Default Disk', $storage->defaultDisk),
                ]),
                ...$this->componentList($storage->disks),
            ]);
    }

    private function runtimeTab(InfrastructurePostureSummary $summary): Tab
    {
        $runtime = $summary->runtimeResources;

        return Tab::make('Runtime')
            ->icon($runtime->status->icon())
            ->badgeColor($runtime->status->color())
            ->schema([
                Grid::make(3)->schema([
                    ...$this->labeledText('Status', $runtime->status->label(), color: $runtime->status->color(), icon: $runtime->status->icon(), badge: true),
                    ...$this->labeledText('PHP Version', $runtime->phpVersion),
                    ...$this->labeledText('Laravel Version', $runtime->laravelVersion),
                ]),
                Grid::make(3)->schema([
                    ...$this->labeledText('Memory Usage', sprintf('%d MB', $runtime->memoryUsageMb)),
                    ...$this->labeledText('Memory Limit', sprintf('%d MB', $runtime->memoryLimitMb)),
                    ...$this->labeledText('Memory %', sprintf('%.1f%%', $runtime->memoryUsagePercent), color: $runtime->status->color(), badge: true),
                ]),
            ]);
    }

    /**
     * @param  array<int, InfrastructureComponentStatus>  $components
     * @return array<int, Section>
     */
    private function componentList(array $components): array
    {
        if ($components === []) {
            return [];
        }

        return [
            Section::make('Components')
                ->schema(array_merge(...array_map(
                    fn (InfrastructureComponentStatus $c): array => $this->labeledText(
                        $c->component,
                        $c->message,
                        color: $c->status->color(),
                        icon: $c->status->icon(),
                    ),
                    $components,
                ))),
        ];
    }

    /**
     * @return array{Text, Text}
     */
    private function labeledText(
        string $label,
        string $value,
        ?string $color = null,
        ?string $icon = null,
        bool $badge = false,
    ): array {
        $valueText = Text::make($value);

        if ($badge) {
            $valueText = $valueText->badge();
        }

        if ($color !== null) {
            $valueText = $valueText->color($color);
        }

        if ($icon !== null) {
            $valueText = $valueText->icon($icon);
        }

        return [
            Text::make($label)
                ->badge()
                ->color('gray'),
            $valueText,
        ];
    }

    private function actionsSection(): Actions
    {
        return Actions::make([
            Action::make('refresh')
                ->label('Refresh Snapshot')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Refresh Infrastructure Snapshot')
                ->modalDescription('This will probe all infrastructure components and generate a fresh posture snapshot.')
                ->visible(fn (): bool => Gate::check('ops.infrastructure.manage'))
                ->action(function (): void {
                    app(RefreshInfrastructureSnapshotAction::class)->execute('filament');

                    Notification::make()
                        ->success()
                        ->title('Infrastructure snapshot refreshed')
                        ->send();
                }),
        ]);
    }
}

<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/yezzmedia/.github/main/profile/yezzmedia-dark.svg">
    <img src="https://raw.githubusercontent.com/yezzmedia/.github/main/profile/yezzmedia-light.svg" alt="Yezz Media" height="40">
  </picture>
</p>

<p align="center">
  <a href="https://packagist.org/packages/yezzmedia/laravel-ops-infrastructure"><img src="https://img.shields.io/packagist/v/yezzmedia/laravel-ops-infrastructure?style=flat-square" alt="Latest Version"></a>
  <a href="https://packagist.org/packages/yezzmedia/laravel-ops-infrastructure"><img src="https://img.shields.io/packagist/php-v/yezzmedia/laravel-ops-infrastructure?style=flat-square" alt="PHP Version"></a>
  <a href="https://packagist.org/packages/yezzmedia/laravel-ops-infrastructure"><img src="https://img.shields.io/packagist/l/yezzmedia/laravel-ops-infrastructure?style=flat-square" alt="License"></a>
</p>

---

# Laravel Ops &middot; Infrastructure

`yezzmedia/laravel-ops-infrastructure` provides live infrastructure posture diagnostics for the Yezz Media ops panel.

It resolves runtime posture for queue, cache, database, storage, and runtime resources, building aggregated summaries and surfacing actionable warnings through shared doctor checks and cached diagnostics snapshots.

## Version

Current release: `0.2.0`

## Requirements

- PHP `^8.5`
- Laravel `^13.0` components
- `spatie/laravel-package-tools ^1.93`
- `yezzmedia/laravel-foundation ^0.2`
- `yezzmedia/laravel-ops ^0.2`

## Installation

```bash
composer require yezzmedia/laravel-ops-infrastructure
```

## What The Package Provides

### Posture Resolvers

Individual resolvers inspect live infrastructure state:

- **Queue** — connection health, pending jobs, failed jobs
- **Cache** — store connectivity, hit/miss ratios
- **Database** — connection status, migration state
- **Storage** — disk availability, permissions
- **Runtime** — PHP version, extensions, memory limits

### Summary Building

`InfrastructureSummary` aggregates resolver results into a normalized posture summary surfaced in the ops panel overview, with color-coded status indicators (Healthy, Warning, Failed, Unsupported).

### Doctor Checks

Declares foundation-aligned doctor checks for:

- Connectivity verification per infrastructure domain
- Configuration validation
- Runtime metrics thresholds

### Cached Diagnostics

Diagnostics snapshots are cached with configurable TTLs to avoid repeated live checks during a single ops panel session.

## Development

```bash
composer test
composer analyse
composer format
```

## License

MIT

---
name: ops-infrastructure-development
description: "Build and maintain yezzmedia/laravel-ops-infrastructure. Activate when changing infrastructure posture resolvers, summary building, infrastructure refresh flows, doctor or install declarations, infrastructure Filament pages, audit integration, or package tests that depend on the approved infrastructure V1 surface."
license: MIT
metadata:
  author: yezzmedia
---

# Ops Infrastructure Development

## Documentation

Use `search-docs` for Laravel, Filament, Pest, Package Tools, and Boost details. Use the reference files in this skill for the approved infrastructure runtime surface.

Use the `foundation-package-development` skill when descriptor capability choices or foundation registration behavior change.

## When To Use This Skill

Activate this skill when working inside `yezzmedia/laravel-ops-infrastructure`, especially when changing:

- queue, cache, database, storage, or runtime posture resolvers
- summary building and snapshot aggregation
- install-step or doctor-check declarations
- infrastructure refresh flow, audit integration, or operator page content
- package tests that prove registration, diagnostics, and UI behavior

## Core Rules

- Keep the package thin and posture-focused.
- Keep resolver logic separated by infrastructure domain.
- Keep the summary builder as the aggregation boundary.
- Keep optional host health-provider ideas out of the current runtime unless the package surface is explicitly expanded.
- Keep audit integration optional and config-driven.

## References

- Use [references/runtime-surface.md](references/runtime-surface.md) for the approved infrastructure surface.
- Use [references/install-and-doctor.md](references/install-and-doctor.md) for current install and doctor boundaries.
- Use [references/filament-surface.md](references/filament-surface.md) for operator page ownership.
- Use [references/testing.md](references/testing.md) for verification expectations.
- Use [references/checklist.md](references/checklist.md) before finalizing infrastructure changes.

## Common Pitfalls

- adding persistence or unrelated runtime ownership to a posture-only package
- merging queue, cache, database, storage, and runtime logic into one resolver
- documenting optional health-provider context as if it were a shipped health-ingestion runtime
- adding pages or modules without keeping descriptor declarations aligned

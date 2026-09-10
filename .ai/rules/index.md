# Project Rules Index

Before planning or editing, find the row whose globs match the file's path and read that rule file.

| Applies to | Rule file |
| --- | --- |
| app/**, resources/**, database/**, routes/**, tests/**, config/** | .ai/rules/language.md |
| app/**, database/**, routes/**, tests/** | .ai/rules/php.md |
| app/** | .ai/rules/app.md |
| app/Ai/** | .ai/rules/ai.md |
| app/Console/** | .ai/rules/console.md |
| app/Jobs/** | .ai/rules/jobs.md |
| app/Livewire/** | .ai/rules/livewire.md |
| app/Models/** | .ai/rules/models.md |
| config/** | .ai/rules/config.md |
| database/** | .ai/rules/database.md |
| resources/views/** | .ai/rules/views.md |
| routes/** | .ai/rules/routes.md |
| tests/** | .ai/rules/tests.md |
| ** (all commands) | .ai/rules/docker.md |

## Directory map

The structure the rules assume — these directories exist (kept by `.gitkeep`); put new classes in the matching one rather than inventing a folder.

| Path | Holds |
| --- | --- |
| `app/Actions/<Domain>/` | Use cases — the only place business logic and writes live |
| `app/Queries/<Domain>/` | Non-trivial reads, read-only |
| `app/Services/<Vendor>/` | HTTP / external integrations |
| `app/Data/<Domain>/` | `final readonly` DTOs |
| `app/Enums/` | Backed enums (status, role, type) |
| `app/Exceptions/<Domain>/` | Domain exception base + cases |
| `app/Policies/` | One policy per model |
| `app/Rules/` | Reusable validation rule objects |
| `app/Http/Requests/` | Form Requests (controller boundary) |
| `app/Livewire/Forms/` | Livewire Form objects |
| `app/Jobs/` | Queued wrappers around Actions |
| `tests/Feature/{Actions,Queries,Services,Livewire,Policies,Jobs,Console}/` | Mirrors the class paths above |
| `tests/Unit/{Enums,Data}/` | Only for enums/DTOs that carry real logic |

## PHP style (applies to every PHP file)

`declare(strict_types=1);` in every file, `final` by default (`final readonly` for Actions/Queries/Services/DTOs), explicit types everywhere. `nunomaduro/mock-final-classes` strips `final`/`readonly` during test runs, so `final` never blocks mocking. Details: `.ai/rules/php.md`.

## Language (applies everywhere)

Identifiers, schema, route names, log/exception messages, comments and test names in **English**. Everything a user reads — labels, buttons, toasts, validation messages, URL slugs — in **pt-BR**. Details: `.ai/rules/language.md`.

## Docker (MANDATORY, applies to every command)

This project runs inside Docker. **Always** execute project commands inside the app container — never on the host: `docker compose exec -T blade_app <command>`. Details in `.ai/rules/docker.md`.

## Database drivers

`sqlsrv` (SQL Server) in dev/production, `sqlite` `:memory:` in tests. Migrations and queries must work on both — see `.ai/rules/database.md`.

## Framework defaults (nunomaduro/essentials)

`nunomaduro/essentials` v1.2 is installed, config published at `config/essentials.php`. Active defaults, application-wide:

| Configurable | Effect |
| --- | --- |
| `ShouldBeStrict` | Lazy loading, missing attributes and discarded attributes all throw |
| `Unguard` | Mass-assignment protection OFF — only ever pass validated data to `create()`/`update()`/`fill()` |
| `AutomaticallyEagerLoadRelationships` | Relationship access on a collection auto-eager-loads |
| `ImmutableDates` | Dates are `CarbonImmutable` — never mutate in place |
| `PreventStrayRequests` | Unfaked HTTP in tests fails |
| `FakeSleep` | `Sleep` is faked in tests |
| `AggressivePrefetching` | Livewire/Laravel link prefetching |
| `ProhibitDestructiveCommands` | `migrate:fresh` and friends blocked in production |
| `ForceScheme` | HTTPS forced in `production` only |
| `SetDefaultPassword` | `Password::defaults()` = min 12, mixed case, numbers, symbols, uncompromised (production only) |

Details for the Eloquent consequences: `.ai/rules/models.md`.

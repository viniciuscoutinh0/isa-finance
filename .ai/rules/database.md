---
paths:
  - 'database/**'
---

# Database

## Migrations must run on both SQL Server and SQLite
Two drivers, always: **`sqlsrv`** for dev/production (`DB_CONNECTION=sqlsrv`) and **`sqlite` `:memory:`** for the test suite (`phpunit.xml`). Every migration must run on both, and any behavior that differs between them is a production bug the suite cannot see.

**Portability — avoid what only one driver has**
- No `enum()` column: SQL Server has no ENUM. Use `string()` and cast to a PHP enum in the model (`.ai/rules/app.md`).
- No `->after()` / `->first()` column positioning — MySQL only, silently useless here.
- No `whereFullText`, no `->where('payload->key')` JSON path queries. A `json()` column is `nvarchar(max)` on sqlsrv; filter in PHP or add a real column.
- `boolean()` is `bit` — always cast to `bool` in `casts()`; never compare with `'1'`/`'true'`.
- `text()`/`longText()` map to `nvarchar(max)`, which cannot be indexed — index a `string()` column instead.
- Collation differs — **do not add a shadow column for it**. SQL Server compares case-insensitively by default; SQLite's `LIKE` is case-insensitive for ASCII and case-**sensitive** for accented characters. Make the query explicit instead of duplicating data: lowercase both sides in SQL, `->whereRaw('lower(title) like ?', ['%'.Str::lower($search).'%'])`, so the behavior is the same on both drivers and lives in the Query class. Accepted limit: SQLite without ICU does not fold accents (`CAFÉ` will not match `café` there), which is a test-only artifact — production runs on sqlsrv. A duplicated/normalized column is only justified when a real index on it is needed for volume, and that is a deliberate decision, not the default.
- `datetime()` is `datetime2` on sqlsrv; keep `timestamps()` and let Laravel handle it. Dates come back as `CarbonImmutable` (Essentials).

**Structure**
- One migration per change; never edit a migration that is already committed/applied — add a new one.
- Always a real `down()`; anonymous-class migration style (`return new class extends Migration`).
- Foreign keys with `->constrained()` and an explicit `->cascadeOnDelete()`/`->nullOnDelete()`. On SQL Server two cascade paths to the same table fail — use `->nullOnDelete()` or handle it in the Action.
- Index every column used in a `where`/`orderBy` by a Query class. Name long composite indexes explicitly (SQL Server limit is 128 chars).
- Money is a `bigint` of minor units (centavos), never `float` and never `decimal` — see `docs/adr/0002-dinheiro-em-centavos.md`. Wrap it in the `Money` DTO (`app/Data/`); parse/format pt-BR only at the edge.
- Column and table names: `snake_case`, tables plural, FK `<singular>_id`, booleans `is_`/`has_`.

**Factories & seeders**
- Every model gets a factory (`database/factories/`), with named states for meaningful variations; factory attributes must be real columns — strict mode throws otherwise.
- `DatabaseSeeder` stays runnable and idempotent; demo data goes in its own seeder, never in a migration.

**Commands** (always inside Docker):
`docker compose exec -T blade_app php artisan make:migration <name> --no-interaction`
`docker compose exec -T blade_app php artisan migrate`
`migrate:fresh` is blocked in production by Essentials' `ProhibitDestructiveCommands` — never try to force it there.

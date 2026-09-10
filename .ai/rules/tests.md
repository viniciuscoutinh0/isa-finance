---
paths:
  - 'tests/**'
---

# Tests

## Test layout per layer, real DB, faked network
Pest 5 + `pest-plugin-laravel` + `pest-plugin-livewire`. Create with `php artisan make:test --pest {Name}Test` (`--unit` only for framework-free logic). Read the `testing-best-practices` skill before writing tests.

**Where each layer is tested** — file is `{ClassName}Test.php` at the same relative path as the class:
- Action → `tests/Feature/Actions/...` — the real use case: writes persisted, events/jobs dispatched, exceptions on invalid input. Every branch.
- Query → `tests/Feature/Queries/...` — seed records that must and must not match, assert filtering/ordering/pagination.
- Service (HTTP) → `tests/Feature/Services/...` — `Http::fake()` on the exact endpoint (stray requests already fail: Essentials enables `PreventStrayRequests`); assert the returned DTO and the mapped exception on 4xx/5xx/timeout. Never hit the network.
- Livewire → `tests/Feature/Livewire/...` — `Livewire::test(Component::class)`; assert wiring only (validation errors, `assertSee`/`assertSet`, redirect, authorization). Business assertions belong to the Action test.
- Policy → `tests/Feature/Policies/...` — one test per method with a dataset or explicit cases: owner allowed, non-owner denied, and any role/state that changes the answer. Assert through `$user->can('update', $model)`, not by calling the policy class directly.
- Job → `tests/Feature/Jobs/...` — call `handle()` directly with the Action's dependencies in place; assert idempotency (running twice leaves one result) and the `failed()` path when it matters. The dispatch itself is asserted with `Queue::fake([TheJob::class])` in the caller's test.
- Console command → `tests/Feature/Console/...` — `$this->artisan('name --option')->assertSuccessful()` plus an assertion on the Action's effect or a faked queue push.
- Model → no test file of its own. A cast, a scope or a relationship is exercised through the Action or Query that uses it.
- Enum / DTO → no test file of its own either, unless it carries real logic (`isFinal()`, `fromResponse()` mapping); then `tests/Unit/Enums/...` or `tests/Unit/Data/...` with a dataset over `cases()`.
- Form object → covered through its component's test.

**Rules**
- Real database, real queries — never mock the query builder. `tests/Pest.php` applies `RefreshDatabase` to the whole `Feature` suite, so every feature test starts on a migrated, empty SQLite database. (If the suite ever gets slow with many DB-free tests, `LazilyRefreshDatabase` is the drop-in swap.)
- Always factories with named states, created inside the test, not in `beforeEach()`. Datasets for enum/role/boundary variations.
- Fake anything nondeterministic: `freezeTime()`/`travelTo()`, `Queue::fake()`/`Event::fake()` with explicit class names, `Mail`/`Storage` fakes. `Sleep` and stray HTTP are already handled by Essentials (`FakeSleep`, `PreventStrayRequests`).
- Strict mode is on (`Model::shouldBeStrict()`): a test that lazy-loads or reads a missing attribute throws. Factory attributes must be real columns. See `.ai/rules/models.md`.
- Name states the observable result: `it('cancels the order and refunds the payment')`, never `it('works')` or a method name.
- Every action reachable by a user gets an authorization test (guest, wrong owner/tenant, right user).
- Run inside Docker: `docker compose exec -T blade_app php artisan test --compact` (narrow with a path or `--filter`).

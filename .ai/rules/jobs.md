---
paths:
  - 'app/Jobs/**'
---

# Jobs

## Jobs are thin wrappers around Actions
`QUEUE_CONNECTION=sync` locally and in tests; the `database` driver (the `jobs` table already exists) is the production target. Code must be correct on both — `sync` hides latency, retries and serialization bugs.

**Shape**
- A job holds no business logic. It resolves an Action and calls it: constructor takes ids/scalars/DTOs, `handle(SendInvoice $action): void` type-hints the Action for container injection.
- Never store a whole Eloquent model in a job payload — pass the id and load it in `handle()`, or use `SerializesModels` knowing it re-queries on unserialize. A deleted record must be handled, not crash the worker forever.
- `implements ShouldQueue`. Declare `public int $tries`, `public int $timeout`, and `public function backoff(): array` (exponential, e.g. `[10, 60, 300]`) explicitly — never rely on defaults for anything touching an external service.
- Make `handle()` idempotent: a job can run twice. Guard with a state check or a unique key, not with hope. Use `ShouldBeUnique` when double-dispatch is possible.
- Dispatch **after** the transaction commits: `DB::afterCommit()` or `->afterCommit()` on the dispatch. A job dispatched inside a transaction can start before the row exists.
- `failed(Throwable $e)` for cleanup/notification on final failure. Domain exceptions that must not retry: catch and `$this->fail($e)` (see `.ai/rules/app.md`).
- Long chains use `Bus::chain()`/`batch()`, not a job that dispatches the next one at the end.

**Tests** — `Queue::fake([SendInvoice::class])` in the dispatcher's test and assert the push plus its payload; test the job itself by calling `handle()` directly with the Action's dependencies faked. Do not assert side effects through `sync` execution.

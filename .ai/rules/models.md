---
paths:
  - 'app/Models/**'
---

# Models

## Eloquent under Essentials strict mode
`nunomaduro/essentials` v1.2 is installed with `config/essentials.php` published; its defaults change how Eloquent behaves here.

**Models are unguarded (`Model::unguard()`)** — mass-assignment protection is OFF globally.
- Do not write `$fillable`/`$guarded`, nor the Laravel 13 `#[Fillable]` attribute; they do nothing while unguarded. `#[Hidden]` still matters: it controls serialization, not mass assignment.
- Security is now the caller's job: **never** pass `$request->all()`, `$this->form->all()` of an unvalidated form, or any raw user array into `create()`/`update()`/`fill()`. Pass validated data only (`$request->validated()`, `$this->form->validate()`), or an explicit array of keys.

**Strict mode (`Model::shouldBeStrict()`)** — throws instead of failing silently:
- Lazy loading throws → eager load explicitly with `with()` in the Query class. (`automaticallyEagerLoadRelationships()` is also on, so collection access auto-loads; still declare `with()` — it keeps the query intentional and works for single models.)
- Accessing a missing attribute throws → any `select()` must include every column read afterwards, including in Blade.
- Discarding an unknown attribute throws → factory states and `create()` arrays must use real columns.

**Model content**
- Model holds schema concerns only: relationships (typed return, e.g. `: BelongsTo`), `casts()` method, accessors/mutators, small reusable scopes. No business logic (that is an Action), no non-trivial reads (that is a Query).
- Enum-backed columns cast to an Enum in `casts()`; dates are `CarbonImmutable` (`ImmutableDates` is on) — `$model->created_at->addDay()` returns a new instance, it never mutates.
- Every model gets a factory; named states for meaningful variations.

Create with `php artisan make:model <Name> -mf --no-interaction`.

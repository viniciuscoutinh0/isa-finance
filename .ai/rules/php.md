---
paths:
  - 'app/**'
  - 'database/**'
  - 'tests/**'
  - 'routes/**'
---

# PHP style

## final by default, strict_types everywhere

PHP 8.4. Every PHP file that declares a class starts with:

```php
<?php

declare(strict_types=1);
```

Right after `<?php`, blank line on each side. This is not optional — mixed strict/non-strict files make type errors appear only in some call paths.

**`final` by default**
- Every class is `final` unless there is a written reason not to: `final class TodoPolicy`, `final class Todo` (models too), `final class TodoList` (Livewire components).
- The only non-final classes: an abstract domain-exception base (`abstract class TodoException`), an abstract base a framework requires, and anonymous migration classes (`return new class extends Migration`).
- Do not extend an application class to reuse code — inject and compose. Inheritance here is for framework base classes only.
- Stateless collaborators are `final readonly`: Actions, Queries, Services, DTOs. A class with mutable state (Livewire component, Form object, model) is `final` only.

**Types**
- Explicit parameter and return types on every method, `void` included. Never leave a return type off.
- Constructor property promotion for dependencies; no empty zero-argument `__construct()`.
- `array` gets a PHPDoc array shape or generic (`@param array{title: string, notes?: ?string}`, `@return LengthAwarePaginator<int, Todo>`). Bare `array` with no docblock is incomplete.
- Curly braces on every control structure, single-line bodies included.

**Mocking is not blocked by `final`**
- `nunomaduro/mock-final-classes` v1.2 is installed as a dev dependency. Through `dg/bypass-finals` v1.11 it strips `final` **and** `readonly` from source on the fly while Pest/PHPUnit runs, so a `final readonly` Action or Service can still be mocked. No `@internal`/interface gymnastics needed just to make a class testable.
- That is a safety net, not a default: the testing rule still ranks framework fakes → project fake → mock → real implementation, and the database is always real (`.ai/rules/tests.md`).
- It only applies at test runtime. Never write application code that depends on `final` being removable, and note it cannot help with static calls or facade internals.

**Formatting**
- Run `docker compose exec -T blade_app vendor/bin/pint --dirty --format agent` before finishing any PHP change. Pint is the authority on spacing and import order — do not hand-format against it.
- `--dirty` needs git; this project is **not a git repository yet**, so until `git init` runs, pass the paths instead: `vendor/bin/pint --format agent app routes tests database`.

No known drift: every PHP file in `app/`, `database/`, `routes/` and `tests/` is `declare(strict_types=1)` and `final` where the rule asks (checked 2026-09-09).

---
paths:
  - 'config/**'
---

# Config

## env() only inside config/, secrets never in code
- Call `env()` **only** inside `config/*.php`. Anywhere else (Action, Service, Livewire, Blade, job) it returns `null` once `config:cache` runs in production. Read `config('services.foo.key')` instead.
- Every integration gets an entry in `config/services.php`: base URL, key, timeout, and any feature flag — one array per vendor, keys in `snake_case`. A Service class reads only from there (`.ai/rules/app.md`).
- Give each `env()` a sane default in the config file when one exists; for a required secret use no default so a missing value fails loudly.
- Add every new key to `.env.example` with a placeholder (never a real value) in the same commit. Never commit `.env`, never paste a real credential into a config file, a test, a factory, or a rule file.
- Never call `config()->set()` at runtime to change behavior; pass the value instead. Tests may use `config()->set()` for arrangement only.
- Booleans/ints come back as strings from the environment — wrap with `(bool)`/`(int)`, or use `config()->boolean(...)` / `->integer(...)` when reading.
- Test-only overrides belong in `phpunit.xml`'s `<php>` block, not in `.env.testing` duplicated by hand.
- After changing anything under `config/`, clear the cache in the container: `docker compose exec -T blade_app php artisan config:clear`.

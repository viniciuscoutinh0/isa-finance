---
paths:
  - 'routes/**'
---

# Routes

## Route::livewire for pages, always named routes
Livewire v4 is the default UI layer here — a page is a Livewire component, not a controller.

- Register full-page components with `Route::livewire('/orders/{order}', OrderShow::class)->name('orders.show');`. `Route::get('/x', Component::class)` still works for class-based components but is no longer the recommended form (`routes/web.php` is already converted).
- **Every route gets `->name()`**, dot-namespaced by resource (`orders.index`, `orders.show`). Link with `route('orders.show', $order)` and redirect with `$this->redirectRoute('orders.show', ...)` — never a hardcoded path.
- Route model binding over an id: type-hint the model in `mount()` (or declare the typed public property). Scope nested bindings with `->scopeBindings()`.
- Group by area with a shared prefix, name prefix and middleware; auth-protected pages live in a single `middleware('auth')` group, not repeated per route. Coarse checks (`auth`, `verified`, `can:`) belong in middleware; per-record authorization belongs in the Policy call inside the component (`.ai/rules/app.md`).
- A controller is for what a Livewire page cannot do: file download/stream, webhook receiver, OAuth callback, API endpoint. Those stay thin and delegate to an Action.
- Keep `routes/web.php` a route table only — no closures with logic, no queries. Scheduled tasks go in `routes/console.php` (`.ai/rules/console.md`).
- Inspect with `docker compose exec -T blade_app php artisan route:list`. Do **not** filter with `--except-vendor` here: a `Route::livewire()` page resolves to Livewire's own `LivewirePageController`, so that flag hides every page in the app. Filter with `--name=` or `--path=` instead.

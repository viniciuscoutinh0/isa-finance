---
paths:
  - 'resources/views/**'
---

# Views

## Flux Pro first, Tailwind v4, layout via partials
Stack: Flux **Pro** 2.19 + Tailwind **v4** (CSS-first, `resources/css/app.css` — no `tailwind.config.js`) + Livewire v4 class-based components (`app/Livewire/` + `resources/views/livewire/`).

**Flux first**
- Look for a `<flux:*>` component before writing raw HTML or hand-styled markup. Pro is licensed here, so the full set is available: accordion, autocomplete, avatar, badge, breadcrumbs, button, calendar, callout, card, chart, checkbox, command, date-picker, dropdown, editor, field, file-upload, icon, input, kanban, modal, navbar, pagination, popover, profile, progress, radio, select, separator, skeleton, slider, switch, table, tabs, text, textarea, time-picker, timeline, toast, tooltip.
- Use `search-docs` for a component's real API instead of guessing props.
- Forms are always `<flux:field>` + `<flux:label>` + input + `<flux:error name="form.field" />` — never a bare `<input>` with utility classes.
- Style a Flux component through its own props (`variant`, `size`, `icon`) first; add utility classes only for layout (spacing, grid, width).
- Icons are Heroicons by default — copy exact names from heroicons.com, never invent one. A Lucide icon must be imported first: `php artisan flux:icon <name>`.
- Only build a Blade component when no Flux component fits, or when composing several Flux components into a repeated project-specific block.

**Blade vs Livewire**
- Livewire component = has server state or actions. Blade component (`resources/views/components/`) = presentation only, no state. Do not create a Livewire component just to render static markup.
- Page layout lives in `resources/views/layouts/app.blade.php`, composed from `<x-partials.head />` / `<x-partials.body>`. Add global assets/meta in those partials, never inline in a page.
- `<body>` carries Flux's own baseline (`min-h-screen bg-white dark:bg-zinc-800 antialiased`) via `$attributes->merge()` in `partials/body.blade.php`; `@fluxAppearance` in the head puts the `dark` class on `<html>`. A page adds classes by passing them to `<x-partials.body class="…">`, never by editing that default.
- A full-page component's title comes from `#[Title]` on the class; the layout must forward it (`<x-partials.head :title="$title ?? null" />`) because a Blade component does not inherit variables from its parent scope.
- Views hold no queries and no business logic: read a computed property (`$this->orders` → `$orders`), never call the model or a Query from Blade. Strict mode makes a lazy load in Blade throw.

**Tailwind v4**
- Theme tokens go in the `@theme` blocks in `app.css`; do not add a v3-style config file.
- **The grey ramp is remapped**: `app.css` points every `--color-zinc-*` at `--color-neutral-*`. Flux components are built on `zinc`, so the whole UI follows. Consequence: keep writing `zinc-*` utilities (`bg-zinc-50`, `border-zinc-200`) — they resolve to neutral. Never write `gray-*`, `slate-*`, `stone-*`, or `neutral-*` directly: those bypass the remap and drift away from the Flux surfaces.
- **Accent is rose**, declared once as `--color-accent` / `--color-accent-content` / `--color-accent-foreground` (with a lighter `accent-content` under `.dark` for contrast). Reach for the accent through Flux (`variant="primary"`, `color="accent"`) or the `accent` utilities — never hardcode `rose-*` in a template, or a future palette change misses it.
- Semantic colors (success, warning, danger) stay their own hues (`flux:badge color="green"`, `variant="danger"`) and are not the accent.
- Every surface pairs light and dark (`bg-white dark:bg-zinc-800`). The one allowed exception is an **identity band** — a hero or marketing section that deliberately commits to a single look (the cosmic hero on `welcome`). Fix its palette explicitly and say so in a comment; never reach that state by forgetting the `dark:` half.
- Decorative layers are `aria-hidden="true"` and `pointer-events-none`, and any animation is gated behind `motion-safe:` so `prefers-reduced-motion` kills it.
- Arbitrary values are for what has no scale step — radial gradients, inset shadows, animation durations. Spacing, colors from the ramp and type sizes always use the scale.
- Dark mode is the `.dark` class variant (`@custom-variant dark` in `app.css`) with `@fluxAppearance` in the head — every custom color pairs with a `dark:` variant. Flux components already handle their own.
- Prefer Flux tokens/variants over ad-hoc palette values; keep arbitrary values (`[13px]`) out unless there is no scale step.

After changing assets the user must run `npm run dev`/`npm run build` (`docker compose exec -T blade_app npm run build`).

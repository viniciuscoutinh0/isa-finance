---
paths:
  - 'app/**'
---

# App

## Business logic lives in single-purpose Actions
All business logic goes in Action classes under `app/Actions/<Domain>/`, never in controllers, Livewire components, models, jobs or commands — those only validate/authorize, call the Action, and return the result.

Rules:
- One Action = one use case. Name it as an imperative verb phrase: `CreateOrder`, `CancelSubscription`, `SyncProductStock`.
- Single public entry point: `public function handle(...): <type>`. Explicit param and return types.
- Dependencies via constructor property promotion; resolve the Action from the container (inject it or `app(CreateOrder::class)`), do not `new` it with dependencies.
- No HTTP concerns inside an Action (no Request, no redirect, no response). Accept scalars/DTOs/models, return data or a model.
- Actions may call other Actions. Wrap multi-write flows in `DB::transaction()` inside the Action.
- Each Action gets its own feature test; controllers/Livewire tests then only assert wiring.

Create with `php artisan make:class Actions/<Domain>/<ActionName>`.

## Services for HTTP/external, Queries for reads — keep Actions thin
An Action orchestrates a use case; it must not hold integration or query code. Extract those:

**Services — `app/Services/<Vendor|Domain>/`** (e.g. `Services/Correios/CorreiosClient.php`)
- Wrap every external/HTTP call. Use Laravel `Http::` with `baseUrl`, `timeout`, `retry`; config/keys from `config/services.php`, never inline.
- Public methods are domain verbs (`quoteShipping()`), not endpoint names. Return DTOs/arrays, never a raw `Response`.
- Translate failures into own exceptions (`app/Exceptions/`), never leak `RequestException` to callers.
- Bind interface → implementation in `AppServiceProvider` when a fake is needed; test with `Http::fake()`.

**Queries — `app/Queries/<Domain>/`** (e.g. `Queries/Orders/PendingOrdersQuery.php`)
- Any read with more than a trivial `where`: multi-join, aggregation, filters/sorting, reporting, reused Eloquent chain.
- Single entry point `handle(...)` returning Collection/LengthAwarePaginator/Builder. Filters as typed params or a DTO.
- Read-only: no writes, no side effects. Prefer scopes on the model for small reusable fragments; a Query when the chain grows.

**Boundaries**
- Action = orchestration + writes + transaction. It calls Services and Queries; they never call an Action.
- Controllers/Livewire may call a Query directly for plain listings — no empty Action wrapper.
- Inject Services/Queries via constructor promotion, resolved by the container.
- Action longer than ~40 lines, or containing `Http::`/a long Eloquent chain, means an extraction is missing.

Create with `php artisan make:class Services/<Vendor>/<Name>` / `make:class Queries/<Domain>/<Name>Query`.

## Validation at the boundary, authorization in Policies
**Validation — at the entry point, never inside an Action**
- Livewire: a Form object with `#[Validate]` (see the Livewire rule); call `$this->form->validate()` before handing data to the Action.
- Controllers/HTTP: a Form Request in `app/Http/Requests/`, one per use case (`StoreOrderRequest`). No inline `$request->validate()` once there is more than a field or two, and never `Validator::make()` by hand.
- Actions receive already-valid input (typed params or a DTO). They still enforce *domain* invariants — state machine, stock, balance — by throwing a domain exception, not by returning `false`.
- Rules that hit the database (`exists`, `unique`) stay in the Form Request/Form object. Shared rule sets go in a custom `Rule` object in `app/Rules/`, not copy-pasted.
- Because models are unguarded (Essentials), validated data is the *only* thing that may reach `create()`/`update()`. See `.ai/rules/models.md`.

**Authorization — Policies, checked at every entry point**
- One policy per model in `app/Policies/<Model>Policy.php` (auto-discovered). Create with `php artisan make:policy <Model>Policy --model=<Model> --no-interaction`.
- Roles/permissions are backed enums in `app/Enums/` (TitleCase cases). Never compare raw strings like `'admin'`.
- Livewire: `$this->authorize('update', $order)` at the top of **every** public action method, and in `mount()` for the page itself. A hidden button is not authorization.
- Controllers: `Gate::authorize(...)` in the action, or `authorizeResource()` for a resource controller. Route middleware `can:` for the coarse check only.
- Actions do not authorize — they assume the caller did. Exception: an Action reachable from a queue/command with a user context authorizes explicitly.
- Ownership/tenant filtering belongs in the Query (`where('user_id', ...)`) as well as the policy — never rely on the policy alone to scope a listing.
- Every policy method gets a test: guest, wrong owner, right user. Blocking a hidden UI element is not proof.

## DTOs in app/Data, Enums in app/Enums
No DTO package is installed — plain PHP 8.4 classes.

**DTOs — `app/Data/<Domain>/<Name>Data.php`**
- `final readonly class` with promoted, typed constructor properties. No setters, no logic beyond deriving a value from its own fields.
- Named constructors for each source: `fromResponse(array $payload)`, `fromRequest(...)`, `fromModel(...)`. Keep the mapping from an external payload inside the DTO, not in the Service method.
- Use one when data crosses a boundary with more than ~2 fields: Service → Action, Action → caller, or a filter set passed into a Query. Do not wrap a single scalar, and do not build a DTO that just mirrors a model — pass the model.
- Nullable/optional fields get explicit defaults; collections are typed in PHPDoc with an array shape or `Collection<int, XData>`.
- `toArray()` only when something actually needs an array (a `create()` call, a job payload). Never make it serialize for Blade convenience.
- Livewire: a DTO cannot sit in a public property unless it implements `Livewire\Wireable`. Prefer keeping DTOs out of component state — hold ids/scalars, rebuild the DTO in the computed prop or the action method.

**Enums — `app/Enums/<Name>.php`**
- Always backed (`: string` unless the domain is genuinely numeric), cases in TitleCase: `case AwaitingPayment = 'awaiting_payment';`.
- Any fixed set of values is an enum: status, role, type, channel. Never a bare string compared with `===` and never a class constant.
- Behavior lives on the enum: `label()` for the UI, `color()` for a Flux badge, and predicates like `isFinal()` — instead of `match` blocks scattered across components.
- Cast the column in the model's `casts()`, validate with `Rule::enum(OrderStatus::class)`, and store the backing value in migrations (`->default(OrderStatus::Draft->value)`).
- Livewire public properties may hold an enum (it serializes fine); pass `OrderStatus::cases()` to the view for selects.
- Test an enum only through behavior that uses it, or with a dataset over `cases()`.

Create with `php artisan make:class Data/<Domain>/<Name>Data` and `php artisan make:enum <Name> --string --no-interaction`.

## Domain exceptions: one base per domain, thrown by Actions and Services
Failures are exceptions, never `false`, `null`, or an `['ok' => false]` array.

**Shape — `app/Exceptions/<Domain>/`**
- One abstract base per domain (`abstract class OrderException extends RuntimeException`), concrete cases extend it, so a caller can catch the whole domain.
- Named constructors carry the context: `OrderAlreadyPaid::for($order)`, `CorreiosUnavailable::fromResponse($response)`. No message built at the throw site.
- Message is for a developer/log. User-facing text is chosen at the boundary (Livewire/controller), from `getMessage()` only when it is safe to show.
- Never throw or catch bare `\Exception`. Never catch an exception only to `report()` and continue without a decision.

**Who throws what**
- Service: catches the transport failure (`RequestException`, timeout, invalid payload) and rethrows its own — `RequestException` must not escape a Service. Use `Http::throw()` inside a `try`, or check `$response->failed()`.
- Action: throws a domain exception when an invariant fails (wrong state, no stock, insufficient balance). Validation-shaped problems are the boundary's job, not the Action's.
- Query: throws nothing; an empty result is a result. Use `findOrFail()` only when absence really is a 404.

**Rendering**
- HTTP: implement `render()` on the exception, or map it in `bootstrap/app.php` via `$exceptions->render(...)`. JSON rendering is already enabled for `api/*` and `expectsJson()` requests.
- Status codes: 404 not found, 403 policy/authorization, 409 wrong state (`OrderAlreadyPaid`), 422 invalid input, 502/503 upstream failure. Never 500 for an expected domain failure.
- Livewire: catch the domain exception in the action method and turn it into `Flux::toast()` / `addError()`. Do not let an expected failure become an error screen.
- Not-worth-reporting exceptions (expected domain failures) go in `$exceptions->dontReport([...])`; upstream/integration failures stay reported.
- Never leak a vendor payload, URL with credentials, or stack detail into a user-visible message.

**Tests** — assert the specific class, not just that something threw: `expect(fn () => $action->handle(...))->toThrow(OrderAlreadyPaid::class);` and, for a Service, one test per mapped failure (4xx, 5xx, timeout).

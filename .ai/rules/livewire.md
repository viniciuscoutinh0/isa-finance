---
paths:
  - 'app/Livewire/**'
---

# Livewire

## Livewire: queries in computed props, Form objects for long forms
Livewire v4, class-based components (`config/livewire.php` → `make_command.type = 'class'`): class in `app/Livewire/`, view in `resources/views/livewire/`. Create with `php artisan make:livewire <Name> --no-interaction` (a Pest test is generated too).

**Queries always in computed properties**
- Every data read goes in a `#[Computed]` method — never in `mount()`, never assigned to a public property, never inline in the view.
- Public properties hold only state/filters (ids, search, sort, page), which are serialized on every request; a Model/Collection in a public property bloats the payload.
- Computed results are memoized per request, so use `$this->posts` freely in the class and `$this->posts` in the view.
- Cache across requests with `#[Computed(persist: true)]` only for stable data; invalidate with `unset($this->posts)` after any write.
- Non-trivial reads go through a Query class (see the Services/Queries rule); the computed prop just calls it.

```php
#[Computed]
public function orders(): LengthAwarePaginator
{
    return app(PendingOrdersQuery::class)->handle($this->search, $this->status);
}
```

**Long forms use Form objects**
- 3+ fields, or any form with validation rules: extract to `app/Livewire/Forms/<Name>Form.php` extending `Livewire\Form`.
- Component declares `public PostForm $form;`; the view binds `wire:model="form.title"` and shows `@error('form.title')`.
- Form properties are named after the wire payload, so `snake_case`: `public ?int $account_id`, bound as `wire:model="form.account_id"`. See the Naming rule below.
- `rules()` and `validationAttributes()` delegate to a ruleset in `app/Rules/<Domain>/` — never inline, so the assistant tools validate the same payload. See the Validation rule in `.ai/rules/app.md`.
- The Form holds the model being edited (`#[Locked] public ?Post $post = null`), not its id — `$this->form->post === null` is what tells create from update.
- Actions call `$this->form->validate()`, build a DTO from the validated array, and hand it to an Action; no business logic in the component.
- `$this->form->reset()` after saving.

## CRUD splits into Index, Create and Update

A domain with CRUD gets three components — `app/Livewire/<Domain>/{Index,Create,Update}.php` — not one. Full rationale and trade-offs: `docs/adr/0006-crud-em-componentes-livewire.md`. `app/Livewire/Transactions/` is the reference.

- **`Index`** lists, filters, paginates, and hosts every action that has no form of its own: `delete`, `archive`, `unarchive`. Those are `wire:click="delete({{ $id }})"` with `wire:confirm`, never a component per row.
- **`Create`** and **`Update`** own one modal each, and nothing else.
- **Modals by name**: `Flux::modal('<domain>-create')->show()` / `->close()`. Never `public bool $showModal` once the form lives in a child component.
- **One change event per domain**: `Create` and `Update` dispatch `<domain>::changed` after writing. `Index` listens in an explicit method, never by stacking `#[On]` on the computed property:

```php
#[On('transaction::changed')]
public function refreshList(): void
{
    unset($this->transactions);
}
```

- **Opening a modal is a broadcast**: the view dispatches `$dispatch('transaction::edit', { id: 12 })`; `Update` listens with `#[On]`. Never `dispatchTo('transactions.update', …)` — that ties the parent view to the child's registered name and breaks silently when it moves.
- **Filters go in `#[Url] public array $filters`**, with one shared `updated()` calling `resetPage()`. Not one public property per filter.
- **`Update::onShow()` scopes by owner** before filling the form (`Post::query()->ownedBy(Auth::user())->find($id)`); authorizing only on save leaks the record into the modal.
- **Shared select options** are granular traits in `app/Livewire/Concerns/` (`WithAccountOptions`, `WithCategoryOptions`), composed by whoever needs them. Grouping/sorting for display belongs in the view, not in the query.
- **Skip the split** when the `Index` carries no state the form ignores — no pagination, no filter, no long table. `app/Livewire/Categories/` stays a single component for that reason. Everything else on this page still applies to it: ruleset, DTO, snake_case, authorization. Split it when it gains its first paginated listing or filter.

## Naming across the wire

Keys that cross a boundary — wire payloads, AI tool arguments, validated arrays, JSON — are `snake_case`. Internal class properties keep PHP style (`TransactionData::$accountId` is camel; `$data['account_id']` is not). A mixed boundary already cost a silent write failure between the assistant's approval form and the tool contract.

**General**
- Component = state + wiring only: validate/authorize, call an Action, emit events, redirect. No `Http::`, no long Eloquent chains, no transactions.
- Authorize in every action method, never trust the view for it. Catch `AuthorizationException` and toast — do not let it escape as a 403 from a click.
- Tests mirror the components: `tests/Feature/Livewire/<Domain>/{Index,Create,Update}Test.php`. `Index` covers listing, each filter branch, `resetPage`, the `<domain>::changed` refresh and the no-form actions. Refactoring a component without moving its tests leaves them exercising a class that no longer exists.

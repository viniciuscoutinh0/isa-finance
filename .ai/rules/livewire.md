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
- 3+ fields, or any form with validation rules: extract to `app/Livewire/Forms/<Name>Form.php` extending `Livewire\Form`, with `#[Validate]` on each property.
- Component declares `public PostForm $form;`; the view binds `wire:model="form.title"` and shows `@error('form.title')`.
- Actions call `$this->form->validate()`, then hand the data to an Action; no business logic in the component.
- `$this->form->reset()` after saving.

**General**
- Component = state + wiring only: validate/authorize, call an Action, emit events, redirect. No `Http::`, no long Eloquent chains, no transactions.
- Authorize in every action method (`$this->authorize(...)`), never trust the view for it.

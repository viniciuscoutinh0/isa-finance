---
paths:
  - 'app/**'
  - 'resources/**'
  - 'database/**'
  - 'tests/**'
  - 'routes/**'
  - 'config/**'
---

# Language

## Code in English, user-facing text in pt-BR

Two languages, split by audience. Never mix them inside one identifier (`criarPedido`, `OrderPendente`).

**English — everything a developer reads**
- Class, method, property, variable, parameter names: `CreateTodo`, `handle()`, `$completedAt`, `isFinal()`.
- Table and column names, migration file names, enum case names (`TodoStatus::Pending`), route names (`todos.index`), event/job names, config keys.
- Exception messages (they go to logs), PHPDoc, code comments, commit messages, test names (`it('rejects completing a todo that is already completed')`).

**pt-BR — everything a user reads**
- Labels, placeholders, buttons, headings, toasts, validation messages, e-mail and notification copy, PDF/report text.
- Human-readable enum output: the case stays English, `label()` returns pt-BR.
- URL slugs are pt-BR when the user sees them (`/tarefas`), while the route name stays English (`todos.index`).

```php
enum TodoStatus: string
{
    case Pending = 'pending';          // identifier + stored value: English

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente', // what the user sees: pt-BR
        };
    }
}
```

**Translations**
- `lang/pt_BR/` and `lang/pt_BR.json` already exist — put framework/validation overrides there rather than hardcoding a translated string in a rule message.
- Short UI copy may stay inline in Blade; anything reused, or any validation message, goes through `__()` with an English key.
- Attribute names in validation messages come from `lang/pt_BR/validation.php` under `attributes`, so a Form object keeps English property names and still reports "O campo Título é obrigatório".

**Locale config**
- `.env.example` is the source of truth: `APP_LOCALE="pt-BR"`, `APP_FALLBACK_LOCALE=en`, `APP_FAKER_LOCALE="pt-BR"`.
- The local `.env` matches it. Keep them in sync — anything locale-dependent (dates, validation messages, faker data) follows `.env`.
- Dates/numbers formatted for display use pt-BR conventions (`d/m/Y`, comma decimal); stored values stay ISO/neutral.

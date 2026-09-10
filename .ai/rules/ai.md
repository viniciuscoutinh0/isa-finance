---
paths:
  - 'app/Ai/**'
---

# Ai

## AI assistant lives in app/Ai (laravel/ai SDK)
The finance chat assistant is `App\Ai\Agents\Assistant` (Agent + Conversational + HasTools, `RemembersConversations`), scaffolded with `php artisan make:agent` / `make:tool` — this is the SDK's own convention, so `app/Ai/{Agents,Tools}` is an accepted base dir. Tests mirror it at `tests/Feature/Ai/{Agents,Tools}`.

Rules:
- Every tool takes `User $user` in its constructor (the agent passes its own user down); tools scope every read/write to that user, never `auth()`.
- Read tools reuse the existing `App\Queries\**` classes and return JSON strings; account/category filters resolve by name case-insensitively and drop an unmatched filter with a `notes` entry instead of failing.
- Write tools (`CreateTransactionTool`, `CreateTransferTool`) implement `Approvable` + `InteractsWithApprovals` (always require approval), call the existing `App\Actions\**`, and catch `ValidationException`/domain exceptions to return a readable string. The user picks the real account/category in the Livewire approval form, which submits `Decision::edit([...ids...])`.
- Instructions are English; the model is told to answer in pt-BR. `instructions()` embeds the user's account + category names.
- `config/ai.php` has a `conversations` block with `generate_title => false` (env `AI_GENERATE_CONVERSATION_TITLES`) — title generation would fire a real provider HTTP call that `PreventStrayRequests` fails in tests.
- Test streaming/approval with `Assistant::fake([...])` and `AgentResponse::fakeWithPendingApprovals([...])`; the fake does NOT run the tool on an approval-continuation, so assert the real DB write in the tool test, not the Livewire test.

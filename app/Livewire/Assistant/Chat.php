<?php

declare(strict_types=1);

namespace App\Livewire\Assistant;

use App\Ai\Agents\Assistant;
use App\Data\Money;
use App\Models\Account;
use App\Models\Category;
use App\Queries\Accounts\AccountsQuery;
use App\Queries\Categories\CategoriesQuery;
use App\Rules\MoneyString;
use Carbon\CarbonImmutable;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Ai\Approvals\Decision;
use Laravel\Ai\Approvals\Decisions;
use Laravel\Ai\Approvals\PendingApproval;
use Laravel\Ai\Responses\StreamableAgentResponse;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolApprovalRequest;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Chat extends Component
{
    /** @var list<array{role: string, html: string}> */
    public array $messages = [];

    public ?string $conversationId = null;

    public string $draft = '';

    public string $pendingPrompt = '';

    public bool $awaitingReply = false;

    public bool $showApproval = false;

    public ?string $approvalCallId = null;

    public string $approvalKind = 'transaction';

    public string $approvalAmount = '';

    public string $approvalDescription = '';

    public string $approvalDate = '';

    public ?int $approvalAccountId = null;

    public ?int $approvalCategoryId = null;

    public ?int $approvalToAccountId = null;

    public function mount(): void
    {
        $conversation = auth()->user()?->conversations()->latest('updated_at')->first();

        if ($conversation === null) {
            return;
        }

        $this->conversationId = $conversation->id;
        $this->messages = $conversation->messages()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(fn ($message): bool => in_array($message->role, ['user', 'assistant'], true) && filled($message->content))
            ->map(fn ($message): array => [
                'role' => $message->role,
                'html' => $this->renderMessage($message->role, $message->content),
            ])
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Account>
     */
    #[Computed]
    public function accounts(): Collection
    {
        return app(AccountsQuery::class)->handle(auth()->user());
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return app(CategoriesQuery::class)->handle(auth()->user());
    }

    public function send(): void
    {
        $text = trim($this->draft);

        if ($text === '' || $this->awaitingReply || $this->showApproval) {
            return;
        }

        if ($this->rateLimited()) {
            Flux::toast(
                'Você atingiu o limite de mensagens do assistente por agora. Tente novamente mais tarde.',
                variant: 'warning',
            );

            return;
        }

        $this->hit();

        $this->messages[] = ['role' => 'user', 'html' => $this->renderMessage('user', $text)];
        $this->draft = '';
        $this->pendingPrompt = $text;
        $this->awaitingReply = true;

        $this->js('$wire.runAgent()');
    }

    public function runAgent(): void
    {
        $prompt = $this->pendingPrompt;
        $this->pendingPrompt = '';

        if ($prompt === '') {
            $this->awaitingReply = false;

            return;
        }

        $agent = Assistant::make(user: auth()->user());

        $stream = $this->conversationId !== null
            ? $agent->continue($this->conversationId, as: auth()->user())->stream($prompt)
            : $agent->forUser(auth()->user())->stream($prompt);

        $this->consumeStream($stream);

        $this->conversationId = $agent->currentConversation() ?? $this->conversationId;
        $this->awaitingReply = false;
    }

    public function confirmApproval(): void
    {
        $this->validate($this->approvalRules(), attributes: $this->approvalAttributes());

        $arguments = $this->approvalKind === 'transfer'
            ? [
                'amount' => $this->approvalAmount,
                'date' => $this->approvalDate,
                'from_account_id' => $this->approvalAccountId,
                'to_account_id' => $this->approvalToAccountId,
            ]
            : [
                'amount' => $this->approvalAmount,
                'description' => $this->approvalDescription,
                'date' => $this->approvalDate,
                'account_id' => $this->approvalAccountId,
                'category_id' => $this->approvalCategoryId,
            ];

        $this->resumeWithDecision(Decision::edit($arguments));
    }

    public function rejectApproval(): void
    {
        if ($this->approvalCallId === null) {
            return;
        }

        $this->resumeWithDecision(Decision::reject('O usuário cancelou a operação.'));
    }

    public function newConversation(): void
    {
        $this->reset(
            'messages', 'conversationId', 'draft', 'pendingPrompt',
            'awaitingReply', 'showApproval', 'approvalCallId',
            'approvalAmount', 'approvalDescription', 'approvalDate',
            'approvalAccountId', 'approvalCategoryId', 'approvalToAccountId',
        );
    }

    public function render(): View
    {
        return view('livewire.assistant.chat');
    }

    private function resumeWithDecision(Decision $decision): void
    {
        $callId = $this->approvalCallId;

        $this->closeApproval();
        $this->awaitingReply = true;

        $agent = Assistant::make(user: auth()->user());
        $stream = $agent->continue($this->conversationId, as: auth()->user())
            ->stream(Decisions::from([$callId => $decision]));

        $this->consumeStream($stream);

        $this->conversationId = $agent->currentConversation() ?? $this->conversationId;
        $this->awaitingReply = false;
    }

    private function consumeStream(StreamableAgentResponse $stream): void
    {
        $buffer = '';
        $pending = null;

        foreach ($stream as $event) {
            if ($event instanceof TextDelta) {
                if ($buffer === '') {
                    $this->stream(to: 'assistant-status', content: '', replace: true);
                }

                $buffer .= $event->delta;
                $this->stream(to: 'assistant-current', content: $event->delta);

                continue;
            }

            if ($event instanceof ToolCall) {
                $this->stream(
                    to: 'assistant-status',
                    content: $this->toolStatusHtml($event->toolCall->name),
                    replace: true,
                );

                continue;
            }

            if ($event instanceof ToolResult) {
                $this->stream(to: 'assistant-status', content: '', replace: true);

                continue;
            }

            if ($event instanceof ToolApprovalRequest) {
                $pending = $event->pendingApprovals->first();
            }
        }

        if (trim($buffer) !== '') {
            $this->messages[] = ['role' => 'assistant', 'html' => $this->renderMessage('assistant', $buffer)];
        }

        if ($pending instanceof PendingApproval) {
            $this->openApproval($pending);
        }
    }

    private function openApproval(PendingApproval $approval): void
    {
        $args = $approval->arguments;

        $this->approvalCallId = $approval->id;
        $this->approvalKind = $approval->tool === 'CreateTransferTool' ? 'transfer' : 'transaction';
        $this->approvalAmount = $this->prefillAmount(is_string($args['amount'] ?? null) ? $args['amount'] : '');
        $this->approvalDescription = is_string($args['description'] ?? null) ? $args['description'] : '';
        $this->approvalDate = $this->prefillDate($args['date'] ?? null);

        if ($this->approvalKind === 'transfer') {
            $this->approvalAccountId = $this->matchAccount($args['from_account'] ?? null);
            $this->approvalToAccountId = $this->matchAccount($args['to_account'] ?? null);
            $this->approvalCategoryId = null;
        } else {
            $this->approvalAccountId = $this->matchAccount($args['account'] ?? null);
            $this->approvalCategoryId = $this->matchCategory($args['category'] ?? null);
            $this->approvalToAccountId = null;
        }

        $this->showApproval = true;
    }

    private function closeApproval(): void
    {
        $this->reset(
            'showApproval', 'approvalCallId', 'approvalAmount', 'approvalDescription',
            'approvalDate', 'approvalAccountId', 'approvalCategoryId', 'approvalToAccountId',
        );
        $this->approvalKind = 'transaction';
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function approvalRules(): array
    {
        $rules = [
            'approvalAmount' => ['required', 'string', new MoneyString(allowNegative: false, allowZero: false)],
            'approvalDate' => ['required', 'date'],
            'approvalAccountId' => ['required', 'integer'],
        ];

        if ($this->approvalKind === 'transfer') {
            $rules['approvalToAccountId'] = ['required', 'integer', 'different:approvalAccountId'];
        } else {
            $rules['approvalDescription'] = ['required', 'string', 'max:255'];
            $rules['approvalCategoryId'] = ['required', 'integer'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function approvalAttributes(): array
    {
        return [
            'approvalAmount' => 'valor',
            'approvalDate' => 'data',
            'approvalDescription' => 'descrição',
            'approvalAccountId' => $this->approvalKind === 'transfer' ? 'conta de origem' : 'conta',
            'approvalToAccountId' => 'conta de destino',
            'approvalCategoryId' => 'categoria',
        ];
    }

    private function prefillAmount(string $raw): string
    {
        try {
            return Money::parse($raw)->forInput();
        } catch (InvalidArgumentException) {
            return '';
        }
    }

    private function prefillDate(mixed $raw): string
    {
        return is_string($raw) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) === 1
            ? $raw
            : CarbonImmutable::now()->toDateString();
    }

    private function matchAccount(mixed $name): ?int
    {
        if (! is_string($name) || $name === '') {
            return null;
        }

        return $this->accounts
            ->first(fn (Account $account): bool => Str::lower($account->name) === Str::lower($name))
            ?->id;
    }

    private function matchCategory(mixed $name): ?int
    {
        if (! is_string($name) || $name === '') {
            return null;
        }

        return $this->categories
            ->first(fn (Category $category): bool => Str::lower($category->name) === Str::lower($name))
            ?->id;
    }

    private function renderMessage(string $role, string $content): string
    {
        if ($role === 'assistant') {
            return Str::markdown($content, ['html_input' => 'escape', 'allow_unsafe_links' => false]);
        }

        return nl2br(e($content));
    }

    private function toolStatusHtml(string $tool): string
    {
        $label = match ($tool) {
            'ListAccounts' => 'Consultando suas contas…',
            'ListCategories' => 'Consultando suas categorias…',
            'SearchTransactions' => 'Buscando seus lançamentos…',
            'MonthlyCashFlow' => 'Somando entradas e saídas…',
            'ListTransfers' => 'Consultando suas transferências…',
            'CreateTransactionTool' => 'Preparando o lançamento…',
            'CreateTransferTool' => 'Preparando a transferência…',
            default => 'Consultando seus dados…',
        };

        return '<span class="motion-safe:animate-chat-pop inline-block text-xs text-zinc-500 dark:text-zinc-400">'
            .e($label)
            .'</span>';
    }

    private function rateLimited(): bool
    {
        $key = 'ai-assistant:'.auth()->id();

        return RateLimiter::tooManyAttempts($key.':minute', 15)
            || RateLimiter::tooManyAttempts($key.':day', 150);
    }

    private function hit(): void
    {
        $key = 'ai-assistant:'.auth()->id();

        RateLimiter::hit($key.':minute', 60);
        RateLimiter::hit($key.':day', 86400);
    }
}

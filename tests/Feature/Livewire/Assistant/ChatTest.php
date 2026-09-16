<?php

declare(strict_types=1);

use App\Ai\Agents\Assistant;
use App\Ai\Tools\CreateTransactionTool;
use App\Livewire\Assistant\Chat;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Ai\Approvals\PendingApproval;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\ToolCall;
use Laravel\Ai\Tools\Request;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    RateLimiter::clear('ai-assistant:'.$this->user->id.':minute');
    RateLimiter::clear('ai-assistant:'.$this->user->id.':day');
});

it('renders successfully', function (): void {
    Livewire::test(Chat::class)->assertOk();
});

it('sends a prompt and appends the streamed assistant reply', function (): void {
    Assistant::fake(['Você não tem contas cadastradas ainda.']);

    Livewire::test(Chat::class)
        ->set('draft', 'quanto eu tenho?')
        ->call('send')
        ->assertSet('awaitingReply', true)
        ->call('runAgent')
        ->assertSet('awaitingReply', false)
        ->assertSet('draft', '')
        ->assertSee('quanto eu tenho?')
        ->assertSee('Você não tem contas cadastradas ainda.');

    Assistant::assertPrompted('quanto eu tenho?');
});

it('runs a tool mid-stream and still lands the final reply', function (): void {
    Account::factory()->ownedBy($this->user)->create(['name' => 'Nubank']);

    Assistant::fake([
        new ToolCall('c1', 'ListAccounts', []),
        'Você tem uma conta ativa: Nubank.',
    ]);

    Livewire::test(Chat::class)
        ->set('draft', 'quais contas eu tenho?')
        ->call('send')
        ->call('runAgent')
        ->assertSet('awaitingReply', false)
        ->assertSee('Você tem uma conta ativa: Nubank.');
});

it('throttles a user who sends too many messages in a minute', function (): void {
    Assistant::fake();

    $component = Livewire::test(Chat::class);

    foreach (range(1, 15) as $i) {
        $component->set('draft', "mensagem {$i}")->call('send')->call('runAgent');
    }

    $component->set('draft', 'mensagem a mais')->call('send')
        ->assertSet('awaitingReply', false)
        ->assertDontSee('mensagem a mais');

    Assistant::assertPromptedTimes(15);
});

it('surfaces a write-tool approval as a prefilled on-screen form', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create(['name' => 'Nubank']);
    $category = Category::factory()->ownedBy($this->user)->expense()->create(['name' => 'Mercado']);

    Assistant::fake([
        AgentResponse::fakeWithPendingApprovals([
            new PendingApproval('call_1', 'CreateTransactionTool', [
                'amount' => '150,00',
                'description' => 'Feira do mês',
                'account' => 'Nubank',
                'category' => 'Mercado',
            ]),
        ]),
    ]);

    Livewire::test(Chat::class)
        ->set('draft', 'adiciona 150 no mercado')
        ->call('send')
        ->call('runAgent')
        ->assertSet('showApproval', true)
        ->assertSet('approvalKind', 'transaction')
        ->assertSet('approvalAccountId', $account->id)
        ->assertSet('approvalCategoryId', $category->id)
        ->assertSet('approvalAmount', '150,00')
        ->assertSet('approvalDescription', 'Feira do mês');
});

it('submits the approval decision and resumes the reply once the form is confirmed', function (): void {
    $account = Account::factory()->ownedBy($this->user)->create(['name' => 'Nubank']);
    $category = Category::factory()->ownedBy($this->user)->expense()->create(['name' => 'Mercado']);

    Assistant::fake([
        AgentResponse::fakeWithPendingApprovals([
            new PendingApproval('call_1', 'CreateTransactionTool', ['amount' => '150,00', 'description' => 'Feira']),
        ]),
        'Pronto, lançamento registrado.',
    ]);

    Livewire::test(Chat::class)
        ->set('draft', 'adiciona 150 no mercado')
        ->call('send')
        ->call('runAgent')
        ->assertSet('showApproval', true)
        ->set('approvalAccountId', $account->id)
        ->set('approvalCategoryId', $category->id)
        ->set('approvalAmount', '150,00')
        ->set('approvalDescription', 'Feira')
        ->set('approvalDate', '2026-03-10')
        ->call('confirmApproval')
        ->assertHasNoErrors()
        ->assertSet('showApproval', false)
        ->assertSet('awaitingReply', false)
        ->assertSee('Pronto, lançamento registrado.');

    expect((new CreateTransactionTool($this->user))->handle(new Request([
        'amount' => '150,00',
        'description' => 'Feira',
        'date' => '2026-03-10',
        'account_id' => $account->id,
        'category_id' => $category->id,
    ])))->toContain('Feira');

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'description' => 'Feira',
        'amount' => 15000,
    ]);
});

it('validates the approval form before submitting a decision', function (): void {
    Account::factory()->ownedBy($this->user)->create(['name' => 'Nubank']);
    Category::factory()->ownedBy($this->user)->expense()->create(['name' => 'Mercado']);

    Assistant::fake([
        AgentResponse::fakeWithPendingApprovals([
            new PendingApproval('call_1', 'CreateTransactionTool', ['amount' => '150,00']),
        ]),
    ]);

    Livewire::test(Chat::class)
        ->set('draft', 'adiciona 150')
        ->call('send')
        ->call('runAgent')
        ->set('approvalAccountId', null)
        ->set('approvalCategoryId', null)
        ->set('approvalDescription', '')
        ->call('confirmApproval')
        ->assertHasErrors(['approvalAccountId', 'approvalCategoryId', 'approvalDescription'])
        ->assertSet('showApproval', true);
});

it('creates nothing when the approval form is cancelled', function (): void {
    Account::factory()->ownedBy($this->user)->create(['name' => 'Nubank']);
    Category::factory()->ownedBy($this->user)->expense()->create(['name' => 'Mercado']);

    Assistant::fake([
        AgentResponse::fakeWithPendingApprovals([
            new PendingApproval('call_1', 'CreateTransactionTool', ['amount' => '150,00', 'description' => 'Feira']),
        ]),
        'Tudo bem, cancelei.',
    ]);

    Livewire::test(Chat::class)
        ->set('draft', 'adiciona 150 no mercado')
        ->call('send')
        ->call('runAgent')
        ->assertSet('showApproval', true)
        ->call('rejectApproval')
        ->assertSet('showApproval', false);

    $this->assertDatabaseCount('transactions', 0);
});

it('starts a fresh conversation on demand', function (): void {
    Assistant::fake(['Oi!']);

    Livewire::test(Chat::class)
        ->set('draft', 'oi')
        ->call('send')
        ->call('runAgent')
        ->assertSee('Oi!')
        ->call('newConversation')
        ->assertSet('messages', [])
        ->assertSet('conversationId', null);
});

it('restores the running conversation transcript when the panel is re-mounted', function (): void {
    Assistant::fake(['Primeira resposta.']);

    Livewire::test(Chat::class)
        ->set('draft', 'primeira pergunta')
        ->call('send')
        ->call('runAgent')
        ->assertSee('Primeira resposta.');

    Livewire::test(Chat::class)
        ->assertSee('primeira pergunta')
        ->assertSee('Primeira resposta.')
        ->assertNotSet('conversationId', null);
});

it('never loads another user conversation on mount', function (): void {
    $stranger = User::factory()->create();
    Assistant::fake(['Resposta privada do estranho.']);
    Assistant::make(user: $stranger)->forUser($stranger)->prompt('segredo');

    Livewire::actingAs($this->user)
        ->test(Chat::class)
        ->assertSet('conversationId', null)
        ->assertDontSee('Resposta privada do estranho.');
});

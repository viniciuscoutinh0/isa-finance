<?php

declare(strict_types=1);

use App\Livewire\Transactions\Delete;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    actingAs($this->user);

    $this->account = Account::factory()->ownedBy($this->user)->create();

    $this->expense = Category::factory()
        ->ownedBy($this->user)
        ->expense()
        ->create();
});

it('renders successfully', function (): void {
    $transaction = Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create();

    Livewire::test(Delete::class, ['transaction' => $transaction])->assertStatus(200);
});

it('deletes a transaction', function (): void {
    $transaction = Transaction::factory()
        ->forAccount($this->account)
        ->forCategory($this->expense)
        ->create();

    Livewire::test(Delete::class, ['transaction' => $transaction])
        ->call('delete')
        ->assertHasNoErrors()
        ->assertDispatched('transaction::deleted');

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

it('keeps a transaction owned by someone else', function (): void {
    $transaction = Transaction::factory()->create();

    Livewire::test(Delete::class, ['transaction' => $transaction])
        ->call('delete')
        ->assertNotDispatched('transaction::deleted');

    $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
});

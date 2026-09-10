<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;
use App\Queries\Transfers\TransfersQuery;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->a = Account::factory()->ownedBy($this->user)->create(['name' => 'A']);
    $this->b = Account::factory()->ownedBy($this->user)->create(['name' => 'B']);
    $this->c = Account::factory()->ownedBy($this->user)->create(['name' => 'C']);
});

it('returns only the user transfers newest first', function (): void {
    Transfer::factory()->between($this->a, $this->b)->on('2026-01-05')->create(['notes' => 'Antiga']);
    Transfer::factory()->between($this->a, $this->b)->on('2026-03-05')->create(['notes' => 'Recente']);
    Transfer::factory()->create(['notes' => 'De outro usuário']);

    $result = app(TransfersQuery::class)->handle($this->user);

    expect($result->pluck('notes')->all())->toBe(['Recente', 'Antiga']);
});

it('filters by an account on either side', function (): void {
    Transfer::factory()->between($this->a, $this->b)->create(['notes' => 'A para B']);
    Transfer::factory()->between($this->c, $this->a)->create(['notes' => 'C para A']);
    Transfer::factory()->between($this->b, $this->c)->create(['notes' => 'B para C']);

    $result = app(TransfersQuery::class)->handle($this->user, ['account_id' => $this->a->id]);

    expect($result->pluck('notes')->sort()->values()->all())->toBe(['A para B', 'C para A']);
});

it('paginates', function (): void {
    Transfer::factory()->count(12)->between($this->a, $this->b)->create();

    $result = app(TransfersQuery::class)->handle($this->user, [], perPage: 5);

    expect($result->total())->toBe(12)
        ->and($result->count())->toBe(5);
});

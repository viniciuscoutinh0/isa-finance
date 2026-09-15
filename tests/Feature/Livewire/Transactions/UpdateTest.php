<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('transactions.update')
        ->assertStatus(200);
});

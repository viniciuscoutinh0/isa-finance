<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('transactions.delete')
        ->assertStatus(200);
});

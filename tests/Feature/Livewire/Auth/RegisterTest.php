<?php

declare(strict_types=1);

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

it('renders the register screen for guests', function (): void {
    $this->get(route('register'))
        ->assertOk()
        ->assertSeeLivewire(Register::class);
});

it('registers a user, logs them in and redirects to the dashboard', function (): void {
    Livewire::test(Register::class)
        ->set('form.name', 'Maria Souza')
        ->set('form.email', 'maria@example.com')
        ->set('form.password', 'senha-super-forte-123')
        ->set('form.password_confirmation', 'senha-super-forte-123')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect(Auth::check())->toBeTrue();
    $this->assertDatabaseHas('users', ['email' => 'maria@example.com']);
});

it('rejects a duplicate e-mail', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test(Register::class)
        ->set('form.name', 'Outro')
        ->set('form.email', 'taken@example.com')
        ->set('form.password', 'senha-super-forte-123')
        ->set('form.password_confirmation', 'senha-super-forte-123')
        ->call('register')
        ->assertHasErrors(['form.email' => 'unique']);

    expect(Auth::check())->toBeFalse();
});

it('requires the password confirmation to match', function (): void {
    Livewire::test(Register::class)
        ->set('form.name', 'Maria Souza')
        ->set('form.email', 'maria@example.com')
        ->set('form.password', 'senha-super-forte-123')
        ->set('form.password_confirmation', 'diferente-123')
        ->call('register')
        ->assertHasErrors(['form.password' => 'confirmed']);
});

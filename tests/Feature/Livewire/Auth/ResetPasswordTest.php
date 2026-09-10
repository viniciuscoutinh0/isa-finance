<?php

declare(strict_types=1);

use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

it('renders the reset-password screen for guests', function (): void {
    $this->get(route('password.reset', ['token' => 'some-token', 'email' => 'a@b.com']))
        ->assertOk()
        ->assertSeeLivewire(ResetPassword::class);
});

it('prefills the token and e-mail from the URL', function (): void {
    Livewire::withQueryParams(['email' => 'maria@example.com'])
        ->test(ResetPassword::class, ['token' => 'the-token'])
        ->assertSet('form.token', 'the-token')
        ->assertSet('form.email', 'maria@example.com');
});

it('resets the password and redirects to login', function (): void {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('form.email', $user->email)
        ->set('form.password', 'a-brand-new-strong-password')
        ->set('form.password_confirmation', 'a-brand-new-strong-password')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('login'));

    expect(Hash::check('a-brand-new-strong-password', $user->fresh()->password))->toBeTrue();
});

it('shows an error for an invalid token', function (): void {
    $user = User::factory()->create(['password' => Hash::make('original-password')]);

    Livewire::test(ResetPassword::class, ['token' => 'not-a-real-token'])
        ->set('form.email', $user->email)
        ->set('form.password', 'a-brand-new-strong-password')
        ->set('form.password_confirmation', 'a-brand-new-strong-password')
        ->call('resetPassword')
        ->assertHasErrors('form.email');

    expect(Hash::check('original-password', $user->fresh()->password))->toBeTrue();
});

<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('renders the login screen for guests', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertSeeLivewire(Login::class);
});

it('logs a user in with valid credentials and redirects to the dashboard', function (): void {
    $user = User::factory()->create(['password' => Hash::make('senha-super-forte-123')]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'senha-super-forte-123')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect(Auth::id())->toBe($user->id);
});

it('rejects an invalid password', function (): void {
    $user = User::factory()->create(['password' => Hash::make('senha-super-forte-123')]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'senha-errada')
        ->call('login')
        ->assertHasErrors('form.email');

    expect(Auth::check())->toBeFalse();
});

it('throttles after five failed attempts', function (): void {
    $user = User::factory()->create(['password' => Hash::make('senha-super-forte-123')]);

    $component = Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'senha-errada');

    foreach (range(1, 5) as $ignored) {
        $component->call('login');
    }

    $component->call('login')
        ->assertHasErrors('form.email');

    expect(RateLimiter::tooManyAttempts(
        Str::transliterate(Str::lower($user->email).'|127.0.0.1'),
        5,
    ))->toBeTrue();
});

it('redirects authenticated users away from login', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('login'))
        ->assertRedirect(route('dashboard'));
});

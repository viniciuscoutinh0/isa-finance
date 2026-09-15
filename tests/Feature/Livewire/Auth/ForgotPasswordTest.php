<?php

declare(strict_types=1);

use App\Livewire\Auth\ForgotPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('renders the forgot-password screen for guests', function (): void {
    $this->get(route('password.request'))->assertOk()->assertSeeLivewire(ForgotPassword::class);
});

it('sends a reset link to a known e-mail', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)->set('form.email', $user->email)->call('sendResetLink')->assertHasNoErrors();

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('reports when the e-mail is unknown', function (): void {
    Notification::fake();

    Livewire::test(ForgotPassword::class)
        ->set('form.email', 'ninguem@example.com')
        ->call('sendResetLink')
        ->assertHasErrors('form.email');

    Notification::assertNothingSent();
});

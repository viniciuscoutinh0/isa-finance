<?php

declare(strict_types=1);

use App\Actions\Auth\ResetUserPassword;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('resets the password when the token is valid', function (): void {
    Event::fake([PasswordReset::class]);

    $user = User::factory()->create();
    $token = Password::createToken($user);

    $status = app(ResetUserPassword::class)->handle([
        'email' => $user->email,
        'password' => 'a-brand-new-strong-password',
        'token' => $token,
    ]);

    expect($status)->toBe(Password::PASSWORD_RESET);
    expect(Hash::check('a-brand-new-strong-password', $user->fresh()->password))->toBeTrue();

    Event::assertDispatched(PasswordReset::class);
});

it('returns an invalid-token status and leaves the password untouched', function (): void {
    $user = User::factory()->create(['password' => Hash::make('original-password')]);

    $status = app(ResetUserPassword::class)->handle([
        'email' => $user->email,
        'password' => 'a-brand-new-strong-password',
        'token' => 'not-a-real-token',
    ]);

    expect($status)->toBe(Password::INVALID_TOKEN);
    expect(Hash::check('original-password', $user->fresh()->password))->toBeTrue();
});

<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final readonly class ResetUserPassword
{
    /**
     * @param  array{email: string, password: string, token: string}  $data
     */
    public function handle(array $data): string
    {
        return Password::reset($data, function (User $user, string $password): void {
            $user->update([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ]);

            event(new PasswordReset($user));
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use Illuminate\Validation\Rules\Password;
use Livewire\Form;

final class ResetPasswordForm extends Form
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use Illuminate\Validation\Rules\Password;
use Livewire\Form;

final class RegisterForm extends Form
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }
}

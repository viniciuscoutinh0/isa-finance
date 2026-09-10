<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use Livewire\Attributes\Validate;
use Livewire\Form;

final class ForgotPasswordForm extends Form
{
    #[Validate('required|string|email')]
    public string $email = '';
}

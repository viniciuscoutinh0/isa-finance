<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\ForgotPasswordForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Esqueci a senha')]
final class ForgotPassword extends Component
{
    public ForgotPasswordForm $form;

    public ?string $status = null;

    public function sendResetLink(): void
    {
        $this->form->validate();

        $status = Password::sendResetLink(['email' => $this->form->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->form->reset();
            $this->status = __($status);

            return;
        }

        $this->addError('form.email', __($status));
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password');
    }
}

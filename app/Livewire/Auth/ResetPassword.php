<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\Auth\ResetUserPassword;
use App\Livewire\Forms\Auth\ResetPasswordForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Redefinir senha')]
final class ResetPassword extends Component
{
    public ResetPasswordForm $form;

    public function mount(string $token): void
    {
        $this->form->token = $token;
        $this->form->email = (string) request()->query('email', '');
    }

    public function resetPassword(ResetUserPassword $resetUserPassword): void
    {
        $data = $this->form->validate();

        $status = $resetUserPassword->handle([
            'email' => $data['email'],
            'password' => $data['password'],
            'token' => $data['token'],
        ]);

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', __($status));
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $this->addError('form.email', __($status));
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password');
    }
}

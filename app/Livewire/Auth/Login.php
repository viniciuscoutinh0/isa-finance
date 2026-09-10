<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\LoginForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Entrar')]
final class Login extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(route('dashboard'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}

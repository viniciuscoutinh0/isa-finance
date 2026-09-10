<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\Auth\RegisterUser;
use App\Livewire\Forms\Auth\RegisterForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Criar conta')]
final class Register extends Component
{
    public RegisterForm $form;

    public function register(RegisterUser $registerUser): void
    {
        $data = $this->form->validate();

        $user = $registerUser->handle([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Auth::login($user);

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register');
    }
}

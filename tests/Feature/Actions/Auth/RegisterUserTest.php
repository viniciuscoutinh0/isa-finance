<?php

declare(strict_types=1);

use App\Actions\Auth\RegisterUser;
use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

it('creates a user with a hashed password', function (): void {
    $user = app(RegisterUser::class)->handle([
        'name' => 'Maria Souza',
        'email' => 'maria@example.com',
        'password' => 'senha-super-forte-123',
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Maria Souza')
        ->and($user->email)->toBe('maria@example.com')
        ->and(Hash::check('senha-super-forte-123', $user->password))->toBeTrue();

    $this->assertDatabaseHas('users', ['email' => 'maria@example.com']);
});

it('seeds the default categories for the new user', function (): void {
    $user = app(RegisterUser::class)->handle([
        'name' => 'Maria Souza',
        'email' => 'maria@example.com',
        'password' => 'senha-super-forte-123',
    ]);

    expect(Category::where('user_id', $user->id)->count())->toBe(15);
});

it('dispatches the Registered event', function (): void {
    Event::fake([Registered::class]);

    $user = app(RegisterUser::class)->handle([
        'name' => 'João Lima',
        'email' => 'joao@example.com',
        'password' => 'senha-super-forte-123',
    ]);

    Event::assertDispatched(Registered::class, fn (Registered $event) => $event->user->is($user));
});

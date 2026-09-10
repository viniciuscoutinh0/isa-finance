<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('logs the user out and redirects to login', function (): void {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    expect(Auth::check())->toBeFalse();
});

it('is not reachable by guests', function (): void {
    $this->post(route('logout'))
        ->assertRedirect(route('login'));
});

<?php

declare(strict_types=1);

use App\Models\User;

it('sends guests to login when they hit a protected page', function (string $path): void {
    $this->get($path)->assertRedirect(route('login'));
})->with([
    '/painel',
    '/contas',
    '/lancamentos',
    '/transferencias',
    '/categorias',
]);

it('lets an authenticated user reach the dashboard', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Painel');
});

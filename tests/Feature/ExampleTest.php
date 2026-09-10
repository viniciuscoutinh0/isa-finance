<?php

declare(strict_types=1);

it('redirects guests from the root to the login screen', function (): void {
    $this->get('/')
        ->assertRedirect('/painel');

    $this->get('/painel')
        ->assertRedirect(route('login'));
});

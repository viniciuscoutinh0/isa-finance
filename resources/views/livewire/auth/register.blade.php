<div>
    <flux:heading size="lg">Criar conta</flux:heading>
    <flux:text class="mt-1">Comece a organizar suas finanças.</flux:text>

    <form wire:submit="register" class="mt-6 flex flex-col gap-4">
        <flux:input
            wire:model="form.name"
            label="Nome"
            type="text"
            autocomplete="name"
            required
        />

        <flux:input
            wire:model="form.email"
            label="E-mail"
            type="email"
            autocomplete="email"
            required
        />

        <flux:input
            wire:model="form.password"
            label="Senha"
            type="password"
            autocomplete="new-password"
            viewable
            required
        />

        <flux:input
            wire:model="form.password_confirmation"
            label="Confirme a senha"
            type="password"
            autocomplete="new-password"
            viewable
            required
        />

        <flux:button type="submit" variant="primary" class="mt-2 w-full">
            Criar conta
        </flux:button>
    </form>

    <flux:text class="mt-6 text-center">
        Já tem conta?
        <flux:link :href="route('login')" wire:navigate>Entrar</flux:link>
    </flux:text>
</div>

<div>
    <flux:heading size="lg">Redefinir senha</flux:heading>
    <flux:text class="mt-1">Escolha uma nova senha para sua conta.</flux:text>

    <form wire:submit="resetPassword" class="mt-6 flex flex-col gap-4">
        <flux:input
            wire:model="form.email"
            label="E-mail"
            type="email"
            autocomplete="email"
            required
        />

        <flux:input
            wire:model="form.password"
            label="Nova senha"
            type="password"
            autocomplete="new-password"
            viewable
            required
        />

        <flux:input
            wire:model="form.password_confirmation"
            label="Confirme a nova senha"
            type="password"
            autocomplete="new-password"
            viewable
            required
        />

        <flux:button type="submit" variant="primary" class="mt-2 w-full">
            Redefinir senha
        </flux:button>
    </form>
</div>

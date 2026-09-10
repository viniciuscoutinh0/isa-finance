<div>
    <flux:heading size="lg">Entrar</flux:heading>
    <flux:text class="mt-1">Acesse sua conta.</flux:text>

    @if (session('status'))
        <flux:callout variant="success" class="mt-4" icon="check-circle">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form wire:submit="login" class="mt-6 flex flex-col gap-4">
        <flux:input
            wire:model="form.email"
            label="E-mail"
            type="email"
            autocomplete="email"
            required
        />

        <flux:field>
            <div class="flex items-center justify-between">
                <flux:label>Senha</flux:label>
                <flux:link :href="route('password.request')" wire:navigate variant="subtle" class="text-sm">
                    Esqueci a senha
                </flux:link>
            </div>
            <flux:input
                wire:model="form.password"
                type="password"
                autocomplete="current-password"
                viewable
                required
            />
            <flux:error name="form.password" />
        </flux:field>

        <flux:checkbox wire:model="form.remember" label="Manter conectado" />

        <flux:button type="submit" variant="primary" class="mt-2 w-full">
            Entrar
        </flux:button>
    </form>

    <flux:text class="mt-6 text-center">
        Não tem conta?
        <flux:link :href="route('register')" wire:navigate>Criar conta</flux:link>
    </flux:text>
</div>

<div>
    <flux:heading size="lg">Esqueci a senha</flux:heading>
    <flux:text class="mt-1">Vamos te enviar um link para criar uma nova senha.</flux:text>

    @if (filled($status = session('status')))
        <flux:callout
            variant="success"
            class="mt-4"
            icon="check-circle"
        >
            <flux:callout.heading>{{ $status }}</flux:callout.heading>
        </flux:callout>
    @endif

    <form
        wire:submit="sendResetLink"
        class="mt-6 flex flex-col gap-4"
    >
        <flux:input
            wire:model="form.email"
            label="E-mail"
            type="email"
            autocomplete="email"
            required
        />

        <flux:button
            type="submit"
            variant="primary"
            class="mt-2 w-full"
        >
            Enviar link
        </flux:button>
    </form>

    <flux:text class="mt-6 text-center">
        <flux:link
            :href="route('login')"
            wire:navigate
        >Voltar para entrar</flux:link>
    </flux:text>
</div>

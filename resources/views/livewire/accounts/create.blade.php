<flux:modal
    class="w-full md:w-96"
    name="account-create"
    flyout
    variant="floating"
>
    <form
        wire:submit="create"
        class="grid gap-y-5"
    >
        <flux:heading size="lg">
            Nova conta
        </flux:heading>

        <flux:input
            wire:model="form.name"
            label="Nome"
            badge="Obrigatório"
            placeholder="Ex.: Nubank, Carteira, Poupança"
            autocomplete="off"
            required
        />

        <flux:select
            wire:model="form.type"
            label="Tipo"
            badge="Obrigatório"
            variant="listbox"
            required
        >
            @foreach (\App\Enums\AccountType::cases() as $type)
                <flux:select.option
                    :value="$type->value"
                    :icon="$type->icon()"
                >
                    {{ $type->label() }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:input
            wire:model="form.initial_balance"
            label="Saldo inicial"
            inputmode="decimal"
            autocomplete="off"
            description="Use vírgula para os centavos. Se for dívida no cartão, use um valor negativo."
        />

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="subtle">
                    Cancelar
                </flux:button>
            </flux:modal.close>

            <flux:button
                type="submit"
                variant="primary"
            >
                Salvar
            </flux:button>
        </div>
    </form>
</flux:modal>

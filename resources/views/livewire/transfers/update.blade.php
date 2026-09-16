<flux:modal
    class="w-full md:w-112"
    name="transfer-update"
    flyout
    variant="floating"
>
    <form
        wire:submit="update"
        class="grid gap-y-5"
    >
        <flux:heading size="lg">
            Editar transferência
        </flux:heading>

        <flux:select
            label="De"
            required
            badge="Obrigatório"
            variant="listbox"
            placeholder="Selecione uma das opções"
            wire:model="form.from_account_id"
        >
            @foreach ($this->accounts as $account)
                <flux:select.option
                    :value="$account->id"
                    :icon="$account->type->icon()"
                >
                    {{ $account->name }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select
            label="Para"
            required
            badge="Obrigatório"
            variant="listbox"
            placeholder="Selecione uma das opções"
            wire:model="form.to_account_id"
        >
            @foreach ($this->accounts as $account)
                <flux:select.option
                    :value="$account->id"
                    :icon="$account->type->icon()"
                >
                    {{ $account->name }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <div class="grid gap-3 sm:grid-cols-2">
            <flux:date-picker
                label="Data"
                badge="Obrigatório"
                required
                wire:model="form.date"
            />

            <flux:input
                label="Valor"
                badge="Obrigatório"
                required
                icon="currency-dollar"
                inputmode="decimal"
                wire:model="form.amount"
                placeholder="0,00"
                autocomplete="off"
            />
        </div>

        <flux:textarea
            wire:model="form.notes"
            label="Observação"
            rows="2"
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

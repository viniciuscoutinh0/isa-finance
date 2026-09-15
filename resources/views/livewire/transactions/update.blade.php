<flux:modal
    class="md:w-md"
    name="transaction-update"
    flyout
    variant="floating"
>
    @if (filled($transaction = $this->form->transaction))
        <form
            wire:submit="update"
            class="grid gap-y-6"
        >
            <flux:heading size="lg">
                Editar lançamento
            </flux:heading>

            <flux:select
                label="Categoria"
                required
                badge="Obrigatório"
                variant="listbox"
                searchable
                placeholder="Selecione uma das opções"
                wire:model="form.category_id"
            >
                @foreach ($this->categories as $type => $items)
                    <flux:select.group :label="\App\Enums\CategoryType::from($type)->label()">
                        @foreach ($items as $category)
                            <flux:select.option :value="$category->id">
                                <div class="flex items-center gap-2">
                                    <span @class([
                                        'rounded-full size-4',
                                        'bg-rose-500' => \App\Enums\CategoryType::from($type)->color() === 'rose',
                                        'bg-green-500' => \App\Enums\CategoryType::from($type)->color() === 'green',
                                    ])></span>
                                    {{ $category->name }}
                                </div>
                            </flux:select.option>
                        @endforeach
                    </flux:select.group>
                @endforeach
            </flux:select>

            <flux:select
                label="Conta"
                required
                badge="Obrigatório"
                placeholder="Selecione uma das opções"
                variant="listbox"
                wire:model="form.account_id"
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

            <flux:input
                wire:model="form.description"
                badge="Obrigatório"
                label="Descrição"
                placeholder="Ex.: Mercado, Uber, salário"
                autocomplete="off"
                required
            />

            <flux:textarea
                wire:model="form.notes"
                label="Observação"
                rows="2"
                cols="2"
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
    @endif
</flux:modal>

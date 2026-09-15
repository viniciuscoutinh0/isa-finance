<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading
                size="xl"
                level="1"
            >Lançamentos</flux:heading>
            <flux:text class="mt-2">Tudo que entrou e saiu das suas contas.</flux:text>
        </div>
        <flux:button
            class="w-full shrink-0 sm:w-auto"
            variant="primary"
            icon="plus"
            x-on:click="$flux.modal('transaction-create').show();"
        >Novo lançamento</flux:button>
    </div>

    <flux:separator
        variant="subtle"
        class="my-6"
    />

    <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <flux:input
            wire:model.live.debounce.400ms="search"
            icon="magnifying-glass"
            placeholder="Buscar descrição"
        />

        <flux:select
            wire:model.live="filterAccount"
            placeholder="Todas as contas"
            variant="listbox"
        >
            <flux:select.option value="">Todas as contas</flux:select.option>
            @foreach ($this->accounts as $account)
                <flux:select.option
                    value="{{ $account->id }}"
                    :icon="$account->type->icon()"
                >{{ $account->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select
            wire:model.live="filterCategory"
            placeholder="Todas as categorias"
        >
            <flux:select.option value="">Todas as categorias</flux:select.option>
            @foreach ($this->categories as $category)
                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select
            wire:model.live="filterType"
            placeholder="Entradas e saídas"
        >
            <flux:select.option value="">Entradas e saídas</flux:select.option>
            @foreach (\App\Enums\CategoryType::cases() as $type)
                <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($this->transactions->isEmpty())
        <flux:callout
            icon="banknotes"
            variant="secondary"
        >
            <flux:callout.heading>Nenhum lançamento encontrado</flux:callout.heading>
            <flux:callout.text>Ajuste os filtros ou registre um novo lançamento.</flux:callout.text>
        </flux:callout>
    @else
        <flux:table :paginate="$this->transactions">
            <flux:table.columns>
                <flux:table.column>Data</flux:table.column>
                <flux:table.column>Descrição</flux:table.column>
                <flux:table.column>Categoria</flux:table.column>
                <flux:table.column>Conta</flux:table.column>
                <flux:table.column align="end">Valor</flux:table.column>
                <flux:table.column />
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->transactions as $transaction)
                    @php($type = $transaction->category->type)
                    <flux:table.row wire:key="transaction-{{ $transaction->id }}">
                        <flux:table.cell class="whitespace-nowrap">
                            {{ $transaction->date->translatedFormat('d/m/Y') }}
                        </flux:table.cell>
                        <flux:table.cell class="font-medium">
                            {{ $transaction->description }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="$type->color()"
                            >{{ $transaction->category->name }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $transaction->account->name }}</flux:table.cell>
                        <flux:table.cell
                            align="end"
                            class="whitespace-nowrap font-medium {{ $type === \App\Enums\CategoryType::Income ? 'text-green-600 dark:text-green-400' : 'text-rose-600 dark:text-rose-400' }}"
                        >
                            {{ $type === \App\Enums\CategoryType::Income ? '+' : '−' }}{{ \App\Data\Money::fromCents($transaction->amount)->format() }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:dropdown>
                                <flux:button
                                    size="sm"
                                    variant="subtle"
                                    icon="ellipsis-horizontal"
                                />
                                <flux:menu>
                                    <flux:menu.item
                                        icon="pencil-square"
                                        x-on:click="$wire.dispatchTo('transactions.update', 'open-transaction-update', { id: '{{ $transaction->id }}' })"
                                    >
                                        Editar
                                    </flux:menu.item>

                                    <livewire:transactions.delete
                                        :transaction="$transaction"
                                        wire:key="delete-transaction-{{ $transaction->id }}"
                                    />
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    <livewire:transactions.create />
    <livewire:transactions.update />
</div>

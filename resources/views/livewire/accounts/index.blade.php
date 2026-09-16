<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">Contas</flux:heading>
            <flux:text class="mt-2">Onde seu dinheiro fica: conta-corrente, poupança, dinheiro e cartão.</flux:text>
        </div>
        <flux:button class="w-full shrink-0 sm:w-auto" variant="primary" icon="plus" x-on:click="$dispatch('account::create')">Nova conta</flux:button>
    </div>

    <flux:separator variant="subtle" class="my-6" />

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:switch wire:model.live="filters.archived" label="Mostrar arquivadas" />
        <div class="sm:text-right">
            <flux:text size="sm">Saldo total (ativas)</flux:text>
            <flux:heading @class(['tabular-nums', 'text-expense' => $this->activeTotal->isNegative()])>
                {{ $this->activeTotal->format() }}
            </flux:heading>
        </div>
    </div>

    @if ($this->accounts->isEmpty())
        <flux:callout icon="wallet" variant="secondary">
            @if ($filters['archived'])
                <flux:callout.heading>Nenhuma conta arquivada</flux:callout.heading>
                <flux:callout.text>As contas que você arquivar aparecem aqui.</flux:callout.text>
            @else
                <flux:callout.heading>Você ainda não tem nenhuma conta</flux:callout.heading>
                <flux:callout.text>Toque em “Nova conta” para criar a primeira.</flux:callout.text>
            @endif
        </flux:callout>
    @else
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <flux:button size="sm" class="shrink-0" :variant="($filters['type'] === null || $filters['type'] === '') ? 'primary' : 'filled'"
                wire:click="$set('filters.type', null)">
                Todas
            </flux:button>

            @foreach ($this->availableTypes as $type)
                <flux:button size="sm" class="shrink-0" :icon="$type->icon()"
                    :variant="$filters['type'] === $type->value ? 'primary' : 'filled'"
                    wire:click="$set('filters.type', '{{ $type->value }}')">
                    {{ $type->label() }}
                </flux:button>
            @endforeach
        </div>

        @if ($this->visibleAccounts->isEmpty())
            <flux:callout icon="wallet" variant="secondary">
                <flux:callout.heading>Nenhuma conta desse tipo</flux:callout.heading>
                <flux:callout.text>
                    <flux:link as="button" wire:click="$set('filters.type', null)">Ver todas as contas</flux:link>
                </flux:callout.text>
            </flux:callout>
        @else
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($this->visibleAccounts as $account)
                    <flux:card wire:key="account-{{ $account->id }}" class="flex flex-col gap-3 max-sm:p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex min-w-0 items-center gap-2">
                                <flux:icon :icon="$account->type->icon()" variant="mini" class="shrink-0 text-zinc-400" />
                                <div class="min-w-0">
                                    <flux:heading class="truncate">{{ $account->name }}</flux:heading>
                                    <flux:text size="sm">{{ $account->type->label() }}</flux:text>
                                </div>
                            </div>
                            @if ($account->archived)
                                <flux:badge size="sm" color="zinc">Arquivada</flux:badge>
                            @endif
                        </div>

                        <div>
                            <flux:text size="sm">Saldo atual</flux:text>
                            <flux:heading size="lg" @class(['tabular-nums', 'text-expense' => $account->balance->isNegative()])>
                                {{ $account->balance->format() }}
                            </flux:heading>
                            @if ($account->initialBalance->cents !== $account->balance->cents)
                                <flux:text size="sm">Inicial: {{ $account->initialBalance->format() }}</flux:text>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-1">
                            <flux:button size="sm" variant="subtle" icon="pencil-square"
                                x-on:click="$dispatch('account::edit', { id: {{ $account->id }} })">Editar</flux:button>

                            @if ($account->archived)
                                <flux:button size="sm" variant="subtle" icon="arrow-uturn-up"
                                    wire:click="unarchive({{ $account->id }})">Reativar</flux:button>
                            @else
                                <flux:button size="sm" variant="subtle" icon="archive-box"
                                    wire:click="archive({{ $account->id }})">Arquivar</flux:button>
                            @endif

                            <flux:button size="sm" variant="subtle" icon="trash"
                                wire:click="delete({{ $account->id }})"
                                wire:confirm="Tem certeza que quer excluir a conta “{{ $account->name }}”? Essa ação não pode ser desfeita.">Excluir</flux:button>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        @endif
    @endif

    <livewire:accounts.create />
    <livewire:accounts.update />
</div>

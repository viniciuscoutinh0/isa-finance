<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">Contas</flux:heading>
            <flux:text class="mt-2">Onde seu dinheiro fica: conta-corrente, poupança, dinheiro e cartão.</flux:text>
        </div>
        <flux:button class="w-full shrink-0 sm:w-auto" variant="primary" icon="plus" wire:click="create">Nova conta</flux:button>
    </div>

    <flux:separator variant="subtle" class="my-6" />

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:switch wire:model.live="showArchived" label="Mostrar arquivadas" />
        <div class="sm:text-right">
            <flux:text size="sm">Saldo total (ativas)</flux:text>
            <flux:heading @class(['text-rose-500 dark:text-rose-400' => $this->activeTotal->isNegative()])>
                {{ $this->activeTotal->format() }}
            </flux:heading>
        </div>
    </div>

    @if ($this->accounts->isEmpty())
        <flux:callout icon="wallet">
            {{ $showArchived ? 'Nenhuma conta arquivada.' : 'Nenhuma conta ainda. Crie a primeira.' }}
        </flux:callout>
    @else
        {{-- Filtro por tipo — clique numa tag para filtrar os cards. --}}
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <flux:button size="sm" :variant="($filterType === null || $filterType === '') ? 'primary' : 'filled'"
                wire:click="$set('filterType', null)">
                Todas
            </flux:button>

            @foreach ($this->availableTypes as $type)
                <flux:button size="sm" :icon="$type->icon()"
                    :variant="$filterType === $type->value ? 'primary' : 'filled'"
                    wire:click="$set('filterType', '{{ $type->value }}')">
                    {{ $type->label() }}
                </flux:button>
            @endforeach
        </div>

        @if ($this->visibleAccounts->isEmpty())
            <flux:callout icon="wallet">
                Nenhuma conta desse tipo.
                <flux:link as="button" wire:click="$set('filterType', null)">Ver todas</flux:link>.
            </flux:callout>
        @else
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($this->visibleAccounts as $account)
                    <flux:card wire:key="account-{{ $account->id }}" class="flex flex-col gap-3">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon :icon="$account->type->icon()" class="size-5 text-zinc-400" />
                                <div>
                                    <flux:heading>{{ $account->name }}</flux:heading>
                                    <flux:text size="sm">{{ $account->type->label() }}</flux:text>
                                </div>
                            </div>
                            @if ($account->archived)
                                <flux:badge size="sm" color="zinc">Arquivada</flux:badge>
                            @endif
                        </div>
    
                        <div>
                            <flux:text size="sm">Saldo atual</flux:text>
                            <flux:heading size="lg" @class(['text-rose-500 dark:text-rose-400' => $account->balance->isNegative()])>
                                {{ $account->balance->format() }}
                            </flux:heading>
                            @if ($account->initialBalance->cents !== $account->balance->cents)
                                <flux:text size="sm">Inicial: {{ $account->initialBalance->format() }}</flux:text>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-1">
                            <flux:button size="sm" variant="subtle" icon="pencil-square"
                                wire:click="edit({{ $account->id }})">Editar</flux:button>

                            @if ($account->archived)
                                <flux:button size="sm" variant="subtle" icon="arrow-uturn-up"
                                    wire:click="unarchive({{ $account->id }})">Reativar</flux:button>
                            @else
                                <flux:button size="sm" variant="subtle" icon="archive-box"
                                    wire:click="archive({{ $account->id }})">Arquivar</flux:button>
                            @endif

                            <flux:button size="sm" variant="subtle" icon="trash"
                                wire:click="delete({{ $account->id }})"
                                wire:confirm="Excluir a conta “{{ $account->name }}”? Isso não pode ser desfeito.">Excluir</flux:button>
                        </div>
                    </flux:card>
                @endforeach
            </div>
        @endif
    @endif

    <flux:modal wire:model.self="showModal" class="md:w-96">
        <form wire:submit="save" class="flex flex-col gap-6">
            <flux:heading size="lg">
                {{ $form->accountId ? 'Editar conta' : 'Nova conta' }}
            </flux:heading>

            <flux:input wire:model="form.name" label="Nome" placeholder="Ex.: Nubank" required />

            <flux:select wire:model="form.type" label="Tipo" required>
                @foreach (\App\Enums\AccountType::cases() as $type)
                    <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="form.initialBalance" label="Saldo inicial" inputmode="decimal"
                description="Use vírgula para os centavos. Negativo para dívida de cartão." />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Salvar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">Transferências</flux:heading>
            <flux:text class="mt-2">Dinheiro movido entre as suas contas. Não conta como entrada nem saída.</flux:text>
        </div>
        <flux:button class="w-full shrink-0 sm:w-auto" variant="primary" icon="plus" wire:click="create">Nova transferência</flux:button>
    </div>

    <flux:separator variant="subtle" class="my-6" />

    <flux:select wire:model.live="filterAccount" placeholder="Todas as contas" class="mb-4 w-full sm:max-w-xs">
        <flux:select.option value="">Todas as contas</flux:select.option>
        @foreach ($this->accounts as $account)
            <flux:select.option value="{{ $account->id }}">{{ $account->name }}</flux:select.option>
        @endforeach
    </flux:select>

    @if ($this->transfers->isEmpty())
        <flux:callout icon="arrows-right-left" variant="secondary">
            <flux:callout.heading>Nenhuma transferência ainda</flux:callout.heading>
            <flux:callout.text>Quando você mover dinheiro entre contas, ela aparece aqui.</flux:callout.text>
        </flux:callout>
    @else
        <flux:table :paginate="$this->transfers">
            <flux:table.columns>
                <flux:table.column>Data</flux:table.column>
                <flux:table.column>De</flux:table.column>
                <flux:table.column>Para</flux:table.column>
                <flux:table.column>Observação</flux:table.column>
                <flux:table.column align="end">Valor</flux:table.column>
                <flux:table.column />
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->transfers as $transfer)
                    <flux:table.row wire:key="transfer-{{ $transfer->id }}">
                        <flux:table.cell class="whitespace-nowrap">{{ $transfer->date->translatedFormat('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell>{{ $transfer->fromAccount->name }}</flux:table.cell>
                        <flux:table.cell>
                            <span class="inline-flex items-center gap-1">
                                <flux:icon icon="arrow-long-right" variant="micro" class="text-zinc-400" />
                                {{ $transfer->toAccount->name }}
                            </span>
                        </flux:table.cell>
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $transfer->notes }}</flux:table.cell>
                        <flux:table.cell align="end" class="whitespace-nowrap font-medium">
                            {{ \App\Data\Money::fromCents($transfer->amount)->format() }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:dropdown>
                                <flux:button size="sm" variant="subtle" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil-square" wire:click="edit({{ $transfer->id }})">Editar</flux:menu.item>
                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="delete({{ $transfer->id }})"
                                        wire:confirm="Tem certeza que quer excluir essa transferência? Essa ação não pode ser desfeita.">Excluir</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    <flux:modal wire:model.self="showModal" class="md:w-[28rem]">
        <form wire:submit="save" class="flex flex-col gap-5">
            <flux:heading size="lg">
                {{ $form->transferId ? 'Editar transferência' : 'Nova transferência' }}
            </flux:heading>

            <flux:select wire:model="form.fromAccountId" label="De" required>
                <flux:select.option value="" disabled>Selecione…</flux:select.option>
                @foreach ($this->accounts as $account)
                    <flux:select.option value="{{ $account->id }}">{{ $account->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="form.toAccountId" label="Para" required>
                <flux:select.option value="" disabled>Selecione…</flux:select.option>
                @foreach ($this->accounts as $account)
                    <flux:select.option value="{{ $account->id }}">{{ $account->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="grid gap-3 sm:grid-cols-2">
                <flux:input wire:model="form.date" type="date" label="Data" required />
                <flux:input wire:model="form.amount" label="Valor" inputmode="decimal" placeholder="0,00" required />
            </div>

            <flux:textarea wire:model="form.notes" label="Observação (opcional)" rows="2" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Salvar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

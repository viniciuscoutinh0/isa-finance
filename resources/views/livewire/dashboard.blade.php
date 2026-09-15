<div>
    <flux:heading size="xl" level="1">Painel</flux:heading>
    <flux:text class="mt-2">Oi, {{ auth()->user()->name }}! Aqui está o resumo das suas finanças.</flux:text>

    <flux:separator variant="subtle" class="my-6" />

    {{-- Key numbers --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <flux:text size="sm">Saldo total</flux:text>
                <flux:icon.banknotes variant="mini" class="text-zinc-400" />
            </div>
            <flux:heading size="xl" @class(['tabular-nums', 'text-rose-500 dark:text-rose-400' => $this->totalBalance->isNegative()])>
                {{ $this->totalBalance->format() }}
            </flux:heading>
            <flux:text size="sm">Contas ativas somadas.</flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <flux:text size="sm">Entradas no mês</flux:text>
                <flux:icon.arrow-trending-up variant="mini" class="text-green-500 dark:text-green-400" />
            </div>
            <flux:heading size="xl" class="tabular-nums text-green-600 dark:text-green-400">
                {{ $this->currentMonth->income->format() }}
            </flux:heading>
            <flux:text size="sm">{{ $this->currentMonth->monthLabel() }}</flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <flux:text size="sm">Saídas no mês</flux:text>
                <flux:icon.arrow-trending-down variant="mini" class="text-rose-500 dark:text-rose-400" />
            </div>
            <flux:heading size="xl" class="tabular-nums text-rose-600 dark:text-rose-400">
                {{ $this->currentMonth->expense->format() }}
            </flux:heading>
            <flux:text size="sm">{{ $this->currentMonth->monthLabel() }}</flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <flux:text size="sm">Resultado do mês</flux:text>
                <flux:icon.scale variant="mini" class="text-zinc-400" />
            </div>
            <flux:heading size="xl"
                @class(['tabular-nums', 'text-rose-500 dark:text-rose-400' => $this->currentMonth->net()->isNegative(), 'text-green-600 dark:text-green-400' => $this->currentMonth->net()->isPositive()])>
                {{ $this->currentMonth->net()->format() }}
            </flux:heading>
            <flux:text size="sm">Entradas menos saídas.</flux:text>
        </flux:card>
    </div>

    {{-- Cash flow over time --}}
    <flux:card class="mt-6">
        <flux:heading size="lg">Fluxo de caixa</flux:heading>
        <flux:text size="sm" class="mt-1">Entradas e saídas dos últimos 6 meses.</flux:text>

        <flux:chart :value="$this->cashFlowSeries" class="mt-6">
            <flux:chart.viewport class="aspect-[3/1] min-h-[16rem] w-full">
                <flux:chart.svg>
                    <flux:chart.group>
                        <flux:chart.bar field="income" class="text-green-500 dark:text-green-400" radius="4 4" />
                        <flux:chart.bar field="expense" class="text-rose-500 dark:text-rose-400" radius="4 4" />
                    </flux:chart.group>

                    <flux:chart.axis axis="x" field="month">
                        <flux:chart.axis.tick class="text-xs text-zinc-400" />
                        <flux:chart.axis.line class="text-zinc-200 dark:text-zinc-700" />
                    </flux:chart.axis>

                    <flux:chart.axis axis="y" :format="[
                        'style' => 'currency',
                        'currency' => 'BRL',
                        'notation' => 'compact',
                        'maximumFractionDigits' => 1,
                    ]">
                        <flux:chart.axis.grid class="text-zinc-100 dark:text-zinc-800" />
                        <flux:chart.axis.tick class="text-xs text-zinc-400" />
                    </flux:chart.axis>

                    <flux:chart.cursor type="area" />
                </flux:chart.svg>
            </flux:chart.viewport>

            <div class="mt-4 flex justify-center gap-6">
                <flux:chart.legend label="Entradas">
                    <flux:chart.legend.indicator class="bg-green-500 dark:bg-green-400" />
                </flux:chart.legend>
                <flux:chart.legend label="Saídas">
                    <flux:chart.legend.indicator class="bg-rose-500 dark:bg-rose-400" />
                </flux:chart.legend>
            </div>

            <flux:chart.tooltip>
                <flux:chart.tooltip.heading field="month" />
                <flux:chart.tooltip.value field="income" label="Entradas"
                    :format="['style' => 'currency', 'currency' => 'BRL']" />
                <flux:chart.tooltip.value field="expense" label="Saídas"
                    :format="['style' => 'currency', 'currency' => 'BRL']" />
            </flux:chart.tooltip>
        </flux:chart>
    </flux:card>

    <flux:heading size="lg" class="mt-8 mb-3">Contas</flux:heading>
    @if ($this->accounts->isEmpty())
        <flux:callout icon="wallet" variant="secondary" class="mb-6">
            <flux:callout.heading>Você ainda não tem nenhuma conta</flux:callout.heading>
            <flux:callout.text>
                <flux:link :href="route('accounts.index')" wire:navigate>Crie a primeira</flux:link> — leva menos de um minuto.
            </flux:callout.text>
        </flux:callout>
    @else
        <div class="mb-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->accounts as $account)
                <flux:card wire:key="account-{{ $account->id }}" class="flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <flux:icon :icon="$account->type->icon()" variant="micro" class="text-zinc-400" />
                        <flux:text class="font-medium">{{ $account->name }}</flux:text>
                    </div>
                    <flux:heading size="lg" @class(['text-rose-500 dark:text-rose-400' => $account->balance->isNegative()])>
                        {{ $account->balance->format() }}
                    </flux:heading>
                </flux:card>
            @endforeach
        </div>
    @endif

    <div class="mb-3 flex items-center justify-between">
        <flux:heading size="lg">Últimos lançamentos</flux:heading>
        <flux:link :href="route('transactions.index')" wire:navigate>Ver todos</flux:link>
    </div>

    @if ($this->recentTransactions->isEmpty())
        <flux:callout icon="banknotes" variant="secondary">
            <flux:callout.heading>Nenhum lançamento ainda</flux:callout.heading>
            <flux:callout.text>Assim que você registrar algo, ele aparece aqui.</flux:callout.text>
        </flux:callout>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Data</flux:table.column>
                <flux:table.column>Descrição</flux:table.column>
                <flux:table.column>Conta</flux:table.column>
                <flux:table.column align="end">Valor</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->recentTransactions as $transaction)
                    @php($type = $transaction->category->type)
                    <flux:table.row wire:key="recent-{{ $transaction->id }}">
                        <flux:table.cell class="whitespace-nowrap">{{ $transaction->date->translatedFormat('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $transaction->description }}</flux:table.cell>
                        <flux:table.cell>{{ $transaction->account->name }}</flux:table.cell>
                        <flux:table.cell align="end"
                            class="whitespace-nowrap font-medium {{ $type === \App\Enums\CategoryType::Income ? 'text-green-600 dark:text-green-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $type === \App\Enums\CategoryType::Income ? '+' : '−' }}{{ \App\Data\Money::fromCents($transaction->amount)->format() }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>

<div>
    <flux:heading size="xl" level="1">Painel</flux:heading>
    <flux:text class="mt-2">Oi, {{ auth()->user()->name }}! Aqui está o resumo das suas finanças.</flux:text>

    <flux:separator variant="subtle" class="my-6" />

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <flux:card class="flex flex-col gap-1.5 max-sm:p-4">
            <div class="flex items-center justify-between">
                <flux:text size="sm">Saldo total</flux:text>
                <flux:icon.banknotes variant="mini" class="text-zinc-400" />
            </div>
            <flux:heading size="lg" @class(['tabular-nums sm:text-2xl', 'text-expense' => $this->totalBalance->isNegative()])>
                {{ $this->totalBalance->format() }}
            </flux:heading>
            <flux:text size="sm">Contas ativas somadas.</flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-1.5 max-sm:p-4">
            <div class="flex items-center justify-between">
                <flux:text size="sm">Entradas no mês</flux:text>
                <flux:icon.arrow-trending-up variant="mini" class="text-income" />
            </div>
            <flux:heading size="lg" class="text-income tabular-nums sm:text-2xl">
                {{ $this->currentMonth->income->format() }}
            </flux:heading>
            <flux:text size="sm">{{ $this->currentMonth->monthLabel() }}</flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-1.5 max-sm:p-4">
            <div class="flex items-center justify-between">
                <flux:text size="sm">Saídas no mês</flux:text>
                <flux:icon.arrow-trending-down variant="mini" class="text-expense" />
            </div>
            <flux:heading size="lg" class="text-expense tabular-nums sm:text-2xl">
                {{ $this->currentMonth->expense->format() }}
            </flux:heading>
            <flux:text size="sm">{{ $this->currentMonth->monthLabel() }}</flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-1.5 max-sm:p-4">
            <div class="flex items-center justify-between">
                <flux:text size="sm">Resultado do mês</flux:text>
                <flux:icon.scale variant="mini" class="text-zinc-400" />
            </div>
            <flux:heading size="lg"
                @class(['tabular-nums sm:text-2xl', 'text-expense' => $this->currentMonth->net()->isNegative(), 'text-income' => $this->currentMonth->net()->isPositive()])>
                {{ $this->currentMonth->net()->format() }}
            </flux:heading>
            <flux:text size="sm">Entradas menos saídas.</flux:text>
        </flux:card>
    </div>

    <flux:card class="mt-4 sm:mt-6">
        <flux:heading size="lg">Fluxo de caixa</flux:heading>
        <flux:text size="sm" class="mt-1">Entradas e saídas dos últimos 6 meses.</flux:text>

        <flux:chart :value="$this->cashFlowSeries" class="mt-6">
            <flux:chart.viewport class="aspect-4/3 w-full sm:aspect-3/1 sm:min-h-64">
                <flux:chart.svg>
                    <flux:chart.group>
                        <flux:chart.bar field="income" class="text-income" radius="4 4" />
                        <flux:chart.bar field="expense" class="text-expense" radius="4 4" />
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

            <div class="mt-4 flex flex-wrap justify-center gap-x-6 gap-y-2">
                <flux:chart.legend label="Entradas">
                    <flux:chart.legend.indicator class="bg-income" />
                </flux:chart.legend>
                <flux:chart.legend label="Saídas">
                    <flux:chart.legend.indicator class="bg-expense" />
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

    <flux:heading size="lg" class="mt-6 mb-3 sm:mt-8">Contas</flux:heading>
    @if ($this->accounts->isEmpty())
        <flux:callout icon="wallet" variant="secondary" class="mb-6">
            <flux:callout.heading>Você ainda não tem nenhuma conta</flux:callout.heading>
            <flux:callout.text>
                <flux:link :href="route('accounts.index')" wire:navigate>Crie a primeira</flux:link> — leva menos de um minuto.
            </flux:callout.text>
        </flux:callout>
    @else
        <div class="mb-6 grid gap-3 sm:mb-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->accounts as $account)
                <flux:card wire:key="account-{{ $account->id }}" class="flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <flux:icon :icon="$account->type->icon()" variant="micro" class="text-zinc-400" />
                        <flux:text class="font-medium">{{ $account->name }}</flux:text>
                    </div>
                    <flux:heading size="lg" @class(['text-expense' => $account->balance->isNegative()])>
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
        {{-- Mobile: one row per transaction; the table below takes over from md up. --}}
        <ul class="divide-y divide-zinc-200 md:hidden dark:divide-zinc-700">
            @foreach ($this->recentTransactions as $transaction)
                @php($type = $transaction->category->type)
                <li
                    wire:key="recent-card-{{ $transaction->id }}"
                    class="flex items-center justify-between gap-3 py-3"
                >
                    <div class="min-w-0">
                        <flux:text class="truncate font-medium">{{ $transaction->description }}</flux:text>
                        <flux:text size="sm" class="truncate">
                            {{ $transaction->date->translatedFormat('d/m') }} · {{ $transaction->account->name }}
                        </flux:text>
                    </div>
                    <flux:text
                        @class([
                            'shrink-0 font-medium tabular-nums',
                            'text-income' => $type === \App\Enums\CategoryType::Income,
                            'text-expense' => $type !== \App\Enums\CategoryType::Income,
                        ])
                    >
                        {{ $type === \App\Enums\CategoryType::Income ? '+' : '−' }}{{ \App\Data\Money::fromCents($transaction->amount)->format() }}
                    </flux:text>
                </li>
            @endforeach
        </ul>

        <flux:table class="max-md:hidden">
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
                            class="whitespace-nowrap font-medium tabular-nums {{ $type === \App\Enums\CategoryType::Income ? 'text-income' : 'text-expense' }}">
                            {{ $type === \App\Enums\CategoryType::Income ? '+' : '−' }}{{ \App\Data\Money::fromCents($transaction->amount)->format() }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>

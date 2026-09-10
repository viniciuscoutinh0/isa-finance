<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<x-partials.head :title="$title ?? null" />

<x-partials.body class="grid min-h-screen lg:grid-cols-2">
    {{-- Form side --}}
    <div class="flex flex-col items-center justify-center bg-white p-6 dark:bg-zinc-800">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex flex-col items-center gap-2 lg:items-start">
                <flux:icon.banknotes class="size-8 text-accent" />
                <flux:heading size="lg">{{ config('app.name') }}</flux:heading>
            </div>

            <flux:card>
                {{ $slot }}
            </flux:card>
        </div>
    </div>

    {{--
        Visual panel — identity band: deliberately commits to a single dark look
        (no dark: pairs), matching the app's dark surfaces (zinc → neutral ramp)
        and the indigo accent. Hidden below lg.
    --}}
    <div class="relative hidden overflow-hidden bg-zinc-950 lg:block">
        {{-- Decorative accent glow --}}
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0"
        >
            <div class="absolute -left-24 -top-24 size-96 rounded-full bg-accent/25 blur-3xl"></div>
            <div class="absolute -bottom-32 -right-16 size-[28rem] rounded-full bg-accent-content/20 blur-3xl"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-zinc-900/40 to-zinc-950"></div>
        </div>

        <div class="relative flex h-full flex-col justify-between p-12">
            <div class="flex items-center gap-2">
                <flux:icon.banknotes class="size-6 text-accent" />
                <span class="font-medium text-white">{{ config('app.name') }}</span>
            </div>

            <div class="max-w-md">
                <h2 class="text-3xl font-semibold leading-tight text-white">
                    Suas finanças, organizadas em um só lugar.
                </h2>
                <p class="mt-3 text-zinc-400">
                    Acompanhe contas, lançamentos e transferências com clareza — do saldo total ao último centavo.
                </p>

                {{-- Product preview mock --}}
                <div class="mt-8 rounded-xl border border-white/10 bg-white/5 p-5 shadow-2xl backdrop-blur-sm">
                    <p class="text-sm text-zinc-400">Saldo total</p>
                    <p class="mt-1 text-3xl font-semibold text-white">R$ 12.480,00</p>

                    <div class="mt-4 flex flex-col gap-2">
                        <div class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2">
                            <span class="flex items-center gap-2 text-sm text-zinc-300">
                                <flux:icon.building-library class="size-4 text-zinc-400" />
                                Conta-corrente
                            </span>
                            <span class="text-sm font-medium text-white">R$ 9.120,00</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2">
                            <span class="flex items-center gap-2 text-sm text-zinc-300">
                                <flux:icon.wallet class="size-4 text-zinc-400" />
                                Dinheiro
                            </span>
                            <span class="text-sm font-medium text-white">R$ 3.360,00</span>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-sm text-zinc-500">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>

    <flux:toast />
</x-partials.body>

</html>

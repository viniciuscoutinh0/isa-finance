<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<x-partials.head :title="$title ?? null" />

<x-partials.body class="bg-zinc-100 dark:bg-zinc-900">
    <flux:sidebar
        sticky
        collapsible="mobile"
        class="bg-transparent"
    >
        <flux:sidebar.header>
            <flux:sidebar.brand
                :href="route('dashboard')"
                :name="config('app.name')"
            >
                <flux:icon
                    icon="banknotes"
                    variant="mini"
                    class="text-accent"
                />
            </flux:sidebar.brand>
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group heading="Geral">
                <flux:sidebar.item
                    icon="home"
                    icon:variant="solid"
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    wire:navigate
                >Painel</flux:sidebar.item>
            </flux:sidebar.group>

            <flux:sidebar.group heading="Movimentações">
                <flux:sidebar.item
                    icon="arrows-up-down"
                    icon:variant="solid"
                    :href="route('transactions.index')"
                    :current="request()->routeIs('transactions.*')"
                    wire:navigate
                >Lançamentos</flux:sidebar.item>
                <flux:sidebar.item
                    icon="arrows-right-left"
                    icon:variant="solid"
                    :href="route('transfers.index')"
                    :current="request()->routeIs('transfers.*')"
                    wire:navigate
                >Transferências</flux:sidebar.item>
            </flux:sidebar.group>

            <flux:sidebar.group heading="Cadastros">
                <flux:sidebar.item
                    icon="wallet"
                    icon:variant="solid"
                    :href="route('accounts.index')"
                    :current="request()->routeIs('accounts.*')"
                    wire:navigate
                >Contas</flux:sidebar.item>
                <flux:sidebar.item
                    icon="tag"
                    icon:variant="solid"
                    :href="route('categories.index')"
                    :current="request()->routeIs('categories.*')"
                    wire:navigate
                >Categorias</flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:sidebar.nav>
            <flux:modal.trigger name="assistant">
                <flux:sidebar.item
                    icon="sparkles"
                    icon:variant="solid"
                    as="button"
                >Assistente</flux:sidebar.item>
            </flux:modal.trigger>
        </flux:sidebar.nav>

        <flux:dropdown
            position="top"
            align="start"
            class="max-lg:hidden"
        >
            <flux:sidebar.profile :name="auth()->user()->name" />
            <x-user-menu />
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle
            class="lg:hidden"
            icon="bars-2"
            inset="left"
        />
        <flux:spacer />
        <flux:modal.trigger name="assistant">
            <flux:button
                variant="subtle"
                icon="sparkles"
                aria-label="Abrir assistente"
            />
        </flux:modal.trigger>
        <flux:dropdown
            position="top"
            align="end"
        >
            <flux:profile
                :name="auth()->user()->name"
                :chevron="false"
            />
            <x-user-menu />
        </flux:dropdown>
    </flux:header>

    <flux:main
        class="m-2 md:rounded-xl md:border md:border-zinc-200 md:bg-white md:shadow-sm lg:my-3 lg:me-3 lg:ms-0 md:dark:border-zinc-700 md:dark:bg-zinc-800"
    >
        <div class="mx-auto w-full max-w-5xl pb-24 lg:pb-0">
            {{ $slot }}
        </div>
    </flux:main>

    <x-mobile-tab-bar />

    @persist('assistant')
        <livewire:assistant.chat />
    @endpersist

    <flux:toast />
</x-partials.body>

</html>

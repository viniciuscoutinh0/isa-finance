{{-- Shared dropdown menu for the authenticated user: account, appearance, logout. --}}
<flux:menu>
    <flux:menu.item disabled>{{ auth()->user()->email }}</flux:menu.item>

    <flux:menu.separator />

    {{-- Dark/light mode toggle — Flux keeps the preference in localStorage. --}}
    <flux:menu.item x-data x-on:click="$flux.dark = ! $flux.dark">
        <x-slot:icon>
            <flux:icon.moon variant="mini" class="me-2" x-show="! $flux.dark" data-flux-menu-item-icon />
            <flux:icon.sun variant="mini" class="me-2" x-show="$flux.dark" data-flux-menu-item-icon />
        </x-slot:icon>
        <span x-text="$flux.dark ? 'Tema claro' : 'Tema escuro'">Tema escuro</span>
    </flux:menu.item>

    <flux:menu.separator />

    <form method="POST" action="{{ route('logout') }}" class="w-full">
        @csrf
        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" variant="danger"
            class="w-full">Sair</flux:menu.item>
    </form>
</flux:menu>

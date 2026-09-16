@php
    $items = [
        ['label' => 'Painel', 'icon' => 'home', 'route' => 'dashboard', 'active' => 'dashboard'],
        ['label' => 'Contas', 'icon' => 'wallet', 'route' => 'accounts.index', 'active' => 'accounts.*'],
        [
            'label' => 'Lançar',
            'icon' => 'arrows-up-down',
            'route' => 'transactions.index',
            'active' => 'transactions.*',
        ],
        [
            'label' => 'Transferir',
            'icon' => 'arrows-right-left',
            'route' => 'transfers.index',
            'active' => 'transfers.*',
        ],
        ['label' => 'Categorias', 'icon' => 'tag', 'route' => 'categories.index', 'active' => 'categories.*'],
    ];
@endphp

<nav
    aria-label="Navegação principal"
    class="fixed inset-x-0 bottom-0 z-40 border-t border-zinc-200 bg-white/85 pb-[calc(env(safe-area-inset-bottom))] backdrop-blur-md lg:hidden dark:border-zinc-700 dark:bg-zinc-900/85"
>
    <ul
        class="grid h-14"
        style="grid-template-columns: repeat({{ count($items) }}, minmax(0, 1fr))"
    >
        @foreach ($items as $item)
            @php($current = request()->routeIs($item['active']))
            <li>
                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    @if ($current) aria-current="page" @endif
                    @class([
                        'relative flex h-full flex-col items-center justify-center gap-0.5 transition-colors',
                        'after:absolute after:inset-x-3 after:top-0 after:h-0.5 after:rounded-full after:bg-accent' => $current,
                        'text-accent' => $current,
                        'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100' => !$current,
                    ])
                >
                    <flux:icon
                        :icon="$item['icon']"
                        :variant="$current ? 'solid' : 'outline'"
                        class="size-5 shrink-0"
                    />
                    <span class="w-full truncate px-0.5 text-center text-[10px] leading-none font-medium">
                        {{ $item['label'] }}
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>

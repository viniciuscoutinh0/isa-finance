@php
    /**
     * App-style bottom tab bar for mobile. Presentation only — mirrors the
     * sidebar nav in resources/views/layouts/dashboard.blade.php.
     *
     * @var array<int, array{label: string, icon: string, route: string, active: string}> $items
     */
    $items = [
        ['label' => 'Painel', 'icon' => 'home', 'route' => 'dashboard', 'active' => 'dashboard'],
        ['label' => 'Contas', 'icon' => 'wallet', 'route' => 'accounts.index', 'active' => 'accounts.*'],
        ['label' => 'Lançamentos', 'icon' => 'arrows-up-down', 'route' => 'transactions.index', 'active' => 'transactions.*'],
        ['label' => 'Transferências', 'icon' => 'arrows-right-left', 'route' => 'transfers.index', 'active' => 'transfers.*'],
        ['label' => 'Categorias', 'icon' => 'tag', 'route' => 'categories.index', 'active' => 'categories.*'],
    ];
@endphp

<nav aria-label="Navegação principal"
    class="fixed inset-x-0 bottom-0 z-40 border-t border-zinc-200 bg-white/90 backdrop-blur-sm lg:hidden dark:border-zinc-700 dark:bg-zinc-900/90"
    style="padding-bottom: env(safe-area-inset-bottom)">
    <ul class="grid h-16" style="grid-template-columns: repeat({{ count($items) }}, minmax(0, 1fr))">
        @foreach ($items as $item)
            @php($current = request()->routeIs($item['active']))
            <li>
                <a href="{{ route($item['route']) }}" wire:navigate @if ($current) aria-current="page" @endif
                    @class([
                        'relative flex h-full flex-col items-center justify-center gap-1 transition-colors',
                        'after:absolute after:inset-x-0 after:top-0 after:h-0.5 after:bg-accent' => $current,
                        'text-accent' => $current,
                        'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100' => ! $current,
                    ])>
                    <flux:icon :icon="$item['icon']" :variant="$current ? 'solid' : 'outline'" class="size-6 shrink-0" />
                    {{-- 11px: tab labels sit below the type scale, matching native app tab bars --}}
                    <span class="w-full truncate px-1 text-center text-[11px] font-medium leading-none">{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>

<body {{ $attributes->merge(['class' => 'min-h-dvh bg-zinc-50 dark:bg-zinc-900 antialiased']) }}>
    {{ $slot }}

    <flux:toast.group>
        <flux:toast position="top center" />
    </flux:toast.group>

    @livewireScripts
    @fluxScripts
</body>

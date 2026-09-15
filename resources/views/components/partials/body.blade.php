<body {{ $attributes->merge(['class' => 'min-h-screen bg-white dark:bg-zinc-900 antialiased']) }}>
    {{ $slot }}

    <flux:toast.group>
        <flux:toast position="top center" />
    </flux:toast.group>

    @livewireScripts
    @fluxScripts
</body>

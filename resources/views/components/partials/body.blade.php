<body {{ $attributes->merge(['class' => 'min-h-screen bg-white dark:bg-zinc-900 antialiased']) }}>
    {{ $slot }}

    @livewireScripts
    @fluxScripts
</body>

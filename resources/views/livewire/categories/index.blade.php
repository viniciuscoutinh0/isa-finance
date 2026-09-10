<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">Categorias</flux:heading>
            <flux:text class="mt-2">Classifique seus lançamentos de entrada e saída.</flux:text>
        </div>
        <flux:button class="w-full shrink-0 sm:w-auto" variant="primary" icon="plus" wire:click="create">Nova categoria</flux:button>
    </div>

    <flux:separator variant="subtle" class="my-6" />

    @php
        $groups = $this->categories->groupBy(fn ($category) => $category->type->value);
    @endphp

    @if ($this->categories->isEmpty())
        <flux:callout icon="tag">Nenhuma categoria ainda. Crie a primeira.</flux:callout>
    @else
        <div class="flex flex-col gap-8">
            @foreach (\App\Enums\CategoryType::cases() as $type)
                <div>
                    <flux:heading size="lg" class="mb-3 flex items-center gap-2">
                        {{ $type->label() }}
                        <flux:badge size="sm" :color="$type->color()">
                            {{ ($groups[$type->value] ?? collect())->count() }}
                        </flux:badge>
                    </flux:heading>

                    @forelse ($groups[$type->value] ?? [] as $category)
                        <div wire:key="category-{{ $category->id }}"
                            class="flex items-center justify-between gap-2 border-b border-zinc-200 py-2 last:border-0 dark:border-zinc-700">
                            <flux:text class="min-w-0 truncate font-medium">{{ $category->name }}</flux:text>
                            <div class="flex shrink-0 items-center gap-1">
                                <flux:button size="sm" variant="subtle" icon="pencil-square"
                                    wire:click="edit({{ $category->id }})">Editar</flux:button>
                                <flux:button size="sm" variant="subtle" icon="trash"
                                    wire:click="delete({{ $category->id }})"
                                    wire:confirm="Excluir a categoria “{{ $category->name }}”?">Excluir</flux:button>
                            </div>
                        </div>
                    @empty
                        <flux:text size="sm">Nenhuma categoria de {{ Str::lower($type->label()) }}.</flux:text>
                    @endforelse
                </div>
            @endforeach
        </div>
    @endif

    <flux:modal wire:model.self="showModal" class="md:w-96">
        <form wire:submit="save" class="flex flex-col gap-6">
            <flux:heading size="lg">
                {{ $form->categoryId ? 'Editar categoria' : 'Nova categoria' }}
            </flux:heading>

            <flux:input wire:model="form.name" label="Nome" required />

            <flux:select wire:model="form.type" label="Tipo" required>
                @foreach (\App\Enums\CategoryType::cases() as $type)
                    <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Salvar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

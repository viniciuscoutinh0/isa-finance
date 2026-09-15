<div>
    <flux:menu.item
        type="button"
        icon="trash"
        variant="danger"
        wire:click="delete"
        wire:confirm="Tem certeza que quer excluir o lançamento &quot;{{ $transaction->description }}&quot;? Essa ação não pode ser desfeita."
    >
        Excluir
    </flux:menu.item>
</div>

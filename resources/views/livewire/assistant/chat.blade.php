<div>
    <flux:modal
        name="assistant"
        flyout
        :dismissible="!$awaitingReply && !$showApproval"
        class="flex h-full flex-col md:w-[28rem]"
        variant="floating"
    >
        <div>
            <flux:heading size="lg">Assistente</flux:heading>
            <flux:text size="sm">Pergunte sobre suas contas e lançamentos, ou peça para eu registrar algo por você.</flux:text>
        </div>

        <flux:separator
            variant="subtle"
            class="my-4"
        />

        {{-- Transcript --}}
        @php($me = auth()->user())
        <div
            class="scroll-fade flex min-h-0 flex-1 flex-col gap-4 overflow-y-auto pe-1"
            x-data="{
                pinned: true,
                toBottom() { this.$el.scrollTop = this.$el.scrollHeight },
                init() {
                    this.$nextTick(() => this.toBottom())
                    this._obs = new MutationObserver(() => { if (this.pinned) this.toBottom() })
                    this._obs.observe(this.$el, { childList: true, subtree: true, characterData: true })
                },
                destroy() { this._obs?.disconnect() },
            }"
            x-on:scroll.passive="pinned = $el.scrollHeight - $el.scrollTop - $el.clientHeight < 48"
        >
            @forelse ($messages as $message)
                @php($isUser = $message['role'] === 'user')
                <div
                    wire:key="msg-{{ $loop->index }}"
                    @class(['motion-safe:animate-chat-pop flex items-start gap-2', 'flex-row-reverse' => $isUser])
                >
                    @if ($isUser)
                        <flux:avatar
                            circle
                            size="xs"
                            :name="$me->name"
                            color="auto"
                            color:seed="{{ (string) $me->id }}"
                            class="mt-0.5 shrink-0"
                        />
                    @else
                        <flux:avatar
                            circle
                            size="xs"
                            icon="sparkles"
                            class="mt-0.5 shrink-0"
                        />
                    @endif

                    <div
                        @class([
                            'max-w-[85%] rounded-xl px-3 py-2 text-sm',
                            'bg-accent text-accent-foreground' => $isUser,
                            'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-100' => ! $isUser,
                        ])
                    >
                        <div
                            class="[&_a]:underline [&_li]:my-0.5 [&_ol]:my-1 [&_ol]:list-decimal [&_ol]:ps-4 [&_p]:my-1 [&_p:first-child]:mt-0 [&_p:last-child]:mb-0 [&_ul]:my-1 [&_ul]:list-disc [&_ul]:ps-4">
                            {!! $message['html'] !!}
                        </div>
                    </div>
                </div>
            @empty
                <flux:text
                    size="sm"
                    class="m-auto text-center"
                >
                    Ainda não tem nada por aqui. Pergunte “quanto gastei esse mês?” ou
                    peça “adiciona um lançamento de 50 reais no mercado”.
                </flux:text>
            @endforelse

            {{-- Live streamed reply --}}
            @if ($awaitingReply)
                <div class="motion-safe:animate-chat-pop flex items-start gap-2">
                    <flux:avatar
                        circle
                        size="xs"
                        icon="sparkles"
                        class="mt-0.5 shrink-0"
                    />
                    <div
                        class="max-w-[85%] rounded-xl bg-zinc-100 px-3 py-2 text-sm text-zinc-800 dark:bg-zinc-700 dark:text-zinc-100"
                    >
                        <span
                            wire:stream="assistant-status"
                            class="block empty:hidden"
                        ></span>
                        <span
                            wire:stream="assistant-current"
                            class="whitespace-pre-wrap"
                        ></span>
                        <flux:icon.loading
                            variant="micro"
                            class="ms-1 inline align-[-2px]"
                        />
                    </div>
                </div>
            @endif
        </div>

        {{-- Approval form: shown when a write tool is waiting for confirmation --}}
        @if ($showApproval)
            <flux:separator
                variant="subtle"
                class="my-4"
            />

            <form
                wire:submit="confirmApproval"
                class="flex flex-col gap-4"
            >
                <flux:heading size="sm">
                    {{ $approvalKind === 'transfer' ? 'Confirmar transferência' : 'Confirmar lançamento' }}
                </flux:heading>

                <div class="flex flex-col gap-2">
                    <flux:label>{{ $approvalKind === 'transfer' ? 'Conta de origem' : 'Conta' }}</flux:label>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($this->accounts as $account)
                            <flux:button
                                size="sm"
                                :variant="$approvalAccountId === $account->id ? 'primary' : 'filled'"
                                wire:click="$set('approvalAccountId', {{ $account->id }})"
                            >
                                {{ $account->name }}
                            </flux:button>
                        @endforeach
                    </div>
                    <flux:error name="approvalAccountId" />
                </div>

                @if ($approvalKind === 'transfer')
                    <div class="flex flex-col gap-2">
                        <flux:label>Conta de destino</flux:label>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($this->accounts as $account)
                                <flux:button
                                    size="sm"
                                    :variant="$approvalToAccountId === $account->id ? 'primary' : 'filled'"
                                    wire:click="$set('approvalToAccountId', {{ $account->id }})"
                                >
                                    {{ $account->name }}
                                </flux:button>
                            @endforeach
                        </div>
                        <flux:error name="approvalToAccountId" />
                    </div>
                @else
                    <div class="flex flex-col gap-2">
                        <flux:label>Categoria</flux:label>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($this->categories as $category)
                                <flux:button
                                    size="sm"
                                    :variant="$approvalCategoryId === $category->id ? 'primary' : 'filled'"
                                    wire:click="$set('approvalCategoryId', {{ $category->id }})"
                                >
                                    {{ $category->name }}
                                </flux:button>
                            @endforeach
                        </div>
                        <flux:error name="approvalCategoryId" />
                    </div>

                    <flux:input
                        wire:model="approvalDescription"
                        label="Descrição"
                        placeholder="Ex.: Feira no mercado"
                    />
                @endif

                <flux:input
                    wire:model="approvalAmount"
                    label="Valor"
                    inputmode="decimal"
                    description="Use vírgula para os centavos."
                />

                <flux:input
                    wire:model="approvalDate"
                    type="date"
                    label="Data"
                />

                <div class="flex justify-end gap-2">
                    <flux:button
                        type="button"
                        variant="filled"
                        wire:click="rejectApproval"
                    >Cancelar</flux:button>
                    <flux:button
                        type="submit"
                        variant="primary"
                    >Confirmar</flux:button>
                </div>
            </form>
        @endif

        {{-- Composer --}}
        <flux:separator
            variant="subtle"
            class="my-4"
        />

        <form wire:submit="send">
            <flux:composer
                wire:model="draft"
                name="draft"
                label="Mensagem"
                label:sr-only
                placeholder="Escreva sua mensagem…"
                rows="1"
                max-rows="6"
                submit="enter"
                inline
                :disabled="$awaitingReply || $showApproval"
            >
                <x-slot name="actionsLeading">
                    <flux:button
                        type="button"
                        size="sm"
                        variant="subtle"
                        icon="plus"
                        tooltip="Nova conversa"
                        wire:click="newConversation"
                        :disabled="$awaitingReply || $showApproval"
                    />
                </x-slot>

                <x-slot name="actionsTrailing">
                    <flux:button
                        type="submit"
                        size="sm"
                        variant="primary"
                        icon="paper-airplane"
                        :disabled="$awaitingReply || $showApproval"
                    />
                </x-slot>
            </flux:composer>
        </form>
    </flux:modal>
</div>

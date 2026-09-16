# CRUD dividido em componentes Livewire

Cada domínio com CRUD é servido por três componentes Livewire — `Index`,
`Create` e `Update` — e não por um componente único. Ações sem formulário
(`delete`, `archive`, `unarchive`) são métodos do `Index`.

`app/Livewire/Transactions/` é a implementação de referência.

## Por que não um componente só

O componente único acumula: estado de listagem, estado de filtro, estado de
formulário, e um `save()` que decide entre criar e atualizar por `if ($this->form->xId === null)`.
Tudo isso é serializado no payload de toda requisição, inclusive enquanto o
usuário só está filtrando a lista.

Separar mantém cada componente com um motivo para mudar, e faz o formulário
existir só quando o modal existe.

## Como as peças conversam

**Um evento de mudança por domínio.** `Create` e `Update` disparam
`<domínio>::changed` depois de escrever. O `Index` escuta num método explícito:

```php
#[On('transaction::changed')]
public function refreshList(): void
{
    unset($this->transactions);
}
```

A forma anterior empilhava `#[On]` no próprio método computado. Funcionava por
efeito colateral: o computado é memoizado por requisição, então qualquer
requisição nova já traria dados frescos — o listener só garantia que o evento
não estourasse `EventHandlerDoesNotExist`. Um `#[Computed(persist: true)]`
futuro congelaria a lista sem nenhum aviso.

**Abrir a edição é um broadcast.** A linha da tabela dispara
`$dispatch('transaction::edit', { id: 12 })`, e o `Update` escuta com `#[On]`.
A forma anterior era `$wire.dispatchTo('transactions.update', …)`, que amarra a
view do pai ao nome registrado do filho: mover ou renomear o componente quebra
em silêncio, sem erro de tipo nem teste vermelho.

**Modal por nome.** `Flux::modal('transaction-create')->show()` / `->close()`,
em vez de `public bool $showModal`. Com o formulário num componente filho, uma
propriedade booleana exigiria o pai conhecer o estado interno do filho.

## Por que `delete` não é componente

Excluir não tem formulário nem estado próprio. Como componente, ele precisa ser
renderizado uma vez por linha da tabela — cada instância com seu snapshot e seu
payload. Uma listagem de 20 linhas paga 20 componentes para oferecer 20 botões.

Como método do `Index`, é `wire:click="delete({{ $id }})"` com `wire:confirm`,
a custo zero. Mesmo critério vale para `archive` e `unarchive`.

## Quando o formato não se aplica

A divisão paga por si quando o `Index` carrega estado que o formulário não usa:
paginação, filtros, uma tabela longa. Sem isso, ela só adiciona arquivos.

`app/Livewire/Categories/` fica deliberadamente num componente único. Categorias
não tem paginação, não tem filtro, não tem componente por linha e cabe em uma
tela — o `save()` bifurcado entre criar e atualizar custa menos que três
componentes e dois modais para o mesmo trabalho.

O que vale para todo domínio, independente de dividir ou não: ruleset em
`app/Rules/`, DTO em `app/Data/`, Action recebendo o DTO, e chaves em
`snake_case` na fronteira. Categorias segue tudo isso.

Um módulo que hoje é único migra para o formato quando ganhar a primeira
listagem paginada ou o primeiro filtro.

## Validação compartilhada

As regras ficam em `app/Rules/<Domínio>/<X>Rules.php`: classe sem estado, métodos
estáticos, recebendo o `User` explicitamente. O Form object do Livewire e as
tools do assistente consomem a mesma fonte.

Form Request não serve: `Illuminate\Foundation\Http\FormRequest` depende do ciclo
de resolução HTTP, e nem `Livewire\Form` nem `Laravel\Ai\Tools\Request` são
requests HTTP. Resolver um Form object pelo container também não funciona —
`Livewire\Form::__construct()` exige um `Component` e o nome da propriedade.

## Nomenclatura

Chaves que atravessam fronteira — payload do wire, argumentos de tool, arrays
validados, JSON — são `snake_case`. Propriedades internas de classe seguem o
estilo PHP. A fronteira misturada já custou um bug: o formulário de aprovação do
assistente enviava `account_id` enquanto o contrato exigia `accountId`, e o
lançamento silenciosamente não era gravado.

## O que se perde

- Mais arquivos por domínio.
- O contrato entre componentes passa a ser evento, que o PHP não tipa. Um nome
  de evento errado só aparece em teste — daí a cobertura do `Index` incluir
  explicitamente o refresh por `<domínio>::changed`.
- Componentes irmãos que precisam dos mesmos dados repetem consultas. Mitigado
  com traits granulares em `app/Livewire/Concerns/` (`WithAccountOptions`,
  `WithCategoryOptions`), compostos por quem precisa.

## Alternativas descartadas

- **Manter o componente único** e só extrair o formulário: não resolve o payload
  nem o `save()` bifurcado.
- **Componente `Delete` fora do laço**, recebendo o id por evento: some o custo
  por linha, mas sobra um componente para uma chamada de uma linha.
- **Um evento por operação** (`created`, `updated`, `deleted`): o `Index` não
  usa a distinção — só precisa saber que a lista envelheceu.

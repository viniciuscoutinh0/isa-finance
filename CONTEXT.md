# Finanças Pessoais

Webapp de controle de finanças pessoais: cada usuário registra o dinheiro que entra
e sai das suas contas e acompanha o saldo. O v1 é um livro-caixa — sem orçamento,
sem relatórios, sem recorrências (ver `docs/adr/0001-escopo-do-v1.md`).

## Language

**Usuário**:
Pessoa que se cadastra e acessa o app. Cada usuário só enxerga os próprios dados;
nada é compartilhado entre usuários.
_Avoid_: Cliente, conta (no sentido de login), perfil

**Conta** *(código: `Account`)*:
Um lugar onde o dinheiro do usuário fica — conta-corrente, poupança, dinheiro em
espécie ou cartão de crédito. Tem um tipo e um saldo inicial (editável). O saldo
atual é sempre derivado dos lançamentos, nunca um valor guardado
(ver `docs/adr/0003-saldo-derivado.md`). Uma Conta com histórico não pode ser
excluída, só **arquivada**: sai das listas e do saldo total, o histórico
permanece.
_Avoid_: Carteira, banco

**Lançamento** *(código: `Transaction`)*:
Um registro de dinheiro que entrou ou saiu de uma Conta, numa data, com uma
descrição, um valor e uma Categoria. É um fato que aconteceu — não há lançamento
"previsto" ou "pendente" no v1. No texto em pt-BR, evite "transação" — o usuário
lê "lançamento"; `Transaction` é só o identificador de código.
_Avoid_ (pt-BR): Transação, movimentação, entrada

**Categoria**:
Rótulo que classifica um Lançamento (ex.: "Mercado", "Salário"). Pertence ao
usuário, é de entrada ou de saída, e um Lançamento só aceita Categoria do mesmo
tipo. Lista plana — não há subcategoria. Todo usuário nasce com um conjunto
padrão.
_Avoid_: Tag, rótulo, classificação

**Transferência** *(código: `Transfer`)*:
Movimento de Valor entre duas Contas do mesmo usuário (conta de origem ≠ conta de
destino). Não é um Lançamento e não tem Categoria: afeta o Saldo das duas Contas
mas fica fora das somas de entrada e saída.
_Avoid_: Transação interna, movimentação entre contas

**Valor**:
Quantia monetária de um Lançamento ou saldo, sempre em Reais (BRL). É sempre
positiva num Lançamento; se o dinheiro entrou ou saiu é dito pelo tipo do
Lançamento, não pelo sinal do valor.
_Avoid_: Montante, quantia, importância, preço

**Saldo**:
Quanto dinheiro há numa Conta num dado momento: saldo inicial mais tudo que
entrou, menos tudo que saiu. Um saldo negativo numa Conta de cartão de crédito
representa dívida.
_Avoid_: Balanço, total, balance

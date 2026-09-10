# Transferência é uma entidade própria, não dois Lançamentos

Mover Valor entre duas Contas do usuário é uma linha na tabela `transferencias`
(conta_origem_id, conta_destino_id, valor, data), não um par de Lançamentos
ligados. O cálculo de Saldo de uma Conta soma os Lançamentos e, à parte, subtrai
transferências que saíram e soma as que entraram.

Alternativa rejeitada: dois Lançamentos com um `transfer_id` comum. Descartada
porque toda soma "por categoria" ou "total de despesas" passaria a precisar
excluir explicitamente os Lançamentos de transferência — um filtro fácil de
esquecer e que inflaria receita e despesa com dinheiro que nunca entrou nem saiu
do patrimônio do usuário.

Consequência: a Query de Saldo tem duas fontes (Lançamentos + Transferências) em
vez de uma. É um custo pequeno e explícito, pago num único lugar.

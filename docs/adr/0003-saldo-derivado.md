# Saldo de Conta é derivado, nunca armazenado

O saldo de uma Conta é sempre calculado: `saldo_inicial` + soma dos Lançamentos
de entrada − soma dos Lançamentos de saída. Não existe coluna `saldo` que o
código mantenha em sincronia.

Alternativa rejeitada: coluna `saldo` atualizada a cada Lançamento (via evento ou
na Action). Descartada porque abre espaço para divergência entre o saldo guardado
e a soma real (bug clássico de app financeiro), e o volume de dados de um usuário
pessoal torna o `SUM` agregado barato.

Consequência: telas que mostram saldo dependem de uma Query com agregação; se um
dia a performance doer (muitos anos de histórico), a saída é um saldo materializado
por período fechado — não uma coluna mutável.

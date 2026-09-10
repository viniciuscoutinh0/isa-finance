# Escopo do v1: livro-caixa, nada além

O v1 entrega apenas: cadastro/login isolado por usuário, Contas com saldo
inicial, e Lançamentos categorizados de entrada e saída. O saldo de cada Conta e
um saldo total são exibidos, mas não há gráficos nem relatórios.

Ficou deliberadamente **fora** do v1, cada item candidato a ADR próprio quando for
retomado:

- **Orçamento mensal** por categoria (tetos de gasto)
- **Relatórios e gráficos** (gasto por categoria, evolução mensal)
- **Recorrências** (lançamentos que se repetem)
- **Importação** de extrato (OFX / CSV)
- **Metas** de economia
- **Multi-moeda** — só BRL (ver `docs/adr/0002-dinheiro-em-centavos.md`)
- **Compartilhamento / household** — cada usuário é uma ilha
- **Ciclo de fatura de cartão de crédito** (fechamento, vencimento) — cartão é
  tratado como uma Conta comum, saldo negativo = dívida
- **Subcategorias** — Categoria é uma lista plana
- **Verificação de e-mail** e tela de perfil/troca de senha logado

Motivo: chegar a algo utilizável em dias, não meses, e deixar registrado que as
ausências são escolha e não esquecimento.

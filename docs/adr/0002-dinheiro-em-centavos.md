# Dinheiro em centavos, DTO Money, só BRL

Todo valor monetário é guardado como inteiro de centavos (`bigint`) e manipulado
por um DTO `Money` (`app/Data/`). Formatação e parsing pt-BR (R$ 1.234,56)
acontecem só na borda (Livewire Forms / views). Não há coluna de moeda: o sistema
é BRL-only no v1.

Alternativa rejeitada: `decimal(15,2)`. Descartada porque aritmética de saldo e
futuras somas/divisões (rateio, orçamento) acumulam erro de ponto flutuante com
mais facilidade, e o cast decimal do Laravel devolve string, empurrando conversão
para todo lugar de qualquer forma.

Consequência: introduzir multi-moeda depois exige uma migration adicionando
`currency` e uma decisão sobre conversão — custo aceitável e localizado.

Nota: `.ai/rules/database.md` originalmente dizia "Money is `decimal`". Essa linha
foi um default não-intencional e foi trocada para `bigint` de centavos quando
esta ADR foi aceita.

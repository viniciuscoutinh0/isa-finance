# Autenticação feita à mão, sem starter kit

Cadastro, login, logout e redefinição de senha são componentes Livewire 4 + Flux
escritos no projeto, com cada escrita numa Action em `app/Actions/Auth/`. Não
usamos `laravel/livewire-starter-kit` nem Fortify.

Alternativa rejeitada: o starter kit oficial. Descartada porque ele traz uma
estrutura própria (lógica nos componentes, sem camada de Actions/Queries, testes
e nomes fora do padrão) que conflitaria com as `.ai/rules` do projeto; adaptá-lo
custaria mais do que escrever o mínimo à mão.

Consequência: recursos que o kit daria de graça (verificação de e-mail, troca de
senha logado, OAuth) ficam por nossa conta quando forem necessários. No v1 só
existe o caminho essencial: criar conta, entrar, sair, recuperar senha.

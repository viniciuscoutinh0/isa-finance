# Boilerplate Laravel 13 + Livewire 4

Base para novos projetos internos. Já vem com o ambiente Docker (PHP 8.4 + FPM, Nginx, drivers SQL Server), a stack Livewire 4 + Flux Pro + Tailwind 4, a suíte Pest 5 e — o ponto principal — o conjunto de regras versionadas em `.ai/rules` que orienta agentes de IA a escreverem código no padrão da casa.

Clonar, rodar `init.sh` com o nome do projeto e começar.

## Requisitos da máquina host

- Docker e Docker Compose (única dependência obrigatória).
- Git.

Nada de PHP, Composer ou Node no host: tudo roda dentro do container.

## Inicialização

### 1. Renomear os serviços com `init.sh`

O boilerplate não tem nome fixo. `init.sh` define o prefixo dos containers e propaga esse nome por todos os arquivos que dependem dele.

```bash
./init.sh mini-operion
# ou, interativo:
./init.sh
```

O nome é normalizado (minúsculas, espaços e `-` viram `_`, demais caracteres removidos), gerando dois serviços: `<nome>_app` e `<nome>_web`.

O script reescreve:

| Arquivo | O que muda |
| --- | --- |
| `docker-compose.yaml` | Serviços e `container_name` (`<nome>_app`, `<nome>_web`) |
| `.docker/nginx/default.conf` | `fastcgi_pass <nome>_app:9000` |
| `.mcp.json` | Container usado pelo servidor MCP do Laravel Boost (cria o arquivo se não existir) |
| `.ai/rules/**` | Substitui o nome antigo do container em todas as regras |

É idempotente: pode ser rodado de novo para renomear o projeto. Ele lê o nome anterior do próprio `docker-compose.yaml` para saber o que substituir nas regras. Se avisar que `.mcp.json` ou `.ai/rules` citam um container diferente, ajuste manualmente — o script não adivinha.

### 2. Credenciais do Flux Pro

`livewire/flux-pro` vem do repositório privado `composer.fluxui.dev`. O `auth.json` na raiz é ignorado pelo Git; sem ele o `composer install` falha na autenticação. Copie o `auth.json` de outro projeto ou gere um com as credenciais da licença.

### 3. Subir os containers

```bash
docker compose up -d --build
```

O primeiro build é longo: compila as extensões `sqlsrv` e `pdo_sqlsrv` a partir dos tarballs em `.docker/php/drivers/` e instala o driver ODBC da Microsoft.

Serviços:

- `<nome>_web` — Nginx na porta `80` (`http://localhost`).
- `<nome>_app` — PHP-FPM 8.4 + Composer + Node, porta `5173` publicada para o Vite.

### 4. Instalar dependências e preparar a aplicação

```bash
docker compose exec -T <nome>_app composer setup
```

O script `setup` do `composer.json` faz: `composer install`, cria o `.env` a partir do `.env.example`, `key:generate`, `migrate --force`, `npm install --ignore-scripts` e `npm run build`.

Ajuste o `.env` depois: `APP_NAME`, `APP_URL` e a conexão de banco. `DB_CONNECTION=sqlite` é só o default do boilerplate — em dev/produção use `sqlsrv`.

### 5. Servidor de desenvolvimento

```bash
docker compose exec <nome>_app composer run dev
```

Equivale a `php artisan dev` (Vite + fila + logs). Se uma alteração de frontend não aparecer, rode `npm run build` ou mantenha o `dev` rodando.

## Comandos do dia a dia

Todo comando de projeto roda dentro do container — nunca no host. Exceções: `docker`/`docker compose` e `git`.

```bash
docker compose exec -T <nome>_app php artisan route:list
docker compose exec -T <nome>_app php artisan test --compact
docker compose exec -T <nome>_app php artisan migrate
docker compose exec -T <nome>_app composer show --direct
docker compose exec -T <nome>_app vendor/bin/pint --format agent app routes tests database
docker compose exec -T <nome>_app npm run build
```

Notas:

- `.npmrc` define `ignore-scripts=true`; instale com `npm install --ignore-scripts`.
- Depois de mexer em `config/`, rode `php artisan config:clear`.
- Em `route:list` não use `--except-vendor`: páginas registradas com `Route::livewire()` resolvem para o controller do Livewire e desaparecem da listagem.

## Estrutura

Os diretórios abaixo já existem (mantidos por `.gitkeep`). Classes novas vão no diretório correspondente; não crie pastas base novas.

| Caminho | Conteúdo |
| --- | --- |
| `app/Actions/<Domínio>/` | Casos de uso — único lugar com regra de negócio e escrita |
| `app/Queries/<Domínio>/` | Leituras não triviais, somente leitura |
| `app/Services/<Vendor>/` | Integrações HTTP / externas |
| `app/Data/<Domínio>/` | DTOs `final readonly` |
| `app/Enums/` | Enums com backing (status, role, type) |
| `app/Exceptions/<Domínio>/` | Base de exceção do domínio + casos |
| `app/Policies/` | Uma policy por model |
| `app/Rules/` | Regras de validação reutilizáveis |
| `app/Http/Requests/` | Form Requests (fronteira do controller) |
| `app/Livewire/` + `resources/views/livewire/` | Componentes class-based e suas views |
| `app/Livewire/Forms/` | Form objects do Livewire |
| `app/Jobs/` | Wrappers enfileirados de Actions |
| `tests/Feature/{Actions,Queries,Services,Livewire,Policies,Jobs,Console}/` | Espelha os caminhos acima |
| `tests/Unit/{Enums,Data}/` | Só para enums/DTOs com lógica real |
| `.docker/` | Dockerfile, `php.ini`, `php-fpm.conf`, drivers sqlsrv, conf do Nginx |
| `.ai/rules/` | Regras de projeto lidas por agentes de IA |

## Pacotes instalados

### PHP (runtime)

| Pacote | Uso |
| --- | --- |
| `laravel/framework` ^13.17 | Framework, PHP ^8.3 (container roda 8.4) |
| `livewire/livewire` ^4.4 | Camada de UI; componentes class-based |
| `livewire/flux` + `livewire/flux-pro` ^2.19 | Biblioteca de componentes (licença Pro, repositório privado) |
| `nunomaduro/essentials` ^1.2 | Defaults estritos do framework (ver abaixo) |
| `laravel/tinker` ^3.0 | REPL no contexto da aplicação |

### PHP (dev)

| Pacote | Uso |
| --- | --- |
| `pestphp/pest` ^5.1 + plugins `laravel`, `livewire` | Suíte de testes |
| `laravel/pint` ^1.27 | Formatador; obrigatório antes de finalizar mudanças em PHP |
| `laravel/boost` ^2.8 | Servidor MCP + skills para agentes |
| `laravel/pail` ^1.2 | Tail de logs (`php artisan pail`) |
| `laravel/pao` ^1.0 | Ferramental de dev do Laravel |
| `nunomaduro/collision` ^8.6 | Erros legíveis no CLI |
| `nunomaduro/mock-final-classes` ^1.2 | Remove `final`/`readonly` durante os testes, então `final` nunca bloqueia mock |
| `lucascudo/laravel-pt-br-localization` ^3.0 | Traduções pt-BR de framework e validação |
| `fakerphp/faker`, `mockery/mockery` | Dados falsos e mocks |

### JavaScript

| Pacote | Uso |
| --- | --- |
| `vite` ^8 + `laravel-vite-plugin` ^3.1 | Bundler |
| `tailwindcss` ^4 + `@tailwindcss/vite` | CSS-first, configurado em `resources/css/app.css` — sem `tailwind.config.js` |
| `fontaine` | Fallback de fontes / redução de CLS |
| `@laravel/multiplex` (opcional) | Multiplexação de requests do Livewire |
| `concurrently` | Usado pelo `artisan dev` |

### Defaults do `nunomaduro/essentials`

Publicado em `config/essentials.php`, valendo para toda a aplicação:

| Configurable | Efeito |
| --- | --- |
| `ShouldBeStrict` | Lazy loading, atributo ausente e atributo descartado lançam exceção |
| `Unguard` | Mass assignment desligado — passe apenas dados validados para `create()`/`update()`/`fill()` |
| `AutomaticallyEagerLoadRelationships` | Acesso a relação em coleção faz eager load automático |
| `ImmutableDates` | Datas são `CarbonImmutable`; nunca mutam no lugar |
| `PreventStrayRequests` | HTTP não fakeado em teste falha |
| `FakeSleep` | `Sleep` fakeado nos testes |
| `AggressivePrefetching` | Prefetch de links |
| `ProhibitDestructiveCommands` | `migrate:fresh` e afins bloqueados em produção |
| `ForceScheme` | HTTPS forçado apenas em `production` |
| `SetDefaultPassword` | `Password::defaults()`: mín. 12, maiúsculas e minúsculas, números, símbolos, não comprometida (produção) |

## Banco de dados

Dois drivers, sempre: `sqlsrv` (SQL Server) em dev/produção e `sqlite` `:memory:` na suíte de testes (`phpunit.xml`). Toda migration e toda query precisam funcionar nos dois. Consequências práticas em `.ai/rules/database.md`: sem `enum()`, sem `->after()`, sem consultas por JSON path, `boolean()` é `bit`, `text()` não indexa, dinheiro em `decimal`.

## Testes

```bash
docker compose exec -T <nome>_app php artisan test --compact
docker compose exec -T <nome>_app vendor/bin/pest --filter=nomeDoTeste
```

`tests/Pest.php` aplica `RefreshDatabase` em toda a suíte `Feature`. Rode o subconjunto mais estreito que cobre a mudança durante o desenvolvimento e a suíte completa antes de abrir PR. Não apague testes sem aprovação.

## Regras para IA

O boilerplate assume desenvolvimento assistido por agentes. A configuração vive em quatro lugares.

### `.ai/rules/` — regras versionadas do projeto

Fonte da verdade compartilhada, commitada no repositório. `.ai/rules/index.md` mapeia globs para arquivos de regra; o agente deve ler todo arquivo cujo glob cobre o caminho em que vai mexer, antes de escrever código.

| Aplica-se a | Arquivo |
| --- | --- |
| `app/**`, `resources/**`, `database/**`, `routes/**`, `tests/**`, `config/**` | `language.md` |
| `app/**`, `database/**`, `routes/**`, `tests/**` | `php.md` |
| `app/**` | `app.md` |
| `app/Console/**` | `console.md` |
| `app/Jobs/**` | `jobs.md` |
| `app/Livewire/**` | `livewire.md` |
| `app/Models/**` | `models.md` |
| `config/**` | `config.md` |
| `database/**` | `database.md` |
| `resources/views/**` | `views.md` |
| `routes/**` | `routes.md` |
| `tests/**` | `tests.md` |
| `**` (todo comando) | `docker.md` |

Três regras transversais, resumidas:

- **PHP** — `declare(strict_types=1)` em todo arquivo, `final` por default (`final readonly` em Actions/Queries/Services/DTOs), tipos explícitos em parâmetros e retornos.
- **Idioma** — identificadores, schema, nomes de rota, mensagens de log/exceção, comentários e nomes de teste em **inglês**; tudo que o usuário lê (labels, botões, toasts, mensagens de validação, slugs de URL) em **pt-BR**. Traduções em `lang/pt_BR/`.
- **Docker** — todo comando de projeto prefixado com `docker compose exec -T <nome>_app`.

Para gravar uma regra nova, use a ferramenta MCP `record-rule` do Boost (glob + título + nota) em vez da memória pessoal do agente: só `.ai/rules` é compartilhado com o time e persiste no repositório.

### `CLAUDE.md` / `AGENTS.md` — instruções do agente

Gerados pelo `boost:install` e **ignorados pelo Git** (como `.mcp.json`, `boost.json` e `auth.json`). Trazem as diretrizes do Laravel Boost, o resumo das regras do projeto e o ponteiro para `.ai/rules/index.md`. Ao clonar o boilerplate, regenere ou copie esses arquivos:

```bash
docker compose exec -T <nome>_app composer require laravel/boost --dev
docker compose exec -T <nome>_app php artisan boost:install
```

### `.mcp.json` — servidor MCP do Boost

Expõe ao agente ferramentas do contexto real da aplicação: `database-schema`, `database-query`, `search-docs`, `read-log-entries`, `last-error`, `browser-logs`, `get-absolute-url`, `record-rule`. O comando aponta para o container do projeto, escrito pelo `init.sh`:

```json
["compose", "exec", "-T", "<nome>_app", "php", "artisan", "boost:mcp"]
```

Prefira essas ferramentas a comandos de shell equivalentes; sempre use `search-docs` antes de mexer em API do ecossistema Laravel que dependa de versão.

### `.claude/skills/` — skills de domínio

Instaladas via `boost.json` e ativadas quando o trabalho entra no domínio correspondente:

| Skill | Quando |
| --- | --- |
| `laravel-best-practices` | Qualquer código PHP/Laravel |
| `testing-best-practices` | Escrever ou revisar testes |
| `livewire-development` | Componentes, `wire:`, reatividade |
| `fluxui-development` | Componentes `<flux:*>` |
| `tailwindcss-development` | Classes utilitárias, layout, dark mode |
| `infer-conventions` | Detectar convenções e gravá-las em `.ai/rules` |

## Checklist para um projeto novo

1. Clonar o boilerplate, remover o histórico Git e iniciar um novo (`git init`).
2. `./init.sh <nome-do-projeto>`.
3. Colocar o `auth.json` (Flux Pro) na raiz.
4. `docker compose up -d --build`.
5. `composer setup` dentro do container; ajustar `.env` (`APP_NAME`, `APP_URL`, conexão `sqlsrv`).
6. Regenerar `CLAUDE.md` / `AGENTS.md` com `boost:install`.
7. Revisar `.ai/rules/` — remover o que não se aplica, adicionar as decisões do novo domínio com `record-rule`.
8. Substituir a página `Welcome` (`app/Livewire/Welcome.php`, rota `home` em `routes/web.php`) e este README.

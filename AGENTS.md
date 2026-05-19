# AGENTS.md

## Leitura Obrigatória
Toda tarefa neste repositório **deve começar pela leitura deste arquivo**.

## Escopo do Projeto
- Aplicação alvo: `api/` (Laravel API).
- Esta base está organizada por domínios em `Api/V1`.
- O diretório `api/vendor/` não deve ser usado para análise arquitetural.

## Resumo Executivo da Arquitetura (confirmado em código)
- Framework: Laravel 12 (composer.lock: `laravel/framework v12.56.0`).
- API versionada por arquivos em `api/routes/api/v1.php`, com carregamento dinâmico de rotas públicas e privadas por domínio.
- Domínios principais no código:
  - `Admin`
  - `Common`
- Camadas por domínio (principalmente em `App\Http` e `App\Services`):
  - `Controllers/Api/V1/Common`
  - `Controllers/Api/V1/Admin`
  - `Requests/Api/V1/Common`
  - `Requests/Api/V1/Admin`
  - `Resources/Api/V1/Common`
  - `Resources/Api/V1/Admin`
  - `Services/Api/V1/Common`
  - `Services/Api/V1/Admin`
- Núcleo compartilhado em `app/Models`, `app/Traits`,  `app/Http/Middleware`, `database/*`.
- Resposta JSON padronizada via `TraitReturnJsonOlirum` + `ApiExceptionHandler` na configuração do app.
- Autenticação por Sanctum (`auth:sanctum`) e autorização por middleware custom `check.permission`.

## Organização por Domínios

### `admin`
Responsabilidades observadas:
- Gestão de usuários (`Users/UserController`).

Padrão predominante:
- Controller delega regra de negócio para Service.
- Request dedicado para validação.
- Resource para formato de saída.


### `common`
Responsabilidades observadas:
- Autenticação compartilhada (`login`, `logout`, `me`) para fluxo não-admin.
- Perfil do usuário logado (`show`, `update`).

Papel do domínio:
- `common` atua como base compartilhada de recursos transversais como (auth, perfil), não como “lixeira” de regra de negócio.

## Relação Entre Domínios
- `admin` compartilham entidades referentes ao gerenciamento da plataforma.
- `common` compartilha autenticação/perfil e endpoints públicos usados por consumidores gerais da API.
- Modelos e regras-base ficam fora dos domínios (ex.: `app/Models`, `app/Services`, `app/Traits`).

## Stack Técnica Encontrada
- PHP `^8.2`.
- Laravel `v12.56.0`.
- Sanctum `v4.3.1`.
- Firebase (Kreait) `7.1.0`.
- PHPUnit `11.5.55`.
- Pint `v1.29.0`.
- Front-end no `api/` com Vite + Tailwind v4 (estrutura padrão Laravel, sem app SPA do projeto mapeada em `resources/js`).

## Comandos Reais do Projeto

### Dependências
- `cd api && composer install`
- `cd api && npm install`

### Setup rápido (script Composer)
- `cd api && composer run setup`

### Ambiente local (sem Docker)
- `cd api && php artisan serve`
- `cd api && composer run dev`

### Ambiente local (Docker do repositório)
- `docker compose up -d`
- `docker compose exec app composer install --working-dir=/var/www/api`
- `docker compose exec app php artisan key:generate`
- `docker compose exec app php artisan migrate --seed`

### Testes
- `cd api && composer run test`
- `cd api && php artisan test`

### Lint/format
- `cd api && ./vendor/bin/pint`

### Front-end build/dev (quando aplicável)
- `cd api && npm run dev`
- `cd api && npm run build`

## Convenções de Código Observadas
- Namespace por versão/domínio: `App\Http\...\Api\V1\{Admin|Common}`.
- Controllers geralmente enxutos e orientados a Service.
- FormRequests com validações e mensagens em PT-BR.
- Resources para shape de resposta por contexto.
- Uso frequente de eager loading (`with(...)`) para reduzir N+1.
- Resposta padrão usando a TraitReturnJsonOlirum: com o método `ReturnJson($data = null, $message = '', $status = true, $code = 200)`.
- Permissão por módulo+ação derivada do método da rota no middleware `check.permission`.

## Regras Obrigatórias para Alterações
- Respeitar a separação de responsabilidades entre `admin` e `common`.
- Não mover lógica entre domínios sem justificativa técnica explícita.
- Código compartilhado só deve ir para `common` quando houver uso real transversal.
- Antes de codar, identificar domínio dono da regra e seguir padrão já existente naquele domínio.
- Não impor arquitetura nova se o padrão atual do domínio já resolve o caso.
- Toda mudança deve avaliar impacto em:
  - rotas
  - controllers
  - requests
  - services
  - models
  - policies (quando existirem)
  - jobs
  - events/listeners (quando existirem)
  - resources
  - testes
- Evitar:
  - N+1
  - inconsistência de payload
  - duplicação desnecessária
  - violações de autorização

## Orientações para Mudanças Seguras
- Alterar primeiro o domínio afetado; extrair para compartilhado apenas após evidência de duplicação entre domínios.
- Garantir que middleware de autenticação/permissão continue coerente com a rota.
- Manter contrato de resposta compatível com o Resource/consumidor atual.
- Validar paginação, filtros e eager loading em endpoints de listagem.
- Em mudanças maiores, mapear risco de regressão por rota afetada.

## Checklist de Validação Antes de Finalizar
- O domínio correto foi escolhido?
- A separação `admin/common` foi preservada?
- Request, Service e Resource foram ajustados de forma consistente?
- Middleware e autorização da rota continuam corretos?
- Não foi introduzido N+1?
- Contrato JSON do endpoint ficou consistente com o padrão existente?
- Testes relevantes foram executados ou a lacuna foi explicitada?
- Impactos colaterais em reservas, hospedagens, mídias, usuários e plataforma foram avaliados?

## Definição de Pronto (Definition of Done)
Uma alteração só é considerada pronta quando:
- Respeita o domínio dono da responsabilidade.
- Mantém coerência arquitetural com padrões reais do projeto.
- Mantém autenticação/autorização corretas.
- Mantém consistência de resposta e validação.
- Não aumenta risco de N+1 ou regressão funcional evidente.
- Inclui validação mínima executada (teste/comando) ou justificativa explícita da limitação.


# VPS Git Deploy Automático

Estrutura pública pronta para provisionamento de VPS + deploy automático CI/CD utilizando:

* Docker
* Docker Compose
* Traefik
* Laravel API
* React Frontend
* GitHub Actions
* SSL automático Let's Encrypt
* Git Flow (`develop` = homolog / `main` = production)

---

# Objetivo

Este projeto tem como objetivo disponibilizar uma estrutura desacoplada e escalável para aplicações modernas, separando:

* Backend Laravel (API)
* Frontend React
* Infraestrutura Docker
* Proxy reverso Traefik
* Ambientes independentes:

  * homolog
  * production
  * local

Tudo com deploy automatizado diretamente da branch do GitHub para VPS.

---

# Arquitetura

## Frontend

| Ambiente   | URL                                              |
| ---------- | ------------------------------------------------ |
| Homolog    | `git-public-front-homolog.olirumcloud.com.br`    |
| Production | `git-public-front-production.olirumcloud.com.br` |

---

## Backend API

| Ambiente   | URL                                            |
| ---------- | ---------------------------------------------- |
| Homolog    | `git-public-api-homolog.olirumcloud.com.br`    |
| Production | `git-public-api-production.olirumcloud.com.br` |

---

# Fluxo de Deploy

## Homolog

```text
push develop
    ↓
GitHub Actions
    ↓
Deploy automático VPS
    ↓
docker-compose.homolog.yml
```

---

## Production

```text
push main
    ↓
GitHub Actions
    ↓
Deploy automático VPS
    ↓
docker-compose.production.yml
```

---

# Estrutura do Projeto

```text
.
├── .codex/
├── .github/
│   └── workflows/
│       ├── deploy-homolog.yml
│       └── deploy-prod.yml
│
├── api/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   ├── vendor/
│   │
│   ├── .env
│   ├── .env.homolog
│   ├── .env.production
│   ├── artisan
│   ├── composer.json
│   └── vite.config.js
│
├── docker/
│   ├── mysql/
│   ├── mysql-backup/
│   ├── nginx/
│   │   └── conf.d/
│   │       ├── homolog.conf
│   │       ├── local.conf
│   │       └── production.conf
│   │
│   └── php/
│
├── docs/
├── docs-privado/
├── mcp-pescala/
│
├── .env.example
├── .env.homolog
├── .env.local
├── .env.production
│
├── docker-compose.local.yml
├── docker-compose.homolog.yml
├── docker-compose.production.yml
│
├── Dockerfile
├── README.md
└── AGENTS.md
```

---

# Conceito dos ENVs

O projeto possui dois níveis de `.env`.

---

# 1. ENV ROOT (Infraestrutura)

Arquivos localizados na raiz do projeto.

Responsáveis por:

* Docker
* Containers
* Portas
* Domínios
* Traefik
* SSL
* Configurações de infraestrutura

Arquivos:

```text
.env.local
.env.homolog
.env.production
```

---

## Exemplo

```env
PROJECT_NAME=git-public-api

APP_PORT=8000

MYSQL_PORT=3306

DOMAIN_API=git-public-api-homolog.olirumcloud.com.br

TRAEFIK_NETWORK=traefik-public
```

---

# 2. ENV API (Laravel)

Arquivos localizados dentro:

```text
/api/
```

Responsáveis por:

* Configurações Laravel
* Banco de dados
* Cache
* Queue
* Mail
* JWT
* Redis
* APP_KEY
* Serviços externos

Arquivos:

```text
api/.env
api/.env.homolog
api/.env.production
```

---

## Exemplo

```env
APP_NAME=Laravel
APP_ENV=production
APP_KEY=
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=app
DB_USERNAME=root
DB_PASSWORD=123456
```

---

# Docker Compose por Ambiente

Cada ambiente possui seu próprio compose:

| Arquivo                       | Ambiente   |
| ----------------------------- | ---------- |
| docker-compose.local.yml      | Local      |
| docker-compose.homolog.yml    | Homolog    |
| docker-compose.production.yml | Production |

---

# NGINX por Ambiente

Cada ambiente possui configuração própria:

| Arquivo         | Ambiente   |
| --------------- | ---------- |
| local.conf      | Local      |
| homolog.conf    | Homolog    |
| production.conf | Production |

---

# Traefik

O Traefik é responsável por:

* Proxy reverso
* HTTPS automático
* SSL Let's Encrypt
* Roteamento de containers
* Exposição pública

---

# SSL Automático

Os certificados são gerados automaticamente pelo Let's Encrypt via Traefik.

Não é necessário configurar SSL manualmente.

---

# Git Flow

| Branch  | Ambiente   |
| ------- | ---------- |
| develop | Homolog    |
| main    | Production |

---

# GitHub Actions

Deploy automatizado utilizando:

```text
.github/workflows/
```

Arquivos:

```text
deploy-homolog.yml
deploy-prod.yml
```

---

# Secrets GitHub Necessários

## Homolog

```text
SSH_PRIVATE_KEY
ENV_ROOT
ENV_API
```

---

## Production

```text
SSH_PRIVATE_KEY
ENV_ROOT
ENV_API
```

---

# Fluxo de ENV no Deploy

Durante o deploy:

## 1. GitHub Actions

Envia:

* código
* envs
* docker compose

para VPS.

---

## 2. VPS

Executa:

```bash
docker compose up -d --build
```

---

## 3. Containers

Sobem automaticamente:

* nginx
* php-fpm
* mysql
* redis
* queue
* scheduler
* traefik

---

# Inicialização Local

## Subir ambiente

```bash
docker compose -f docker-compose.local.yml up -d --build
```

---

## Instalar dependências Laravel

```bash
docker exec -it app composer install
```

---

## Gerar APP_KEY

```bash
docker exec -it app php artisan key:generate
```

---

## Rodar migrations

```bash
docker exec -it app php artisan migrate
```

---

# Deploy Manual VPS

## Homolog

```bash
docker compose -f docker-compose.homolog.yml up -d --build
```

---

## Production

```bash
docker compose -f docker-compose.production.yml up -d --build
```

---

# Segurança

Recomendações:

* Nunca commitar `.env`
* Utilizar GitHub Secrets
* Utilizar SSH Key sem senha para Actions
* Restringir firewall
* Utilizar fail2ban
* Desabilitar login root por senha
* Utilizar somente autenticação SSH

---

# Tecnologias

* Laravel
* PHP
* React
* Docker
* Docker Compose
* NGINX
* Traefik
* GitHub Actions
* MySQL
* Redis
* Linux VPS

---

# Objetivo Final

Ter uma infraestrutura:

* reutilizável
* escalável
* padronizada
* desacoplada
* pronta para CI/CD
* pronta para múltiplos projetos
* pronta para homolog e produção
* pronta para SSL automático

---

# Licença

MIT License

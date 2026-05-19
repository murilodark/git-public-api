# Git Public API

API Laravel pronta para deploy automatizado utilizando Docker, múltiplos ambientes e integração com Traefik.

---

# Objetivo

Este repositório contém exclusivamente a estrutura backend da arquitetura.

Responsabilidades:

- API Laravel;
- containers Docker;
- PHP;
- Nginx;
- MySQL;
- workers;
- ambientes separados;
- workflows de deploy;
- homologação e produção.

A documentação geral da infraestrutura está centralizada no repositório âncora:

```txt
laravel-vps-multidomain-deploy
````

---

# Stack Backend

* Laravel
* PHP
* Nginx
* MySQL
* Docker
* Docker Compose
* GitHub Actions

---

# Estrutura do Projeto

```txt
.
├── api/
├── docker/
├── .github/
├── docker-compose.local.yml
├── docker-compose.homolog.yml
├── docker-compose.production.yml
└── README.md
```

---

# Estrutura Backend

## API Laravel

```txt
api/
```

Contém:

* controllers;
* services;
* models;
* migrations;
* providers;
* rotas;
* middlewares;
* filas;
* autenticação;
* regras de negócio.

---

## Docker

```txt
docker/
```

Responsável por:

* PHP;
* Nginx;
* MySQL;
* backup;
* configurações da infraestrutura backend.

---

## GitHub Actions

```txt
.github/workflows
```

Responsável pelos deploys automáticos:

```txt
deploy-homolog.yml
deploy-prod.yml
```

---

# Ambientes

## Local

```txt
docker-compose.local.yml
```

---

## Homologação

```txt
docker-compose.homolog.yml
```

Deploy automático pela branch:

```txt
develop
```

---

## Produção

```txt
docker-compose.production.yml
```

Deploy automático pela branch:

```txt
main
```

---

# Fluxo de Deploy

```txt
develop -> homologação
main    -> produção
```

---

# Variáveis de Ambiente

## Infraestrutura Docker

Arquivo:

```txt
.env
```

Responsável por:

* containers;
* portas;
* domínios;
* docker compose;
* variáveis da infraestrutura.

---

## Laravel

Arquivo:

```txt
api/.env
```

Responsável por:

* banco de dados;
* cache;
* filas;
* autenticação;
* mail;
* drivers Laravel.

---

# Arquivos de Ambiente

## Root

```txt
.env.local
.env.homolog
.env.production
```

---

## Laravel

```txt
api/.env
api/.env.homolog
api/.env.production
```

---

# Subindo Ambiente Local

## Copiar variáveis

```bash
cp .env.local .env
cp api/.env.local api/.env
```

---

## Subir containers

```bash
docker compose -f docker-compose.local.yml up -d --build
```

---

## Instalar dependências Laravel

```bash
docker exec -it laravel_app composer install
```

---

## Executar migrations

```bash
docker exec -it laravel_app php artisan migrate
```

---

# Estrutura Docker

## PHP

```txt
docker/php
```

---

## Nginx

```txt
docker/nginx
```

---

## MySQL

```txt
docker/mysql
```

---

## Backup

```txt
docker/mysql-backup
```

---

# Nginx

Os ambientes possuem configurações independentes:

```txt
docker/nginx/conf.d/local.conf
docker/nginx/conf.d/homolog.conf
docker/nginx/conf.d/production.conf
```

---

# Deploy Automático

Os deploys utilizam:

* GitHub Actions;
* SSH;
* RSync;
* Docker Compose.

O pipeline executa:

* sincronização dos arquivos;
* rebuild dos containers;
* restart automático;
* atualização da aplicação.

---

# Estrutura de Deploy

## Homologação

```txt
git-public-api-homolog.olirumcloud.com.br
```

---

## Produção

```txt
git-public-api-production.olirumcloud.com.br
```

---

# Traefik

A instalação do Traefik NÃO está neste repositório.

Toda a documentação da infraestrutura está centralizada em:

```txt
laravel-vps-multidomain-deploy
```

Diretório:

```txt
infra/traefik
```

---

# Recursos Implementados

* múltiplos ambientes;
* deploy automatizado;
* containers isolados;
* Docker;
* SSL automático;
* CI/CD;
* arquitetura desacoplada;
* integração com frontend React.

---

# Roadmap

* filas distribuídas;
* Redis;
* monitoramento;
* observabilidade;
* cache distribuído;
* testes automatizados;
* health checks;
* backup automatizado.

---

# Integração Frontend

Frontend oficial:

```txt
git-public-front
```

---

# Repositório Âncora

Documentação completa da infraestrutura:

```txt
laravel-vps-multidomain-deploy
```

---

# Topics GitHub

```txt
laravel
php
docker
docker-compose
api
backend
nginx
mysql
github-actions
deployment
ci-cd
vps
```

---

# Autor

Murilo Dark
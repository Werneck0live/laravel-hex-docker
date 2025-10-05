# Laravel Portfolio (Docker + OAuth2 + Hexagonal-ish)

Projeto demo para portfólio **backend** em Laravel, com:
- Docker (Nginx + PHP-FPM + MySQL + Adminer)
- Laravel 12, Passport (OAuth2) com Password Grant e Client Credentials (M2M)
- Estrutura hexagonal-ish: `Domain/`, `Application/`, `Infrastructure/` sob `src/app`

> **Estrutura de pastas**
>
> - `src/` → código Laravel  
> - `docker/` → infra (compose, Nginx, etc)  
> - suba com: `docker compose -f docker/docker-compose.yml up -d`

---

## Sumário

1. [Requisitos](#1-requisitos)
2. [Subir os containers](#2-subir-os-containers)
3. [Instalar dependências do PHP](#3-instalar-dependências-do-php)
4. [Criar o .env](#4-criar-o-env)
5. [Migrations & Passport keys](#5-migrations--passport-keys)
6. [Criar os OAuth clients (Password & M2M) e configurar o .env](#6-criar-os-oauth-clients-password--m2m-e-configurar-o-env)
7. [Usuário de teste](#7-usuário-de-teste)
8. [Testes rápidos por cURL](#8-testes-rápidos-por-curl)
9. [Adminer (UI para o MySQL)](#9-adminer-ui-para-o-mysql)
10. [Coleção Postman (com Setup automático)](#10-coleção-postman-com-setup-automático)
11. [Rotas úteis](#11-rotas-úteis)
12. [Rodando testes](#12-rodando-testes)
13. [Dicas & Troubleshooting](#13-dicas--troubleshooting)
14. [Arquitetura (visão rápida)](#14-arquitetura-visão-rápida)
15. [Comandos úteis](#15-comandos-úteis)

---

## 1) Requisitos

- Docker + Docker Compose
- Porta HTTP do Nginx mapeada (ex.: `http://localhost:8001`)
- (Opcional) Postman para testar via coleção

---

## 2) Subir os containers

```bash
docker compose -f docker/docker-compose.yml up -d
docker compose -f docker/docker-compose.yml ps
```

---

## 3) Instalar dependências do PHP

O serviço app não tem composer — use o serviço composer.

```bash
docker compose -f docker/docker-compose.yml run --rm -w /var/www/html composer install --no-interaction --prefer-dist --optimize-autoloader
```

---

## 4) Criar o .env

Copie do exemplo e ajuste:

```bash
cp src/.env.example src/.env
```

<b><u>IMPORTANTE: </u></b> Edite `src/.env` (valores padrão abaixo — ajuste se seu compose for diferente):

```env
APP_NAME="Laravel Portfolio"
APP_ENV=local
APP_KEY=                 # será gerada no passo seguinte
APP_DEBUG=true
APP_URL=http://localhost:8001

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=appdb
DB_USERNAME=app
DB_PASSWORD=app

CACHE_STORE=file
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

OAUTH_BASE_URL=http://web

PASSPORT_PASSWORD_CLIENT_ID=
PASSPORT_PASSWORD_CLIENT_SECRET=

PASSPORT_M2M_CLIENT_ID=
PASSPORT_M2M_CLIENT_SECRET=

# (ATENÇÃO: contém espaço, então precisa estar entre aspas)
PASSPORT_PASSWORD_DEFAULT_SCOPE="skills:read skills:write"
```

Gerar APP_KEY e limpar caches:

```bash
docker compose -f docker/docker-compose.yml exec app php artisan key:generate
docker compose -f docker/docker-compose.yml exec app php artisan optimize:clear
```

---

## 5) Migrations & Passport keys

```bash
# Gerar APP_KEY
docker compose -f docker/docker-compose.yml exec app php artisan key:generate
# Migrates
docker compose -f docker/docker-compose.yml exec app php artisan migrate
# Passaport Keys
docker compose -f docker/docker-compose.yml exec app php artisan passport:keys --force
```

<b><u>IMPORTANTE: </u></b> Para garantir que nenhum erro de permissão em `storage/logs/laravel.log` ocorra, corrija:

```bash
docker compose -f docker/docker-compose.yml exec app sh -lc \
'chown -R www-data:www-data storage bootstrap/cache && chmod -R u+rwX,g+rwX storage bootstrap/cache'
```

---

## 6) Criar os OAuth clients (Password & M2M) e configurar o .env

### 6.1) Password Grant

```bash
docker compose -f docker/docker-compose.yml exec app \
  php artisan passport:client --password --name "Password Grant" --provider=users
```

Guarde Client ID e Client secret e atualize no `.env`:

```env
PASSPORT_PASSWORD_CLIENT_ID=SEU_ID
PASSPORT_PASSWORD_CLIENT_SECRET=SEU_SECRET
```

### 6.2) Client Credentials (M2M) — opcional

```bash
docker compose -f docker/docker-compose.yml exec app \
  php artisan passport:client --client --name "M2M"
```

Atualize no `.env`:

```env
PASSPORT_M2M_CLIENT_ID=SEU_ID
PASSPORT_M2M_CLIENT_SECRET=SEU_SECRET
```

Aplique as mudanças:

```bash
docker compose -f docker/docker-compose.yml exec app php artisan optimize:clear
```

---

## 7) Usuário de teste

Crie manualmente via Tinker (interativo):

```bash
docker compose -f docker/docker-compose.yml exec app php artisan tinker
```

No prompt do Tinker:

```php
$user = \App\Models\User::factory()->create([
  'name' => 'Dev Pleno',
  'email' => 'dev@example.com',
  'password' => bcrypt('secret123'),
]);
```

---

## 8) Testes rápidos por cURL

### 8.1) Password Grant direto no Passport

```bash
curl -s -X POST http://localhost:8001/oauth/token \
 -H 'Content-Type: application/x-www-form-urlencoded' \
 -d "grant_type=password&client_id=SEU_ID&client_secret=SEU_SECRET&username=dev@example.com&password=secret123&scope=*"
```

### 8.2) Client Credentials (M2M)

```bash
curl -s -X POST http://localhost:8001/oauth/token \
 -H 'Content-Type: application/x-www-form-urlencoded' \
 -d "grant_type=client_credentials&client_id=SEU_ID&client_secret=SEU_SECRET&scope=*"
```

---

## 9) Adminer (UI para o MySQL)

Descubra a porta mapeada do serviço adminer:

```bash
docker compose -f docker/docker-compose.yml ps
docker compose -f docker/docker-compose.yml port adminer 8080
```

Abra no navegador: `http://localhost:<PORTA>`

Login no Adminer:

- System: MySQL
- Server: mysql (nome do serviço docker)
- Username: app
- Password: app
- Database: appdb

---

## 10) Coleção Postman (com Setup automático)

A coleção (na raiz do projeto, com o nome "laravel-portifolio... .json") tem um request OAuth → Fetch OAuth Clients (DEV) que lê do .env (via rota de DEV) e preenche automaticamente:

- passwordClientId / passwordClientSecret
- m2mClientId / m2mClientSecret (se definidos)

Baixe e importe:

- Coleção: laravel-portfolio.oauth-setup.postman_collection.json
- Environment (opcional): laravel-portfolio.oauth-setup.local.postman_environment.json

Rota DEV (somente APP_ENV=local) — adicione em `src/routes/api.php`:

```php
use Illuminate\Support\Facades\Route;

Route::get('/dev/oauth-clients', function () {
    abort_unless(app()->environment('local'), 403, 'forbidden');

    return response()->json([
        'passwordClientId'     => env('PASSPORT_PASSWORD_CLIENT_ID'),
        'passwordClientSecret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
        'm2mClientId'          => env('PASSPORT_M2M_CLIENT_ID'),
        'm2mClientSecret'      => env('PASSPORT_M2M_CLIENT_SECRET'),
    ]);
});
```

Depois limpe caches:

```bash
docker compose -f docker/docker-compose.yml exec app php artisan route:clear
docker compose -f docker/docker-compose.yml exec app php artisan optimize:clear
```

Fluxo no Postman:

- Rode OAuth → Fetch OAuth Clients (DEV) (preenche variáveis da coleção)
- Auth → Login (salva accessToken e refreshToken)
- Auth → Me (usa accessToken)
- Auth → Login 2 - before refresh (igual ao Login) → Auth → Refresh
- OAuth → Token (Client Credentials) → OAuth → M2M ping

---

## 11) Rotas úteis

- GET `/api/healthz` — healthcheck JSON
- POST `/api/auth/login` — login (Password Grant via serviço)
- POST `/api/auth/refresh` — refresh token
- POST `/api/auth/logout` — revoga token atual
- GET `/api/skills` — lista (requer skills:read)
- POST `/api/skills` — cria (requer skills:write)
- GET `/api/m2m/ping` — teste com token M2M

---

## 12) Rodando testes

### Testes reutilizando a mesma APP_KEY do `.env`

Para evitar manter duas chaves, faça um symlink do `.env.testing` para `.env`:

```bash
docker compose -f docker/docker-compose.yml exec app sh -lc 'rm -f .env.testing && ln -s .env .env.testing'
docker compose -f docker/docker-compose.yml exec app php artisan config:clear
docker compose -f docker/docker-compose.yml exec app php artisan optimize:clear
```

Rode os testes:

```bash
docker compose -f docker/docker-compose.yml exec app php artisan test
```

---

## 13) Dicas & Troubleshooting

- 401 com HTML: já há handler para responder JSON consistente (inclusive 401/403/404/422/500).
- Token expirado: faça Login novamente ou use Refresh.
- Target class [CheckScopes]: garanta as middlewares do Passport registradas em bootstrap/app.php ou AppServiceProvider.
- Permissão em storage: veja o comando de chown/chmod no passo 5.
- OAUTH_BASE_URL: mantenha http://web em Docker (o serviço Nginx/laravel web), para chamadas internas ao /oauth/token.

---

## 14) Arquitetura (visão rápida)

```
src/app
├── Domain
│   ├── Contracts/       # portas (interfaces)
│   └── Entities/ValueObjects
├── Application
│   └── UseCases/        # casos de uso
└── Infrastructure
    ├── Auth/            # PassportAuthService (OAuth2)
    └── Persistence/
        └── Eloquent/Repositories/  # adapters p/ repos
```

---

## 15) Comandos úteis

```bash
# artisan (dentro do container)
docker compose -f docker/docker-compose.yml exec app php artisan <cmd>

# composer (usando serviço dedicado)
docker compose -f docker/docker-compose.yml run --rm composer <cmd>

# logs Laravel
docker compose -f docker/docker-compose.yml exec app tail -f storage/logs/laravel.log
```
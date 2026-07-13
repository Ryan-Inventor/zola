# Zola

Plateforme de gestion des points de dépôt/retrait **Orange Money** et **MTN MoMo** au Cameroun.

- **API** : Laravel (`zola-api/`) — http://localhost:8000/api/v1
- **Frontend** : Nuxt 3 PWA (`zola-web/`) — http://localhost:3000

Documentation : [INSTRUCTIONS/INSTRUCTION.md](INSTRUCTIONS/INSTRUCTION.md) · Suivi : [MEMORY.md](MEMORY.md) · Tickets : [docs/PROMPTS.md](docs/PROMPTS.md)

---

## Prérequis

| Outil | Version min. | Usage |
|---|---|---|
| PHP | 8.4+ | API Laravel |
| Composer | 2.x | Dépendances PHP |
| Node.js | 22+ | Frontend Nuxt |
| npm | 10+ | Dépendances JS |
| MySQL | 8.0+ | Base de données `zola` |

**Optionnel** : Docker Desktop (pour `docker compose up`)

Extensions PHP requises : `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`

---

## Démarrage rapide (une commande)

### Windows (PowerShell)

```powershell
.\scripts\dev.ps1
```

### Linux / macOS

```bash
chmod +x scripts/dev.sh scripts/seed.sh
./scripts/dev.sh
```

Ce script :
1. Crée la base `zola` si nécessaire
2. Exécute les migrations et le seed initial
3. Démarre l'API (port 8000) et le frontend (port 3000)
4. Vérifie que les deux services répondent

---

## Démarrage avec Docker

```bash
docker compose up --build
```

Services :
- **mysql** — port 3306, base `zola`, mot de passe root `secret`
- **api** — http://localhost:8000
- **web** — http://localhost:3000

---

## Installation manuelle (première fois)

### 1. API Laravel

```bash
cd zola-api
cp .env.example .env
composer install
php artisan key:generate
```

Configurer `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zola
DB_USERNAME=root
DB_PASSWORD=
FRONTEND_URL=http://localhost:3000
```

### 2. Base de données

```bash
# Depuis la racine du projet
php scripts/ensure-database.php
cd zola-api
php artisan migrate
php artisan db:seed
```

### 3. Frontend Nuxt

```bash
cd zola-web
cp .env.example .env
npm install
npm run dev
```

---

## Seed initial

Le seeder `InitialSeeder` crée un utilisateur de dev :

| Champ | Valeur |
|---|---|
| Email | `dev@zola.test` |
| Mot de passe | `password` |

> **Note** : AUTH-01 remplacera ce seed par le modèle User Zola (`admin` / `owner` / `superviseur`, `phone`, `status`).

---

## Commandes utiles

```bash
# Migrations + seed uniquement
.\scripts\seed.ps1          # Windows
./scripts/seed.sh           # Linux/macOS

# Build production frontend
cd zola-web && npm run build

# Tests API (à partir de INF-05)
cd zola-api && php artisan test
```

---

## Structure du monorepo

```
Zola/
├── zola-api/          # Laravel API
├── zola-web/          # Nuxt 3 PWA
├── docs/              # Maquettes + spécifications
├── INSTRUCTIONS/      # Instructions projet
├── scripts/           # dev.ps1, seed.ps1, ensure-database.php
├── docker-compose.yml
└── MEMORY.md          # Journal de développement
```

---

## Dépôt

https://github.com/Ryan-Inventor/zola

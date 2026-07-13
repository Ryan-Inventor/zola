# MEMORY.md — Journal de suivi du projet Zola

> Fichier vivant mis à jour à chaque ticket.  
> Référence tickets : [docs/PROMPTS.md](docs/PROMPTS.md)  
> Dépôt GitHub : https://github.com/Ryan-Inventor/zola

---

## État global

| Champ | Valeur |
|---|---|
| **Dernière mise à jour** | 2026-07-13 |
| **Ticket courant** | AUTH-03 (prochain) |
| **Sprint** | 1 — Setup + Auth |
| **Tickets terminés** | 8 / 46 (BOOT-00 + INF-01 à INF-05 + AUTH-01 + AUTH-02) |

### Structure du monorepo

```
Zola/
├── docs/              # Maquettes HTML + documentation
├── INSTRUCTIONS/      # Instructions projet + Campay
├── zola-api/          # Laravel API (INF-01 ✅)
├── zola-web/          # Nuxt 3 PWA (INF-03 ✅)
├── MEMORY.md
└── roadmap.md
```

---

## Règles permanentes (rappel)

1. Montants : `DECIMAL(12,0)` uniquement.
2. Soldes points : recalcul via `FloatCalculationService` uniquement.
3. Campay : appel Laravel uniquement, token dans `.env`.
4. Transactions : `idempotency_key` UUID côté client, API idempotente.
5. Opérations solde : `lockForUpdate()` dans `DB::transaction()`.
6. TDD obligatoire pour toute logique métier (rouge → vert → refactor).
7. Commit + push GitHub à la fin de chaque ticket validé.

---

## Journal des tickets

### BOOT-00 — Fichiers de gouvernance + initialisation dépôt

| Champ | Détail |
|---|---|
| **Statut** | ✅ Terminé |
| **Objectif** | Créer MEMORY.md, roadmap.md, INSTRUCTIONS/, initialiser Git |
| **Commit** | `7cd4bd9` — feat(BOOT-00): gouvernance projet |

#### Fichiers créés
- `MEMORY.md`, `roadmap.md`, `.gitignore`
- `INSTRUCTIONS/INSTRUCTION.md`, `INSTRUCTIONS/CAMPAY-API.md`

#### Validations
- [x] Fichiers de gouvernance créés
- [x] Dépôt Git initialisé (`main`)
- [x] Remote `origin` → https://github.com/Ryan-Inventor/zola
- [x] Push réussi

---

### INF-01 — Initialiser Laravel 11

| Champ | Détail |
|---|---|
| **Statut** | ✅ Terminé |
| **Objectif** | API Laravel + Sanctum, réponses JSON sur `/api/*` |
| **Références** | docs/PROMPTS.md, docs/SPECS.md §7 |

#### Contexte / décisions
- `composer create-project` a installé **Laravel 13** (dernière version stable) — compatible avec la spec « Laravel 11+ ».
- Exception handling via `app/Exceptions/Handler.php` + `bootstrap/app.php` (pattern Laravel 11+).
- Routes API : préfixe `/api/v1` via `routes/api.php`.

#### Fichiers créés / modifiés
- `zola-api/` — projet Laravel complet
- `zola-api/routes/api.php` — groupe `v1`
- `zola-api/app/Exceptions/Handler.php` — format JSON standardisé
- `zola-api/bootstrap/app.php` — routing API + exception render
- `zola-api/config/cors.php` — `allowed_origins` via `FRONTEND_URL`
- `zola-api/config/sanctum.php` — publié (localhost:3000 inclus)
- `zola-api/.env` / `.env.example` — `APP_NAME=Zola`, MySQL `zola`

#### Commandes exécutées
```bash
composer create-project laravel/laravel zola-api
cd zola-api && composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan serve --host=127.0.0.1 --port=8000
curl.exe -s http://127.0.0.1:8000/api/v1/test
```

#### Résultat validation
```json
{"error":"NOT_FOUND","message":"Ressource introuvable.","details":{}}
```
→ JSON d'erreur, jamais HTML. ✅

#### Confirmation
✅ INF-01 Done — API Laravel répond en JSON sur `/api/v1/*`

---

### INF-02 — Configurer MySQL

| Champ | Détail |
|---|---|
| **Statut** | ✅ Terminé |
| **Objectif** | Base `zola` MySQL connectée, charset utf8mb4 |

#### Actions réalisées
1. `CREATE DATABASE zola CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci` (via PDO PHP — client `mysql` absent du PATH)
2. `.env` déjà configuré (INF-01) : `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=zola`
3. `config/database.php` :
   - `default` → `mysql` (au lieu de `sqlite`)
   - connexion `mysql` : `charset` et `collation` fixés à `utf8mb4` / `utf8mb4_unicode_ci`
   - `database` par défaut → `zola`

#### Commandes exécutées
```bash
php -r "PDO create database zola utf8mb4..."
php artisan db:show
php artisan migrate:install   # requis pour migrate:status sur base vide
php artisan migrate:status
```

#### Résultat validation
```
MySQL 9.1.0 | Connection mysql | Database zola | Host 127.0.0.1 | Port 3306
```
`migrate:status` → 4 migrations en statut **Pending** (table `migrations` créée). ✅

#### Note
- `db:show` affiche la connexion puis peut avertir sur l'extension PHP `intl` manquante (cosmétique, connexion OK).
- Mot de passe root MySQL vide sur l'environnement local.

#### Confirmation
✅ INF-02 Done — MySQL connecté

---

### INF-03 — Initialiser Nuxt 3 + PWA

| Champ | Détail |
|---|---|
| **Statut** | ✅ Terminé |
| **Objectif** | Nuxt 3 + Tailwind + Pinia + PWA, tokens marque Zola |

#### Contexte / décisions
- Template `nuxi init` installait Nuxt 4 → **rebasé sur Nuxt 3.21.8** (spec projet).
- `tsconfig.json` corrigé (`extends .nuxt/tsconfig.json`) — le template Nuxt 4 référençait des fichiers inexistants.
- Icônes PWA : placeholders depuis `docs/z1.PNG` / `z2.PNG`.

#### Fichiers créés
- `zola-web/package.json`, `nuxt.config.ts`, `tailwind.config.ts`, `postcss.config.js`
- `zola-web/app.vue` — `<NuxtPage />` uniquement
- `zola-web/assets/css/main.css` — Tailwind + chiffres tabulaires
- `zola-web/pages/index.vue` — page d'accueil minimale
- `zola-web/.env` / `.env.example` — `NUXT_PUBLIC_API_URL`
- `zola-web/public/pwa-192x192.png`, `pwa-512x512.png`

#### Config PWA
- `name`: Zola, `theme_color`: `#F56001`, `background_color`: `#F4F4F5`
- Service worker généré (workbox), `devOptions.enabled: true`

#### Commandes exécutées
```bash
npx nuxi init zola-web -t minimal --packageManager npm --no-install -f --gitInit false
npm install
npm run build
npm run dev
curl.exe http://localhost:3000/ → 200
```

#### Confirmation
✅ INF-03 Done — Nuxt 3 tourne, PWA configurée

---

### INF-04 — Environnement de dev

| Champ | Détail |
|---|---|
| **Statut** | ✅ Terminé |
| **Objectif** | Docker Compose + scripts locaux + README + seed initial |

#### Fichiers créés
- `docker-compose.yml` — mysql, api (Laravel), web (Nuxt)
- `docker/api/Dockerfile`, `docker/web/Dockerfile`
- `scripts/dev.ps1`, `scripts/dev.sh` — démarrage une commande
- `scripts/seed.ps1`, `scripts/seed.sh` — migrate + seed
- `scripts/ensure-database.php` — création base `zola`
- `README.md` — instructions de démarrage
- `zola-api/database/seeders/InitialSeeder.php` — préparation AUTH-01

#### Correctifs MySQL (local)
- `AppServiceProvider` : `Schema::defaultStringLength(191)`
- Migration `failed_jobs` : colonnes `connection`/`queue` limitées à 50 chars (index composite utf8mb4)

#### Seed initial
- Utilisateur dev : `dev@zola.test` / `password` (schéma Laravel actuel, remplacé en AUTH-01)

#### Validation
```powershell
.\scripts\dev.ps1
# [OK] Environnement Zola operationnel
# API : http://localhost:8000/api/v1
# Web : http://localhost:3000
```

#### Note
- Docker non installé sur la machine de dev actuelle — `docker compose up` disponible pour les autres environnements.
- Script local `dev.ps1` validé comme équivalent.

#### Confirmation
✅ INF-04 Done — Environnement de dev opérationnel

---

### INF-05 — Outils de test (TDD)

| Champ | Détail |
|---|---|
| **Statut** | ✅ Terminé |
| **Objectif** | Pest (backend) + Vitest (frontend) opérationnels |

#### Backend
- `pestphp/pest` v4.7 installé (`vendor/bin/pest --init`)
- `tests/Pest.php` — `RefreshDatabase` sur les tests `Feature/`
- `tests/Unit/ExampleTest.php` — test trivial existant

#### Frontend
- `vitest`, `@vue/test-utils`, `happy-dom`, `@vitejs/plugin-vue`
- `vitest.config.ts` — happy-dom, alias `~` et `@`
- `tests/unit/example.test.ts` — test trivial
- `npm run test` / `npm run test:watch`

#### Validation
```bash
cd zola-api && php artisan test  → 2 passed (Pest)
cd zola-web && npm run test     → 1 passed (Vitest)
```

#### Confirmation
✅ INF-05 Done — Pest et Vitest opérationnels, prêts pour le TDD

---

### AUTH-01 — Migration et modèle User

| Champ | Détail |
|---|---|
| **Statut** | ✅ Terminé |
| **Objectif** | Table `users` conforme au schéma Zola, enums castés, Sanctum |
| **Références** | docs/zola-schema-db.md table users, docs/SPECS.md §1 |

#### Fichiers créés / modifiés
- `zola-api/database/migrations/0001_01_01_000000_create_users_table.php` — colonnes exactes : `name(150)`, `phone(20) UNIQUE`, `email(150) NULL UNIQUE`, `password`, `role ENUM(admin,owner,superviseur)`, `status ENUM(pending,active,suspended) DEFAULT 'pending'` ; table `password_reset_tokens` Laravel supprimée (remplacée par `password_resets` custom en AUTH-04)
- `zola-api/app/Enums/UserRole.php`, `app/Enums/UserStatus.php` — enums string PHP 8
- `zola-api/app/Models/User.php` — `$fillable` complet, casts `role`/`status` vers enums, `password => hashed`, trait `HasApiTokens` (Sanctum)
- `zola-api/database/factories/UserFactory.php` — phone camerounais 9 chiffres, states `admin()/owner()/superviseur()/pending()/suspended()`
- `zola-api/database/seeders/InitialSeeder.php` — 3 comptes (un par rôle) : `admin@zola.test`, `owner@zola.test`, `superviseur@zola.test` / `password`, idempotent (`firstOrCreate`)
- `zola-api/tests/Feature/Auth/UserModelTest.php` — 6 tests Pest (colonnes, casts enums, default status, hash password, unique phone, token Sanctum)
- `zola-api/phpunit.xml` — tests sur MySQL `zola_test` (driver sqlite absent en local)
- `scripts/ensure-test-database.php` — création base `zola_test`

#### Validation
```bash
php artisan migrate:fresh --seed   # 4 migrations DONE + InitialSeeder DONE
php artisan tinker tinker-auth01.php
# role instanceof UserRole : true  (value: superviseur)
# status instanceof Status : true  (value: pending)
# password hashed (bcrypt) : true
php artisan test                   # 8 passed (13 assertions)
```

#### Notes
- Base de test dédiée `zola_test` (MySQL) car l'extension `pdo_sqlite` n'est pas activée en local.
- `remember_token` et `email_verified_at` retirés : hors schéma Zola (API stateless Sanctum).

#### Confirmation
✅ AUTH-01 Done — Table users créée, enums fonctionnels

---

### AUTH-02 — Endpoint login [TDD]

| Champ | Détail |
|---|---|
| **Statut** | ✅ Terminé |
| **Objectif** | POST `/auth/login` — identifiant email ou téléphone, token Sanctum si `active` |
| **Références** | docs/PROMPTS.md AUTH-02, docs/SPECS.md §2, maquette `01-connexion.html` |

#### Cycle TDD
1. **Rouge** — 8 tests `LoginTest.php` écrits en premier (404 route inexistante)
2. **Vert** — implémentation AuthController + LoginRequest + UserResource + route

#### Fichiers créés
- `zola-api/tests/Feature/Auth/LoginTest.php` — 8 tests (email, phone, 401 générique, pending 403, suspended 403, active seul émet token)
- `zola-api/app/Http/Controllers/Api/AuthController.php` — `login()` : `@` → email, sinon phone ; vérif status avant token
- `zola-api/app/Http/Requests/LoginRequest.php` — `identifier` + `password` requis
- `zola-api/app/Http/Resources/UserResource.php` — id, name, phone, email, role, status (sans password)
- `zola-api/routes/api.php` — `POST /auth/login`

#### Règles métier implémentées
- Identifiants invalides (inexistant ou mauvais mdp) → `401 UNAUTHORIZED`, message unique : *« Identifiants incorrects. Vérifiez votre email/téléphone et votre mot de passe. »*
- `pending` → `403 FORBIDDEN` *« Compte en attente d'activation »*, aucun token
- `suspended` → `403 FORBIDDEN` *« Compte suspendu, contactez le support »*, aucun token
- `active` → `200` avec `data.token` + `data.user` (UserResource)

#### Validation
```bash
php artisan test --filter=LoginTest  → 8 passed (40 assertions)
php artisan test                   → 16 passed (53 assertions)
```

#### Confirmation
✅ AUTH-02 Done — 8 tests Pest verts, statuts bloquants couverts

---

## Historique des commits

| Date | Ticket | Message | SHA |
|---|---|---|---|
| 2026-07-13 | BOOT-00 | feat(BOOT-00): gouvernance projet | `7cd4bd9` |
| 2026-07-13 | INF-01 | feat(INF-01): init Laravel API + Sanctum + JSON errors | `66ec4d5` |
| 2026-07-13 | INF-02 | feat(INF-02): config MySQL zola utf8mb4 | `25dc6ac` |
| 2026-07-13 | INF-03 | feat(INF-03): init Nuxt 3 PWA Tailwind Pinia | `269a496` |
| 2026-07-13 | INF-04 | feat(INF-04): environnement dev Docker + scripts + seed | `7e82c4d` |
| 2026-07-13 | INF-05 | feat(INF-05): Pest backend + Vitest frontend TDD | `3973208` |
| 2026-07-13 | AUTH-01 | feat(AUTH-01): migration users + enums + modèle User Sanctum | `54415bb` |
| 2026-07-13 | AUTH-02 | feat(AUTH-02): endpoint login email/téléphone + tests TDD | `f12c5fc` |

---

## Points d'attention / risques

| ID | Sujet | Statut |
|---|---|---|
| R-01 | Dépôt Git non initialisé au démarrage | ✅ Résolu |
| R-02 | Laravel 13 installé au lieu de 11 (version plus récente) | Accepté |
| R-03 | MySQL non encore configuré (INF-02) | ✅ Résolu |
| R-04 | Comportement Campay numéro non enregistré (TXN-02) | Ouvert |

---

## Cycle de travail établi (ticket-loop)

Pour chaque ticket :
1. Lire docs requis
2. Mettre à jour MEMORY.md (début)
3. Tests d'abord si logique métier [TDD]
4. Implémenter
5. Valider (commandes PROMPTS.md)
6. Mettre à jour MEMORY.md (fin)
7. `git commit` + `git push origin main`

---

## Backlog rapide

Sprint 1 : ~~INF-01~~ ~~INF-05~~ ~~AUTH-01~~ ~~AUTH-02~~ → **AUTH-03** → …

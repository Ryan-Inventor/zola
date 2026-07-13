# MEMORY.md — Journal de suivi du projet Zola

> Fichier vivant mis à jour à chaque ticket.  
> Référence tickets : [docs/PROMPTS.md](docs/PROMPTS.md)  
> Dépôt GitHub : https://github.com/Ryan-Inventor/zola

---

## État global

| Champ | Valeur |
|---|---|
| **Dernière mise à jour** | 2026-07-13 |
| **Ticket courant** | INF-03 (prochain) |
| **Sprint** | 1 — Setup + Auth |
| **Tickets terminés** | 3 / 46 (BOOT-00 + INF-01 + INF-02) |

### Structure du monorepo

```
Zola/
├── docs/              # Maquettes HTML + documentation
├── INSTRUCTIONS/      # Instructions projet + Campay
├── zola-api/          # Laravel API (INF-01 ✅)
├── zola-web/          # Nuxt 3 PWA (à créer — INF-03)
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
| **Statut** | ⏳ Prochain ticket |

## Historique des commits

| Date | Ticket | Message | SHA |
|---|---|---|---|
| 2026-07-13 | BOOT-00 | feat(BOOT-00): gouvernance projet | `7cd4bd9` |
| 2026-07-13 | INF-01 | feat(INF-01): init Laravel API + Sanctum + JSON errors | `66ec4d5` |
| 2026-07-13 | INF-02 | feat(INF-02): config MySQL zola utf8mb4 | (à pousser) |

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

Sprint 1 : ~~INF-01~~ ~~INF-02~~ → **INF-03** → INF-04 → INF-05 → AUTH-01 → …

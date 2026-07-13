# Zola — Schéma DB détaillé (Phase 3.3)
## MySQL 8 — types, index, contraintes

Convention générale : toutes les tables ont `created_at` / `updated_at` (`TIMESTAMP`), non listés ci-dessous sauf mention contraire. Tous les montants sont en FCFA, **jamais de décimales** → `DECIMAL(12,0)`, jamais `FLOAT`/`DOUBLE` (arrondis flottants inacceptables sur de l'argent réel).

---

### `users`

| Colonne | Type | Contrainte |
|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT |
| name | VARCHAR(150) | NOT NULL |
| phone | VARCHAR(20) | NOT NULL, UNIQUE |
| email | VARCHAR(150) | NULL, UNIQUE |
| password | VARCHAR(255) | NOT NULL (hash bcrypt) |
| role | ENUM('admin','owner','superviseur') | NOT NULL |
| status | ENUM('pending','active','suspended') | NOT NULL, DEFAULT 'pending' |

**Index** : `UNIQUE(phone)`, `UNIQUE(email)` — nécessaires puisque la connexion accepte l'un ou l'autre comme identifiant.

---

### `password_resets`

⚠️ **Table ajoutée lors d'un audit** : le flux mot de passe oublié à 3 étapes (OTP puis `reset_token` puis nouveau mot de passe, voir `docs/SPECS.md` §2) n'a nulle part où stocker l'OTP et le `reset_token` sans cette table. Sans elle, `AUTH-04` est impossible à coder tel que documenté.

| Colonne | Type | Contrainte |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| user_id | BIGINT UNSIGNED | FK → users.id, NOT NULL |
| otp_code_hash | VARCHAR(255) | NOT NULL (hashé comme un mot de passe, jamais en clair) |
| otp_expires_at | TIMESTAMP | NOT NULL (10 min après génération) |
| reset_token_hash | VARCHAR(255) | NULL (rempli seulement après vérification OTP réussie) |
| reset_token_expires_at | TIMESTAMP | NULL |
| used_at | TIMESTAMP | NULL (flux consommé, empêche la réutilisation) |

**Index** : `INDEX(user_id, used_at)`
**Logique** : à chaque `POST /auth/forgot-password`, supprimer les lignes précédentes non utilisées pour ce `user_id` avant d'en créer une nouvelle (un seul flux de reset actif à la fois par utilisateur).

---

### `points`

| Colonne | Type | Contrainte |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| owner_id | BIGINT UNSIGNED | FK → users.id, NOT NULL |
| supervisor_id | BIGINT UNSIGNED | FK → users.id, NULL |
| name | VARCHAR(150) | NOT NULL |
| cash_balance | DECIMAL(12,0) | NOT NULL, DEFAULT 0 |
| om_balance | DECIMAL(12,0) | NOT NULL, DEFAULT 0 |
| momo_balance | DECIMAL(12,0) | NOT NULL, DEFAULT 0 |
| status | ENUM('active','archived') | NOT NULL, DEFAULT 'active' |

**Index** : `INDEX(owner_id)`, `INDEX(supervisor_id)`
**Validation applicative** (pas une contrainte SQL) : si `supervisor_id` renseigné, l'utilisateur référencé doit avoir `role = 'superviseur'`.
**Important** : `*_balance` sont des colonnes de **cache dénormalisé**, recalculées par `FloatCalculationService` à partir de `float_movements` + `transactions`. Elles ne sont jamais la source de vérité — en cas de doute, on peut toujours les reconstruire depuis le journal.
**⚠️ `status='archived'` n'a aucun endpoint qui l'active en V1** (vérifié : aucune des 16 maquettes ne montre de bouton "archiver un point"). La colonne existe pour ne pas avoir à migrer la table plus tard, mais en V1 tous les points restent `active` en permanence — ce n'est pas un ticket manquant, c'est une portée volontairement limitée à documenter comme telle plutôt qu'à laisser ambiguë.

---

### `transactions`

| Colonne | Type | Contrainte |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| point_id | BIGINT UNSIGNED | FK → points.id, NOT NULL |
| performed_by_user_id | BIGINT UNSIGNED | FK → users.id, NOT NULL |
| idempotency_key | CHAR(36) | NOT NULL, UNIQUE (UUID généré côté client) |
| type | ENUM('deposit','withdraw') | NOT NULL |
| operator | ENUM('orange_money','mtn_momo') | NOT NULL |
| client_phone | VARCHAR(20) | NOT NULL |
| client_name | VARCHAR(150) | NULL |
| amount | DECIMAL(12,0) | NOT NULL |
| verification_status | ENUM('verified','not_found','offline_unverified') | NOT NULL |
| occurred_at | TIMESTAMP | NOT NULL — heure réelle de la saisie par l'agent, distincte de `created_at` (heure de synchronisation serveur) en cas de queue offline |

**Index** :
- `UNIQUE(idempotency_key)` — clé de la sécurité anti-doublon offline
- `INDEX(point_id, occurred_at)` — historique par point, trié par date
- `INDEX(client_phone)` — recherche rapide dans l'historique
- `INDEX(client_name)` — recherche par nom (à envisager en `FULLTEXT` si le volume grossit)

---

### `float_movements`

| Colonne | Type | Contrainte |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| point_id | BIGINT UNSIGNED | FK → points.id, NOT NULL |
| performed_by_user_id | BIGINT UNSIGNED | FK → users.id, NOT NULL |
| movement_type | ENUM('cash','electronic') | NOT NULL |
| operator | ENUM('orange_money','mtn_momo') | NULL (NULL si `movement_type = cash`) |
| direction | ENUM('add','remove') | NOT NULL |
| amount | DECIMAL(12,0) | NOT NULL |
| comment | VARCHAR(255) | NULL |
| is_initial | BOOLEAN | NOT NULL, DEFAULT FALSE — marque le mouvement d'ouverture à la création du point |

**Index** : `INDEX(point_id, created_at)`

---

### `cash_closings`

| Colonne | Type | Contrainte |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| point_id | BIGINT UNSIGNED | FK → points.id, NOT NULL |
| performed_by_user_id | BIGINT UNSIGNED | FK → users.id, NOT NULL |
| closing_date | DATE | NOT NULL |
| cash_theoretical | DECIMAL(12,0) | NOT NULL |
| cash_counted | DECIMAL(12,0) | NOT NULL |
| om_theoretical | DECIMAL(12,0) | NOT NULL |
| om_counted | DECIMAL(12,0) | NOT NULL |
| momo_theoretical | DECIMAL(12,0) | NOT NULL |
| momo_counted | DECIMAL(12,0) | NOT NULL |
| comment | TEXT | NULL |
| status | ENUM('ok','gap_reported','resolved') | NOT NULL, DEFAULT 'ok' |
| reviewed_by_user_id | BIGINT UNSIGNED | FK → users.id, NULL (rempli seulement si un owner a traité un écart) |
| decision | TEXT | NULL |
| resolved_at | TIMESTAMP | NULL |

**Index** : `UNIQUE(point_id, closing_date)` — une seule clôture par point et par jour.

**⚠️ Correctif d'audit** : la table `cash_controls` initialement prévue en relation 1:1 avec `cash_closings` a été **supprimée et fusionnée directement dans `cash_closings`** (colonnes `reviewed_by_user_id`, `decision`, `resolved_at` ci-dessus). Une table séparée n'avait de sens que si plusieurs contrôles pouvaient exister par clôture, ce qui n'est jamais le cas — c'était de la complexité inutile pour une relation qui est en réalité 1:1 et créée une seule fois. Cette table figurait dans le schéma initial et dans le diagramme de classes de la Phase 3.1 : ces deux artefacts sont désormais **obsolètes sur ce point précis**, ce document fait foi.

---

### `notifications`

| Colonne | Type | Contrainte |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| user_id | BIGINT UNSIGNED | FK → users.id, NOT NULL |
| type | VARCHAR(50) | NOT NULL (ex : `low_float`, `cash_gap`, `closing_ok`) |
| title | VARCHAR(150) | NOT NULL |
| description | VARCHAR(255) | NOT NULL |
| link | VARCHAR(255) | NULL — route Nuxt vers laquelle rediriger au clic |
| read_at | TIMESTAMP | NULL |

**Index** : `INDEX(user_id, read_at)` — pour lister rapidement les non-lues d'un utilisateur.

---

## Notes de conception transverses

- **Aucune table `float_balances` séparée** : les 3 soldes vivent en colonnes sur `points`, recalculables depuis le journal — décision actée en 3.1, confirmée ici.
- **Aucune table `roles` ou `permissions`** : `role` est une simple `ENUM` sur `users`, cohérent avec la suppression du rôle Agent et la simplicité voulue.
- **Aucune table de préfixes opérateurs** : reste une constante applicative (`OperatorDetectionService`), jamais persistée.
- **`occurred_at` vs `created_at`** sur `transactions` est la seule table où cette distinction compte réellement, à cause du mode hors-ligne — partout ailleurs `created_at` suffit.

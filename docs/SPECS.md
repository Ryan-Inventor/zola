# SPECS.md — Spécifications techniques précises

---

## 1. Schéma de base de données

Référence complète : `zola-schema-db.md` (types exacts, index, contraintes pour les 7 tables : `users`, `points`, `transactions`, `float_movements`, `cash_closings`, `notifications`, `password_resets` — `cash_controls` a été fusionnée dans `cash_closings`, `password_resets` a été ajoutée pour le flux OTP, voir correctifs d'audit dans `zola-schema-db.md`).

Rappels critiques :
- Tous les montants : `DECIMAL(12,0)`
- `transactions.idempotency_key` : `CHAR(36) UNIQUE` — clé anti-doublon offline, non négociable
- `cash_closings` : `UNIQUE(point_id, closing_date)` — une seule clôture par point et par jour

---

## 2. Contrat des endpoints API (`routes/api.php`, préfixe `/api/v1`)

Format de réponse standard :
- Succès : `{"data": {...}}` ou `{"data": [...], "meta": {...}}` pour les listes
- Erreur : `{"error": "CODE", "message": "...", "details": {}}`
- **Pagination** (⚠️ absente de la version précédente de ce document) : toute route qui retourne une liste potentiellement longue (`GET /points/{id}/transactions`, `GET /notifications`) accepte `page` et `per_page` (défaut 20, max 100) en query params, et retourne `meta: {current_page, last_page, total}`. `GET /points`, `GET /team` et `GET /reports` n'ont pas besoin de pagination en V1 (un owner a rarement plus d'une poignée de points/superviseurs).

| Méthode | Route | Rôles autorisés | Description |
|---|---|---|---|
| POST | `/auth/login` | tous | email/téléphone + mot de passe → token Sanctum |
| POST | `/auth/forgot-password` | tous | envoi OTP |
| POST | `/auth/reset-password` | tous | nouveau mot de passe, via `reset_token` (pas l'OTP brut — voir précision ci-dessous) |
| POST | `/auth/register` | public | création compte owner, statut `pending` |
| GET | `/me` | tous | profil de l'utilisateur connecté |
| PATCH | `/me` | tous | modification profil |
| PATCH | `/me/password` | tous | changement mot de passe (avec vérif ancien mdp) |
| GET | `/points` | owner, superviseur | liste des points accessibles (scope selon rôle) |
| POST | `/points` | owner | création point + `FloatMovement` initial |
| GET | `/points/{id}` | owner (ses points), superviseur (assignés) | détail point |
| POST | `/points/{id}/verify-beneficiary` | owner, superviseur | proxy Campay, ne persiste rien |
| POST | `/points/{id}/transactions` | owner, superviseur | **idempotent sur `idempotency_key`** |
| GET | `/points/{id}/transactions` | owner, superviseur | historique filtrable |
| POST | `/points/{id}/float-movements` | owner, superviseur | réapprovisionnement |
| POST | `/points/{id}/cash-closings` | owner, superviseur | clôture, calcule les 3 écarts |
| GET | `/cash-closings/{id}` | owner | pour contrôle |
| PATCH | `/cash-closings/{id}/resolve` | owner | résolution d'un écart |
| POST | `/points/{id}/team` | owner | ajout superviseur (nom, tél, email optionnel, mot de passe) — remplace le superviseur existant du point s'il y en avait déjà un |
| DELETE | `/team/{userId}` | owner | retrait superviseur |
| GET | `/reports` | owner | KPIs + comparaison par point (params : `from`, `to`, `point_id`, `operator`) |
| GET | `/notifications` | tous | liste, non-lues en premier |
| PATCH | `/notifications/{id}/read` | tous | marquer comme lu |
| PATCH | `/notifications/read-all` | tous | marquer toutes les notifications de l'utilisateur comme lues (bulk, évite une boucle de N appels côté client) |
| GET | `/team` | owner | liste des superviseurs de l'owner, tous points confondus — utilisé notamment par la recherche de superviseur existant à la création d'un point |
| POST | `/auth/verify-otp` | tous | vérifie le code OTP seul, retourne un `reset_token` de courte durée (10 min) à usage unique |
| GET | `/admin/accounts` | admin | liste des comptes owner |
| PATCH | `/admin/accounts/{id}/activate` | admin | activation |
| PATCH | `/admin/accounts/{id}/suspend` | admin | suspension |

**Précision sur le flux mot de passe oublié** : `POST /auth/reset-password` n'accepte PAS l'OTP brut — il attend le `reset_token` retourné par `/auth/verify-otp`. C'est ce qui permet à l'UI (voir maquette `02-mot-de-passe-oublie.html`) de valider le code à l'étape 2 avant même d'afficher le champ nouveau mot de passe à l'étape 3, sans exposer l'OTP une seconde fois.

---

## 3. `OperatorDetectionService` — table de préfixes (constante applicative)

```php
const PREFIXES = [
    'MTN' => ['67', '650', '651', '652', '653', '654'],
    'Orange' => ['69', '655', '656', '657', '658', '659'],
];
```

Logique : tester d'abord les préfixes à 2 chiffres (`67`, `69`), puis les préfixes à 3 chiffres (`650`-`654`, `655`-`659`). Si aucune correspondance → `unknown`, l'UI affiche "préfixe non reconnu".

Cette même table doit exister **côté Nuxt** (`useOperatorDetection.ts`) pour un retour instantané à la saisie, et **côté Laravel** (`OperatorDetectionService`) pour la validation serveur — les deux doivent rester strictement synchronisées si jamais un préfixe est ajouté.

### ⚠️ Correspondance obligatoire : raccourcis des maquettes ↔ valeurs API/DB

Les maquettes HTML utilisent des raccourcis courts pour l'affichage et les variables JS internes (`'om'`, `'momo'`, `'MTN'`, `'Orange'`). **Ce ne sont pas les valeurs à envoyer à l'API.** Table de correspondance à respecter dans tout le code :

| Raccourci maquette | Valeur API/DB (`operator` enum) |
|---|---|
| `'om'` / `'OM'` / `'Orange'` | `orange_money` |
| `'momo'` / `'MoMo'` / `'MTN'` | `mtn_momo` |

Cette conversion doit être centralisée dans un seul endroit du code Nuxt (par exemple dans `useOperatorDetection.ts` lui-même, en plus de la détection), jamais dupliquée à la main dans chaque composant.

---

## 4. Intégration Campay

- **Base URL** : `https://demo.campay.net` en développement (`CAMPAY_BASE_URL` en `.env`). ⚠️ URL de production à confirmer auprès de Campay avant le déploiement — non documentée dans la doc fournie.
- **Authentification** : token permanent, header `Authorization: Token {CAMPAY_TOKEN}`
- **Endpoint utilisé** : `GET /api/holder_info/?phone_number=237XXXXXXXXX`
  - Le numéro local à 9 chiffres saisi dans l'app doit être préfixé `237` avant l'appel — fait côté Laravel, jamais côté Nuxt.
  - Réponse succès : `{"full_name": "JOHN DOE"}`
  - Codes d'erreur documentés : `ER101` (numéro invalide), `ER102` (opérateur non supporté par Campay)
- **Timeout HTTP à configurer explicitement** (recommandé : 6 secondes) — c'est ce qui distingue "hors ligne" (timeout, pas de réponse) de "introuvable" (réponse reçue, négative).

### Mapping des statuts de vérification

| Situation Campay | `verification_status` stocké | Message UI |
|---|---|---|
| 200 + `full_name` présent | `verified` | Nom affiché, confirmation normale |
| Erreur `ER101`/`ER102` ou réponse sans `full_name` | `not_found` | "Aucune correspondance — vérifiez la pièce d'identité" |
| Timeout / pas de réponse dans le délai | `offline_unverified` | "Vérification impossible (hors ligne)" |

**Point à tester en développement** (non confirmé par la doc Campay) : le comportement exact de `holder_info` pour un numéro non enregistré — code HTTP d'erreur ou réponse vide. À vérifier contre l'environnement demo avant de finaliser `CampayService::getHolderInfo()`.

### ⚠️ Correspondance obligatoire : états JS des maquettes ↔ `verification_status`

Comme pour l'opérateur, les maquettes utilisent des noms d'état internes différents des valeurs DB. Ne jamais envoyer `'found'`, `'error'` ou `'offline'` tels quels à l'API :

| État JS maquette (`05-nouvelle-transaction.html`) | `verification_status` (DB) |
|---|---|
| `'found'` | `verified` |
| `'error'` | `not_found` |
| `'offline'` | `offline_unverified` |

---

## 5. Calcul du solde théorique (`FloatCalculationService`)

Pour un point donné, à un instant T :

```
cash_balance = float_movements(cash, add) - float_movements(cash, remove)
             + transactions(deposit) - transactions(withdraw)

om_balance / momo_balance = float_movements(electronic, add, operator=X) - float_movements(electronic, remove, operator=X)
                           - transactions(deposit, operator=X) + transactions(withdraw, operator=X)
```

(Le sens s'inverse entre cash et électronique : un dépôt augmente le cash mais diminue l'électronique, puisque le point donne du solde électronique au client en échange du cash reçu.)

Ce service doit être appelé après **chaque** insertion dans `transactions` ou `float_movements`, pour mettre à jour les colonnes de cache sur `points`. Ne jamais laisser un contrôleur modifier ces colonnes directement.

### Seuil d'alerte "float bas" (règle manquante identifiée à l'audit, tranchée ici pour le V1)

Après chaque recalcul, `FloatCalculationService` compare le nouveau solde au seuil suivant, **par opérateur (et pour le cash)** :

```
seuil = 20% du montant déclaré dans le FloatMovement initial (is_initial = true)
        pour ce point et ce type de solde
```

Si le solde recalculé passe **en dessous** de ce seuil (transition, pas rechecké à chaque appel si déjà bas — voir note ci-dessous), déclencher une notification `low_float` via `NotificationService`.

**Note d'implémentation** : ne notifier qu'au moment où le solde **franchit** le seuil vers le bas (pas à chaque transaction tant qu'il reste bas), pour éviter de spammer l'owner de notifications identiques. Comparer l'ancien solde (avant recalcul) et le nouveau : notifier seulement si `ancien_solde >= seuil ET nouveau_solde < seuil`.

C'est une règle simple, volontairement approximative pour le V1 (pas de moyenne glissante sur l'historique) — à affiner en V2 si le seuil relatif à l'initial se révèle peu pertinent une fois le point plus ancien.

---

## 6. Idempotence des transactions (mode hors-ligne)

1. Le client (Nuxt) génère un `idempotency_key` (UUID v4) au moment de la saisie, avant tout envoi réseau.
2. La transaction est écrite en IndexedDB immédiatement, statut `pending`.
3. À la synchronisation, `POST /points/{id}/transactions` est appelé avec ce même UUID.
4. Côté Laravel : si une transaction avec ce `idempotency_key` existe déjà → retourner la transaction existante (200), ne jamais en créer une seconde, même si l'appel est reçu plusieurs fois (retry réseau, double synchro, etc.).
5. `occurred_at` (heure réelle de saisie par l'agent) est envoyé par le client et stocké séparément de `created_at` (heure de réception serveur) — important pour l'exactitude de l'historique en cas de synchro différée.

---

## 7. Codes d'erreur API internes (`error` dans les réponses JSON)

| Code | Signification |
|---|---|
| `VALIDATION_ERROR` | payload invalide (détails dans `details`) |
| `UNAUTHORIZED` | token manquant/invalide |
| `FORBIDDEN` | rôle ou accès au point non autorisé |
| `POINT_NOT_FOUND` | point inexistant ou hors du périmètre de l'utilisateur |
| `DUPLICATE_CLOSING` | une clôture existe déjà pour ce point à cette date |
| `INSUFFICIENT_BALANCE` | un retrait de float rendrait un solde négatif |
| `CAMPAY_UNAVAILABLE` | timeout ou erreur Campay (mappé vers `offline_unverified`, pas exposé tel quel au front) |

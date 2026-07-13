# Zola — Architecture technique (Phase 3.2)
## Stack : Laravel 11 (API) · Nuxt 3 (PWA) · MySQL

---

## 0. Principes d'architecture actés

1. **Un seul `User` avec un champ `role`** (admin / owner / superviseur) — pas de tables séparées par rôle.
2. **Les soldes ne sont jamais écrits directement** — `cash_balance`, `om_balance`, `momo_balance` sur `Point` sont des colonnes de cache, recalculées à partir du journal (`transactions` + `float_movements`). Conséquence directe : la synchronisation offline n'a jamais besoin de résoudre un conflit de solde, seulement d'éviter les doublons d'événements.
3. **Les préfixes opérateurs sont codés en dur** (constante applicative), jamais en base — décision actée en Phase 2.
4. **Campay n'est jamais appelé depuis Nuxt** — uniquement depuis Laravel, token secret côté serveur.
5. **Mode hors-ligne complet** : file d'attente locale (IndexedDB) + synchronisation automatique au retour réseau, avec clé d'idempotence pour éviter les doublons.
6. **Notifications V1 = in-app uniquement** (table + polling léger), FCM repoussé en V2.

---

## 1. Backend — Laravel 11

```
app/
├── Enums/
│   ├── UserRole.php                 # admin | owner | superviseur
│   ├── UserStatus.php               # pending | active | suspended
│   ├── PointStatus.php              # active | archived
│   ├── TransactionType.php          # deposit | withdraw
│   ├── OperatorType.php             # orange_money | mtn_momo
│   ├── VerificationStatus.php       # verified | not_found | offline_unverified
│   ├── MovementType.php             # cash | electronic
│   ├── MovementDirection.php        # add | remove
│   └── ClosingStatus.php            # ok | gap_reported | resolved
│
├── Models/
│   ├── User.php
│   ├── Point.php
│   ├── Transaction.php
│   ├── FloatMovement.php
│   ├── CashClosing.php
│   └── Notification.php
│
├── Services/
│   ├── CampayService.php            # getHolderInfo() — seul point de contact avec Campay
│   ├── OperatorDetectionService.php # table de préfixes codée en dur
│   ├── FloatCalculationService.php  # calcule le solde théorique à partir du journal
│   ├── TransactionIngestService.php # traite une transaction, gère l'idempotence offline
│   └── NotificationService.php      # crée les notifications in-app
│
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php               # login, mot de passe oublié, création compte owner
│   │   ├── ProfileController.php            # profil, changement mot de passe
│   │   ├── PointController.php              # CRUD points, création avec float initial
│   │   ├── TransactionController.php        # store (avec idempotency_key), historique
│   │   ├── VerificationController.php       # POST vérification bénéficiaire (proxy Campay)
│   │   ├── FloatMovementController.php      # réapprovisionnement
│   │   ├── CashClosingController.php        # clôture de caisse ET contrôle owner (cash_controls fusionnée)
│   │   ├── TeamController.php               # inviter/retirer superviseur
│   │   ├── ReportController.php             # KPIs, comparaison par point
│   │   ├── NotificationController.php       # liste, marquer comme lu
│   │   └── AdminAccountController.php       # activation/suspension comptes owner
│   │
│   ├── Middleware/
│   │   ├── EnsureRole.php                   # vérifie owner/superviseur/admin
│   │   └── EnsurePointAccess.php            # vérifie que le point appartient/est assigné à l'utilisateur
│   │
│   ├── Requests/
│   │   ├── StoreTransactionRequest.php
│   │   ├── StoreFloatMovementRequest.php
│   │   ├── StoreCashClosingRequest.php
│   │   ├── StorePointRequest.php
│   │   └── InviteSupervisorRequest.php
│   │
│   └── Resources/
│       ├── UserResource.php
│       ├── PointResource.php
│       ├── TransactionResource.php
│       ├── FloatMovementResource.php
│       └── CashClosingResource.php
│
└── Policies/
    ├── PointPolicy.php              # owner: tous ses points / superviseur: points assignés
    ├── TransactionPolicy.php
    └── TeamPolicy.php               # seul owner peut gérer l'équipe

database/
├── migrations/
│   ├── 0001_create_users_table.php
│   ├── 0002_create_points_table.php
│   ├── 0003_create_transactions_table.php
│   ├── 0004_create_float_movements_table.php
│   ├── 0005_create_cash_closings_table.php  # inclut les colonnes de contrôle owner (reviewed_by_user_id, decision, resolved_at) — plus de table cash_controls séparée
│   └── 0006_create_notifications_table.php
└── seeders/
    └── DemoDataSeeder.php

config/
└── campay.php                        # base_url, token (depuis .env)

routes/
└── api.php
```

### Points d'attention spécifiques

- **`TransactionController::store`** accepte un `idempotency_key` (UUID généré côté client au moment de la saisie, même hors ligne). Si une transaction avec cette clé existe déjà, l'API renvoie la transaction existante au lieu d'en créer une nouvelle — c'est ce qui rend la synchronisation offline sûre sans logique de conflit complexe.
- **`VerificationController`** : si un client se synchronise plus tard avec `verification_status = offline_unverified`, on peut proposer une **vérification rétroactive** — Laravel retente l'appel Campay au moment de la synchronisation et met à jour le statut si un nom est trouvé après coup. C'est un vrai plus rendu possible par l'architecture offline, à considérer pour la Phase 5.
- **Authentification** : Laravel Sanctum (tokens Bearer), pas de session cookie — plus adapté à un frontend Nuxt découplé et à une PWA qui doit fonctionner hors ligne par intermittence.

---

## 2. Frontend — Nuxt 3 (PWA)

```
├── pages/
│   ├── connexion.vue
│   ├── mot-de-passe-oublie.vue
│   ├── creation-compte.vue
│   ├── profil.vue
│   ├── notifications.vue
│   │
│   ├── points/
│   │   ├── index.vue                # dashboard owner multi (liste des points)
│   │   ├── [id].vue                 # détail d'un point (= dashboard owner solo si 1 seul point)
│   │   └── creer.vue
│   │
│   ├── transactions/
│   │   └── nouvelle.vue             # saisie + vérification inline
│   │
│   ├── reapprovisionnement.vue
│   ├── cloture-caisse.vue
│   ├── historique.vue
│   ├── equipe.vue
│   ├── rapports.vue
│   ├── controle-caisse/
│   │   └── [id].vue
│   └── admin/
│       └── comptes.vue
│
├── components/
│   ├── layout/
│   │   ├── Dock.vue                 # navigation flottante, adaptée au rôle
│   │   └── Topbar.vue
│   ├── float/
│   │   ├── FloatCard.vue
│   │   └── FloatMovementPreview.vue # aperçu avant/après
│   ├── transaction/
│   │   ├── QuickAmounts.vue
│   │   ├── PhoneSuggestions.vue
│   │   ├── BeneficiaryCheck.vue     # les 3 états : trouvé/introuvable/hors-ligne
│   │   └── PendingQueueBadge.vue    # "3 transactions en attente de synchronisation"
│   └── ui/
│       ├── AlertBanner.vue
│       └── PillBadge.vue
│
├── composables/
│   ├── useAuth.ts
│   ├── useOperatorDetection.ts      # même table de préfixes que le backend, pour un retour instantané
│   ├── useOfflineQueue.ts           # cœur de la synchronisation
│   ├── useFloatPreview.ts
│   └── useNotifications.ts
│
├── stores/                          # Pinia
│   ├── auth.ts
│   ├── point.ts
│   └── transactionQueue.ts
│
├── plugins/
│   ├── offline-sync.client.ts       # écoute online/offline, déclenche la synchronisation
│   └── pwa.client.ts
│
└── nuxt.config.ts                   # module @vite-pwa/nuxt configuré ici
```

### Le mécanisme de la file d'attente offline (`useOfflineQueue.ts`)

1. Une transaction saisie génère un `idempotency_key` (UUID) et est écrite immédiatement dans IndexedDB (`pending_transactions`), statut `pending`.
2. L'écran affiche la transaction comme "en attente" (badge visible), sans bloquer l'agent qui peut enchaîner le client suivant.
3. Un écouteur `window.addEventListener('online', ...)` déclenche la purge de la file : chaque élément `pending` est renvoyé à l'API avec sa clé d'idempotence.
4. Succès → l'élément est retiré de la file locale et remplacé par la version confirmée par le serveur (ID réel, solde à jour).
5. Échec définitif (ex : donnée invalide) → l'élément reste visible avec un statut d'erreur explicite, jamais supprimé silencieusement.

---

## 3. Décisions ouvertes pour la Phase 3.3 (schéma DB)

- Format exact des colonnes monétaires (`DECIMAL(12,0)` vu qu'on manipule des FCFA sans décimales, jamais de `FLOAT`)
- Index nécessaires pour les recherches de l'historique (numéro de téléphone, nom client, date)

### Point clarifié : `owner_id` et `supervisor_id` ne s'excluent jamais

En relisant la règle d'héritage (l'owner garde toujours ses pleins pouvoirs sur tous ses points, même quand un superviseur y est assigné), il n'y a en réalité **aucune contrainte d'exclusion à imposer**. `Point.supervisor_id` est simplement nullable et indépendant de `owner_id` :
- `supervisor_id = null` → seul l'owner opère
- `supervisor_id` renseigné → l'owner et le superviseur opèrent tous les deux, sans conflit

La seule validation nécessaire : si `supervisor_id` est renseigné, vérifier que l'utilisateur référencé a bien `role = superviseur` — une validation applicative simple dans `StorePointRequest`, pas une contrainte SQL particulière.

# PROMPTS.md — Guide des instructions par ticket (Zola)
# Copie-colle le prompt exact dans Claude Code pour chaque ticket.
# TOUJOURS commencer par : "Lis CLAUDE.md, docs/CONTEXT.md et docs/SPECS.md entièrement."

---

## COMMENT UTILISER CE DOCUMENT

1. Avant chaque ticket : copie le prompt correspondant dans Claude Code.
2. **TDD obligatoire pour tout ticket avec de la logique métier** (voir CLAUDE.md règle 11) :
   écrire les tests d'abord (rouge), confirmer qu'ils échouent, puis coder jusqu'au vert.
   Les tickets marqués `[TDD]` ci-dessous ont leurs cas de test explicitement listés —
   pour les autres tickets backend non marqués mais qui contiennent de la logique
   (pas du CRUD trivial), le principe s'applique quand même, à toi d'identifier les
   cas de test pertinents à partir de docs/SPECS.md.
3. Claude Code doit : lire les docs, écrire les tests, les voir échouer, coder, les voir passer.
4. Exécute les commandes de validation avant de passer au ticket suivant.
5. Ne jamais sauter un ticket ni continuer sans validation verte (tests + vérifications manuelles).

---

## ═══════════════════════════════
## SPRINT 1 — SETUP + AUTHENTIFICATION
## Jalon : compte créé, connexion (email et téléphone), mdp oublié, profil — bout en bout,
## + infrastructure de notifications en place (requise dès le Sprint 4)
## ═══════════════════════════════

### TICKET INF-01 — Initialiser Laravel 11

```
Lis CLAUDE.md, docs/CONTEXT.md et docs/SPECS.md entièrement avant de commencer.

TICKET INF-01 — Initialiser le projet Laravel 11 (0.5j)

COMMANDES :
composer create-project laravel/laravel zola-api
cd zola-api
composer require laravel/sanctum

FICHIERS À CRÉER/MODIFIER :
1. .env — DB_CONNECTION=mysql, DB_DATABASE=zola, APP_NAME=Zola
2. config/sanctum.php — stateful domains incluant localhost:3000
3. config/cors.php — allowed_origins: [env('FRONTEND_URL')]
4. app/Exceptions/Handler.php — render() retourne toujours JSON pour /api/*,
   format {"error":"CODE","message":"...","details":{}} (voir docs/SPECS.md section 7)

VALIDATION :
php artisan serve
curl http://localhost:8000/api/v1/test → JSON d'erreur, jamais de HTML

Confirme : ✅ INF-01 Done — API Laravel répond en JSON
```

---

### TICKET INF-02 — Base de données MySQL

```
TICKET INF-02 — Configurer MySQL (0.5j)

ACTIONS :
1. CREATE DATABASE zola CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
2. .env : DB_HOST=127.0.0.1, DB_PORT=3306, DB_DATABASE=zola
3. config/database.php — charset utf8mb4 par défaut sur la connexion mysql

VALIDATION :
php artisan db:show
php artisan migrate:status

Confirme : ✅ INF-02 Done — MySQL connecté
```

---

### TICKET INF-03 — Initialiser Nuxt 3 + PWA

```
TICKET INF-03 — Initialiser Nuxt 3, Tailwind, PWA (1j)

COMMANDES :
npx nuxi init zola-web
cd zola-web
npm install @pinia/nuxt @vueuse/core
npm install -D tailwindcss @vite-pwa/nuxt

FICHIERS À CRÉER :
1. nuxt.config.ts — modules: ['@pinia/nuxt', '@vite-pwa/nuxt'],
   config PWA (manifest name "Zola", theme_color #F56001, icônes)
2. tailwind.config.ts — tokens de marque exacts (voir docs/CONTEXT.md section 4) :
   orange: '#F56001', ink: '#0A0A0A', slate: '#5C5C5E', mist: '#F4F4F5',
   success: '#1E8E5A', alert: '#D14343', info: '#B8860B'
   fontFamily.sans: ['Inter', 'sans-serif']
3. app.vue — <NuxtPage /> uniquement
4. .env — NUXT_PUBLIC_API_URL=http://localhost:8000/api/v1

VALIDATION :
npm run dev → http://localhost:3000 sans erreur
npm run build → build sans erreur

Confirme : ✅ INF-03 Done — Nuxt 3 tourne, PWA configurée
```

---

### TICKET INF-04 — Environnement de dev

```
TICKET INF-04 — Environnement de développement (1j)

ACTIONS :
1. Docker Compose (ou config locale équivalente) avec services : mysql, php (Laravel), node (Nuxt)
2. Script de seed initial (voir AUTH-01 pour le premier seeder)
3. README.md à la racine avec instructions de démarrage

VALIDATION :
Environnement complet démarre en une commande, API et frontend accessibles simultanément

Confirme : ✅ INF-04 Done — Environnement de dev opérationnel
```

---

### TICKET INF-05 — Outils de test (TDD)

```
TICKET INF-05 — Configuration Pest (backend) et Vitest (frontend) (0.5j)

⚠️ Ticket fondateur pour le TDD (CLAUDE.md règle 11) — à faire avant tout
ticket contenant de la logique métier (AUTH-02 et suivants).

COMMANDES BACKEND :
composer require pestphp/pest --dev --with-all-dependencies
php artisan pest:install

COMMANDES FRONTEND :
npm install -D vitest @vue/test-utils happy-dom

FICHIERS :
1. tests/Pest.php — config de base, uses(RefreshDatabase) pour les tests
   touchant la DB
2. vitest.config.ts — environnement happy-dom, alias identiques à nuxt.config.ts
3. Un test trivial de chaque côté pour valider la chaîne :
   tests/Unit/ExampleTest.php et tests/unit/example.test.ts

VALIDATION :
php artisan test → au moins 1 test vert
npm run test → au moins 1 test vert

Confirme : ✅ INF-05 Done — Pest et Vitest opérationnels, prêts pour le TDD
```

---

### TICKET AUTH-01 — Modèle User + migration

```
TICKET AUTH-01 — Migration et modèle User (0.5j)

FICHIERS :
1. database/migrations/xxxx_create_users_table.php
   Colonnes exactes : voir docs/SPECS.md section 1 / zola-schema-db.md table users
   role: ENUM('admin','owner','superviseur')
   status: ENUM('pending','active','suspended') DEFAULT 'pending'
   UNIQUE(phone), UNIQUE(email)
2. app/Enums/UserRole.php, app/Enums/UserStatus.php
3. app/Models/User.php — casts role/status vers les enums, HasApiTokens (Sanctum)

VALIDATION :
php artisan migrate
php artisan tinker → User::create([...]) puis vérifier les enums castés correctement

Confirme : ✅ AUTH-01 Done — Table users créée, enums fonctionnels
```

---

### TICKET AUTH-02 — Endpoint login [TDD]

```
TICKET AUTH-02 — Login email/téléphone (1j)

ÉTAPE 1 — TESTS (à écrire en premier, doivent échouer avant tout code) :
tests/Feature/Auth/LoginTest.php
- login réussi avec identifiant = email → token retourné
- login réussi avec identifiant = téléphone → token retourné
- mot de passe incorrect → 401, message générique (pas d'indice sur la cause)
- identifiant inexistant → 401, même message générique que ci-dessus
- status = 'pending' → 403 FORBIDDEN, "Compte en attente d'activation", AUCUN token émis
- status = 'suspended' → 403 FORBIDDEN, "Compte suspendu, contactez le support", AUCUN token émis
- status = 'active' → seul cas qui émet un token

ÉTAPE 2 — IMPLÉMENTATION (une fois les tests ci-dessus rouges) :
1. app/Http/Controllers/Api/AuthController.php — méthode login()
   Accepte "identifier" (email OU téléphone) + password
   Si l'identifiant contient '@' → recherche par email, sinon par phone
   Vérifie status = 'active' AVANT d'émettre le token (voir cas de test)
   Retourne token Sanctum + UserResource
2. app/Http/Requests/LoginRequest.php
3. app/Http/Resources/UserResource.php
4. routes/api.php — POST /auth/login

VALIDATION :
php artisan test --filter=LoginTest → tous verts

Confirme : ✅ AUTH-02 Done — [nombre] tests Pest verts, statuts bloquants couverts
```

---

### TICKET AUTH-03 — Page Connexion (Nuxt)

```
TICKET AUTH-03 — Page Connexion connectée à l'API (0.5j)

Reprendre EXACTEMENT le HTML/CSS de la maquette 01-connexion.html fournie
(design system, panneau de marque, responsive, états d'erreur) et le convertir
en composant Nuxt fonctionnel branché sur POST /auth/login.

FICHIERS :
1. pages/connexion.vue
2. stores/auth.ts (Pinia) — login(), logout(), état user/token
3. composables/useAuth.ts

VALIDATION :
Connexion réussie redirige vers le dashboard adapté au rôle.
Connexion échouée affiche l'erreur inline (pas d'alert() navigateur).

Confirme : ✅ AUTH-03 Done — Page Connexion fonctionnelle
```

---

### TICKET AUTH-04 — Mot de passe oublié

```
Lis CLAUDE.md, docs/CONTEXT.md et docs/SPECS.md entièrement avant de commencer.

TICKET AUTH-04 — Flux mot de passe oublié en 3 endpoints (1j) [TDD]

⚠️ CORRECTIF AUDIT : ce flux a 3 endpoints, pas 2 — la vérification de l'OTP
est séparée de la réinitialisation, pour correspondre exactement au
comportement de la maquette 02-mot-de-passe-oublie.html (étape 2 "Vérifier"
distincte de l'étape 3 "Nouveau mot de passe"). Nécessite la table
password_resets (voir zola-schema-db.md, ajoutée lors d'un audit — sans
elle, l'OTP et le reset_token n'ont nulle part où être stockés).

ÉTAPE 1 — TESTS (à écrire en premier) :
tests/Feature/Auth/ForgotPasswordTest.php
- forgot-password avec identifiant valide → ligne password_resets créée,
  otp_code_hash rempli, otp_expires_at ≈ +10 min
- verify-otp avec le bon code avant expiration → reset_token_hash rempli,
  retourne un reset_token en clair au client (une seule fois)
- verify-otp avec un mauvais code → 401, reset_token jamais généré
- verify-otp avec un code expiré → 401, message explicite "code expiré"
- reset-password avec un reset_token valide → mot de passe changé (vérifier
  le hash, pas la valeur en clair), used_at rempli
- reset-password avec un reset_token déjà utilisé → 401, mot de passe
  inchangé
- un nouveau forgot-password invalide toute ligne password_resets non
  utilisée précédente pour ce user_id (un seul flux actif à la fois)

ÉTAPE 2 — IMPLÉMENTATION (une fois les tests ci-dessus rouges) :
1. database/migrations/xxxx_create_password_resets_table.php
2. AuthController::forgotPassword() — POST /auth/forgot-password, génère et
   envoie/logue l'OTP (4 chiffres — voir maquette, pas 6 —, expire 10 min)
3. AuthController::verifyOtp() — POST /auth/verify-otp, vérifie l'OTP,
   retourne un reset_token à usage unique (expire 10 min)
4. AuthController::resetPassword() — POST /auth/reset-password, accepte
   reset_token (PAS l'OTP brut) + nouveau mot de passe, hash via Hash::make()

FICHIERS FRONTEND :
5. pages/mot-de-passe-oublie.vue — reprendre le flux 3 étapes de la maquette
   (identifiant → OTP → nouveau mdp), l'étape 2 appelle bien verify-otp
   et stocke le reset_token pour l'étape 3

NOTE : pour le V1, l'envoi de l'OTP peut être simulé/loggé plutôt que réellement
envoyé par SMS — décision à confirmer selon le budget SMS disponible.

VALIDATION :
php artisan test --filter=ForgotPasswordTest → tous verts

Confirme : ✅ AUTH-04 Done — Mot de passe oublié fonctionnel (3 endpoints, tests TDD)
```

---

### TICKET AUTH-05 — Création de compte Owner

```
TICKET AUTH-05 — Inscription Owner (0.5j)

FICHIERS :
1. AuthController::register() — POST /auth/register, crée un User role=owner,
   status=pending
2. pages/creation-compte.vue — reprendre 03-creation-compte.html (2 étapes + écran
   "en attente d'activation")

VALIDATION :
Compte créé avec status pending, ne peut pas se connecter tant qu'un admin
ne l'a pas activé (vérifier ce blocage explicitement).

Confirme : ✅ AUTH-05 Done — Inscription owner fonctionnelle, statut pending respecté
```

---

### TICKET AUTH-06 — Page Profil

```
TICKET AUTH-06 — Profil et changement de mot de passe (1j)

FICHIERS :
1. ProfileController — GET/PATCH /me, PATCH /me/password (vérifie l'ancien mdp)
2. pages/profil.vue — reprendre 15-profil.html

VALIDATION :
Changement de mot de passe refuse si l'ancien mdp est incorrect.

Confirme : ✅ AUTH-06 Done — Profil fonctionnel
```

---

### TICKET AUTH-07 — Middleware EnsureRole

```
TICKET AUTH-07 — Middleware de rôle (0.5j)

FICHIERS :
1. app/Http/Middleware/EnsureRole.php — accepte une liste de rôles autorisés
2. Enregistrement dans bootstrap/app.php
3. app/Policies/PointPolicy.php — squelette (affiné en PT-03)

VALIDATION :
Un superviseur qui appelle une route owner-only reçoit 403 FORBIDDEN
(format d'erreur standard, voir docs/SPECS.md section 7).

Confirme : ✅ AUTH-07 Done — Middleware de rôle actif
```

---

### TICKET NO-01 — Table et endpoint notifications

```
⚠️ CORRECTIF AUDIT : ce ticket était initialement placé au Sprint 6, mais
CL-03 (Sprint 4) en dépend directement pour notifier l'owner d'un écart de
caisse. Déplacé ici en infrastructure de base pour éviter d'écrire dans une
table qui n'existe pas encore.

TICKET NO-01 — Notifications in-app (0.5j)

FICHIERS :
1. database/migrations/xxxx_create_notifications_table.php
2. NotificationController — GET /notifications, PATCH /notifications/{id}/read,
   PATCH /notifications/read-all (bulk, voir docs/SPECS.md)
3. app/Services/NotificationService.php — createForUser()

Confirme : ✅ NO-01 Done — Notifications persistées et consultables, disponibles
pour les tickets suivants qui en dépendent (CL-03 notamment)
```

---

## ═══════════════════════════════
## SPRINT 2 — POINTS ET FLOAT
## Jalon : créer un point (soi-même OU en assignant un superviseur existant),
## déclarer le float initial, soldes recalculés correctement (pas codés en dur)
## ═══════════════════════════════

### TICKET PT-01 — Migration et modèle Point

```
TICKET PT-01 — Table points (0.5j)

FICHIERS :
1. database/migrations/xxxx_create_points_table.php — voir docs/SPECS.md
   owner_id FK NOT NULL, supervisor_id FK NULL, cash_balance/om_balance/momo_balance
   DECIMAL(12,0) DEFAULT 0, status ENUM('active','archived')
2. app/Models/Point.php — relations owner(), supervisor(), transactions(), floatMovements()

VALIDATION :
php artisan migrate
Vérifier les 2 FK vers users, INDEX(owner_id), INDEX(supervisor_id)

Confirme : ✅ PT-01 Done — Table points créée
```

---

### TICKET PT-02 — FloatCalculationService [TDD]

```
TICKET PT-02 — Service de calcul des soldes (1j)

⚠️ RÈGLE CRITIQUE (voir CLAUDE.md) : ce service est la SEULE source autorisée
à écrire cash_balance/om_balance/momo_balance sur points.

ÉTAPE 1 — TESTS (à écrire en premier) :
tests/Unit/FloatCalculationServiceTest.php
- Un dépôt de 15000 augmente cash de 15000 et diminue le solde électronique
  concerné de 15000
- Un retrait fait l'inverse
- Un FloatMovement (cash, add, 50000) augmente cash de 50000
- Un FloatMovement (electronic, remove, operator=orange_money, 20000)
  diminue om_balance de 20000, ne touche pas momo_balance ni cash_balance
- Plusieurs mouvements/transactions mélangés dans le temps → le solde final
  correspond à la somme algébrique exacte, pas seulement au dernier événement
- Un solde qui franchit le seuil de 20% du montant initial vers le bas
  déclenche un appel à NotificationService (mocké dans le test) avec
  type=low_float — dépend de NO-01 (déjà fait au Sprint 1)
- Un solde qui reste sous le seuil sur 2 recalculs successifs ne déclenche
  la notification qu'une seule fois (pas de spam, voir règle anti-spam en
  docs/SPECS.md section 5)

ÉTAPE 2 — IMPLÉMENTATION (une fois les tests ci-dessus rouges) :
1. app/Services/FloatCalculationService.php
   Méthode recalculate(Point $point) : recalcule les 3 soldes depuis
   float_movements + transactions (formule exacte en docs/SPECS.md section 5),
   met à jour les colonnes de cache sur le point, vérifie le seuil et notifie
   si franchissement.

VALIDATION :
php artisan test --filter=FloatCalculationServiceTest → tous verts

Confirme : ✅ PT-02 Done — FloatCalculationService testé, formule et seuil d'alerte vérifiés
```

---

### TICKET PT-03 — Endpoint création de point [TDD]

```
TICKET PT-03 — POST /points (1j)

ÉTAPE 1 — TESTS (à écrire en premier) :
tests/Feature/Point/StorePointTest.php
- création avec float initial 96800/184200/31000 → point créé, 3
  FloatMovement(is_initial=true) créés, les 3 soldes du point correspondent
  exactement après recalcul (pas de valeur codée en dur, doit passer par
  FloatCalculationService)
- supervisor_id fourni mais référence un user role=owner (pas superviseur)
  → 422 VALIDATION_ERROR
- supervisor_id omis → point créé, supervisor_id NULL en base, owner_id
  reste le seul opérateur
- un superviseur tente de créer un point → 403 FORBIDDEN (policy create,
  owner only)
- un owner voit bien ses propres points via la policy view, un superviseur
  ne voit que les points qui lui sont assignés

ÉTAPE 2 — IMPLÉMENTATION :
1. app/Http/Requests/StorePointRequest.php — valide name, supervisor_id
   (nullable, doit référencer un user role=superviseur si fourni),
   cash_initial, om_initial, momo_initial
2. PointController::store() :
   - Crée le Point
   - Crée un FloatMovement is_initial=true pour chacun des 3 montants déclarés
   - Appelle FloatCalculationService::recalculate()
3. app/Policies/PointPolicy.php — complète : create (owner only),
   view (owner sur ses points, superviseur sur ses points assignés)

VALIDATION :
php artisan test --filter=StorePointTest → tous verts

Confirme : ✅ PT-03 Done — Création de point testée, policy vérifiée
```

---

### TICKET EQ-01 — Endpoint équipe [TDD]

```
⚠️ CORRECTIF AUDIT : ce ticket était initialement placé au Sprint 5, mais
PT-04 (juste après) a besoin de rechercher des superviseurs existants pour
les assigner à un nouveau point. Déplacé ici pour que cette liste ne soit
pas vide au moment de construire PT-04.

TICKET EQ-01 — POST /points/{id}/team + GET /team + DELETE /team/{userId} (1j)

ÉTAPE 1 — TESTS (à écrire en premier) :
tests/Feature/Team/TeamTest.php
- création d'un superviseur avec mot de passe fourni → User créé role=
  superviseur, status=active, password hashé (Hash::check() réussit avec
  le mot de passe en clair fourni, la valeur brute n'est jamais stockée
  telle quelle en base)
- le compte créé peut immédiatement se connecter (test d'intégration avec
  AUTH-02) avec téléphone OU email
- GET /team retourne bien ce superviseur juste après sa création
- ⚠️ Assigner un nouveau superviseur à un point qui en a déjà un →
  remplace Point.supervisor_id (réassignation), l'ancien superviseur
  perd l'accès à CE point précis mais garde ses autres points assignés
  s'il en a — comportement à documenter explicitement, ce n'était nulle
  part avant cet audit
- DELETE /team/{userId} retire l'assignation (Point.supervisor_id = null)
  mais ne supprime pas le compte User (il peut être réassigné ailleurs
  plus tard)
- un superviseur (pas owner) qui appelle ces routes → 403 FORBIDDEN

ÉTAPE 2 — IMPLÉMENTATION :
1. TeamController::store() — crée un User role=superviseur, status=active
   directement (pas de flux d'invitation par lien), password via
   Hash::make() ; assigne au point (Point.supervisor_id), en écrasant
   une assignation précédente si elle existe
2. TeamController::index() — GET /team, liste les superviseurs de l'owner
   connecté, tous points confondus (utilisé par PT-04 pour la recherche)
3. TeamController::destroy() — met Point.supervisor_id à null, ne supprime
   jamais le User

VALIDATION :
php artisan test --filter=TeamTest → tous verts

Confirme : ✅ EQ-01 Done — Création et liste des superviseurs fonctionnelles,
disponibles pour PT-04
```

---

### TICKET PT-04 — Page Création de point

```
TICKET PT-04 — Page création de point (Nuxt) (1j)

Reprendre 10-creation-point.html : choix "Moi-même" / "Assigner un superviseur"
avec recherche live (appelle GET /team, ticket EQ-01 — la liste sera vide si
aucun superviseur n'a encore été créé, c'est un état normal, pas une erreur),
déclaration du float initial (formulation "reprise d'activité", pas
"création à 0").

FICHIERS :
1. pages/points/creer.vue
2. composables/useOperatorDetection.ts — même table de préfixes que le backend
   (docs/SPECS.md section 3), à garder strictement synchronisée

VALIDATION :
Point créé visible dans le dashboard immédiatement après création.

Confirme : ✅ PT-04 Done — Page création de point fonctionnelle
```

---

### TICKET PT-05 — Dashboard Owner solo

```
TICKET PT-05 — Dashboard Owner solo (1j)

Reprendre 04-dashboard-owner-solo.html : si l'owner n'a qu'un seul point,
cette page EST le détail du point (pas de liste intermédiaire).

FICHIERS :
1. pages/points/[id].vue — sert aussi de dashboard owner solo
2. components/float/FloatCard.vue (3 cartes : cash, OM, MoMo)
3. components/layout/Dock.vue — navigation adaptée au rôle connecté

VALIDATION :
Les 3 jauges de float affichent les vraies valeurs de l'API, pas des données
de démo codées en dur dans le composant.

Confirme : ✅ PT-05 Done — Dashboard owner solo connecté à l'API
```

---

### TICKET PT-06 — Dashboard Owner multi

```
TICKET PT-06 — Dashboard Owner multi-points (0.5j)

Reprendre 09-dashboard-owner-multi.html.

FICHIERS :
1. pages/points/index.vue — liste des points, KPIs résumés (points actifs,
   anomalies, volume semaine)

VALIDATION :
Un owner avec 1 seul point est automatiquement redirigé vers points/[id]
plutôt que de voir cette liste (cohérent avec PT-05).

Confirme : ✅ PT-06 Done — Dashboard multi-points fonctionnel
```

---

## ═══════════════════════════════
## SPRINT 3 — MODE HORS-LIGNE + TRANSACTIONS
## Jalon : transaction complète contre Campay demo, ET testée en mode avion (queue + sync sans doublon)
## ═══════════════════════════════

### TICKET OFF-01 — Config IndexedDB

```
TICKET OFF-01 — Store IndexedDB pending_transactions (1j)

FICHIERS :
1. npm install idb
2. composables/useIndexedDB.ts — ouverture DB, store "pending_transactions"
   (clé : idempotency_key)

VALIDATION :
Écriture/lecture manuelle testée dans la console navigateur.

Confirme : ✅ OFF-01 Done — IndexedDB configuré
```

---

### TICKET OFF-02 — useOfflineQueue

```
TICKET OFF-02 — Composable de file d'attente offline (1.5j)

FICHIERS :
1. composables/useOfflineQueue.ts
   - enqueue(transactionPayload) : génère idempotency_key (UUID v4), écrit en
     IndexedDB avec statut 'pending', occurred_at = maintenant
   - flush() : parcourt les éléments pending, POST vers l'API avec le même
     idempotency_key, retire de la queue locale au succès
   - Ne jamais dupliquer un envoi déjà en cours (verrou simple par idempotency_key)

VALIDATION :
Test manuel : couper le réseau, enqueue 3 transactions, réactiver le réseau,
vérifier que exactement 3 transactions apparaissent côté API (pas 6, pas 0).

Confirme : ✅ OFF-02 Done — File d'attente testée sans doublon
```

---

### TICKET OFF-03 — Plugin de synchronisation

```
TICKET OFF-03 — Plugin offline-sync (1j)

FICHIERS :
1. plugins/offline-sync.client.ts — écoute window 'online'/'offline',
   déclenche flush() au retour réseau, expose l'état isOnline globalement

VALIDATION :
Passage offline→online déclenche automatiquement la synchronisation sans
action de l'utilisateur.

Confirme : ✅ OFF-03 Done — Synchronisation automatique fonctionnelle
```

---

### TICKET OFF-04 — Badge de synchronisation

```
TICKET OFF-04 — Indicateur visuel de la file d'attente (0.5j)

FICHIERS :
1. components/transaction/PendingQueueBadge.vue — "3 transactions en attente
   de synchronisation"

VALIDATION :
Badge visible uniquement quand la queue n'est pas vide, disparaît après sync.

Confirme : ✅ OFF-04 Done — Badge de file d'attente fonctionnel
```

---

### TICKET TXN-01 — OperatorDetectionService (backend) [TDD]

```
TICKET TXN-01 — Service de détection d'opérateur (0.5j)

ÉTAPE 1 — TESTS (à écrire en premier) :
tests/Unit/OperatorDetectionServiceTest.php
- '670123456' → MTN (préfixe 67)
- '691234567' → Orange (préfixe 69)
- '650123456' à '654123456' → MTN (bornes 650-654 incluses)
- '655123456' à '659123456' → Orange (bornes 655-659 incluses)
- '600123456' (préfixe hors table) → unknown
- numéro trop court (< 9 chiffres) → unknown, pas d'exception levée

ÉTAPE 2 — IMPLÉMENTATION :
1. app/Services/OperatorDetectionService.php — table exacte en docs/SPECS.md
   section 3

VALIDATION :
php artisan test --filter=OperatorDetectionServiceTest → tous verts

Confirme : ✅ TXN-01 Done — Détection d'opérateur testée sur toutes les tranches
```

---

### TICKET TXN-02 — CampayService [TDD]

```
TICKET TXN-02 — Intégration Campay (1j)

ÉTAPE 1 — TESTS (à écrire en premier, avec Http::fake() — pas d'appel réseau
réel dans les tests automatisés) :
tests/Unit/CampayServiceTest.php
- Http::fake() renvoie 200 + {"full_name": "JOHN DOE"} → getHolderInfo()
  retourne 'JOHN DOE', et vérifier que l'URL appelée contient bien le préfixe
  237 devant le numéro local
- Http::fake() renvoie une erreur ER101 → getHolderInfo() retourne le statut
  interne correspondant à not_found (pas d'exception qui remonte)
- Http::fake() renvoie une erreur ER102 → idem, not_found
- Http::fake() simule un timeout (Http::fake with a delayed/failed response)
  → getHolderInfo() retourne offline_unverified, pas d'exception non gérée
- Vérifier que le header Authorization contient bien "Token {CAMPAY_TOKEN}"

ÉTAPE 2 — IMPLÉMENTATION :
1. config/campay.php — base_url, token depuis .env
2. app/Services/CampayService.php
   - getHolderInfo(string $localPhone): retourne full_name ou null
   - Préfixe 237 + numéro local avant l'appel
   - Timeout HTTP 6 secondes explicite (Http::timeout(6))
   - Mapping erreurs → statuts internes (docs/SPECS.md section 4)

ÉTAPE 3 — TEST MANUEL COMPLÉMENTAIRE (non automatisable, doc Campay incomplète
sur ce point précis) :
⚠️ Tester manuellement contre demo.campay.net avec un vrai numéro de test
pour observer le comportement exact sur un numéro non enregistré (code HTTP
ou réponse vide) — ajuster le mapping de l'étape 2 si le comportement réel
diffère de ce qui est supposé dans les tests automatisés ci-dessus, puis
mettre à jour docs/SPECS.md en conséquence.

VALIDATION :
php artisan test --filter=CampayServiceTest → tous verts (mockés)
+ test manuel documenté contre l'environnement demo

Confirme : ✅ TXN-02 Done — CampayService testé (mocké) et vérifié manuellement contre demo.campay.net
```

---

### TICKET TXN-03 — Endpoint vérification

```
TICKET TXN-03 — POST /points/{id}/verify-beneficiary (0.5j)

FICHIERS :
1. VerificationController::verify() — appelle CampayService, ne persiste rien,
   retourne juste le statut + nom éventuel

Confirme : ✅ TXN-03 Done — Endpoint de vérification fonctionnel
```

---

### TICKET TXN-04 — Endpoint transactions [TDD]

```
TICKET TXN-04 — POST /points/{id}/transactions (idempotent) (1j)

ÉTAPE 1 — TESTS (à écrire en premier, cas les plus critiques du projet) :
tests/Feature/Transaction/StoreTransactionTest.php
- dépôt valide → transaction créée, cash_balance augmente, solde électronique
  de l'opérateur concerné diminue (montants exacts vérifiés)
- retrait valide → l'inverse
- même idempotency_key envoyée 2 fois → une seule ligne en base, même
  transaction_id retourné les deux fois (pas de doublon, pas d'erreur)
- retrait supérieur au solde électronique disponible → 422 INSUFFICIENT_BALANCE,
  AUCUNE ligne créée, solde du point inchangé
- solde du point recalculé correctement même après plusieurs transactions
  successives (pas seulement la première)

tests/Feature/Transaction/ConcurrencyTest.php (test de concurrence)
- deux requêtes de dépôt envoyées en parallèle (via des process/threads
  séparés dans le test, pas juste séquentiellement) sur le même point →
  le solde final = solde initial + somme des deux montants, jamais un
  résultat où l'une des deux transactions a "écrasé" l'autre

ÉTAPE 2 — IMPLÉMENTATION (une fois les tests ci-dessus rouges) :
1. app/Http/Requests/StoreTransactionRequest.php
2. TransactionController::store() :
   - Si idempotency_key existe déjà → retourner la transaction existante (200)
   - Si type=withdraw, vérifier solde suffisant AVANT de créer → sinon
     INSUFFICIENT_BALANCE
   - Sinon créer, appeler FloatCalculationService::recalculate()
3. app/Services/TransactionIngestService.php — encapsule toute cette logique
   dans DB::transaction() avec Point::lockForUpdate()

VALIDATION :
php artisan test --filter=Transaction → tous verts, y compris le test de concurrence

Confirme : ✅ TXN-04 Done — Idempotence, solde insuffisant et concurrence testés (pas juste vérifiés à la main)
```

---

### TICKET TXN-05 — Page saisie de transaction

```
TICKET TXN-05 — Page transaction (1.5j)

Reprendre 05-nouvelle-transaction.html : suggestions de numéros en temps réel,
montants dynamiques basés sur la fréquence, formatage 1-2-2-2-2.

FICHIERS :
1. pages/transactions/nouvelle.vue
2. components/transaction/PhoneSuggestions.vue
3. components/transaction/QuickAmounts.vue

Confirme : ✅ TXN-05 Done — Page transaction fonctionnelle avec suggestions dynamiques
```

---

### TICKET TXN-06 — Vérification bénéficiaire inline

```
TICKET TXN-06 — Vérification inline (3 états) (1j)

Reprendre le comportement fusionné de 05-nouvelle-transaction.html : la
vérification se déclenche automatiquement dès 9 chiffres saisis, sans
changer de page. Le bouton final change de libellé selon le résultat
(voir docs/CONTEXT.md section 5).

FICHIERS :
1. components/transaction/BeneficiaryCheck.vue

VALIDATION :
Les 3 états (verified/not_found/offline_unverified) s'affichent correctement,
y compris en coupant réellement le réseau (pas seulement simulé en JS).

Confirme : ✅ TXN-06 Done — Vérification inline fonctionnelle dans les 3 cas réels
```

---

### TICKET TXN-07 — Page reçu/confirmation

```
TICKET TXN-07 — Page confirmation (0.5j)

Reprendre 06-confirmation.html : ruban d'alerte si verification_status
n'est pas 'verified', compteur du jour.

FICHIERS :
1. pages/transactions/confirmation.vue (ou intégré comme état de nouvelle.vue)

Confirme : ✅ TXN-07 Done — Page de confirmation fonctionnelle
```

---

## ═══════════════════════════════
## SPRINT 4 — RÉAPPROVISIONNEMENT + CLÔTURE
## Jalon : cycle complet réapprovisionnement → clôture → écart → notification → contrôle → résolution
## ═══════════════════════════════

### TICKET RE-01 — Endpoint float_movements [TDD]

```
TICKET RE-01 — POST /points/{id}/float-movements (0.5j)

ÉTAPE 1 — TESTS (à écrire en premier) :
tests/Feature/FloatMovement/StoreFloatMovementTest.php
- ajout cash → cash_balance augmente exactement du montant
- retrait cash valide (montant <= solde actuel) → cash_balance diminue
- retrait cash supérieur au solde → 422 INSUFFICIENT_BALANCE, rien créé,
  solde inchangé
- ajout électronique (operator=orange_money) → om_balance augmente,
  momo_balance et cash_balance inchangés
- mouvement sans operator alors que movement_type=electronic → 422
  VALIDATION_ERROR (operator obligatoire dans ce cas)

ÉTAPE 2 — IMPLÉMENTATION :
1. app/Http/Requests/StoreFloatMovementRequest.php
2. FloatMovementController::store() — rejette si le mouvement rendrait un
   solde négatif (INSUFFICIENT_BALANCE), sinon crée + appelle
   FloatCalculationService::recalculate()

VALIDATION :
php artisan test --filter=StoreFloatMovementTest → tous verts

Confirme : ✅ RE-01 Done — Endpoint réapprovisionnement testé, solde négatif bloqué
```

---

### TICKET RE-02 — Page réapprovisionnement

```
TICKET RE-02 — Page réapprovisionnement (1.5j)

Reprendre 07-reapprovisionnement.html : aperçu avant/après en temps réel,
bouton désactivé si le résultat serait négatif.

FICHIERS :
1. pages/reapprovisionnement.vue
2. composables/useFloatPreview.ts

Confirme : ✅ RE-02 Done — Page réapprovisionnement fonctionnelle
```

---

### TICKET CL-01 — Endpoint cash_closings [TDD]

```
TICKET CL-01 — POST /points/{id}/cash-closings (1j)

ÉTAPE 1 — TESTS (à écrire en premier) :
tests/Feature/CashClosing/StoreCashClosingTest.php
- les 3 comptages correspondent exactement au théorique → status='ok'
- écart sur le cash uniquement → status='gap_reported', écart calculé
  correctement (compté - théorique, avec le bon signe)
- écart sur les 3 pots simultanément → status='gap_reported', chaque écart
  est indépendant et correct
- deuxième clôture le même jour pour le même point → 422 DUPLICATE_CLOSING,
  aucune nouvelle ligne créée
- clôture le lendemain pour le même point → autorisée normalement

ÉTAPE 2 — IMPLÉMENTATION :
1. app/Http/Requests/StoreCashClosingRequest.php
2. CashClosingController::store() :
   - Calcule les 3 soldes théoriques via FloatCalculationService
   - Compare aux 3 valeurs comptées envoyées
   - status = 'gap_reported' si au moins un écart, sinon 'ok'
   - Rejette si une clôture existe déjà pour ce point à cette date
     (DUPLICATE_CLOSING)

VALIDATION :
php artisan test --filter=StoreCashClosingTest → tous verts

Confirme : ✅ CL-01 Done — Calcul d'écart testé sur les 3 pots séparément, doublon bloqué
```

---

### TICKET CL-02 — Page clôture de caisse

```
TICKET CL-02 — Page clôture (0.5j)

Reprendre 08-cloture-caisse.html : écart calculé en direct pour les 3 pots,
commentaire obligatoire si écart.

FICHIERS :
1. pages/cloture-caisse.vue

Confirme : ✅ CL-02 Done — Page clôture fonctionnelle
```

---

### TICKET CL-03 — Notification d'écart

```
TICKET CL-03 — Notification automatique si écart (0.5j)

⚠️ PRÉCISION : ne PAS utiliser le canal 'database' natif de Laravel
(Notification::notify() avec sa propre table polymorphique) — ça créerait
une seconde table de notifications en conflit avec celle de NO-01 (Sprint 1).
Utiliser NotificationService::createForUser() (déjà disponible depuis NO-01)
qui écrit dans notre table notifications custom.

FICHIERS :
1. Déclenchement dans CashClosingController::store() si status=gap_reported
   → NotificationService::createForUser(owner_id du point, type='cash_gap', ...)

Confirme : ✅ CL-03 Done — Notification créée automatiquement en cas d'écart
```

---

### TICKET CL-04 — Contrôle de caisse (owner)

```
TICKET CL-04 — Endpoint + page contrôle de caisse (1.5j)

⚠️ CORRECTIF AUDIT : pas de CashControlController séparé — cash_controls a
été fusionnée dans cash_closings. Tout reste sur CashClosingController.

Reprendre 13-controle-caisse.html.

FICHIERS BACKEND :
1. CashClosingController::show() — GET /cash-closings/{id} (détail avec
   théorique/déclaré/écart par pot + commentaire du superviseur)

FICHIERS FRONTEND :
2. pages/controle-caisse/[id].vue

Confirme : ✅ CL-04 Done — Contrôle de caisse consultable par l'owner
```

---

### TICKET CL-05 — Résolution du contrôle

```
TICKET CL-05 — PATCH /cash-closings/{id}/resolve (0.5j)

FICHIERS :
1. CashClosingController::resolve() — remplit reviewed_by_user_id (owner
   connecté), decision (texte envoyé), resolved_at = maintenant, et passe
   status → 'resolved' sur la même ligne cash_closings (pas de table séparée)

Confirme : ✅ CL-05 Done — Résolution fonctionnelle
```

---

## ═══════════════════════════════
## SPRINT 5 — ÉQUIPE + RAPPORTS
## Jalon : page équipe complète (EQ-01 déjà fait au Sprint 2), rapports/historique exacts sur données de test
## ═══════════════════════════════

### TICKET EQ-02 — Page Équipe

```
TICKET EQ-02 — Page équipe (1j)

Reprendre 11-equipe.html : modal avec nom/téléphone/email optionnel/mot de
passe généré, écran de confirmation avec bouton "Partager par WhatsApp"
(lien wa.me fonctionnel, pas juste visuel).

FICHIERS :
1. pages/equipe.vue

Confirme : ✅ EQ-02 Done — Page équipe fonctionnelle, partage WhatsApp opérationnel
```

---

### TICKET RA-01 — Endpoint rapports

```
TICKET RA-01 — GET /reports (1j)

FICHIERS :
1. ReportController::index() — params from/to/point_id/operator
   Retourne : volume total, nb transactions, panier moyen, écarts cumulés,
   répartition dépôts/retraits, répartition OM/MoMo, comparaison par point
   (PAS de calcul de commission/marge — hors scope V1, voir CLAUDE.md)

Confirme : ✅ RA-01 Done — Endpoint rapports testé sur données connues
```

---

### TICKET RA-02 — Page Rapports

```
TICKET RA-02 — Page rapports (1j)

Reprendre 12-rapports.html : période avec presets + personnalisée, KPIs,
graphique, répartitions, tableau comparatif.

FICHIERS :
1. pages/rapports.vue

Confirme : ✅ RA-02 Done — Page rapports connectée à l'API
```

---

### TICKET RA-03 — Historique des transactions

```
TICKET RA-03 — Endpoint + page historique (1j)

Reprendre 14-historique-transactions.html : filtrage par période (avec
plage personnalisée), recherche client/numéro, filtres point/opérateur/type,
export.

FICHIERS :
1. GET /points/{id}/transactions avec tous les filtres en query params
   + pagination (page, per_page — voir docs/SPECS.md section 2, absente
   de la version précédente de ce document)
2. pages/historique.vue — pagination gérée côté UI (bouton "charger plus"
   ou pages numérotées, au choix, mais jamais tout charger d'un coup)

VALIDATION :
Les filtres recalculent réellement les résultats et le total affiché
(pas de filtrage front-only sur des données déjà limitées côté API).
Tester avec un point ayant plus de per_page transactions pour vérifier
que la pagination fonctionne vraiment (pas juste testée avec 5 lignes).

Confirme : ✅ RA-03 Done — Historique filtrable de bout en bout
```

---

## ═══════════════════════════════
## SPRINT 6 — NOTIFICATIONS, ADMIN, QA, DÉPLOIEMENT
## Jalon : recette complète des 4 parcours, déploiement VPS, test avec point(s) pilote(s) réel(s)
## ═══════════════════════════════

### TICKET NO-02 — Page notifications

```
TICKET NO-02 — Page notifications + badge cloche (1j)

Reprendre 16-notifications.html : chaque notification cliquable renvoie vers
l'écran pertinent, badge visible dans le Topbar avec compteur non-lues.

FICHIERS :
1. pages/notifications.vue
2. composables/useNotifications.ts (polling léger, ex : toutes les 60s)

Confirme : ✅ NO-02 Done — Notifications in-app fonctionnelles
```

---

### TICKET AD-01 — Endpoint admin

```
TICKET AD-01 — Activation/suspension de comptes (0.5j)

FICHIERS :
1. AdminAccountController — GET /admin/accounts, PATCH .../activate,
   PATCH .../suspend (role=admin uniquement, via EnsureRole)
   - activate : status pending|suspended → active
   - suspend : status active → suspended
   Pas de "rejet" distinct pour le V1 : un admin qui ne veut jamais activer
   un compte peut simplement ne rien faire (reste pending indéfiniment) ou
   suspendre directement s'il détecte une fraude évidente.

Confirme : ✅ AD-01 Done — Gestion admin des comptes fonctionnelle
```

---

### TICKET AD-02 — Page Admin

```
TICKET AD-02 — Page admin comptes (1j)

FICHIERS :
1. pages/admin/comptes.vue

Confirme : ✅ AD-02 Done — Page admin fonctionnelle
```

---

### TICKET QA-01 — Tests bout-en-bout

```
TICKET QA-01 — Tests E2E des parcours critiques (1.5j)

Couvrir au minimum :
1. Owner solo : connexion → création point → transaction → clôture
2. Owner multi : création point avec superviseur → contrôle de caisse avec écart
3. Superviseur : connexion avec identifiants créés par l'owner → transaction
4. Mode hors-ligne : transaction en coupant le réseau → synchro sans doublon
   (test réel, pas seulement simulé)
5. Admin : activation d'un compte owner en attente

Confirme : ✅ QA-01 Done — 5 parcours critiques testés et verts
```

---

### TICKET QA-02 — Déploiement VPS

```
TICKET QA-02 — Déploiement production (1j)

ACTIONS :
1. Provisionnement VPS (Laravel Forge ou configuration manuelle Nginx + PHP-FPM)
2. Build Nuxt en mode statique/SSR selon besoin PWA, déployé
3. Variables d'environnement production (CAMPAY_BASE_URL de production —
   à confirmer auprès de Campay avant cette étape, voir docs/SPECS.md section 4)
4. HTTPS actif (Let's Encrypt)

Confirme : ✅ QA-02 Done — Zola accessible en production, HTTPS actif
```

---

### TICKET QA-03 — Recette pilote

```
TICKET QA-03 — Test avec point(s) pilote(s) réel(s) (0.5j)

ACTIONS :
1. Créer 1 à 2 comptes owner réels avec de vrais points
2. Faire fonctionner le point en conditions réelles pendant quelques jours
   avant tout lancement plus large
3. Recueillir le retour direct sur la vérification Campay en conditions
   réelles (pas juste l'environnement demo)

Confirme : ✅ QA-03 Done — Recette pilote réalisée, retours documentés
```

---

*PROMPTS.md v1.0 — Zola — 45 tickets sur 6 sprints, dérivés de zola-planification-phase4.md*

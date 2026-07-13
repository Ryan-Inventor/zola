# CONTEXT.md — Contexte complet du projet Zola

---

## 1. Le problème et la cible

La gestion d'un point de dépôt/retrait Orange Money/MTN MoMo au cahier papier devient intenable sur le long terme, en particulier pour les propriétaires qui gèrent plusieurs points. L'erreur la plus critique et récurrente : l'absence de vérification préalable du nom du bénéficiaire, causant des transactions envoyées à la mauvaise personne.

**Cibles** : agents/gérants solo d'un seul point, et propriétaires (owners) gérant plusieurs points avec délégation à des superviseurs.

**Critère de succès à 6 mois** : 20 à 50 points de dépôt payants actifs sur la plateforme.

**Marché** : le Cameroun compte plus de 100 000 points de vente Orange Money, plus de 25% des points dédiés à ce service sur le continent africain. Le Cameroun exécute la majorité des transactions mobile money de la zone CEMAC.

---

## 2. Les 3 rôles (pas 4 — le rôle Agent a été volontairement supprimé)

- **Admin** (rnexx) : gère la plateforme (activation/suspension des comptes owner), aucun accès aux opérations d'un point.
- **Owner** (solo ou multi-points) : exécute nativement toutes les tâches opérationnelles sur ses points (transactions, caisse, réapprovisionnement), en plus de créer des points et gérer son équipe.
- **Superviseur** : assigné individuellement à un ou plusieurs points précis par l'owner, avec les pleins pouvoirs opérationnels sur ce(s) point(s) uniquement (jamais de visibilité sur les autres points de l'owner).

**Règle d'héritage clé** : l'owner garde toujours ses pleins pouvoirs sur tous ses points, même quand un superviseur y est assigné. Les deux droits coexistent, ils ne s'excluent jamais.

---

## 3. Le cycle de vie du float — le cœur du produit

Chaque point a 3 soldes : cash, Orange Money, MTN MoMo.

1. **Initialisation** : à la création d'un point, l'owner déclare l'état actuel (pas "création à 0" — reprise d'un business existant). Ceci génère un `FloatMovement` de type `ouverture`.
2. **Mise à jour automatique** : chaque transaction (dépôt/retrait) modifie le cash et l'électronique en sens inverse.
   - Dépôt (client apporte du cash) → cash ⬆, électronique ⬇
   - Retrait (client reçoit du cash) → électronique ⬆, cash ⬇
3. **Réapprovisionnement** : mouvements hors transaction client (recharge électronique, dépôt/retrait cash), enregistrés dans `float_movements`, distincts cash vs électronique.
4. **Réconciliation** : à la clôture de caisse, le solde théorique (calculé) est comparé au solde compté physiquement, séparément pour les 3 pots.

---

## 4. Design system (résumé — voir les maquettes HTML pour le détail exact)

- **Couleurs** : Orange marque `#F56001`, Encre `#0A0A0A`, Succès `#1E8E5A`, Alerte `#D14343`, Info `#B8860B`
- **Typographie** : Inter, chiffres tabulaires obligatoires sur tous les montants
- **Navigation** : Dock flottant translucide façon macOS (glassmorphism réservé à cet élément uniquement — jamais sur les écrans de saisie)
- **Logo** : "zola" minuscule, le "z" et le point final toujours en orange, "ola" en noir (fond clair) ou blanc (fond sombre)

---

## 5. Écrans (16 au total, tous prototypés en HTML/CSS/JS avant développement)

Connexion · Mot de passe oublié · Création de compte · Dashboard Owner solo · Nouvelle transaction (avec vérification bénéficiaire inline) · Confirmation/reçu · Réapprovisionnement · Clôture de caisse · Dashboard Owner multi · Création de point · Équipe · Rapports · Contrôle de caisse · Historique des transactions · Profil · Notifications.

Le Superviseur réutilise les écrans Transaction/Réapprovisionnement/Clôture — pas de duplication de composants par rôle.

---

## 6. Décisions d'architecture actées

- Stack découplée : Laravel 11 en API pure (Sanctum, tokens Bearer), Nuxt 3 en PWA consommant l'API.
- **Mode hors-ligne complet** : file d'attente locale (IndexedDB) pour les transactions, synchronisation automatique au retour réseau, idempotence via UUID côté client. Possible car les soldes sont toujours dérivés du journal — la synchro n'a jamais à résoudre de conflit de solde, seulement à éviter les doublons d'événements.
- **Notifications V1 = in-app uniquement** (table + polling léger). Push réel (FCM) reporté en V2.
- **Authentification Campay** : token permanent (APP KEYS), pas de rafraîchissement de token temporaire — plus simple pour le V1.
- **Pas de calcul de commission/marge automatique en V1** — hors scope, à garder pour V2.
- **Correspondance obligatoire raccourcis maquettes ↔ valeurs API** (opérateur, statut de vérification) et **seuil d'alerte float bas (20% du montant initial)** : voir `docs/SPECS.md` sections 3-5. Ces règles n'étaient pas explicites avant un audit croisé des maquettes et de la documentation — désormais tranchées.

---

## 7. Arborescence de référence

### Backend (Laravel)
```
app/Enums/ · app/Models/ · app/Services/ (CampayService, OperatorDetectionService,
FloatCalculationService, TransactionIngestService, NotificationService)
app/Http/Controllers/Api/ · app/Http/Middleware/ · app/Http/Requests/ · app/Http/Resources/
app/Policies/
database/migrations/ · database/seeders/
config/campay.php · routes/api.php
```

### Frontend (Nuxt 3)
```
pages/ (connexion, points/, transactions/, reapprovisionnement, cloture-caisse,
historique, equipe, rapports, controle-caisse/, admin/)
components/ (layout/, float/, transaction/, ui/)
composables/ (useAuth, useOperatorDetection, useOfflineQueue, useFloatPreview, useNotifications)
stores/ (Pinia : auth, point, transactionQueue)
plugins/ (offline-sync.client.ts, pwa.client.ts)
```

Détail complet fichier par fichier : voir `zola-architecture-technique.md`.
Schéma DB complet (types, index) : voir `zola-schema-db.md` et `docs/SPECS.md`.

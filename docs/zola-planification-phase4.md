# Zola — Planification (Phase 4)

Estimations en jours-homme solo (rnexx), à ajuster selon la disponibilité réelle entre les autres projets de l'agence. Le graphe de dépendances entre modules a été validé juste avant ce document — cette planification en découle directement, dans le même ordre.

---

## 4.1 — Découpage en tickets par module

### Épic 0 — Setup et infrastructure (3j)
- T0.1 Init Laravel 11 + config MySQL + Sanctum — 0.5j
- T0.2 Init Nuxt 3 + Tailwind + tokens de marque (couleurs, Inter) — 1j
- T0.3 Config `@vite-pwa/nuxt` (manifest, icônes, service worker de base) — 0.5j
- T0.4 Environnement de dev (Docker ou local) — 1j

### Épic 1 — Authentification et comptes (5j)
- T1.1 Migration + modèle `User`, enums rôle/statut — 0.5j
- T1.2 Endpoint login (email/téléphone) + token Sanctum — 1j
- T1.3 Page Connexion connectée à l'API — 0.5j
- T1.4 Mot de passe oublié (flux OTP) — 1j
- T1.5 Création de compte Owner (statut `pending`) — 0.5j
- T1.6 Page Profil (infos + changement mot de passe) — 1j
- T1.7 Middleware `EnsureRole` — 0.5j

### Épic 2 — Points et float (cœur) (5j)
- T2.1 Migration + modèle `Point` — 0.5j
- T2.2 `FloatCalculationService` (recalcul soldes depuis le journal) — 1j
- T2.3 Endpoint création de point + `FloatMovement` initial — 1j
- T2.4 Page Création de point (choix moi-même/superviseur, déclaration float) — 1j
- T2.5 Dashboard Owner solo (= détail point unique) — 1j
- T2.6 Dashboard Owner multi (liste points, KPIs) — 0.5j

### Épic 3 — Infrastructure offline (4j)
- T3.1 Config IndexedDB (`idb`) + store `pending_transactions` — 1j
- T3.2 Composable `useOfflineQueue` — 1.5j
- T3.3 Plugin `offline-sync` (écoute online/offline) — 1j
- T3.4 Badge "en attente de synchronisation" — 0.5j

### Épic 4 — Transactions et vérification (6j)
- T4.1 `OperatorDetectionService` (préfixes en dur) + tests unitaires — 0.5j
- T4.2 `CampayService` (token permanent, `getHolderInfo`, gestion erreurs/timeout) — 1j
- T4.3 Endpoint vérification (proxy Campay) — 0.5j
- T4.4 Endpoint transactions (avec `idempotency_key`) — 1j
- T4.5 Page saisie transaction (suggestions et montants dynamiques) — 1.5j
- T4.6 Vérification bénéficiaire inline (3 états) — 1j
- T4.7 Page reçu / confirmation avec ruban de risque — 0.5j

### Épic 5 — Réapprovisionnement (2j)
- T5.1 Endpoint `float_movements` — 0.5j
- T5.2 Page réapprovisionnement (aperçu avant/après) — 1.5j

### Épic 6 — Clôture et contrôle de caisse (4j)
- T6.1 Endpoint `cash_closings` (calcul des 3 écarts) — 1j
- T6.2 Page clôture de caisse — 0.5j
- T6.3 Notification déclenchée si écart — 0.5j
- T6.4 Endpoint + page contrôle de caisse (vue owner) — 1.5j
- T6.5 Résolution du contrôle — 0.5j

### Épic 7 — Équipe (2j)
- T7.1 Endpoint invitation/retrait superviseur (mot de passe direct) — 1j
- T7.2 Page Équipe + modal + partage WhatsApp — 1j

### Épic 8 — Rapports et historique (3j)
- T8.1 Endpoint KPIs + comparaison par point — 1j
- T8.2 Page Rapports (période personnalisée, répartitions) — 1j
- T8.3 Endpoint + page Historique (recherche, filtres, export) — 1j

### Épic 9 — Notifications in-app (1.5j)
- T9.1 Table + endpoint notifications — 0.5j
- T9.2 Page notifications + badge cloche — 1j

### Épic 10 — Admin (1.5j)
- T10.1 Endpoint activation/suspension de compte — 0.5j
- T10.2 Page Admin comptes — 1j

### Épic 11 — QA et déploiement (3j)
- T11.1 Tests bout-en-bout des parcours critiques — 1.5j
- T11.2 Déploiement VPS (Laravel Forge ou manuel) + build Nuxt — 1j
- T11.3 Recette avec 1-2 points pilotes réels — 0.5j

**Total estimé : 41 jours-homme.**

---

## 4.2 — Graphe de dépendances

Voir le diagramme validé juste avant ce document. Résumé de la logique :

- **Setup** bloque tout le reste (rien ne peut commencer avant)
- **Auth** bloque tout (chaque écran suivant nécessite un utilisateur authentifié)
- **Points & float** et **Infrastructure offline** peuvent être développés en parallèle une fois Auth terminé
- **Transactions** dépend des deux (a besoin du modèle Point ET de la queue offline) ; **Réapprovisionnement** ne dépend que de Points & float
- **Clôture & contrôle** dépend à la fois de Transactions et Réapprovisionnement (le calcul d'écart a besoin des deux journaux)
- **Équipe, Rapports, Notifications, Admin** peuvent être menés en parallèle une fois la Clôture posée — aucun ne dépend des autres
- **QA & déploiement** ferme la marche, dépend de tout

---

## 4.3 — Planning de sprints avec jalons de test

| Sprint | Contenu | Jalon de test (critère de passage) |
|---|---|---|
| **1** | Épic 0 + Épic 1 + **table/service Notifications** (avancé depuis l'Épic 9) | Un utilisateur peut créer un compte, se connecter (email et téléphone), réinitialiser son mot de passe (flux à 3 étapes : OTP puis reset_token), modifier son profil. |
| **2** | Épic 2 + début Épic 3 + **création/liste de superviseurs** (avancé depuis l'Épic 7) | Un owner crée un point — soit lui-même, soit en assignant un superviseur existant — déclare son float initial, voit ses soldes affichés correctement recalculés (pas codés en dur côté front). |
| **3** | Fin Épic 3 + Épic 4 | Une transaction complète fonctionne de bout en bout contre l'environnement demo Campay, **et** en coupant le réseau manuellement (mode avion) : la transaction reste en file, se synchronise au retour du réseau, sans doublon. |
| **4** | Épic 5 + Épic 6 | Cycle complet : réapprovisionnement enregistré, clôture avec écart volontairement provoqué, notification reçue par l'owner (table déjà en place depuis le Sprint 1), contrôle et résolution effectués. |
| **5** | Épic 7 (page équipe uniquement, le backend est déjà fait) + Épic 8 | Un owner consulte les rapports et l'historique avec des filtres qui donnent des résultats exacts sur des données de test connues. |
| **6** | Épic 9 (page uniquement) + Épic 10 + Épic 11 | Recette complète sur les 4 parcours (Owner solo, Owner multi, Superviseur, Admin), déploiement sur le VPS, **et test réel avec 1 à 2 points pilotes** avant tout lancement plus large. |

**Correctifs issus de l'audit croisé maquettes/documentation** : la table de notifications et la création de superviseurs ont été avancées respectivement aux Sprints 1 et 2, parce que d'autres tickets plus tardifs en dépendent directement (la notification d'écart de caisse au Sprint 4, la recherche de superviseur à la création d'un point au Sprint 2). Le détail par ticket est dans `PROMPTS.md`, qui fait foi en cas de divergence avec ce tableau récapitulatif.

**Note sur le rythme** : ces sprints supposent des semaines pleinement dédiées à Zola. Vu que rnexx mène aussi d'autres projets d'agence en parallèle, le calendrier réel s'étalera probablement au-delà de 6 semaines — le découpage en jours-homme reste la mesure fiable, à répartir selon la disponibilité réelle plutôt que sur un calendrier hebdomadaire rigide.

**Rappel du principe acté en Phase 0** : ce planning n'est pas gravé dans le marbre. Si un sprint révèle un problème (ex : l'API Campay se comporte différemment de la doc en conditions réelles), on ajuste — pas de dogme sur l'ordre, seulement sur la logique de dépendance qui, elle, reste vraie.

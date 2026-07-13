# Roadmap Zola — Feuille de route V1

> Source détaillée des tickets : [docs/PROMPTS.md](docs/PROMPTS.md)  
> Planification : [docs/zola-planification-phase4.md](docs/zola-planification-phase4.md)  
> Suivi en temps réel : [MEMORY.md](MEMORY.md)

---

## Vision

Digitaliser la gestion des points de dépôt/retrait Orange Money et MTN MoMo au Cameroun — visibilité de la liquidité en temps réel, vérification bénéficiaire Campay, mode hors-ligne idempotent, zéro cahier papier.

**Stack** : Laravel 11 (API) · Nuxt 3 (PWA) · MySQL 8

**Estimation** : 41 jours-homme · 45 tickets · 6 sprints

---

## Jalons par sprint

| Sprint | Contenu principal | Jalon de test |
|---|---|---|
| **1** | Setup, Auth, Notifications (table) | Compte créé, connexion, MDP oublié, profil |
| **2** | Points, float, équipe (backend) | Point créé, float initial, soldes recalculés |
| **3** | Offline, transactions, Campay | Transaction + sync mode avion sans doublon |
| **4** | Réappro, clôture, contrôle | Cycle écart → notification → résolution |
| **5** | Équipe (UI), rapports, historique | Filtres exacts sur données de test |
| **6** | Notifications UI, admin, QA, déploiement | 4 parcours E2E + VPS + pilotes réels |

---

## Phase 0 — Gouvernance (hors PROMPTS.md)

| ID | Ticket | Statut |
|---|---|---|
| BOOT-00 | MEMORY.md, roadmap.md, INSTRUCTIONS/, init Git | Terminé |

---

## Phase 1 — Setup + Authentification (Sprint 1)

| ID | Ticket | Est. | Statut |
|---|---|---|---|
| INF-01 | Initialiser Laravel 11 + Sanctum | 0.5j | Terminé |
| INF-02 | Configurer MySQL | 0.5j | Terminé |
| INF-03 | Initialiser Nuxt 3 + PWA + Tailwind | 1j | Terminé |
| INF-04 | Environnement de dev (Docker/local) | 1j | Terminé |
| INF-05 | Pest + Vitest (TDD) | 0.5j | À faire |
| AUTH-01 | Migration + modèle User | 0.5j | À faire |
| AUTH-02 | Endpoint login [TDD] | 1j | À faire |
| AUTH-03 | Page Connexion (Nuxt) | 0.5j | À faire |
| AUTH-04 | Mot de passe oublié (3 endpoints) [TDD] | 1j | À faire |
| AUTH-05 | Création compte Owner | 0.5j | À faire |
| AUTH-06 | Page Profil | 1j | À faire |
| AUTH-07 | Middleware EnsureRole | 0.5j | À faire |
| NO-01 | Table + endpoint notifications | 0.5j | À faire |

---

## Phase 2 — Points et float (Sprint 2)

| ID | Ticket | Est. | Statut |
|---|---|---|---|
| PT-01 | Migration + modèle Point | 0.5j | À faire |
| PT-02 | FloatCalculationService [TDD] | 1j | À faire |
| PT-03 | POST /points [TDD] | 1j | À faire |
| EQ-01 | Équipe backend [TDD] | 1j | À faire |
| PT-04 | Page création point | 1j | À faire |
| PT-05 | Dashboard Owner solo | 1j | À faire |
| PT-06 | Dashboard Owner multi | 0.5j | À faire |

---

## Phase 3 — Offline + Transactions (Sprint 3)

| ID | Ticket | Est. | Statut |
|---|---|---|---|
| OFF-01 | IndexedDB | 1j | À faire |
| OFF-02 | useOfflineQueue | 1.5j | À faire |
| OFF-03 | Plugin offline-sync | 1j | À faire |
| OFF-04 | Badge synchronisation | 0.5j | À faire |
| TXN-01 | OperatorDetectionService [TDD] | 0.5j | À faire |
| TXN-02 | CampayService [TDD] | 1j | À faire |
| TXN-03 | Endpoint vérification | 0.5j | À faire |
| TXN-04 | Endpoint transactions [TDD] | 1j | À faire |
| TXN-05 | Page transaction | 1.5j | À faire |
| TXN-06 | Vérification inline | 1j | À faire |
| TXN-07 | Page confirmation | 0.5j | À faire |

---

## Phase 4 — Réappro + Clôture (Sprint 4)

| ID | Ticket | Est. | Statut |
|---|---|---|---|
| RE-01 | Endpoint float_movements [TDD] | 0.5j | À faire |
| RE-02 | Page réapprovisionnement | 1.5j | À faire |
| CL-01 | Endpoint cash_closings [TDD] | 1j | À faire |
| CL-02 | Page clôture | 0.5j | À faire |
| CL-03 | Notification écart | 0.5j | À faire |
| CL-04 | Contrôle de caisse | 1.5j | À faire |
| CL-05 | Résolution contrôle | 0.5j | À faire |

---

## Phase 5 — Équipe UI + Rapports (Sprint 5)

| ID | Ticket | Est. | Statut |
|---|---|---|---|
| EQ-02 | Page équipe | 1j | À faire |
| RA-01 | Endpoint rapports | 1j | À faire |
| RA-02 | Page rapports | 1j | À faire |
| RA-03 | Historique transactions | 1j | À faire |

---

## Phase 6 — Finalisation (Sprint 6)

| ID | Ticket | Est. | Statut |
|---|---|---|---|
| NO-02 | Page notifications | 1j | À faire |
| AD-01 | Endpoint admin | 0.5j | À faire |
| AD-02 | Page admin | 1j | À faire |
| QA-01 | Tests E2E Playwright | 1.5j | À faire |
| QA-02 | Déploiement VPS | 1j | À faire |
| QA-03 | Recette pilote | 0.5j | À faire |

---

## Hors scope V1 (refus volontaires)

- Commission / marge automatique
- Rôle Agent
- Table de préfixes opérateurs en base
- Archivage de points (colonne existe, pas d'endpoint)
- Notifications push FCM (V2)
- Canal `database` natif Laravel pour notifications

---

## Documents de référence

| Fichier | Rôle |
|---|---|
| [INSTRUCTIONS/INSTRUCTION.md](INSTRUCTIONS/INSTRUCTION.md) | Instructions projet |
| [INSTRUCTIONS/CAMPAY-API.md](INSTRUCTIONS/CAMPAY-API.md) | Intégration Campay |
| [docs/CONTEXT.md](docs/CONTEXT.md) | Vision produit |
| [docs/SPECS.md](docs/SPECS.md) | Contrat technique |
| [docs/AGENTS.md](docs/AGENTS.md) | Règles agent Cursor |

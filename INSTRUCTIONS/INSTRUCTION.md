# INSTRUCTIONS — Projet Zola

> Document de référence principal pour tout développement sur Zola.  
> Toujours lire **avant** de coder un ticket.

---

## 1. Qu'est-ce que Zola ?

Zola digitalise la gestion des points de dépôt/retrait **Orange Money** et **MTN MoMo** au Cameroun.

**Objectifs V1** :
- Visibilité de la liquidité (cash, OM, MoMo) en temps réel
- Vérification du bénéficiaire via Campay avant chaque transaction
- Mode hors-ligne avec synchronisation idempotente
- Clôture et réconciliation de caisse (3 pots)
- Gestion multi-points avec superviseurs

**Stack** : Laravel 11 (API pure, Sanctum) · Nuxt 3 (PWA) · MySQL 8

---

## 2. Documents obligatoires

Lire **entièrement** avant chaque ticket :

| Document | Contenu |
|---|---|
| [docs/CONTEXT.md](../docs/CONTEXT.md) | Vision, rôles, cycle float, design system |
| [docs/SPECS.md](../docs/SPECS.md) | Schéma DB, endpoints API, règles métier |
| [docs/PROMPTS.md](../docs/PROMPTS.md) | Tickets détaillés avec validations |
| [docs/zola-schema-db.md](../docs/zola-schema-db.md) | DDL MySQL exact |
| [docs/zola-architecture-technique.md](../docs/zola-architecture-technique.md) | Arborescence code |
| [INSTRUCTIONS/CAMPAY-API.md](CAMPAY-API.md) | Intégration Campay |
| [MEMORY.md](../MEMORY.md) | Journal de suivi du projet |
| [roadmap.md](../roadmap.md) | Feuille de route |

---

## 3. Rôles utilisateurs

| Rôle | Périmètre |
|---|---|
| `admin` | Activation/suspension comptes owner uniquement |
| `owner` | Tous ses points : transactions, caisse, équipe, rapports |
| `superviseur` | Opérations sur le(s) point(s) assigné(s) |

**Règle** : `owner_id` et `supervisor_id` coexistent — l'owner ne perd jamais ses droits.

---

## 4. Règles non négociables

1. Montants en `DECIMAL(12,0)` — jamais `FLOAT`/`DOUBLE`.
2. Soldes `points.*_balance` recalculés uniquement par `FloatCalculationService`.
3. Préfixes opérateurs codés en dur (`OperatorDetectionService`), pas en DB.
4. Campay appelé **uniquement** depuis Laravel — voir [CAMPAY-API.md](CAMPAY-API.md).
5. `idempotency_key` UUID v4 obligatoire sur chaque transaction (client).
6. `lockForUpdate()` sur `Point` pour toute opération de solde.
7. `cash_controls` n'existe pas — fusionné dans `cash_closings`.
8. Mots de passe, OTP, reset_token toujours hashés (`Hash::make()`).
9. Format erreur API : `{"error":"CODE","message":"...","details":{}}`.
10. TDD obligatoire pour toute logique métier (Pest backend, Vitest frontend).

---

## 5. Méthode de travail

### Par ticket (voir [docs/PROMPTS.md](../docs/PROMPTS.md))

1. Lire les docs requis.
2. Mettre à jour [MEMORY.md](../MEMORY.md) (ticket courant).
3. Écrire les tests d'abord (si logique métier).
4. Confirmer l'échec rouge.
5. Implémenter le minimum.
6. Valider (tests + commandes du ticket).
7. Mettre à jour MEMORY.md (résultats).
8. Commit + push vers https://github.com/Ryan-Inventor/zola

### Confirmation de fin de ticket

```
✅ [ID-TICKET] Done — [résumé validation]
```

---

## 6. Structure du projet

```
Zola/
├── docs/                 # Maquettes HTML (16 écrans) + documentation
├── INSTRUCTIONS/         # Ce dossier
├── zola-api/             # Laravel 11
├── zola-web/             # Nuxt 3 PWA
├── MEMORY.md
└── roadmap.md
```

---

## 7. Design system (rappel)

| Token | Valeur |
|---|---|
| Orange | `#F56001` |
| Encre | `#0A0A0A` |
| Slate | `#5C5C5E` |
| Mist | `#F4F4F5` |
| Succès | `#1E8E5A` |
| Alerte | `#D14343` |
| Info | `#B8860B` |

Police : **Inter**, chiffres tabulaires (`font-feature-settings: "tnum" 1`).

Reprendre **exactement** les maquettes HTML de `docs/` pour l'UI.

---

## 8. Hors scope V1

- Commission automatique
- Rôle Agent
- Table préfixes opérateurs
- Archivage points (endpoint)
- FCM push notifications
- SMS OTP réel (peut être simulé/loggé en V1)

---

## 9. Correspondances maquettes ↔ API

| Maquette | API/DB |
|---|---|
| `om` / `Orange` | `orange_money` |
| `momo` / `MTN` | `mtn_momo` |
| `found` | `verified` |
| `error` | `not_found` |
| `offline` | `offline_unverified` |

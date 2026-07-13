# MEMORY.md — Journal de suivi du projet Zola

> Fichier vivant mis à jour à chaque ticket.  
> Référence tickets : [docs/PROMPTS.md](docs/PROMPTS.md)  
> Dépôt GitHub : https://github.com/Ryan-Inventor/zola

---

## État global

| Champ | Valeur |
|---|---|
| **Dernière mise à jour** | 2026-07-13 |
| **Ticket courant** | BOOT-00 (en cours) |
| **Sprint** | 1 — Setup + Auth |
| **Prochain ticket** | INF-01 |
| **Tickets terminés** | 0 / 45 |

### Structure cible du monorepo

```
Zola/
├── docs/              # Maquettes HTML + documentation (existant)
├── INSTRUCTIONS/      # Instructions projet + Campay
├── zola-api/          # Laravel 11 API (à créer — INF-01)
├── zola-web/          # Nuxt 3 PWA (à créer — INF-03)
├── MEMORY.md          # Ce fichier
└── roadmap.md         # Feuille de route
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
| **Statut** | 🔄 En cours |
| **Objectif** | Créer MEMORY.md, roadmap.md, INSTRUCTIONS/, initialiser Git |
| **Références** | Plan d'exécution, docs existants |
| **Décisions** | Commit + push à chaque ticket validé ; journal détaillé dans MEMORY.md |

#### Fichiers créés
- `MEMORY.md`
- `roadmap.md`
- `INSTRUCTIONS/INSTRUCTION.md`
- `INSTRUCTIONS/CAMPAY-API.md`

#### Validations
- [ ] Fichiers de gouvernance créés
- [ ] Dépôt Git initialisé
- [ ] Remote GitHub configuré
- [ ] Premier commit + push

#### Notes
- `roadmap.md` et `INSTRUCTIONS/` étaient absents du repo ; créés à partir de `docs/`.
- Le dépôt GitHub https://github.com/Ryan-Inventor/zola était vide au démarrage.

---

### INF-01 — Initialiser Laravel 11

| Champ | Détail |
|---|---|
| **Statut** | ⏳ En attente |
| **Objectif** | API Laravel 11 + Sanctum, réponses JSON sur `/api/*` |
| **Références** | docs/PROMPTS.md, docs/SPECS.md §7 |

#### Tests / validations prévues
- `curl http://localhost:8000/api/v1/test` → JSON d'erreur, jamais HTML

#### Fichiers prévus
- `zola-api/` (projet Laravel complet)
- `.env`, `config/sanctum.php`, `config/cors.php`, gestion exceptions API

---

## Historique des commits

| Date | Ticket | Message | SHA |
|---|---|---|---|
| — | — | — | — |

---

## Points d'attention / risques

| ID | Sujet | Statut |
|---|---|---|
| R-01 | Dépôt Git non initialisé au démarrage | En résolution (BOOT-00) |
| R-02 | Comportement Campay numéro non enregistré à valider manuellement (TXN-02) | Ouvert |
| R-03 | OTP V1 peut être simulé/loggé (pas SMS réel) | À confirmer |

---

## Backlog rapide (ordre PROMPTS.md)

Sprint 1 : INF-01 → INF-02 → INF-03 → INF-04 → INF-05 → AUTH-01 → AUTH-02 → … → NO-01

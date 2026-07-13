# CLAUDE.md — Règles pour Claude Code sur le projet Zola

## Règle d'or

**Avant tout ticket, lire entièrement `docs/CONTEXT.md` et `docs/SPECS.md`.** Ne jamais coder à partir d'une supposition — tout ce dont tu as besoin est dans ces deux fichiers ou dans le ticket lui-même.

---

## Qu'est-ce que Zola, en une phrase

Zola digitalise la gestion des points de dépôt/retrait Orange Money et MTN MoMo au Cameroun — visibilité de la liquidité en temps réel, vérification du bénéficiaire avant chaque transaction, et zéro cahier papier.

Stack : **Laravel 11 (API) · Nuxt 3 (PWA) · MySQL 8**

---

## Règles non-négociables (violations = bug critique, pas un détail)

1. **Tout montant est `DECIMAL(12,0)`.** Jamais de `FLOAT`/`DOUBLE` sur une colonne monétaire. Le FCFA n'a pas de décimales.
2. **Les soldes de `points` (`cash_balance`, `om_balance`, `momo_balance`) ne sont JAMAIS écrits directement** par un contrôleur métier. Ils sont recalculés par `FloatCalculationService` à partir du journal (`transactions` + `float_movements`). Toute transaction ou mouvement de float doit passer par ce service pour mettre à jour le cache de solde.
3. **Les préfixes opérateurs sont une constante applicative codée en dur**, jamais une table en base. Voir `OperatorDetectionService` dans `docs/SPECS.md` section 3 pour les valeurs exactes.
4. **Campay n'est appelé QUE depuis Laravel**, jamais depuis Nuxt. Le token Campay ne doit jamais transiter côté client.
5. **Toute transaction possède un `idempotency_key` (UUID) généré côté client.** L'API doit être idempotente sur ce champ — un même UUID renvoyé deux fois ne doit jamais créer deux transactions. C'est le cœur de la sécurité du mode hors-ligne.
6. **`Point.supervisor_id` et `Point.owner_id` ne s'excluent jamais.** L'owner garde toujours ses pleins pouvoirs sur tous ses points, même quand un superviseur est assigné. Ne jamais coder de logique de type "si supervisor_id alors owner ne peut plus...".
7. **Tous les rôles sont dans une seule table `users`** avec un champ `role` enum (`admin`, `owner`, `superviseur`). Ne jamais créer de table séparée par rôle.
8. **Toute réponse d'erreur API suit le format** : `{"error": "CODE", "message": "...", "details": {}}`.
9. **Toute opération qui lit un solde puis l'écrit (transaction, réapprovisionnement, clôture) doit verrouiller la ligne `Point` concernée** (`lockForUpdate()` dans une `DB::transaction()`). Deux opérations simultanées sur le même point ne doivent jamais se marcher dessus — c'est particulièrement critique avec le mode hors-ligne, où plusieurs appareils peuvent synchroniser en même temps.
10. **`cash_controls` n'existe pas comme table séparée** — ses colonnes (`reviewed_by_user_id`, `decision`, `resolved_at`) sont directement sur `cash_closings`. Si tu vois une référence à `CashControlController` ou `CashControl.php` dans un document, c'est un résidu obsolète corrigé lors de l'audit — utilise `CashClosingController`.
11. **Développement en TDD, sans exception, pour tout ce qui contient de la logique métier.** Cycle rouge-vert-refactor strict :
    - **Rouge** : écrire le(s) test(s) d'abord, à partir des règles de `docs/SPECS.md`. Les exécuter, confirmer qu'ils échouent (sinon le test ne teste rien).
    - **Vert** : écrire le minimum de code pour que les tests passent. Pas de fonctionnalité non testée ajoutée "au passage".
    - **Refactor** : nettoyer une fois les tests verts, sans jamais casser le vert.
    - Concerné : tout Service, tout Controller avec de la logique (pas juste du CRUD trivial), toute règle de calcul (float, écarts, idempotence, seuils).
    - Pas concerné : pages Nuxt purement visuelles sans logique (reprises directement des maquettes), fichiers de config.
    - **Outils** : Pest (PHP, Laravel) pour le backend · Vitest + Vue Test Utils pour les tests unitaires/composants Nuxt · Playwright pour les tests E2E (déjà prévu en QA-01).
    - Un ticket n'est jamais confirmé "Done" sans que ses tests existent et soient passés au vert — la confirmation doit citer la commande de test exécutée, pas juste "curl" manuel.
12. **Tout mot de passe, OTP et reset_token est stocké hashé (`Hash::make()`), jamais en clair.** Ça inclut `users.password`, `password_resets.otp_code_hash` et `password_resets.reset_token_hash`. Le seul moment où un reset_token existe en clair, c'est dans la réponse HTTP retournée une fois au client — jamais en base, jamais dans un log.

---

## Format de confirmation attendu

À la fin de chaque ticket, exécute les commandes de validation listées dans `PROMPTS.md`, puis confirme avec :

```
✅ [ID-TICKET] Done — [résumé en une ligne de ce qui a été vérifié]
```

Ne jamais déclarer un ticket terminé sans avoir exécuté sa validation.

---

## Ce qu'il ne faut jamais faire

- Ne jamais ajouter de fonctionnalité non listée dans `docs/SPECS.md` (pas de commission automatique, pas de rôle Agent, pas de table de préfixes en base — ce sont des refus volontaires, pas des oublis).
- Ne jamais court-circuiter `FloatCalculationService` pour "aller plus vite".
- Ne jamais committer de token Campay ou de secret dans le code — uniquement `.env`.
- Ne jamais passer au ticket suivant si la validation du ticket courant échoue.

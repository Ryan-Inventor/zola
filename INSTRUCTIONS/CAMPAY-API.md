# CAMPAY-API — Intégration Campay pour Zola

> Référence complète : [docs/campay.md](../docs/campay.md)  
> Spécifications Zola : [docs/SPECS.md](../docs/SPECS.md) §4

---

## 1. Usage dans Zola

Campay sert **uniquement** à la vérification du titulaire d'un numéro de téléphone avant une transaction (dépôt/retrait).

- Appel **exclusivement côté Laravel** (`CampayService`)
- Le token ne transite **jamais** côté Nuxt/client
- Nuxt appelle `POST /points/{id}/verify-beneficiary` (proxy Laravel)

---

## 2. Environnements

| Environnement | Base URL |
|---|---|
| Démo (dev) | `https://demo.campay.net` |
| Production | À confirmer auprès de Campay avant QA-02 |

Variables `.env` :

```env
CAMPAY_BASE_URL=https://demo.campay.net
CAMPAY_TOKEN=votre_token_permanent
```

Inscription démo : https://demo.campay.net

---

## 3. Authentification (V1 — token permanent)

Méthode retenue pour Zola V1 : **token permanent** (APP KEYS de l'application Campay).

```
Authorization: Token {CAMPAY_TOKEN}
Content-Type: application/json
```

Ne pas utiliser le flux token temporaire (`/api/token/`) en V1 sauf décision contraire.

---

## 4. Endpoint utilisé : Phone Number Holder Info

```
GET {CAMPAY_BASE_URL}/api/holder_info/?phone_number=237XXXXXXXXX
```

### Paramètres

| Param | Description |
|---|---|
| `phone_number` | Numéro **avec** indicatif pays `237` + 9 chiffres locaux |

**Important** : Laravel préfixe `237` au numéro local saisi par l'agent. Le client Nuxt envoie le numéro local (9 chiffres).

### Réponse succès (200)

```json
{
  "full_name": "JOHN DOE"
}
```

### Timeout

**6 secondes** (`Http::timeout(6)`). Au-delà → statut interne `offline_unverified`.

---

## 5. Codes d'erreur Campay

| Code | Description | Mapping Zola |
|---|---|---|
| ER101 | Numéro invalide (format 237...) | `not_found` |
| ER102 | Opérateur non supporté (MTN/Orange uniquement) | `not_found` |
| ER201 | Montant invalide (hors scope holder_info) | — |
| ER301 | Solde insuffisant (hors scope holder_info) | — |
| Timeout / erreur réseau | — | `offline_unverified` |
| 200 sans `full_name` | Numéro non enregistré (à valider manuellement) | `not_found` |

---

## 6. Mapping vers `verification_status` (DB)

| Statut interne | Signification UI |
|---|---|
| `verified` | Nom trouvé — maquette état `found` |
| `not_found` | Numéro introuvable ou opérateur non supporté — `error` |
| `offline_unverified` | Campay indisponible — `offline` |

Le code `CAMPAY_UNAVAILABLE` est un code d'erreur API interne ; côté transaction, on persiste `offline_unverified`, pas ce code brut.

---

## 7. Implémentation Laravel (TXN-02)

### Fichiers

- `config/campay.php` — `base_url`, `token` depuis `.env`
- `app/Services/CampayService.php` — `getHolderInfo(string $localPhone)`

### Tests (Pest + Http::fake)

- 200 + `full_name` → retourne le nom, URL contient `237` + numéro
- ER101 / ER102 → `not_found` (pas d'exception)
- Timeout → `offline_unverified`
- Header `Authorization: Token {token}` présent

### Test manuel complémentaire (obligatoire TXN-02)

Tester contre `demo.campay.net` avec un vrai numéro de test pour observer le comportement sur numéro non enregistré. Ajuster le mapping si le comportement réel diffère des tests mockés, puis mettre à jour `docs/SPECS.md`.

---

## 8. Vérification rétroactive (option architecture)

Lors de la synchronisation offline, si une transaction arrive avec `verification_status = offline_unverified`, Laravel peut retenter Campay et mettre à jour le statut si un nom est trouvé. À considérer en implémentation TXN-04.

---

## 9. Sécurité

- Token **uniquement** dans `.env` — jamais commité
- Jamais exposé dans les réponses API vers le frontend
- Jamais appel direct depuis Nuxt

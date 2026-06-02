# INSTALL — Glowza Store

## Prérequis serveur

- PHP 8.1+
- MySQL 5.7+ ou MariaDB 10.4+
- WP-CLI installé (`wp --version` pour vérifier)
- Accès SSH
- Composer (optionnel)

## Installation en 4 étapes

### 1. Cloner le repo

```bash
git clone https://github.com/tarikmed93-debug/glowza-store.git /var/www/glowza
cd /var/www/glowza
```

### 2. Créer le fichier .env

```bash
cp .env.example .env
nano .env   # Remplir TOUTES les variables
```

Variables obligatoires :
- `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_HOST`
- `SITE_URL` — ex: `https://glowza.fr`
- `ADMIN_USER`, `ADMIN_PASS`, `ADMIN_EMAIL`
- `SMTP_HOST`, `SMTP_USER`, `SMTP_PASS`

### 3. Lancer l'installation

```bash
bash scripts/install.sh
```

Le script installe WordPress, WooCommerce, active le child theme, configure SMTP, crée les catégories, les pages légales et la zone de livraison France.

⏱️ Durée : ~3–5 minutes.

### 4. Vérification

```bash
bash scripts/healthcheck.sh
```

---

## Configuration post-installation (interface admin)

### Stripe France
1. WooCommerce → Réglages → Paiements → Stripe
2. Coller `STRIPE_PUBLIC_KEY` et `STRIPE_SECRET_KEY`
3. Activer le mode "Production"
4. Configurer le webhook Stripe : `https://glowza.fr/wc-api/WC_Stripe`

### PayPal
1. WooCommerce → Réglages → Paiements → PayPal Payments
2. Connecter votre compte PayPal Business

### Pixels Analytics
1. WooCommerce → Pixels & Analytics (menu admin)
2. Remplir : GTM Container ID, Meta Pixel ID, TikTok Pixel ID
3. Vérifier avec les tests Playwright : `cd tests/playwright && npm test`

### DSers Setup
1. WooCommerce → DSers Setup (menu admin)
2. Copier l'URL webhook et le secret
3. Les coller dans DSers → Settings → Webhook

---

## Déploiement GitHub Actions (CI/CD)

### Secrets GitHub à configurer

Dans GitHub → Settings → Secrets → Actions :

| Secret | Valeur |
|---|---|
| `SSH_PRIVATE_KEY` | Clé SSH privée pour accéder au serveur |
| `SSH_HOST` | IP ou domaine du serveur |
| `SSH_USER` | Utilisateur SSH |
| `SSH_PORT` | Port SSH (défaut : 22) |
| `REMOTE_PATH` | Chemin wp-content sur le serveur |
| `WP_PATH` | Chemin racine WordPress |
| `SITE_URL` | https://glowza.fr |
| `WEBHOOK_SECRET` | Secret webhook DSers |

### Déployer

Chaque push sur `main` déclenche automatiquement le déploiement + les tests Playwright.

Pour déclencher manuellement : GitHub → Actions → Deploy Glowza → Run workflow.

---

## Mise à jour du thème / plugins

```bash
# En local : modifier le code, commit, push
git add theme/child/
git commit -m "fix: amélioration checkout mobile"
git push origin main
# → GitHub Actions déploie automatiquement
```

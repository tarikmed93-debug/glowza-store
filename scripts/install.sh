#!/bin/bash
# install.sh — Glowza Store : installation complète via WP-CLI
# Usage : bash scripts/install.sh
# Prérequis : WP-CLI installé, MySQL accessible, variables .env remplies

set -e

# ─── Variables (à remplir dans .env avant de lancer) ──────────────────────────
source "$(dirname "$0")/../.env" 2>/dev/null || {
  echo "❌  Fichier .env manquant. Copie .env.example → .env et remplis les variables."
  exit 1
}

: "${DB_NAME:?}"
: "${DB_USER:?}"
: "${DB_PASS:?}"
: "${DB_HOST:=localhost}"
: "${SITE_URL:?}"
: "${SITE_TITLE:=Glowza}"
: "${ADMIN_USER:?}"
: "${ADMIN_PASS:?}"
: "${ADMIN_EMAIL:?}"
: "${SMTP_HOST:?}"
: "${SMTP_USER:?}"
: "${SMTP_PASS:?}"
: "${SMTP_PORT:=587}"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  GLOWZA — Installation WordPress"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 1. Téléchargement WordPress FR
echo "→ Téléchargement WordPress (FR)..."
wp core download --locale=fr_FR --allow-root

# 2. wp-config.php
echo "→ Création wp-config.php..."
wp config create \
  --dbname="$DB_NAME" \
  --dbuser="$DB_USER" \
  --dbpass="$DB_PASS" \
  --dbhost="$DB_HOST" \
  --extra-php <<PHP
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('DISALLOW_FILE_EDIT', true);
define('WP_POST_REVISIONS', 3);
define('AUTOSAVE_INTERVAL', 300);
define('WP_MEMORY_LIMIT', '256M');
// Sécurité : désactiver XML-RPC si non nécessaire
define('XMLRPC_ENABLED', false);
PHP
  --allow-root

# 3. Installation base de données
echo "→ Installation base de données..."
wp core install \
  --url="$SITE_URL" \
  --title="$SITE_TITLE" \
  --admin_user="$ADMIN_USER" \
  --admin_password="$ADMIN_PASS" \
  --admin_email="$ADMIN_EMAIL" \
  --skip-email \
  --allow-root

# 4. Thème Storefront (parent) + notre child theme
echo "→ Installation thème Storefront..."
wp theme install storefront --allow-root

echo "→ Copie child theme Glowza..."
cp -r "$(dirname "$0")/../theme/child" wp-content/themes/glowza

wp theme activate glowza --allow-root

# 5. Plugins essentiels
echo "→ Installation plugins WooCommerce..."
wp plugin install woocommerce --activate --allow-root
wp plugin install woo-stripe-payment --activate --allow-root         # Stripe France
wp plugin install woocommerce-paypal-payments --activate --allow-root
wp plugin install wp-mail-smtp --activate --allow-root
wp plugin install cookie-notice --activate --allow-root               # Bandeau RGPD/CNIL
wp plugin install woocommerce-checkout-manager --allow-root           # Checkout 1 page

# 6. Nos mu-plugins custom
echo "→ Copie mu-plugins..."
cp "$(dirname "$0")/../mu-plugins/"*.php wp-content/mu-plugins/ 2>/dev/null || true
# Créer le dossier mu-plugins s'il n'existe pas
mkdir -p wp-content/mu-plugins

cp "$(dirname "$0")/../mu-plugins/glowza-smtp.php" wp-content/mu-plugins/
cp "$(dirname "$0")/../mu-plugins/glowza-dsers-statuses.php" wp-content/mu-plugins/
cp "$(dirname "$0")/../mu-plugins/glowza-performance.php" wp-content/mu-plugins/

# 7. Nos plugins custom
echo "→ Copie plugins custom..."
cp -r "$(dirname "$0")/../plugins/pixel-manager" wp-content/plugins/
cp -r "$(dirname "$0")/../plugins/dsers-ready" wp-content/plugins/
cp -r "$(dirname "$0")/../plugins/glowza-tweaks" wp-content/plugins/
wp plugin activate pixel-manager dsers-ready glowza-tweaks --allow-root

# 8. Configuration SMTP via WP-CLI
echo "→ Configuration SMTP..."
wp option update wpmailsmtp_options '{
  "mail": {
    "from_email": "'"$ADMIN_EMAIL"'",
    "from_name": "Glowza",
    "mailer": "smtp",
    "return_path": true
  },
  "smtp": {
    "host": "'"$SMTP_HOST"'",
    "port": '"$SMTP_PORT"',
    "encryption": "tls",
    "auth": true,
    "user": "'"$SMTP_USER"'",
    "pass": "'"$SMTP_PASS"'"
  }
}' --format=json --allow-root

# 9. Configuration WooCommerce de base
echo "→ Configuration WooCommerce..."
bash "$(dirname "$0")/setup-woo.sh"

# 10. Permaliens
echo "→ Activation permaliens..."
wp rewrite structure '/%postname%/' --allow-root
wp rewrite flush --allow-root

# 11. Pages légales
echo "→ Création pages légales..."
bash "$(dirname "$0")/setup-legal-pages.sh"

# 12. Suppression contenu par défaut
wp post delete 1 2 --force --allow-root 2>/dev/null || true  # Hello world + Sample page

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  ✅  Installation terminée !"
echo "  → Site : $SITE_URL"
echo "  → Admin : $SITE_URL/wp-admin"
echo "  → Lance setup-woo.sh si besoin de reconfigurer WooCommerce"
echo "  → Puis configure DSers : voir INSTALL.md"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

#!/bin/bash
set -e

# Démarrer Apache en arrière-plan
apache2-foreground &
APACHE_PID=$!

sleep 3

cd /var/www/html

# Installer WordPress si pas encore fait (SQLite, pas de MySQL à attendre)
if ! wp core is-installed --allow-root 2>/dev/null; then
  echo "🚀 Installation Glowza (WordPress + SQLite)..."

  SITE_URL="${WORDPRESS_SITE_URL:-http://localhost}"

  wp core install \
    --url="$SITE_URL" \
    --title="Glowza" \
    --admin_user="${WORDPRESS_ADMIN_USER:-admin}" \
    --admin_password="${WORDPRESS_ADMIN_PASSWORD:-glowza2024!}" \
    --admin_email="${WORDPRESS_ADMIN_EMAIL:-bonjour@glowza.fr}" \
    --skip-email \
    --allow-root

  # Thème
  wp theme install storefront --allow-root
  wp theme activate glowza --allow-root

  # Plugins
  wp plugin install woocommerce --activate --allow-root
  wp plugin install cookie-notice --activate --allow-root
  wp plugin activate pixel-manager dsers-ready glowza-tweaks --allow-root

  # Config WooCommerce
  wp option update woocommerce_currency EUR --allow-root
  wp option update woocommerce_default_country FR --allow-root
  wp option update woocommerce_email_from_name "Glowza" --allow-root
  wp option update woocommerce_calc_taxes yes --allow-root
  wp option update woocommerce_prices_include_tax yes --allow-root
  wp option update woocommerce_enable_guest_checkout yes --allow-root
  wp option update woocommerce_catalog_columns 3 --allow-root

  # Permaliens
  wp rewrite structure '/%postname%/' --allow-root
  wp rewrite flush --allow-root

  # Pages WooCommerce
  wp wc --user=admin tool run create_pages --allow-root 2>/dev/null || true

  # Catégories
  for cat in "Ensembles" "Accessoires" "Bijoux fantaisie" "Sacs & pochettes" "Coques téléphone" "Beauté & Lifestyle" "Produits viraux TikTok"; do
    wp term create product_cat "$cat" --allow-root 2>/dev/null || true
  done

  # Supprimer contenu par défaut
  wp post delete 1 2 --force --allow-root 2>/dev/null || true

  echo "✅ Glowza prêt sur $SITE_URL"
fi

wait $APACHE_PID

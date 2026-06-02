#!/bin/bash
# Entrypoint Docker — démarre Apache + installe WordPress si première fois
set -e

# Lance d'abord l'entrypoint officiel WordPress en arrière-plan
docker-entrypoint.sh apache2-foreground &
WP_PID=$!

# Attendre que MySQL soit prêt (max 60s)
echo "⏳ Attente MySQL..."
for i in $(seq 1 30); do
  if wp db check --allow-root --quiet 2>/dev/null; then
    echo "✅ MySQL OK"
    break
  fi
  sleep 2
done

# Installer WordPress si pas encore fait
if ! wp core is-installed --allow-root 2>/dev/null; then
  echo "🚀 Installation WordPress Glowza..."

  wp core install \
    --url="${WORDPRESS_SITE_URL:-http://localhost}" \
    --title="Glowza" \
    --admin_user="${WORDPRESS_ADMIN_USER:-admin}" \
    --admin_password="${WORDPRESS_ADMIN_PASSWORD:-glowza2024!}" \
    --admin_email="${WORDPRESS_ADMIN_EMAIL:-bonjour@glowza.fr}" \
    --skip-email \
    --allow-root

  # Thème
  wp theme install storefront --allow-root
  wp theme activate glowza --allow-root

  # Plugins WooCommerce
  wp plugin install woocommerce --activate --allow-root
  wp plugin install wp-mail-smtp --activate --allow-root
  wp plugin install cookie-notice --activate --allow-root

  # Nos plugins
  wp plugin activate pixel-manager dsers-ready glowza-tweaks --allow-root

  # Config WooCommerce de base
  wp option update woocommerce_currency EUR --allow-root
  wp option update woocommerce_default_country FR --allow-root
  wp option update woocommerce_email_from_name "Glowza" --allow-root
  wp option update woocommerce_email_from_address "bonjour@glowza.fr" --allow-root
  wp option update woocommerce_calc_taxes yes --allow-root
  wp option update woocommerce_prices_include_tax yes --allow-root
  wp option update woocommerce_enable_guest_checkout yes --allow-root
  wp option update woocommerce_catalog_columns 3 --allow-root

  # Permaliens
  wp rewrite structure '/%postname%/' --allow-root
  wp rewrite flush --allow-root

  # Pages WooCommerce
  wp wc --user=admin tool run create_pages --allow-root 2>/dev/null || true

  # Catégories produits
  wp term create product_cat "Ensembles" --allow-root 2>/dev/null || true
  wp term create product_cat "Accessoires" --allow-root 2>/dev/null || true
  wp term create product_cat "Bijoux fantaisie" --allow-root 2>/dev/null || true
  wp term create product_cat "Sacs & pochettes" --allow-root 2>/dev/null || true
  wp term create product_cat "Coques téléphone" --allow-root 2>/dev/null || true
  wp term create product_cat "Beauté & Lifestyle" --allow-root 2>/dev/null || true
  wp term create product_cat "Produits viraux TikTok" --allow-root 2>/dev/null || true

  # Supprimer contenu par défaut
  wp post delete 1 2 --force --allow-root 2>/dev/null || true

  echo "✅ Glowza installé sur ${WORDPRESS_SITE_URL:-http://localhost}"
  echo "   Admin : /wp-admin — user: ${WORDPRESS_ADMIN_USER:-admin}"
fi

# Rester au premier plan (Apache)
wait $WP_PID

#!/bin/bash
# setup-woo.sh — Configuration WooCommerce complète pour Glowza
set -e
source "$(dirname "$0")/../.env" 2>/dev/null || true

echo "→ Configuration générale WooCommerce..."

# Monnaie et localisation
wp option update woocommerce_currency EUR --allow-root
wp option update woocommerce_currency_pos left --allow-root
wp option update woocommerce_price_thousand_sep " " --allow-root
wp option update woocommerce_price_decimal_sep "," --allow-root
wp option update woocommerce_default_country FR --allow-root
wp option update woocommerce_allowed_countries specific --allow-root
wp option update woocommerce_specific_allowed_countries '["FR"]' --format=json --allow-root

# Adresse du magasin (placeholder — à remplacer)
wp option update woocommerce_store_address "1 Rue du Commerce" --allow-root
wp option update woocommerce_store_city "Paris" --allow-root
wp option update woocommerce_store_postcode "75001" --allow-root

# Pages WooCommerce
echo "→ Création pages WooCommerce..."
wp wc --user=admin tool run create_pages --allow-root 2>/dev/null || true

# Emails WooCommerce — from name et adresse
wp option update woocommerce_email_from_name "Glowza" --allow-root
wp option update woocommerce_email_from_address "bonjour@glowza.fr" --allow-root

# Taxes
wp option update woocommerce_calc_taxes yes --allow-root
wp option update woocommerce_prices_include_tax yes --allow-root
wp option update woocommerce_tax_display_shop incl --allow-root
wp option update woocommerce_tax_display_cart incl --allow-root

# Créer taux de TVA France 20%
wp wc tax create \
  --user=admin \
  --country=FR \
  --rate=20 \
  --name="TVA France 20%" \
  --shipping=true \
  --allow-root 2>/dev/null || true

# Livraison — zone France
echo "→ Création zone de livraison France..."
ZONE_ID=$(wp wc shipping_zone create --user=admin --name="France" --order=0 --porcelain --allow-root 2>/dev/null || echo "")

if [ -n "$ZONE_ID" ]; then
  # Ajouter FR à la zone
  wp wc shipping_zone_location create \
    --user=admin \
    --zone_id="$ZONE_ID" \
    --code=FR \
    --type=country \
    --allow-root 2>/dev/null || true

  # Méthode : livraison standard (gratuite dès 50€)
  wp wc shipping_zone_method create \
    --user=admin \
    --zone_id="$ZONE_ID" \
    --method_id=free_shipping \
    --allow-root 2>/dev/null || true

  # Méthode : frais de port standard
  wp wc shipping_zone_method create \
    --user=admin \
    --zone_id="$ZONE_ID" \
    --method_id=flat_rate \
    --allow-root 2>/dev/null || true
fi

# Checkout
wp option update woocommerce_enable_guest_checkout yes --allow-root
wp option update woocommerce_enable_checkout_login_reminder yes --allow-root
wp option update woocommerce_enable_signup_and_login_from_checkout yes --allow-root

# Stock
wp option update woocommerce_manage_stock yes --allow-root
wp option update woocommerce_notify_low_stock yes --allow-root
wp option update woocommerce_notify_no_stock yes --allow-root

# Avis produits
wp option update woocommerce_enable_reviews yes --allow-root
wp option update woocommerce_review_rating_required yes --allow-root

# Catalogue — affichage boutique
wp option update woocommerce_shop_page_display products --allow-root
wp option update woocommerce_category_archive_display products --allow-root
wp option update woocommerce_default_catalog_orderby popularity --allow-root
wp option update woocommerce_catalog_columns 3 --allow-root
wp option update woocommerce_catalog_rows 4 --allow-root

echo "→ Création statuts commandes custom DSers..."
# Les statuts custom sont créés par le mu-plugin glowza-dsers-statuses.php
# Ici on configure les options WooCommerce associées

# Activer le webhook tracking (configuré dans le plugin dsers-ready)
wp option update glowza_webhook_tracking_enabled yes --allow-root

echo "→ Création catégories produits Glowza..."
wp term create product_cat "Ensembles" --allow-root 2>/dev/null || true
wp term create product_cat "Accessoires" --allow-root 2>/dev/null || true
wp term create product_cat "Bijoux fantaisie" --allow-root 2>/dev/null || true
wp term create product_cat "Sacs & pochettes" --allow-root 2>/dev/null || true
wp term create product_cat "Coques téléphone" --allow-root 2>/dev/null || true
wp term create product_cat "Beauté & Lifestyle" --allow-root 2>/dev/null || true
wp term create product_cat "Produits viraux TikTok" --allow-root 2>/dev/null || true

echo "✅  WooCommerce configuré."

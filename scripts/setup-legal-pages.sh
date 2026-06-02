#!/bin/bash
# setup-legal-pages.sh — Crée les pages légales françaises dans WordPress
set -e

LEGAL_DIR="$(dirname "$0")/../legal"

create_page() {
  local TITLE="$1"
  local SLUG="$2"
  local FILE="$3"
  local CONTENT
  CONTENT=$(cat "$LEGAL_DIR/$FILE" 2>/dev/null || echo "Contenu à remplir.")
  wp post create \
    --post_type=page \
    --post_status=publish \
    --post_title="$TITLE" \
    --post_name="$SLUG" \
    --post_content="$CONTENT" \
    --user=admin \
    --allow-root 2>/dev/null || true
  echo "  ✓ Page \"$TITLE\" créée."
}

echo "→ Création pages légales..."
create_page "Mentions légales" "mentions-legales" "mentions-legales.md"
create_page "Conditions Générales de Vente" "cgv" "cgv.md"
create_page "Politique de confidentialité" "politique-confidentialite" "politique-rgpd.md"
create_page "Politique des cookies" "cookies" "cookies.md"
create_page "FAQ" "faq" "faq.md"
create_page "Contact" "contact" "contact.md"

# Associer la politique de confidentialité aux réglages WooCommerce
PRIVACY_PAGE_ID=$(wp post list --post_type=page --name="politique-confidentialite" --field=ID --allow-root 2>/dev/null | head -1)
if [ -n "$PRIVACY_PAGE_ID" ]; then
  wp option update wp_page_for_privacy_policy "$PRIVACY_PAGE_ID" --allow-root
fi

echo "✅  Pages légales créées."

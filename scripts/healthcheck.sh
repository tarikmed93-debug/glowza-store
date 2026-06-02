#!/bin/bash
# healthcheck.sh — Vérifie que la boutique est opérationnelle
set -e
source "$(dirname "$0")/../.env" 2>/dev/null || true

BASE="${SITE_URL:-https://glowza.fr}"
PASS=0
FAIL=0

check() {
  local DESC="$1"
  local URL="$2"
  local EXPECT="${3:-200}"
  local CODE
  CODE=$(curl -s -o /dev/null -w "%{http_code}" -L --max-time 10 "$URL")
  if [ "$CODE" = "$EXPECT" ]; then
    echo "  ✅  $DESC ($CODE)"
    PASS=$((PASS+1))
  else
    echo "  ❌  $DESC — attendu $EXPECT, obtenu $CODE — URL: $URL"
    FAIL=$((FAIL+1))
  fi
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  GLOWZA — Health Check"
echo "  Base URL : $BASE"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

check "Homepage"              "$BASE/"
check "Boutique"              "$BASE/boutique/"
check "Panier"                "$BASE/panier/"
check "Checkout"              "$BASE/commande/"
check "Mon compte"            "$BASE/mon-compte/"
check "Mentions légales"      "$BASE/mentions-legales/"
check "CGV"                   "$BASE/cgv/"
check "Politique RGPD"        "$BASE/politique-confidentialite/"
check "Cookies"               "$BASE/cookies/"
check "Sitemap XML"           "$BASE/sitemap.xml"
check "Robots.txt"            "$BASE/robots.txt"
check "WP-JSON (REST API)"    "$BASE/wp-json/wc/v3/products" 200

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Résultat : $PASS OK / $FAIL erreur(s)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
[ "$FAIL" -eq 0 ] || exit 1

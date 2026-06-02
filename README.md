# Glowza Store

Boutique dropshipping mode féminine & accessoires tendance — France.

**Stack :** WooCommerce + WordPress + Child theme custom + DSers (AliExpress) + Stripe France + PayPal

---

## Démarrage rapide

```bash
cp .env.example .env && nano .env
bash scripts/install.sh
bash scripts/healthcheck.sh
```

Voir [INSTALL.md](INSTALL.md) pour le guide complet.

## Structure

```
theme/child/        Child theme Glowza (design + UX)
mu-plugins/         SMTP, statuts DSers, perf
plugins/            Pixel Manager, DSers Ready
scripts/            install.sh, setup-woo.sh, healthcheck.sh
tests/playwright/   Tests E2E (checkout, pixels, tracking)
legal/              Pages légales FR (MD → importées dans WP)
```

## Jalons

| # | Contenu | Status |
|---|---------|--------|
| 0 | Fondations repo + scripts + child theme | ✅ |
| 1 | Design boutique mobile-first Glowza | ✅ |
| 2 | DSers statuts + webhook tracking + emails | ✅ |
| 3 | Stripe + PayPal (tests prod) | ⏳ accès serveur requis |
| 4 | Pixels GTM + Meta + TikTok | ✅ |
| 5 | SEO + Perf | ✅ |
| 6 | Pages légales FR | ✅ |
| 7 | Livraison finale + checklist | ✅ |

## Mise en prod

Voir [CHECKLIST-PROD.md](CHECKLIST-PROD.md).

# Checklist mise en production — Glowza

**À cocher avant de lancer les publicités.**

---

## ✅ Infrastructure

- [ ] Domaine `glowza.fr` acheté et DNS configuré (A record → IP serveur)
- [ ] SSL/HTTPS actif (certificat Let's Encrypt ou hébergeur)
- [ ] Cloudflare configuré : proxy activé, SSL "Full Strict"
- [ ] Page speed Cloudflare : "Standard" ou supérieur
- [ ] WordPress installé via `bash scripts/install.sh`
- [ ] `bash scripts/healthcheck.sh` → 0 erreur

---

## ✅ Boutique — Fonctionnel

- [ ] Child theme Glowza actif, pas d'erreur PHP
- [ ] Storefront (parent) installé
- [ ] 10+ produits importés avec photos, prix, description, variantes
- [ ] Catégories créées et assignées aux produits
- [ ] Page boutique `/boutique/` affiche les produits correctement
- [ ] Fiche produit : délai "2 à 4 semaines" visible
- [ ] Fiche produit : bouton "Ajouter au panier" fonctionnel
- [ ] Schema.org `<script type="application/ld+json">` présent sur les fiches
- [ ] Panier `/panier/` fonctionnel
- [ ] Checkout `/commande/` fonctionnel, tous les champs présents
- [ ] Message "Droit de rétractation 14 jours" visible au checkout

---

## ✅ Paiements

- [ ] Stripe France — mode Production actif (pas sandbox)
- [ ] Stripe — webhook configuré : `https://glowza.fr/wc-api/WC_Stripe`
- [ ] Stripe — test avec vraie carte, remboursée immédiatement
- [ ] PayPal — compte Business connecté, mode Production
- [ ] PayPal — test avec un achat réel, remboursé immédiatement
- [ ] Email de confirmation commande reçu après paiement test

---

## ✅ Emails transactionnels (SMTP)

- [ ] SMTP configuré (Brevo / Mailgun / Postmark, PAS phpmail)
- [ ] Email "Confirmation de commande" reçu (tester avec vraie commande)
- [ ] Email "Commande expédiée / tracking" reçu (tester via webhook DSers)
- [ ] From: "Glowza <bonjour@glowza.fr>"
- [ ] SPF + DKIM configurés sur le domaine glowza.fr
- [ ] Test anti-spam : pas en boîte spam

---

## ✅ DSers Integration

- [ ] Compte DSers créé sur dsers.com
- [ ] Compte AliExpress connecté dans DSers
- [ ] WooCommerce connecté à DSers (clé API)
- [ ] Produits importés depuis AliExpress via DSers
- [ ] Mapping produit WooCommerce ↔ fournisseur AliExpress fait
- [ ] URL webhook DSers configurée : `https://glowza.fr/wp-json/glowza/v1/dsers-tracking`
- [ ] Secret webhook noté dans WooCommerce → DSers Setup
- [ ] Test commande → DSers reçoit la commande → email tracking envoyé

---

## ✅ Pixels & Analytics

- [ ] GTM Container ID renseigné dans WooCommerce → Pixels & Analytics
- [ ] Meta Pixel ID renseigné
- [ ] TikTok Pixel ID renseigné
- [ ] Pixel Helper Chrome : event `PageView` détecté sur la homepage
- [ ] Pixel Helper : event `ViewContent` sur fiche produit
- [ ] Pixel Helper : event `AddToCart` après ajout au panier
- [ ] Pixel Helper : event `Purchase` sur la page confirmation
- [ ] TikTok Pixel Helper : mêmes events confirmés
- [ ] GTM Preview Mode : events GA4 déclenchés correctement
- [ ] `cd tests/playwright && npm test` → 0 test en échec

---

## ✅ SEO & Performance

- [ ] PageSpeed Insights mobile > 80 (fiche produit)
- [ ] PageSpeed Insights desktop > 90
- [ ] Images : format WebP ou optimisées JPEG, < 200 Ko
- [ ] `loading="lazy"` sur toutes les images hors ATF
- [ ] URLs propres : `/boutique/`, `/commande/`, `/mon-compte/`
- [ ] Sitemap XML accessible : `https://glowza.fr/wp-sitemap.xml`
- [ ] `robots.txt` accessible et correct
- [ ] Google Search Console : domaine ajouté, sitemap soumis

---

## ✅ Pages légales (Droit français)

- [ ] Page "Mentions légales" — SIRET / RCS rempli (pas de placeholder)
- [ ] Page "CGV" — délai rétractation 14j mentionné, délais livraison clairs
- [ ] Page "Politique de confidentialité" — RGPD conforme
- [ ] Page "Cookies" — liste des cookies mise à jour
- [ ] Bandeau cookies CNIL : apparaît à la première visite, mémorise le choix
- [ ] Liens légaux dans le footer
- [ ] WooCommerce → Réglages → Avancé → Politique de confidentialité configuré

---

## ✅ Mobile & UX

- [ ] Test complet sur iPhone (Safari) : homepage → produit → panier → checkout → confirmation
- [ ] Test complet sur Android (Chrome)
- [ ] Aucun élément rogné ou cassé sur mobile 375px
- [ ] Bouton "Ajouter au panier" sticky visible sur fiche produit mobile
- [ ] Formulaire checkout mobile : clavier numérique sur les champs téléphone/CP

---

## ✅ Sécurité

- [ ] `DISALLOW_FILE_EDIT` à `true` dans wp-config
- [ ] Préfixe table WP modifié (pas `wp_`)
- [ ] Mot de passe admin fort (20+ caractères)
- [ ] Connexion admin : URL personnalisée ou 2FA activé
- [ ] XML-RPC désactivé
- [ ] Version WordPress masquée dans le HTML

---

## ✅ Repo GitHub

- [ ] Tout le code custom commité sur `main`
- [ ] `.env` absent du repo (dans `.gitignore`)
- [ ] `wp-config.php` absent du repo
- [ ] GitHub Actions : dernier déploiement vert ✅
- [ ] README.md lisible et à jour

---

**Toutes les cases cochées → GO pour les premières publicités. 🚀**

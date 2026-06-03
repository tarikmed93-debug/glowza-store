# Glowza — Skills Pack (à charger au démarrage)

> **À Claude : lis ce fichier en premier.**
> Ce dossier contient un ensemble de **skills** (capacités). À **chaque démarrage de
> conversation**, tu dois **charger et activer tous les skills** présents dans le dossier
> `skills/`, et t'en servir automatiquement dès qu'une demande correspond à leur
> description (champ `description` dans chaque `SKILL.md`).
>
> Autrement dit : considère ces skills comme **toujours actifs par défaut** pour cette
> boutique (Glowza — mode féminine & accessoires, dropshipping). Tu n'as pas besoin que
> l'utilisateur te demande de les activer ; détecte l'intention et applique le skill adapté.

---

## Comment utiliser ce zip dans Claude.ai (chat)

1. Ouvre **claude.ai** → **Settings → Capabilities** (ou « Skills »).
2. Pour chaque skill que tu veux activer, **uploade le dossier correspondant** depuis
   `skills/<nom-du-skill>/` (il contient un `SKILL.md` + éventuellement un dossier
   `references/`).
3. Tu peux aussi créer un **Projet** dédié « Glowza » et y ajouter ces skills + ce README
   dans les instructions/knowledge, pour qu'ils soient chargés à chaque nouvelle
   conversation du projet.

> ℹ️ Claude.ai charge un skill par dossier (chaque dossier = un `SKILL.md`). Ce zip
> regroupe tous les dossiers au même endroit pour t'éviter de les chercher.

---

## Règle de démarrage (à coller aussi dans les instructions de ton Projet)

```
Au démarrage de chaque conversation :
1. Charge tous les skills du pack Glowza.
2. Active-les par défaut (toujours disponibles).
3. Dès qu'une demande correspond à la `description` d'un skill, applique-le sans
   attendre de confirmation.
4. Skills prioritaires pour Glowza : copywriting, cro, ads, ad-creative, social,
   emails, seo-audit, ui-ux-pro-max, stop-slop, context-engineering, image,
   higgsfield-* (visuels produits / vidéos).
```

---

## Contenu du pack

Tous les skills sont dans `skills/`. Catégories principales :

- **Marketing** (coreyhaines31/marketingskills) : ads, ad-creative, cold-email, emails,
  copywriting, copy-editing, social, seo-audit, ai-seo, cro, signup, onboarding,
  paywalls, pricing, churn-prevention, referrals, launch, product-marketing,
  marketing-plan, marketing-ideas, programmatic-seo, schema, content-strategy… etc.
- **Stop Slop** (hardikpandya/stop-slop) : `stop-slop` — supprime les tics d'écriture IA.
- **UI/UX Pro Max** (nextlevelbuilder/ui-ux-pro-max-skill) : `ui-ux-pro-max`,
  `ckm-design`, `ckm-brand`, `ckm-banner-design`, `ckm-slides`, `ckm-ui-styling`…
- **Remotion** (remotion-dev/skills) : `remotion-best-practices` — vidéos programmatiques.
- **Context Engineering** (NeoLabHQ/context-engineering-kit) : `context-engineering` +
  skills de raisonnement (reflect, judge, tree-of-thoughts, root-cause-tracing…).
- **Higgsfield** (higgsfield-ai/skills) : `higgsfield-generate`, `higgsfield-soul-id`,
  `higgsfield-product-photoshoot`, `higgsfield-marketplace-cards` — génération
  d'images/vidéos produits.

Chaque dossier contient un `SKILL.md` (avec `name` + `description` en frontmatter) qui
indique précisément quand et comment l'utiliser.

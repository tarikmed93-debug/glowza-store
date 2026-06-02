<?php get_header(); ?>

<!-- ░░░ HERO ░░░ -->
<section class="glowza-hero">
  <div class="glowza-hero__inner">
    <span class="glowza-hero__tag">✨ Nouvelle collection</span>
    <h1 class="glowza-hero__title">
      Ta mode,<br><span>ton style.</span>
    </h1>
    <p class="glowza-hero__subtitle">
      Pièces tendance sélectionnées pour toi — livrées partout en France.
    </p>
    <div class="glowza-hero__actions">
      <a href="<?= esc_url(wc_get_page_permalink('shop')) ?>" class="btn">
        Découvrir la boutique
      </a>
      <a href="#glowza-trending" class="btn btn--outline">
        Voir les tendances
      </a>
    </div>
    <div class="glowza-hero__trust">
      <span>🔒 Paiement sécurisé</span>
      <span>🚚 Suivi inclus</span>
      <span>↩️ Retour 14 jours</span>
    </div>
  </div>
</section>

<!-- ░░░ TOP BAR ANIMÉE ░░░ -->
<div class="glowza-top-bar">
  <span class="glowza-top-bar__message">🚚 Livraison en 2–4 semaines · Suivi inclus</span>
</div>

<!-- ░░░ CATÉGORIES PILLS ░░░ -->
<section class="glowza-section glowza-cats-section">
  <div class="glowza-container">
    <h2 class="glowza-section__title">Parcourir par catégorie</h2>
    <div class="glowza-cats-grid">
      <?php
      $cats = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'exclude'    => [get_option('default_product_cat')],
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
        'number'     => 7,
      ]);
      $icons = [
        'Ensembles'              => '👗',
        'Accessoires'            => '💎',
        'Bijoux fantaisie'       => '💍',
        'Sacs & pochettes'       => '👜',
        'Coques téléphone'       => '📱',
        'Beauté & Lifestyle'     => '✨',
        'Produits viraux TikTok' => '🔥',
      ];
      foreach ($cats as $cat) :
        $icon = $icons[$cat->name] ?? '🛍️';
        $link = get_term_link($cat);
        $thumb_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
        $thumb = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'glowza-square') : '';
      ?>
      <a href="<?= esc_url($link) ?>" class="glowza-cat-card">
        <div class="glowza-cat-card__icon">
          <?php if ($thumb) : ?>
            <img src="<?= esc_url($thumb) ?>" alt="<?= esc_attr($cat->name) ?>" loading="lazy">
          <?php else : ?>
            <span><?= $icon ?></span>
          <?php endif; ?>
        </div>
        <span class="glowza-cat-card__name"><?= esc_html($cat->name) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ░░░ TENDANCES DU MOMENT ░░░ -->
<section class="glowza-section" id="glowza-trending">
  <div class="glowza-container">
    <div class="glowza-section__header">
      <h2 class="glowza-section__title">🔥 Tendances du moment</h2>
      <a href="<?= esc_url(wc_get_page_permalink('shop')) ?>" class="glowza-link-all">Tout voir →</a>
    </div>
    <?php
    $trending = new WC_Product_Query([
      'limit'    => 8,
      'orderby'  => 'popularity',
      'order'    => 'DESC',
      'status'   => 'publish',
      'featured' => true,
    ]);
    $products = $trending->get_products();

    if (empty($products)) {
      $trending = new WC_Product_Query(['limit' => 8, 'orderby' => 'date', 'order' => 'DESC', 'status' => 'publish']);
      $products = $trending->get_products();
    }
    ?>
    <div class="glowza-products-grid">
      <?php foreach ($products as $product) : ?>
        <?php get_template_part('template-parts/product-card', null, ['product' => $product]); ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ░░░ BANNIÈRE PROMO ░░░ -->
<section class="glowza-promo-banner">
  <div class="glowza-container">
    <div class="glowza-promo-banner__inner">
      <div>
        <span class="glowza-promo-banner__tag">Offre bienvenue</span>
        <h2>-10% sur ta première commande</h2>
        <p>Inscris-toi à notre newsletter et reçois ton code promo instantanément.</p>
        <a href="#" class="btn glowza-popup-trigger">J'en profite</a>
      </div>
      <div class="glowza-promo-banner__deco">✨</div>
    </div>
  </div>
</section>

<!-- ░░░ NOUVEAUTÉS ░░░ -->
<section class="glowza-section glowza-section--light">
  <div class="glowza-container">
    <div class="glowza-section__header">
      <h2 class="glowza-section__title">🆕 Nouveautés</h2>
      <a href="<?= esc_url(add_query_arg('orderby', 'date', wc_get_page_permalink('shop'))) ?>" class="glowza-link-all">Voir tout →</a>
    </div>
    <?php
    $new_products = new WC_Product_Query(['limit' => 4, 'orderby' => 'date', 'order' => 'DESC', 'status' => 'publish']);
    $products = $new_products->get_products();
    ?>
    <div class="glowza-products-grid glowza-products-grid--4">
      <?php foreach ($products as $product) : ?>
        <?php get_template_part('template-parts/product-card', null, ['product' => $product, 'badge' => 'new']); ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ░░░ RÉASSURANCE ░░░ -->
<section class="glowza-reassurance">
  <div class="glowza-container">
    <div class="glowza-reassurance__grid">
      <div class="glowza-reassurance__item">
        <span class="glowza-reassurance__icon">🔒</span>
        <strong>Paiement sécurisé</strong>
        <p>Stripe & PayPal certifiés PCI-DSS</p>
      </div>
      <div class="glowza-reassurance__item">
        <span class="glowza-reassurance__icon">🚚</span>
        <strong>Livraison suivie</strong>
        <p>Numéro de tracking envoyé par email</p>
      </div>
      <div class="glowza-reassurance__item">
        <span class="glowza-reassurance__icon">↩️</span>
        <strong>Retour 14 jours</strong>
        <p>Satisfaite ou remboursée (loi française)</p>
      </div>
      <div class="glowza-reassurance__item">
        <span class="glowza-reassurance__icon">💬</span>
        <strong>SAV réactif</strong>
        <p>Réponse sous 48h à bonjour@glowza.fr</p>
      </div>
    </div>
  </div>
</section>

<!-- ░░░ POP-UP CAPTURE EMAIL ░░░ -->
<?php get_template_part('template-parts/popup-email'); ?>

<?php get_footer(); ?>

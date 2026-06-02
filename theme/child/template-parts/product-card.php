<?php
/** @var WC_Product $product */
$product = $args['product'] ?? null;
$badge   = $args['badge'] ?? '';
if (!$product) return;

$link      = get_permalink($product->get_id());
$img       = wp_get_attachment_image_url($product->get_image_id(), 'glowza-card');
$name      = $product->get_name();
$price     = $product->get_price_html();
$is_sale   = $product->is_on_sale();
$in_stock  = $product->is_in_stock();
?>
<div class="glowza-product-card" data-product-id="<?= $product->get_id() ?>">

  <?php if ($is_sale) : ?>
    <span class="glowza-badge glowza-badge--sale">Promo</span>
  <?php elseif ($badge === 'new') : ?>
    <span class="glowza-badge glowza-badge--new">Nouveau</span>
  <?php elseif ($product->is_featured()) : ?>
    <span class="glowza-badge glowza-badge--trending">✨ Tendance</span>
  <?php endif; ?>

  <a href="<?= esc_url($link) ?>" class="glowza-product-card__img-wrap">
    <?php if ($img) : ?>
      <img src="<?= esc_url($img) ?>" alt="<?= esc_attr($name) ?>" loading="lazy">
    <?php else : ?>
      <div class="glowza-product-card__no-img">🛍️</div>
    <?php endif; ?>
    <div class="glowza-product-card__overlay">
      <span>Voir le produit</span>
    </div>
  </a>

  <div class="glowza-product-card__body">
    <a href="<?= esc_url($link) ?>" class="glowza-product-card__name"><?= esc_html($name) ?></a>
    <div class="glowza-product-card__footer">
      <span class="glowza-product-card__price"><?= $price ?></span>
      <?php if ($in_stock) : ?>
        <button
          class="glowza-btn-cart"
          data-product_id="<?= $product->get_id() ?>"
          data-product_sku="<?= esc_attr($product->get_sku()) ?>"
          aria-label="Ajouter au panier"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        </button>
      <?php else : ?>
        <span class="glowza-product-card__out">Rupture</span>
      <?php endif; ?>
    </div>
  </div>

</div>

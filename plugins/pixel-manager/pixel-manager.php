<?php
/**
 * Plugin Name: Glowza Pixel Manager
 * Description: Google Tag Manager + Meta Pixel + TikTok Pixel — events WooCommerce.
 * Version:     1.0.0
 */
defined('ABSPATH') || exit;

// ─── Réglages (lus depuis options WP, configurables dans wp-admin) ────────────
function glowza_pixels_get(string $key): string {
    return sanitize_text_field(get_option('glowza_' . $key, ''));
}

// ─── GTM <head> ───────────────────────────────────────────────────────────────
add_action('wp_head', function () {
    $gtm = glowza_pixels_get('gtm_id');
    if (!$gtm) return;
    ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?= esc_js($gtm) ?>');</script>
<!-- End Google Tag Manager -->
    <?php
}, 1);

// ─── GTM <body> ───────────────────────────────────────────────────────────────
add_action('wp_body_open', function () {
    $gtm = glowza_pixels_get('gtm_id');
    if (!$gtm) return;
    ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= esc_attr($gtm) ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <?php
});

// ─── Meta Pixel base code ─────────────────────────────────────────────────────
add_action('wp_head', function () {
    $pid = glowza_pixels_get('meta_pixel_id');
    if (!$pid) return;
    ?>
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?= esc_js($pid) ?>');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=<?= esc_attr($pid) ?>&ev=PageView&noscript=1"/></noscript>
<!-- End Meta Pixel Code -->
    <?php
}, 2);

// ─── TikTok Pixel base code ───────────────────────────────────────────────────
add_action('wp_head', function () {
    $tid = glowza_pixels_get('tiktok_pixel_id');
    if (!$tid) return;
    ?>
<!-- TikTok Pixel Code -->
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
  ttq.load('<?= esc_js($tid) ?>');
  ttq.page();
}(window, document, 'ttq');
</script>
<!-- End TikTok Pixel Code -->
    <?php
}, 3);

// ─── dataLayer WooCommerce pour GTM ──────────────────────────────────────────
add_action('wp_head', function () {
    if (!glowza_pixels_get('gtm_id')) return;
    ?>
<script>window.dataLayer = window.dataLayer || [];</script>
    <?php
    // Page produit → ViewContent
    if (is_singular('product')) {
        global $product;
        if (!$product instanceof WC_Product) {
            $product = wc_get_product(get_the_ID());
        }
        if ($product) {
            $data = [
                'event'         => 'view_item',
                'ecommerce'     => [
                    'currency' => get_woocommerce_currency(),
                    'value'    => (float) $product->get_price(),
                    'items'    => [[
                        'item_id'       => $product->get_id(),
                        'item_name'     => $product->get_name(),
                        'price'         => (float) $product->get_price(),
                        'item_category' => implode(',', wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names'])),
                    ]],
                ],
            ];
            echo '<script>dataLayer.push(' . wp_json_encode($data, JSON_UNESCAPED_UNICODE) . ');</script>' . "\n";
        }
    }
}, 99);

// ─── Event add_to_cart (AJAX WooCommerce) ────────────────────────────────────
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    // On envoie les données produit via JS inline sur la page
    return $fragments;
});

// JavaScript : event add_to_cart → dataLayer + Meta + TikTok
add_action('wp_footer', function () {
    if (!is_singular('product') && !is_post_type_archive('product')) return;

    $gtm = glowza_pixels_get('gtm_id');
    $meta = glowza_pixels_get('meta_pixel_id');
    $tt  = glowza_pixels_get('tiktok_pixel_id');
    if (!$gtm && !$meta && !$tt) return;
    ?>
<script>
jQuery(document).on('added_to_cart', function(e, fragments, cart_hash, $btn) {
  var pid   = $btn.data('product_id') || $btn.val();
  var name  = $btn.closest('.product, form.cart').find('.product_title, h1').first().text().trim();
  var price = parseFloat($btn.closest('.product, form.cart').find('.price .amount').first().text().replace(/[^\d.,]/g,'').replace(',','.')) || 0;

  var item = { item_id: pid, item_name: name, price: price, quantity: 1 };

  <?php if ($gtm) : ?>
  dataLayer.push({ ecommerce: null });
  dataLayer.push({
    event: 'add_to_cart',
    ecommerce: { currency: 'EUR', value: price, items: [item] }
  });
  <?php endif; ?>

  <?php if ($meta) : ?>
  if (typeof fbq !== 'undefined') {
    fbq('track', 'AddToCart', {
      content_ids: [pid], content_name: name,
      value: price, currency: 'EUR', content_type: 'product'
    });
  }
  <?php endif; ?>

  <?php if ($tt) : ?>
  if (typeof ttq !== 'undefined') {
    ttq.track('AddToCart', {
      content_id: String(pid), content_name: name,
      value: price, currency: 'EUR'
    });
  }
  <?php endif; ?>
});
</script>
    <?php
});

// ─── Event purchase (page de confirmation) ────────────────────────────────────
add_action('woocommerce_thankyou', function ($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    // Éviter de déclencher deux fois (rechargement de la page)
    if ($order->get_meta('_glowza_purchase_event_fired')) return;
    $order->update_meta_data('_glowza_purchase_event_fired', '1');
    $order->save();

    $items = [];
    foreach ($order->get_items() as $item) {
        $items[] = [
            'item_id'   => $item->get_product_id(),
            'item_name' => $item->get_name(),
            'price'     => (float) $order->get_item_total($item),
            'quantity'  => $item->get_quantity(),
        ];
    }

    $gtm  = glowza_pixels_get('gtm_id');
    $meta = glowza_pixels_get('meta_pixel_id');
    $tt   = glowza_pixels_get('tiktok_pixel_id');

    $content_ids = array_column($items, 'item_id');
    $total       = (float) $order->get_total();
    $order_num   = $order->get_order_number();
    ?>
<script>
(function(){
  <?php if ($gtm) : ?>
  dataLayer.push({ ecommerce: null });
  dataLayer.push({
    event: 'purchase',
    ecommerce: {
      transaction_id: '<?= esc_js($order_num) ?>',
      value: <?= $total ?>,
      currency: 'EUR',
      items: <?= wp_json_encode($items) ?>
    }
  });
  <?php endif; ?>

  <?php if ($meta) : ?>
  if (typeof fbq !== 'undefined') {
    fbq('track', 'Purchase', {
      value: <?= $total ?>, currency: 'EUR',
      content_ids: <?= wp_json_encode($content_ids) ?>,
      content_type: 'product',
      num_items: <?= count($items) ?>
    });
  }
  <?php endif; ?>

  <?php if ($tt) : ?>
  if (typeof ttq !== 'undefined') {
    ttq.track('PlaceAnOrder', {
      value: <?= $total ?>, currency: 'EUR',
      content_id: <?= wp_json_encode($content_ids) ?>
    });
  }
  <?php endif; ?>
})();
</script>
    <?php
}, 10);

// ─── Page admin pour saisir les IDs ──────────────────────────────────────────
add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        'Pixels Glowza',
        'Pixels & Analytics',
        'manage_options',
        'glowza-pixels',
        'glowza_pixels_admin_page'
    );
});

function glowza_pixels_admin_page(): void {
    if (isset($_POST['glowza_pixels_save']) && check_admin_referer('glowza_pixels_save')) {
        $fields = ['gtm_id', 'meta_pixel_id', 'tiktok_pixel_id'];
        foreach ($fields as $f) {
            update_option('glowza_' . $f, sanitize_text_field($_POST[$f] ?? ''));
        }
        echo '<div class="notice notice-success"><p>✅ Pixels enregistrés.</p></div>';
    }
    ?>
<div class="wrap">
  <h1>Pixels & Analytics — Glowza</h1>
  <form method="post">
    <?php wp_nonce_field('glowza_pixels_save'); ?>
    <table class="form-table">
      <tr>
        <th>GTM Container ID</th>
        <td><input type="text" name="gtm_id" value="<?= esc_attr(glowza_pixels_get('gtm_id')) ?>" placeholder="GTM-XXXXXXX" class="regular-text"></td>
      </tr>
      <tr>
        <th>Meta Pixel ID</th>
        <td><input type="text" name="meta_pixel_id" value="<?= esc_attr(glowza_pixels_get('meta_pixel_id')) ?>" placeholder="000000000000000" class="regular-text"></td>
      </tr>
      <tr>
        <th>TikTok Pixel ID</th>
        <td><input type="text" name="tiktok_pixel_id" value="<?= esc_attr(glowza_pixels_get('tiktok_pixel_id')) ?>" placeholder="XXXXXXXXXXXXXXX" class="regular-text"></td>
      </tr>
    </table>
    <p class="submit"><input type="submit" name="glowza_pixels_save" class="button-primary" value="Enregistrer les pixels"></p>
  </form>
</div>
    <?php
}

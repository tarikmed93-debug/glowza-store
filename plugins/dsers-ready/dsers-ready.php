<?php
/**
 * Plugin Name: Glowza DSers Ready
 * Description: Configure WooCommerce pour s'intégrer parfaitement avec DSers.
 *              Gère la structure produits, les variantes, et le webhook secret.
 * Version:     1.0.0
 */
defined('ABSPATH') || exit;

// ─── Réglages DSers ──────────────────────────────────────────────────────────
add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        'DSers Setup',
        'DSers Setup',
        'manage_options',
        'glowza-dsers',
        'glowza_dsers_admin_page'
    );
});

function glowza_dsers_admin_page(): void {
    if (isset($_POST['save_dsers']) && check_admin_referer('glowza_dsers')) {
        update_option('glowza_webhook_secret', sanitize_text_field($_POST['webhook_secret'] ?? ''));
        echo '<div class="notice notice-success"><p>✅ Réglages DSers enregistrés.</p></div>';
    }
    $secret   = get_option('glowza_webhook_secret', bin2hex(random_bytes(16)));
    $webhook  = home_url('/wp-json/glowza/v1/dsers-tracking');
    ?>
<div class="wrap">
  <h1>DSers Setup — Glowza</h1>
  <p>Configure DSers avec ces valeurs pour activer le tracking automatique.</p>

  <h2>🔗 Webhook URL à coller dans DSers</h2>
  <p><code style="font-size:14px;background:#f0f0f0;padding:8px 12px;display:block;"><?= esc_html($webhook) ?></code></p>

  <h2>🔑 Secret Webhook</h2>
  <form method="post">
    <?php wp_nonce_field('glowza_dsers'); ?>
    <table class="form-table">
      <tr>
        <th>Secret (Header X-Glowza-Secret)</th>
        <td>
          <input type="text" name="webhook_secret" value="<?= esc_attr($secret) ?>" class="regular-text">
          <p class="description">Copie ce secret dans DSers → Paramètres → Webhooks → Secret Header.</p>
        </td>
      </tr>
    </table>
    <p class="submit"><input type="submit" name="save_dsers" class="button-primary" value="Enregistrer"></p>
  </form>

  <h2>📋 Guide DSers (ce que tu dois faire)</h2>
  <ol style="line-height:2">
    <li>Connecte ton compte AliExpress dans DSers → Settings → Supplier Account.</li>
    <li>Importe tes produits AliExpress dans DSers, puis pousse-les dans WooCommerce (Mapping).</li>
    <li>Dans DSers → Settings → Notification → Webhook : colle l'URL ci-dessus + le secret.</li>
    <li>Active le webhook pour l'event "Fulfillment" (expédition).</li>
    <li>Teste avec une commande de test : DSers doit envoyer le tracking, WooCommerce l'enregistre et envoie l'email au client automatiquement.</li>
  </ol>
</div>
    <?php
}

// ─── Assurer que les champs meta DSers sont affichés dans les commandes ───────
add_action('woocommerce_admin_order_data_after_shipping_address', function (WC_Order $order) {
    $tracking = $order->get_meta('_tracking_number');
    $url      = $order->get_meta('_tracking_url');
    if (!$tracking) return;
    ?>
    <div class="form-field-wide" style="background:#FDF0F3;padding:12px;border-radius:8px;margin:12px 0;">
        <strong>📦 Tracking DSers :</strong>
        <?= esc_html($tracking) ?>
        <?php if ($url) : ?>
        — <a href="<?= esc_url($url) ?>" target="_blank">Suivre le colis</a>
        <?php endif; ?>
    </div>
    <?php
});

// ─── Colonne "Tracking" dans la liste des commandes ──────────────────────────
add_filter('manage_woocommerce_page_wc-orders_columns', function ($columns) {
    $columns['glowza_tracking'] = 'Tracking';
    return $columns;
});

add_action('manage_woocommerce_page_wc-orders_custom_column', function ($column, $order_id) {
    if ($column !== 'glowza_tracking') return;
    $order   = wc_get_order($order_id);
    $tracking = $order ? $order->get_meta('_tracking_number') : '';
    $url      = $order ? $order->get_meta('_tracking_url') : '';

    if ($tracking) {
        echo $url
            ? '<a href="' . esc_url($url) . '" target="_blank">' . esc_html($tracking) . '</a>'
            : esc_html($tracking);
    } else {
        echo '—';
    }
}, 10, 2);

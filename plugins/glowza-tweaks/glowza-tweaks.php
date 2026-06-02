<?php
/**
 * Plugin Name: Glowza Tweaks
 * Description: Panier abandonné, pop-up newsletter, code promo bienvenue, urgence stock.
 * Version:     1.0.0
 */
defined('ABSPATH') || exit;

// ─── 1. ABONNEMENT NEWSLETTER (AJAX pop-up) ───────────────────────────────────
add_action('wp_ajax_glowza_subscribe',        'glowza_handle_subscribe');
add_action('wp_ajax_nopriv_glowza_subscribe', 'glowza_handle_subscribe');

function glowza_handle_subscribe(): void {
    check_ajax_referer('glowza_subscribe', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    if (!is_email($email)) {
        wp_send_json_error('Email invalide.', 400);
    }

    // Enregistrer dans une option WP (simple, sans dépendance)
    $subscribers = get_option('glowza_newsletter_subscribers', []);
    if (!in_array($email, $subscribers, true)) {
        $subscribers[] = $email;
        update_option('glowza_newsletter_subscribers', $subscribers);

        // Envoyer le code promo par email
        glowza_send_welcome_coupon($email);
    }

    wp_send_json_success(['message' => 'Inscrit·e !']);
}

function glowza_send_welcome_coupon(string $email): void {
    $code    = 'BIENVENUE10';
    $subject = '🎉 Ton code -10% est là !';
    $body    = '
<html><body style="font-family:sans-serif;background:#FAFAFA;padding:24px;">
<div style="max-width:500px;margin:0 auto;background:#fff;border-radius:16px;padding:32px;text-align:center;">
  <div style="font-family:Georgia,serif;font-size:28px;font-weight:700;color:#1E1E1E;margin-bottom:8px;">Glowza</div>
  <h2 style="color:#E8849A;font-size:1.6rem;margin:16px 0 8px;">Voici ton code -10% !</h2>
  <p style="color:#6B6B6B;">Merci de rejoindre la communauté Glowza ✨</p>
  <div style="background:#FDF0F3;border:2px dashed #E8849A;border-radius:12px;padding:20px;margin:20px 0;">
    <p style="color:#999;font-size:13px;margin:0 0 8px;">Ton code promo</p>
    <p style="font-size:2rem;font-weight:700;color:#E8849A;letter-spacing:.1em;margin:0;">' . $code . '</p>
    <p style="color:#999;font-size:12px;margin:8px 0 0;">-10% sur ta première commande · Valable 30 jours</p>
  </div>
  <a href="https://glowza.fr/boutique/" style="display:inline-block;background:#E8849A;color:#fff;padding:14px 28px;border-radius:999px;font-weight:600;text-decoration:none;">
    J\'utilise mon code →
  </a>
</div>
</body></html>';

    wp_mail($email, $subject, $body, ['Content-Type: text/html; charset=UTF-8', 'From: Glowza <bonjour@glowza.fr>']);
}

// ─── Créer le coupon BIENVENUE10 à l'activation ──────────────────────────────
register_activation_hook(__FILE__, 'glowza_create_welcome_coupon');
function glowza_create_welcome_coupon(): void {
    if (wc_get_coupon_id_by_code('BIENVENUE10')) return;

    $coupon = new WC_Coupon();
    $coupon->set_code('BIENVENUE10');
    $coupon->set_discount_type('percent');
    $coupon->set_amount(10);
    $coupon->set_usage_limit(1);         // 1 fois par client
    $coupon->set_usage_limit_per_user(1);
    $coupon->set_date_expires(strtotime('+6 months'));
    $coupon->set_description('Code promo bienvenue newsletter -10%');
    $coupon->save();
}

// ─── 2. PANIER ABANDONNÉ ──────────────────────────────────────────────────────

// Enregistrer le panier en session quand un produit est ajouté
add_action('woocommerce_add_to_cart', 'glowza_track_cart_session');
function glowza_track_cart_session(): void {
    if (!is_user_logged_in()) return;
    $user_id = get_current_user_id();
    update_user_meta($user_id, '_glowza_cart_updated_at', time());
    update_user_meta($user_id, '_glowza_cart_reminded', 0);
}

// Effacer quand commande passée
add_action('woocommerce_checkout_order_processed', function ($order_id) {
    $order = wc_get_order($order_id);
    if ($order && $order->get_user_id()) {
        delete_user_meta($order->get_user_id(), '_glowza_cart_updated_at');
        delete_user_meta($order->get_user_id(), '_glowza_cart_reminded');
    }
});

// Cron toutes les heures → email de relance après 1h d'abandon
add_action('init', function () {
    if (!wp_next_scheduled('glowza_abandoned_cart_cron')) {
        wp_schedule_event(time(), 'hourly', 'glowza_abandoned_cart_cron');
    }
});

add_action('glowza_abandoned_cart_cron', 'glowza_send_abandoned_cart_emails');
function glowza_send_abandoned_cart_emails(): void {
    $threshold = time() - 3600; // 1 heure

    $users = get_users([
        'meta_query' => [
            ['key' => '_glowza_cart_updated_at', 'value' => $threshold, 'compare' => '<', 'type' => 'NUMERIC'],
            ['key' => '_glowza_cart_reminded', 'value' => '0'],
        ],
        'number' => 50,
    ]);

    foreach ($users as $user) {
        $session_key = 'customer_' . $user->ID;
        // Récupérer le panier depuis la session WC
        $cart_items = WC()->session ? WC()->session->get('cart', []) : [];
        if (empty($cart_items)) {
            delete_user_meta($user->ID, '_glowza_cart_updated_at');
            continue;
        }

        glowza_send_abandoned_cart_email($user, $cart_items);
        update_user_meta($user->ID, '_glowza_cart_reminded', 1);
    }
}

function glowza_send_abandoned_cart_email(WP_User $user, array $cart_items): void {
    $email   = $user->user_email;
    $name    = $user->first_name ?: $user->display_name;
    $subject = $name ? "Hey $name, tu as oublié quelque chose 👀" : 'Tu as oublié quelque chose dans ton panier 👀';

    // Construire la liste des produits
    $items_html = '';
    foreach (array_slice($cart_items, 0, 3) as $item) {
        $product = wc_get_product($item['product_id'] ?? 0);
        if (!$product) continue;
        $img = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
        $items_html .= '
        <tr>
          ' . ($img ? '<td width="50" style="padding-right:10px;"><img src="' . esc_url($img) . '" width="50" height="50" style="border-radius:8px;object-fit:cover;"></td>' : '') . '
          <td style="font-size:14px;color:#1E1E1E;font-weight:600;">' . esc_html($product->get_name()) . '</td>
          <td align="right" style="font-size:14px;font-weight:700;color:#E8849A;">' . wc_price($product->get_price()) . '</td>
        </tr>';
    }

    $cart_url = wc_get_cart_url();

    $body = '
<html><body style="font-family:sans-serif;background:#FAFAFA;padding:24px;">
<div style="max-width:500px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.08);">
  <div style="background:linear-gradient(135deg,#F5C2CE,#F5EDE4);padding:24px 32px;text-align:center;">
    <div style="font-family:Georgia,serif;font-size:26px;font-weight:700;color:#1E1E1E;">Glowza</div>
  </div>
  <div style="padding:32px;">
    <h2 style="font-family:Georgia,serif;font-size:22px;color:#1E1E1E;margin:0 0 12px;">
      ' . ($name ? "Hey $name," : "Hey,") . ' ton panier t\'attend 🛍️
    </h2>
    <p style="color:#6B6B6B;line-height:1.7;margin:0 0 20px;">
      Tu as laissé des articles dans ton panier. Ils ne seront peut-être plus disponibles longtemps !
    </p>

    <div style="background:#FDF0F3;border-radius:12px;padding:16px;margin:0 0 20px;">
      <table width="100%" cellpadding="6" cellspacing="0">' . $items_html . '</table>
    </div>

    <div style="text-align:center;margin:24px 0;">
      <a href="' . esc_url($cart_url) . '"
         style="display:inline-block;background:#E8849A;color:#fff;padding:14px 32px;border-radius:999px;font-weight:600;font-size:15px;text-decoration:none;">
        Terminer ma commande →
      </a>
    </div>

    <div style="background:#F5EDE4;border-radius:10px;padding:12px 16px;text-align:center;">
      <p style="margin:0;font-size:13px;color:#6B6B6B;">
        🔒 Paiement sécurisé &nbsp;·&nbsp; ↩️ Retour 14 jours &nbsp;·&nbsp; 🚚 Suivi inclus
      </p>
    </div>
  </div>
  <div style="background:#1E1E1E;padding:16px 32px;text-align:center;">
    <p style="margin:0;color:#666;font-size:11px;">
      Glowza · <a href="https://glowza.fr" style="color:#F5C2CE;">glowza.fr</a>
    </p>
  </div>
</div>
</body></html>';

    wp_mail($email, $subject, $body, ['Content-Type: text/html; charset=UTF-8', 'From: Glowza <bonjour@glowza.fr>']);
}

// ─── 3. COMPTEUR STOCK URGENCE sur les fiches produit ─────────────────────────
add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product || !$product->managing_stock()) return;

    $stock = $product->get_stock_quantity();
    if (!$stock || $stock > 10) return;
    ?>
    <div class="glowza-urgency">
        <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16A8 8 0 0010 2zm1 4.5v5a1 1 0 11-2 0v-5a1 1 0 112 0zm0 7.5a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
        Plus que <strong><?= $stock ?></strong> en stock — commande vite !
    </div>
    <?php
}, 26);

// ─── 4. PAGE ADMIN — ABONNÉS NEWSLETTER ──────────────────────────────────────
add_action('admin_menu', function () {
    add_submenu_page('woocommerce', 'Newsletter Glowza', 'Newsletter', 'manage_options', 'glowza-newsletter', function () {
        $subscribers = get_option('glowza_newsletter_subscribers', []);
        ?>
        <div class="wrap">
          <h1>Abonnés Newsletter — Glowza (<?= count($subscribers) ?>)</h1>
          <?php if (isset($_GET['export'])) : ?>
            <?php header('Content-Type: text/csv'); header('Content-Disposition: attachment;filename=glowza-newsletter.csv'); echo "Email\n"; foreach ($subscribers as $e) echo esc_html($e) . "\n"; exit; ?>
          <?php endif; ?>
          <a href="<?= admin_url('admin.php?page=glowza-newsletter&export=1') ?>" class="button button-primary" style="margin-bottom:16px;">Exporter CSV</a>
          <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>#</th><th>Email</th></tr></thead>
            <tbody>
              <?php foreach ($subscribers as $i => $email) : ?>
              <tr><td><?= $i + 1 ?></td><td><?= esc_html($email) ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php
    });
});

<?php
/**
 * Plugin Name: Glowza DSers Order Statuses
 * Description: Statuts de commande custom pour le flux DSers + webhook tracking auto.
 */
defined('ABSPATH') || exit;

// ─── Enregistrement des statuts custom ────────────────────────────────────────
add_action('init', function () {
    register_post_status('wc-dsers-pending', [
        'label'                     => 'En attente DSers',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'label_count'               => _n_noop('En attente DSers <span class="count">(%s)</span>', 'En attente DSers <span class="count">(%s)</span>'),
    ]);

    register_post_status('wc-dsers-fulfilled', [
        'label'                     => 'Commandé DSers',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'label_count'               => _n_noop('Commandé DSers <span class="count">(%s)</span>', 'Commandé DSers <span class="count">(%s)</span>'),
    ]);

    register_post_status('wc-tracking-sent', [
        'label'                     => 'Tracking envoyé',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'label_count'               => _n_noop('Tracking envoyé <span class="count">(%s)</span>', 'Tracking envoyé <span class="count">(%s)</span>'),
    ]);
});

// Ajouter les statuts custom à WooCommerce
add_filter('wc_order_statuses', function ($statuses) {
    $custom = [
        'wc-dsers-pending'  => 'En attente DSers',
        'wc-dsers-fulfilled'=> 'Commandé DSers',
        'wc-tracking-sent'  => 'Tracking envoyé',
    ];
    return array_merge($statuses, $custom);
});

// ─── Email automatique à l'expédition avec numéro de tracking ─────────────────
add_action('woocommerce_order_status_changed', function ($order_id, $old_status, $new_status) {
    if ($new_status !== 'tracking-sent') return;

    $order        = wc_get_order($order_id);
    $tracking_num = $order->get_meta('_tracking_number');
    $tracking_url = $order->get_meta('_tracking_url');
    $customer_email = $order->get_billing_email();

    if (!$customer_email) return;

    // Marquer l'ordre comme expédié
    $order->update_status('completed', 'Tracking envoyé au client.');

    // Envoyer l'email tracking
    $subject = sprintf('🎉 Votre commande Glowza #%s est en route !', $order->get_order_number());
    $heading = 'Votre commande est expédiée !';

    ob_start();
    glowza_tracking_email_body($order, $tracking_num, $tracking_url);
    $body = ob_get_clean();

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Glowza <bonjour@glowza.fr>',
    ];

    wp_mail($customer_email, $subject, $body, $headers);

    $order->add_order_note('Email de tracking envoyé au client : ' . $customer_email);
}, 10, 3);

function glowza_tracking_email_body(WC_Order $order, string $tracking_num, string $tracking_url): void {
    $first_name = $order->get_billing_first_name();
    $order_num  = $order->get_order_number();
    ?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#FAFAFA;font-family:-apple-system,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#FAFAFA;padding:32px 16px;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.08);">

      <!-- Header rose -->
      <tr>
        <td style="background:linear-gradient(135deg,#F5C2CE,#F5EDE4);padding:32px 32px 24px;text-align:center;">
          <div style="font-family:Georgia,serif;font-size:28px;font-weight:700;color:#1E1E1E;">Glowza</div>
          <p style="color:#6B6B6B;margin:8px 0 0;font-size:14px;">Votre boutique mode tendance</p>
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td style="padding:32px;">
          <h2 style="font-family:Georgia,serif;font-size:22px;margin:0 0 16px;color:#1E1E1E;">
            🎉 C'est parti, <?= esc_html($first_name) ?> !
          </h2>
          <p style="color:#444;line-height:1.7;margin:0 0 20px;">
            Ta commande <strong>#<?= esc_html($order_num) ?></strong> a été expédiée depuis notre entrepôt partenaire.
            Tu peux suivre ton colis en temps réel avec le lien ci-dessous.
          </p>

          <?php if ($tracking_num) : ?>
          <!-- Tracking box -->
          <div style="background:#FDF0F3;border:1px solid #F5C2CE;border-radius:12px;padding:20px;margin:0 0 24px;text-align:center;">
            <p style="color:#6B6B6B;font-size:13px;margin:0 0 6px;">Numéro de suivi</p>
            <p style="font-size:18px;font-weight:700;color:#E8849A;margin:0 0 16px;letter-spacing:.04em;"><?= esc_html($tracking_num) ?></p>
            <?php if ($tracking_url) : ?>
            <a href="<?= esc_url($tracking_url) ?>"
               style="display:inline-block;background:#E8849A;color:#fff;text-decoration:none;padding:12px 28px;border-radius:999px;font-weight:600;font-size:14px;">
              Suivre mon colis →
            </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <!-- Délai livraison -->
          <div style="background:#F5EDE4;border-radius:12px;padding:16px;margin:0 0 24px;">
            <p style="color:#6B6B6B;font-size:14px;margin:0;">
              🚚 <strong>Délai estimé :</strong> 2 à 4 semaines selon la destination.
              Les délais peuvent varier en fonction des transporteurs et de la douane.
            </p>
          </div>

          <p style="color:#444;font-size:14px;line-height:1.7;">
            Des questions ? Réponds simplement à cet email ou contacte-nous à
            <a href="mailto:bonjour@glowza.fr" style="color:#E8849A;">bonjour@glowza.fr</a>
          </p>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#F5EDE4;padding:20px 32px;text-align:center;">
          <p style="color:#999;font-size:12px;margin:0 0 8px;">
            Glowza · Paris, France · <a href="https://glowza.fr" style="color:#999;">glowza.fr</a>
          </p>
          <p style="color:#ccc;font-size:11px;margin:0;">
            Tu as 14 jours pour exercer ton droit de rétractation (loi française).
            <a href="https://glowza.fr/cgv/" style="color:#ccc;">Voir nos CGV</a>
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
    <?php
}

// ─── Webhook entrant depuis DSers : mise à jour tracking ──────────────────────
add_action('rest_api_init', function () {
    register_rest_route('glowza/v1', '/dsers-tracking', [
        'methods'             => 'POST',
        'callback'            => 'glowza_handle_dsers_tracking',
        'permission_callback' => '__return_true',
    ]);
});

function glowza_handle_dsers_tracking(WP_REST_Request $request): WP_REST_Response {
    // Vérifier le secret webhook
    $secret = $request->get_header('X-Glowza-Secret');
    if ($secret !== get_option('glowza_webhook_secret', '')) {
        return new WP_REST_Response(['error' => 'Unauthorized'], 401);
    }

    $params      = $request->get_json_params();
    $order_id    = isset($params['order_id'])    ? absint($params['order_id'])    : 0;
    $tracking    = isset($params['tracking'])    ? sanitize_text_field($params['tracking'])    : '';
    $tracking_url= isset($params['tracking_url'])? esc_url_raw($params['tracking_url']) : '';

    if (!$order_id || !$tracking) {
        return new WP_REST_Response(['error' => 'order_id and tracking required'], 400);
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return new WP_REST_Response(['error' => 'Order not found'], 404);
    }

    $order->update_meta_data('_tracking_number', $tracking);
    $order->update_meta_data('_tracking_url', $tracking_url);
    $order->save();

    // Déclenche l'email tracking
    $order->update_status('tracking-sent', 'Tracking reçu depuis DSers : ' . $tracking);

    return new WP_REST_Response(['success' => true, 'order_id' => $order_id], 200);
}

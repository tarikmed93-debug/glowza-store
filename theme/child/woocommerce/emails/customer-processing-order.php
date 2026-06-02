<?php
/**
 * Email : Confirmation de commande (client)
 * Override WooCommerce : woocommerce/emails/customer-processing-order.php
 */
defined('ABSPATH') || exit;
do_action('woocommerce_email_header', $email_heading, $email); ?>

<p style="color:#1E1E1E;font-size:16px;margin:0 0 16px;">
  Bonjour <strong><?= esc_html($order->get_billing_first_name()) ?></strong> 👋
</p>

<p style="color:#444;line-height:1.7;margin:0 0 20px;">
  Merci pour ta commande ! Nous avons bien reçu ton paiement. Ton colis est en cours de préparation chez notre fournisseur.
</p>

<!-- Récap commande -->
<div style="background:#FDF0F3;border:1px solid #F5C2CE;border-radius:12px;padding:20px;margin:0 0 24px;">
  <p style="margin:0 0 12px;font-weight:700;color:#1E1E1E;">📦 Commande #<?= $order->get_order_number() ?></p>
  <?php
  foreach ($order->get_items() as $item) :
    $product = $item->get_product();
    $img_url = $product ? wp_get_attachment_image_url($product->get_image_id(), 'glowza-square') : '';
  ?>
  <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
    <tr>
      <?php if ($img_url) : ?>
      <td width="60" style="padding-right:12px;">
        <img src="<?= esc_url($img_url) ?>" width="50" height="50" style="border-radius:8px;object-fit:cover;" alt="">
      </td>
      <?php endif; ?>
      <td>
        <div style="font-weight:600;font-size:14px;color:#1E1E1E;"><?= esc_html($item->get_name()) ?></div>
        <div style="font-size:13px;color:#6B6B6B;">Qté : <?= $item->get_quantity() ?></div>
      </td>
      <td align="right" style="font-weight:700;color:#E8849A;font-size:15px;">
        <?= wc_price($order->get_item_total($item, true)) ?>
      </td>
    </tr>
  </table>
  <?php endforeach; ?>

  <hr style="border:none;border-top:1px solid #F5C2CE;margin:12px 0;">

  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td style="font-size:13px;color:#6B6B6B;">Sous-total</td>
      <td align="right" style="font-size:13px;"><?= wc_price($order->get_subtotal()) ?></td>
    </tr>
    <tr>
      <td style="font-size:13px;color:#6B6B6B;">Livraison</td>
      <td align="right" style="font-size:13px;"><?= $order->get_shipping_total() > 0 ? wc_price($order->get_shipping_total()) : 'Gratuite' ?></td>
    </tr>
    <tr>
      <td style="font-size:15px;font-weight:700;padding-top:8px;">Total</td>
      <td align="right" style="font-size:15px;font-weight:700;color:#E8849A;padding-top:8px;"><?= $order->get_formatted_order_total() ?></td>
    </tr>
  </table>
</div>

<!-- Adresse livraison -->
<div style="background:#F5EDE4;border-radius:12px;padding:16px 20px;margin:0 0 24px;">
  <p style="margin:0 0 8px;font-weight:700;font-size:13px;color:#1E1E1E;">📍 Adresse de livraison</p>
  <p style="margin:0;font-size:13px;color:#444;line-height:1.7;">
    <?= esc_html($order->get_formatted_shipping_address()) ?>
  </p>
</div>

<!-- Délai livraison -->
<div style="background:#FFF8E7;border:1px solid #FFD54F;border-radius:12px;padding:16px 20px;margin:0 0 24px;">
  <p style="margin:0;font-size:13px;color:#795548;">
    🚚 <strong>Délai de livraison estimé :</strong> 2 à 4 semaines après expédition.
    Tu recevras un email avec ton numéro de tracking dès que ton colis sera expédié.
  </p>
</div>

<!-- CTA -->
<div style="text-align:center;margin:0 0 8px;">
  <a href="<?= esc_url($order->get_view_order_url()) ?>"
     style="display:inline-block;background:#E8849A;color:#fff;text-decoration:none;padding:14px 32px;border-radius:999px;font-weight:600;font-size:15px;">
    Voir ma commande →
  </a>
</div>

<?php do_action('woocommerce_email_footer', $email); ?>

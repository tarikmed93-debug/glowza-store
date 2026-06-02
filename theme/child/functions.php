<?php
defined('ABSPATH') || exit;

// ─── Chargement des styles parent + child ─────────────────────────────────────
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('storefront-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('glowza-style',     get_stylesheet_directory_uri() . '/assets/css/main.css', ['storefront-style'], '1.0.0');
    wp_enqueue_script('glowza-main',     get_stylesheet_directory_uri() . '/assets/js/main.js', ['jquery'], '1.0.0', true);

    // Variables JS passées au front
    wp_localize_script('glowza-main', 'glowzaVars', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('glowza_nonce'),
        'currency'=> get_woocommerce_currency_symbol(),
    ]);
});

// ─── Supports thème ────────────────────────────────────────────────────────────
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce', [
        'thumbnail_image_width'     => 600,
        'single_image_width'        => 900,
        'product_grid'              => ['default_rows' => 4, 'min_rows' => 2, 'default_columns' => 3, 'min_columns' => 2, 'max_columns' => 4],
    ]);
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption', 'script', 'style']);

    // Menu de navigation
    register_nav_menus([
        'primary' => 'Menu principal',
        'footer'  => 'Menu footer',
    ]);
});

// ─── Taille d'images optimisées ──────────────────────────────────────────────
add_action('after_setup_theme', function () {
    add_image_size('glowza-card',    600, 750, true);   // Carte produit (ratio 4:5 TikTok)
    add_image_size('glowza-hero',   1200, 600, true);   // Banner hero
    add_image_size('glowza-square',  400, 400, true);   // Miniature carrée
});

// ─── Suppression éléments Storefront inutiles ────────────────────────────────
remove_action('storefront_header', 'storefront_header_container', 0);
remove_action('storefront_header', 'storefront_skip_links', 0);

// Désactiver la sidebar sur les pages produit et boutique
add_filter('storefront_sidebar', '__return_false');
add_filter('storefront_woocommerce_sidebar', '__return_false');

// ─── Personnalisation WooCommerce ─────────────────────────────────────────────

// Message livraison sur les fiches produit
add_action('woocommerce_single_product_summary', function () {
    echo '<div class="glowza-shipping-notice">
        <span class="icon">🚚</span>
        <span>Livraison en <strong>2 à 4 semaines</strong> depuis nos entrepôts partenaires</span>
    </div>';
}, 25);

// Badge "Tendance" sur les produits en vedette
add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if ($product->is_featured()) {
        echo '<span class="glowza-badge glowza-badge--trending">✨ Tendance</span>';
    }
}, 9);

// Message droit de rétractation au checkout
add_action('woocommerce_checkout_before_order_review_heading', function () {
    echo '<p class="glowza-retractation">
        🔒 <strong>Paiement sécurisé</strong> —
        Droit de rétractation <strong>14 jours</strong> garanti (conformément à la loi française)
    </p>';
});

// ─── Checkout optimisé ────────────────────────────────────────────────────────

// Supprimer le champ "Société" du checkout (non pertinent)
add_filter('woocommerce_checkout_fields', function ($fields) {
    unset($fields['billing']['billing_company']);
    unset($fields['shipping']['shipping_company']);
    return $fields;
});

// Réordonner les champs checkout pour mobile
add_filter('woocommerce_checkout_fields', function ($fields) {
    $fields['billing']['billing_first_name']['priority'] = 10;
    $fields['billing']['billing_last_name']['priority']  = 20;
    $fields['billing']['billing_email']['priority']      = 30;
    $fields['billing']['billing_phone']['priority']      = 40;
    $fields['billing']['billing_address_1']['priority']  = 50;
    $fields['billing']['billing_postcode']['priority']   = 60;
    $fields['billing']['billing_city']['priority']       = 70;
    return $fields;
});

// ─── Performance ──────────────────────────────────────────────────────────────

// Désactiver emojis WordPress (inutile, pèse 10kb)
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_filter('the_content_feed', 'wp_staticize_emoji');

// Désactiver XML-RPC
add_filter('xmlrpc_enabled', '__return_false');

// Retirer version WordPress des balises (sécurité)
remove_action('wp_head', 'wp_generator');

// Lazy load natif sur les images
add_filter('wp_lazy_loading_enabled', '__return_true');

// ─── Schema.org Product (JSON-LD) ─────────────────────────────────────────────
add_action('wp_head', function () {
    if (!is_singular('product')) return;
    global $product;
    if (!$product instanceof WC_Product) {
        $product = wc_get_product(get_the_ID());
    }
    if (!$product) return;

    $schema = [
        '@context'    => 'https://schema.org/',
        '@type'       => 'Product',
        'name'        => $product->get_name(),
        'description' => wp_strip_all_tags($product->get_description()),
        'sku'         => $product->get_sku(),
        'image'       => wp_get_attachment_url($product->get_image_id()),
        'brand'       => ['@type' => 'Brand', 'name' => 'Glowza'],
        'offers'      => [
            '@type'         => 'Offer',
            'priceCurrency' => get_woocommerce_currency(),
            'price'         => $product->get_price(),
            'availability'  => $product->is_in_stock()
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'seller'        => ['@type' => 'Organization', 'name' => 'Glowza'],
            'url'           => get_permalink(),
        ],
    ];

    if ($product->get_rating_count() > 0) {
        $schema['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => $product->get_average_rating(),
            'reviewCount' => $product->get_review_count(),
        ];
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
});

// ─── Breadcrumb Schema ────────────────────────────────────────────────────────
add_action('woocommerce_before_main_content', function () {
    if (!is_singular('product')) return;
    woocommerce_breadcrumb();
}, 5);

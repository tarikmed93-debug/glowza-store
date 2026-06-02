<?php
/**
 * Plugin Name: Glowza Performance
 * Description: Optimisations perf : headers cache, WebP, désactivation bloat WP.
 */
defined('ABSPATH') || exit;

// ─── Headers cache Cloudflare / navigateur ────────────────────────────────────
add_action('send_headers', function () {
    if (is_admin()) return;

    if (is_singular('product') || is_post_type_archive('product') || is_tax('product_cat')) {
        header('Cache-Control: public, max-age=3600, stale-while-revalidate=86400');
    } elseif (is_page()) {
        header('Cache-Control: public, max-age=86400');
    }

    // Sécurité
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
});

// ─── Désactiver bloat WordPress ───────────────────────────────────────────────
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'wp_oembed_add_discovery_links');
add_filter('wp_resource_hints', function ($hints, $relation) {
    if ('dns-prefetch' === $relation) {
        return array_filter($hints, fn($hint) => str_contains($hint['href'] ?? '', 'glowza.fr'));
    }
    return $hints;
}, 10, 2);

// ─── Désactiver Heartbeat sur le front ───────────────────────────────────────
add_action('init', function () {
    if (!is_admin()) {
        wp_deregister_script('heartbeat');
    }
});

// ─── WebP automatique (si le serveur supporte) ───────────────────────────────
add_filter('wp_get_attachment_image_attributes', function ($attr) {
    if (isset($attr['src']) && str_contains($attr['src'], '/uploads/')) {
        $webp = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $attr['src']);
        if (@file_exists(str_replace(site_url('/'), ABSPATH, $webp))) {
            $attr['src'] = $webp;
        }
    }
    return $attr;
});

// ─── Sitemap XML (natif WP, activer) ─────────────────────────────────────────
add_filter('wp_sitemaps_enabled', '__return_true');

// Ajouter les produits WooCommerce au sitemap
add_filter('wp_sitemaps_post_types', function ($post_types) {
    if (!isset($post_types['product'])) {
        $post_types['product'] = get_post_type_object('product');
    }
    return $post_types;
});

// ─── Robots.txt ───────────────────────────────────────────────────────────────
add_filter('robots_txt', function ($output, $public) {
    $output .= "\nSitemap: " . home_url('/wp-sitemap.xml') . "\n";
    $output .= "Disallow: /wp-admin/\n";
    $output .= "Disallow: /wp-content/cache/\n";
    $output .= "Disallow: /?add-to-cart=\n";
    return $output;
}, 10, 2);

// ─── Minification inline styles WooCommerce (supprimer les balises style inline) ──
add_filter('woocommerce_enqueue_styles', function ($styles) {
    // On garde uniquement les styles essentiels
    unset($styles['woocommerce-smallscreen']); // géré par notre CSS
    return $styles;
});

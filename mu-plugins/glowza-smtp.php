<?php
/**
 * Plugin Name: Glowza SMTP
 * Description: Force SMTP via variables d'environnement. Contourne phpmail().
 */
defined('ABSPATH') || exit;

add_action('phpmailer_init', function (PHPMailer\PHPMailer\PHPMailer $mailer) {
    $host = defined('GLOWZA_SMTP_HOST') ? GLOWZA_SMTP_HOST : getenv('SMTP_HOST');
    $user = defined('GLOWZA_SMTP_USER') ? GLOWZA_SMTP_USER : getenv('SMTP_USER');
    $pass = defined('GLOWZA_SMTP_PASS') ? GLOWZA_SMTP_PASS : getenv('SMTP_PASS');
    $port = defined('GLOWZA_SMTP_PORT') ? GLOWZA_SMTP_PORT : (getenv('SMTP_PORT') ?: 587);

    if (!$host || !$user || !$pass) {
        return; // WP Mail SMTP gère si configuré via l'interface
    }

    $mailer->isSMTP();
    $mailer->Host       = $host;
    $mailer->SMTPAuth   = true;
    $mailer->Username   = $user;
    $mailer->Password   = $pass;
    $mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Port       = (int) $port;
    $mailer->From       = get_option('woocommerce_email_from_address', get_option('admin_email'));
    $mailer->FromName   = get_option('woocommerce_email_from_name', get_bloginfo('name'));
    $mailer->CharSet    = 'UTF-8';
});

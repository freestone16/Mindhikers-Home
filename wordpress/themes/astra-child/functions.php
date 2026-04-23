<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('astra-child-style', get_stylesheet_uri(), ['astra-parent-style']);
});

add_action('after_setup_theme', static function (): void {
    load_theme_textdomain('mindhikers-astra-child', get_stylesheet_directory() . '/languages');
});

// Carbon Fields dependency removed — theme now reads from CMS Core JSON via mindhikers_get_homepage_data()
// require_once __DIR__ . '/lib/carbon-fields.php';

/**
 * English page text translations are now handled via CMS Core JSON payload.
 * The old output buffering approach is deprecated because it is fragile and
 * context-unaware (can misfire inside attributes, scripts, or JSON).
 *
 * Translations should be defined in the `mindhikers_homepage_payload` JSON
 * for each locale (zh/en). The theme reads the correct locale automatically
 * via mindhikers_get_homepage_data().
 *
 * @deprecated 2026-04-23 Remove once all content is migrated to CMS Core JSON.
 */

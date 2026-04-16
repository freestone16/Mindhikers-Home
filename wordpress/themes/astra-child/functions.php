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

require_once __DIR__ . '/lib/carbon-fields.php';

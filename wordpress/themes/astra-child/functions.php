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

/**
 * Override Astra Header Builder button text for English pages
 */
add_action('wp_head', static function (): void {
    if (!function_exists('pll_current_language')) {
        return;
    }
    $lang = pll_current_language('slug');
    if ($lang !== 'en') {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var buttons = document.querySelectorAll('.ast-custom-button, .menu-link');
        buttons.forEach(function(btn) {
            if (btn.textContent.trim() === '开始联系') {
                btn.textContent = 'Get in Touch';
            }
        });
    });
    </script>
    <?php
});

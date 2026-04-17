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
 * Override Chinese text for English pages via output buffering
 */
add_action('template_redirect', static function (): void {
    if (!function_exists('pll_current_language')) {
        return;
    }
    $lang = pll_current_language('slug');
    if ($lang !== 'en') {
        return;
    }
    ob_start(static function (string $buffer): string {
        $replacements = [
            '开始联系' => 'Get in Touch',
            'aria-label="开始联系"' => 'aria-label="Get in Touch"',
            '联系方式' => 'Contact',
            '所在' => 'Location',
            '心行者 Mindhikers Staging' => 'MindHikers',
            '心行者 Mindhikers' => 'MindHikers',
            '心行者 MindHikers' => 'MindHikers',
            'Welcome to 心行者 MindHikers' => 'Welcome to MindHikers',
            '版权所有 © 2026 心行者 Mindhikers Staging' => 'Copyright © 2026 MindHikers',
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $buffer);
    });
});

add_action('shutdown', static function (): void {
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}, 0);

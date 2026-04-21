<?php
/**
 * Fix Blog Posts Language and Categories for Staging
 */

require_once '/var/www/html/wp-load.php';

$posts = get_posts([
    'post_type' => 'post',
    'posts_per_page' => 10,
    'orderby' => 'date',
    'order' => 'DESC',
    'suppress_filters' => true,
]);

$ai_tech = get_term_by('name', 'AI 技术', 'category');
$deep = get_term_by('name', '深度', 'category');

foreach ($posts as $post) {
    echo "Post {$post->ID}: {$post->post_title}\n";

    // Set language to zh
    if (function_exists('pll_set_post_language')) {
        pll_set_post_language($post->ID, 'zh');
        echo "  -> Language set to zh\n";
    } else {
        echo "  -> Polylang not available!\n";
    }

    // Assign categories
    if ($ai_tech && $deep) {
        wp_set_post_categories($post->ID, [$ai_tech->term_id, $deep->term_id]);
        echo "  -> Categories assigned: AI 技术, 深度\n";
    } else {
        echo "  -> Categories not found!\n";
    }
}

echo "Fix completed.\n";

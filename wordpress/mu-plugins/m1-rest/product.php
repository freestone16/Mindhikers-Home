<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

function m1_rest_product(WP_REST_Request $request): WP_REST_Response|WP_Error
{
    $slug = $request->get_param('slug');
    $lang = $request->get_param('lang') ?? 'zh';

    $validated = m1_validate_locale($lang);
    if (is_wp_error($validated)) {
        return $validated;
    }
    $lang = $validated;

    $query = new WP_Query(m1_query_with_lang([
        'post_type'      => 'mh_product',
        'name'           => $slug,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
    ], $lang));

    if (!$query->have_posts()) {
        return new WP_Error(
            'm1_product_not_found',
            sprintf('Product "%s" not found.', $slug),
            ['status' => 404]
        );
    }

    $post = $query->posts[0];
    wp_reset_postdata();

    $postId       = $post->ID;
    $subtitle     = m1_get_post_meta($postId, 'product_subtitle');
    $status       = m1_get_post_meta($postId, 'product_status');
    $entryUrl     = m1_get_post_meta($postId, 'product_entry_url');

    $statusLabels = [
        'idea'   => '构思中',
        'dev'    => '开发中',
        'beta'   => '公测',
        'live'   => '正式发布',
        'sunset' => '已下线',
    ];

    $permalink = get_permalink($postId);
    $oppositeLocale = ($lang === 'zh') ? 'en' : 'zh';
    $oppositePath   = ($oppositeLocale === 'en') ? '/en/' : '/';
    $switchHref     = $oppositePath . $slug;

    if (function_exists('pll_get_post')) {
        $translatedId = pll_get_post($postId, $oppositeLocale);
        if ($translatedId) {
            $translatedPermalink = get_permalink($translatedId);
            if ($translatedPermalink) {
                $switchHref = wp_make_link_relative($translatedPermalink);
            }
        }
    }

    return rest_ensure_response([
        'slug'        => $post->post_name,
        'title'       => get_the_title($postId),
        'subtitle'    => $subtitle,
        'description' => $post->post_excerpt ?: m1_plain_excerpt($post->post_content),
        'content'     => apply_filters('the_content', $post->post_content),
        'status'      => $status,
        'statusLabel' => $statusLabels[$status] ?? ucfirst($status),
        'entryUrl'    => $entryUrl,
        'coverImage'  => m1_get_featured_image_url($postId),
        'permalink'   => $permalink ? wp_make_link_relative($permalink) : '',
        'switchLanguage' => [
            'href'  => $switchHref,
            'label' => ($lang === 'zh') ? 'View in English' : '查看中文版',
        ],
    ]);
}

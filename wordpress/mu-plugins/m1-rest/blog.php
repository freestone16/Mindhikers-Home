<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

function m1_rest_blog_list(WP_REST_Request $request): WP_REST_Response|WP_Error
{
    $lang    = $request->get_param('lang') ?? 'zh';
    $page    = max(1, (int) $request->get_param('page'));
    $perPage = max(1, min(50, (int) ($request->get_param('per_page') ?? 10)));
    $category = $request->get_param('category');

    $validated = m1_validate_locale($lang);
    if (is_wp_error($validated)) {
        return $validated;
    }
    $lang = $validated;

    $args = m1_query_with_lang([
        'post_type'      => 'post',
        'posts_per_page' => $perPage,
        'paged'          => $page,
        'post_status'    => 'publish',
    ], $lang);

    if ($category) {
        $args['category_name'] = sanitize_title($category);
    }

    $query = new WP_Query($args);

    $items = [];
    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $items[] = m1_build_blog_item($post);
        }
    }
    wp_reset_postdata();

    $categories = get_categories([
        'taxonomy'   => 'category',
        'hide_empty' => true,
    ]);

    $catList = [];
    if (!is_wp_error($categories)) {
        foreach ($categories as $cat) {
            $catList[] = [
                'slug' => $cat->slug,
                'name' => $cat->name,
                'count' => (int) $cat->count,
            ];
        }
    }

    return rest_ensure_response([
        'items'    => $items,
        'total'    => (int) $query->found_posts,
        'page'     => $page,
        'perPage'  => $perPage,
        'totalPages' => (int) $query->max_num_pages,
        'categories' => $catList,
    ]);
}

function m1_rest_blog_detail(WP_REST_Request $request): WP_REST_Response|WP_Error
{
    $slug = $request->get_param('slug');
    $lang = $request->get_param('lang') ?? 'zh';

    $validated = m1_validate_locale($lang);
    if (is_wp_error($validated)) {
        return $validated;
    }
    $lang = $validated;

    $query = new WP_Query(m1_query_with_lang([
        'post_type'      => 'post',
        'name'           => $slug,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
    ], $lang));

    if (!$query->have_posts()) {
        return new WP_Error(
            'm1_blog_not_found',
            sprintf('Blog post "%s" not found.', $slug),
            ['status' => 404]
        );
    }

    $post = $query->posts[0];
    wp_reset_postdata();

    $authorId = (int) $post->post_author;

    return rest_ensure_response([
        'slug'       => $post->post_name,
        'title'      => get_the_title($post->ID),
        'content'    => apply_filters('the_content', $post->post_content),
        'excerpt'    => m1_plain_excerpt($post->post_excerpt ?: $post->post_content),
        'date'       => get_the_date('c', $post->ID),
        'coverImage' => m1_get_featured_image_url($post->ID),
        'categories' => m1_get_post_categories($post->ID),
        'author'     => [
            'name'   => get_the_author_meta('display_name', $authorId),
            'avatar' => get_avatar_url($authorId, ['size' => 96]),
        ],
    ]);
}

function m1_build_blog_item(WP_Post $post): array
{
    return [
        'slug'       => $post->post_name,
        'title'      => get_the_title($post->ID),
        'excerpt'    => m1_plain_excerpt($post->post_excerpt ?: $post->post_content),
        'date'       => get_the_date('c', $post->ID),
        'coverImage' => m1_get_featured_image_url($post->ID),
        'categories' => m1_get_post_categories($post->ID),
        'href'       => wp_make_link_relative(get_permalink($post->ID)),
    ];
}

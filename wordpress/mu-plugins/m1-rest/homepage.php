<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

function m1_rest_homepage(WP_REST_Request $request): WP_REST_Response|WP_Error
{
    $locale = $request->get_param('locale');
    $validated = m1_validate_locale($locale);

    if (is_wp_error($validated)) {
        return $validated;
    }

    $locale = $validated;

    return rest_ensure_response([
        'locale'        => $locale,
        'metadata'      => m1_build_metadata($locale),
        'navigation'    => m1_get_navigation($locale),
        'hero'          => m1_build_hero($locale),
        'about'         => m1_build_about($locale),
        'product'       => m1_build_product_section($locale),
        'blog'          => m1_get_blog_section($locale),
        'contact'       => m1_build_contact($locale),
        'productDetail' => m1_build_product_detail_defaults($locale),
    ]);
}

function m1_build_metadata(string $locale): array
{
    $cfTitle = m1_get_theme_option("hero_title_{$locale}");

    if ($cfTitle) {
        return [
            'title'       => $cfTitle . ' — 心行者 Mindhikers',
            'description' => m1_get_theme_option("hero_desc_{$locale}") ?: m1_get_metadata($locale)['description'],
        ];
    }

    return m1_get_metadata($locale);
}

function m1_build_hero(string $locale): array
{
    $static = m1_get_hero_static($locale);

    $primaryText = m1_get_theme_option("hero_cta_primary_text_{$locale}");
    $primaryUrl  = m1_get_theme_option('hero_cta_primary_url') ?: '#product';
    $secondaryText = m1_get_theme_option("hero_cta_secondary_text_{$locale}");
    $secondaryUrl  = m1_get_theme_option('hero_cta_secondary_url') ?: '/blog';

    return [
        'eyebrow'            => m1_get_theme_option("hero_eyebrow_{$locale}") ?: 'Editorial homepage',
        'title'              => m1_get_theme_option("hero_title_{$locale}") ?: ($locale === 'en'
            ? 'A brand home for research, products, and writing that still feels alive.'
            : '把研究、产品与表达，排成一个有呼吸感的品牌入口。'),
        'description'        => m1_get_theme_option("hero_desc_{$locale}") ?: '',
        'primaryAction'      => [
            'href'  => $primaryUrl,
            'label' => $primaryText ?: ($locale === 'en' ? 'See the current product entry' : '查看当前产品入口'),
        ],
        'secondaryAction'    => [
            'href'  => $secondaryUrl,
            'label' => $secondaryText ?: ($locale === 'en' ? 'Open the blog' : '进入博客'),
        ],
        'highlights'         => $static['highlights'],
        'statusLabel'        => $static['statusLabel'],
        'statusValue'        => $static['statusValue'],
        'availabilityLabel'  => $static['availabilityLabel'],
        'availabilityValue'  => $static['availabilityValue'],
        'panelTitle'         => $static['panelTitle'],
    ];
}

function m1_build_about(string $locale): array
{
    $static = m1_get_about_static($locale);

    return [
        'title'      => m1_get_theme_option("about_title_{$locale}") ?: 'About',
        'intro'      => $static['intro'],
        'paragraphs' => $static['paragraphs'],
        'notes'      => $static['notes'],
    ];
}

function m1_build_product_section(string $locale): array
{
    $section = m1_get_product_section($locale);

    $featuredQuery = new WP_Query(m1_query_with_lang([
        'post_type'      => 'mh_product',
        'posts_per_page' => 1,
        'meta_key'       => 'product_is_featured',
        'meta_value'     => 'yes',
        'post_status'    => 'publish',
    ], $locale));

    $featured = null;
    if ($featuredQuery->have_posts()) {
        $featured = m1_build_entry_card($featuredQuery->posts[0]);
    }
    wp_reset_postdata();

    if (!$featured) {
        $featured = m1_get_default_featured($locale);
    }

    $itemsQuery = new WP_Query(m1_query_with_lang([
        'post_type'      => 'mh_product',
        'posts_per_page' => 6,
        'post_status'    => 'publish',
        'meta_query'     => [
            [
                'key'     => 'product_is_featured',
                'compare' => '!=',
                'value'   => 'yes',
            ],
        ],
    ], $locale));

    $items = [];
    if ($itemsQuery->have_posts()) {
        foreach ($itemsQuery->posts as $post) {
            $items[] = m1_build_entry_card($post);
        }
    }
    wp_reset_postdata();

    if (empty($items)) {
        $items = m1_get_default_product_items($locale);
    }

    return [
        'title'       => $section['title'],
        'description' => $section['description'],
        'headline'    => $section['headline'],
        'featured'    => $featured,
        'items'       => $items,
    ];
}

function m1_get_default_featured(string $locale): array
{
    if ($locale === 'en') {
        return [
            'eyebrow'     => 'Featured release',
            'title'       => 'Golden Crucible',
            'description' => 'The first product entry under Mindhikers, built around research, writing, expression, and creator workflows. It is both a page and a signal of where the brand is heading.',
            'href'        => '/en/golden-crucible',
            'ctaLabel'    => 'Open product page',
            'meta'        => 'Live now',
        ];
    }

    return [
        'eyebrow'     => 'Featured release',
        'title'       => '黄金坩埚',
        'description' => '围绕研究、写作、表达与创作者工作流展开的首个产品入口。它承担的不只是一个页面，而是 Mindhikers 第一批品牌化实验的落点。',
        'href'        => '/golden-crucible',
        'ctaLabel'    => '打开产品页',
        'meta'        => 'Live now',
    ];
}

function m1_get_default_product_items(string $locale): array
{
    if ($locale === 'en') {
        return [
            ['eyebrow' => 'Brand system', 'title' => 'Bilingual homepage structure', 'description' => 'A calm bilingual structure gives Chinese and English readers their own clear point of entry.'],
            ['eyebrow' => 'Content flow', 'title' => 'Blog and research columns', 'description' => 'The homepage will gradually connect to writing and research so the site feels like a publication, not a frozen launch page.'],
            ['eyebrow' => 'Contact surface', 'title' => 'Collaboration window', 'description' => 'Contact should feel natural and visible without breaking the visual rhythm of the homepage.'],
        ];
    }

    return [
        ['eyebrow' => 'Brand system', 'title' => '双语首页结构', 'description' => '首页会同时承担中文与英文入口，让不同受众都能快速找到切入点。'],
        ['eyebrow' => 'Content flow', 'title' => '博客与研究栏目', 'description' => '后续会把 blog 与研究内容接入首页，让站点像持续更新的出版物，而不只是产品单页。'],
        ['eyebrow' => 'Contact surface', 'title' => '合作与联系窗口', 'description' => '把联系入口做得更自然，既能留住潜在合作，也不破坏整体节奏。'],
    ];
}

function m1_build_contact(string $locale): array
{
    $static = m1_get_contact_static($locale);
    $location = m1_get_theme_option("contact_location_{$locale}") ?: 'Shanghai / Remote';

    $socialMatrix = m1_get_theme_option_complex('contact_social_matrix');
    $links = [];

    if (!empty($socialMatrix)) {
        foreach ($socialMatrix as $entry) {
            $nameKey  = "platform_name_{$locale}";
            $name     = $entry[$nameKey] ?? '';
            $url      = $entry['platform_url'] ?? '';
            $qrImageId = $entry['platform_qr_image'] ?? '';

            if ($name && $url) {
                $link = [
                    'href'  => $url,
                    'label' => $name,
                    'note'  => '',
                ];

                if ($qrImageId) {
                    $qrImageUrl = wp_get_attachment_url((int) $qrImageId);
                    if ($qrImageUrl) {
                        $link['qrImage'] = $qrImageUrl;
                    }
                }

                $links[] = $link;
            }
        }
    }

    if (empty($links)) {
        $links = $static['links'];
    }

    return [
        'title'             => m1_get_theme_option("contact_title_{$locale}") ?: $static['headline'],
        'description'       => m1_get_theme_option("contact_desc_{$locale}") ?: '',
        'headline'          => $static['headline'],
        'emailLabel'        => $static['emailLabel'],
        'email'             => $static['email'],
        'locationLabel'     => $static['locationLabel'],
        'location'          => $location,
        'availabilityLabel' => $static['availabilityLabel'],
        'availability'      => $static['availability'],
        'links'             => $links,
    ];
}

function m1_build_product_detail_defaults(string $locale): array
{
    return m1_get_product_detail_defaults($locale);
}

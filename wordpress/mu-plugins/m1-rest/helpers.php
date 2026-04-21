<?php

/**
 * M1-R REST Helpers — shared utilities for Carbon Fields reading, Polylang integration, and data normalization.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Safely read a Carbon Fields theme option.
 * Returns empty string if Carbon Fields is not loaded or the option is empty.
 */
function m1_get_theme_option(string $key): string
{
    if (!function_exists('carbon_get_theme_option')) {
        return '';
    }

    $value = carbon_get_theme_option($key);

    if (is_array($value)) {
        return '';
    }

    return (string) $value;
}

/**
 * Read a Carbon Fields complex field (returns array of groups).
 */
function m1_get_theme_option_complex(string $key): array
{
    if (!function_exists('carbon_get_theme_option')) {
        return [];
    }

    $value = carbon_get_theme_option($key);

    return is_array($value) ? $value : [];
}

/**
 * Read a Carbon Fields post meta field.
 */
function m1_get_post_meta(int $postId, string $key): string
{
    if (!function_exists('carbon_get_post_meta')) {
        return get_post_meta($postId, $key, true) ?: '';
    }

    $value = carbon_get_post_meta($postId, $key);

    if (is_array($value)) {
        return '';
    }

    return (string) $value;
}

/**
 * Check whether Polylang is active and available.
 */
function m1_polylang_active(): bool
{
    return function_exists('PLL') || function_exists('pll_get_post');
}

function m1_polylang_lang_slug(string $locale): string
{
    return $locale;
}

/**
 * Build WP_Query args with Polylang language filter.
 * If Polylang is not active, returns args without language filter.
 */
function m1_query_with_lang(array $baseArgs, string $lang): array
{
    if (m1_polylang_active()) {
        $baseArgs['lang'] = m1_polylang_lang_slug($lang);
    }

    return $baseArgs;
}

/**
 * Validate locale parameter. Returns error WP_Error if invalid.
 *
 * @return string|WP_Error  The validated locale, or WP_Error on failure.
 */
function m1_validate_locale(string $locale): string|WP_Error
{
    $allowed = ['zh', 'en'];

    if (!in_array($locale, $allowed, true)) {
        return new WP_Error(
            'm1_invalid_locale',
            sprintf('Invalid locale "%s". Allowed values: zh, en.', $locale),
            ['status' => 400]
        );
    }

    return $locale;
}

/**
 * Get the featured image URL for a post.
 */
function m1_get_featured_image_url(int $postId): string
{
    $url = get_the_post_thumbnail_url($postId, 'full');

    return $url ?: '';
}

/**
 * Get categories for a post as an array of objects.
 *
 * @return array<int, array{slug: string, name: string}>
 */
function m1_get_post_categories(int $postId): array
{
    $categories = get_the_category($postId);

    if (!$categories || is_wp_error($categories)) {
        return [];
    }

    return array_map(static function (WP_Term $cat): array {
        return [
            'slug' => $cat->slug,
            'name' => $cat->name,
        ];
    }, $categories);
}

/**
 * Strip HTML tags and normalize whitespace — used for excerpts.
 */
function m1_plain_excerpt(string $html, int $maxLength = 200): string
{
    $text = wp_strip_all_tags($html);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);

    if (mb_strlen($text) > $maxLength) {
        $text = mb_substr($text, 0, $maxLength) . '…';
    }

    return $text;
}

/**
 * Map Product CPT post + meta to EntryCard shape.
 *
 * @return array{eyebrow: string, title: string, description: string, href?: string, ctaLabel?: string, meta?: string}
 */
function m1_build_entry_card(WP_Post $post): array
{
    $postId = $post->ID;
    $status = m1_get_post_meta($postId, 'product_status');
    $entryUrl = m1_get_post_meta($postId, 'product_entry_url');
    $subtitle = m1_get_post_meta($postId, 'product_subtitle');

    $statusMap = [
        'idea'   => 'Idea',
        'dev'    => 'In development',
        'beta'   => 'Public beta',
        'live'   => 'Live now',
        'sunset' => 'Sunset',
    ];

    $card = [
        'eyebrow'     => $status ? ($statusMap[$status] ?? ucfirst($status)) : '',
        'title'       => get_the_title($postId),
        'description' => $subtitle ?: m1_plain_excerpt($post->post_excerpt ?: ''),
    ];

    if ($entryUrl) {
        $card['href'] = $entryUrl;
        $card['ctaLabel'] = __('Open', 'mindhikers-m1');
    }

    if ($status) {
        $card['meta'] = $statusMap[$status] ?? ucfirst($status);
    }

    return $card;
}

/**
 * Get default navigation data for a locale.
 *
 * @return array{brand: string, links: array<int, array{href: string, label: string}>, switchLanguage: array{href: string, label: string}}
 */
function m1_get_navigation(string $locale): array
{
    $brand = '心行者 Mindhikers';

    if ($locale === 'en') {
        return [
            'brand' => $brand,
            'links' => [
                ['href' => '/en#about', 'label' => 'About'],
                ['href' => '/en#product', 'label' => 'Product'],
                ['href' => '/en#blog', 'label' => 'Blog'],
                ['href' => '/en#contact', 'label' => 'Contact'],
            ],
            'switchLanguage' => [
                'href'  => '/',
                'label' => '中文',
            ],
        ];
    }

    return [
        'brand' => $brand,
        'links' => [
            ['href' => '/#about', 'label' => 'About'],
            ['href' => '/#product', 'label' => 'Product'],
            ['href' => '/#blog', 'label' => 'Blog'],
            ['href' => '/#contact', 'label' => 'Contact'],
        ],
        'switchLanguage' => [
            'href'  => '/en',
            'label' => 'EN',
        ],
    ];
}

/**
 * Get default metadata for a locale.
 *
 * @return array{title: string, description: string}
 */
function m1_get_metadata(string $locale): array
{
    if ($locale === 'en') {
        return [
            'title'       => '心行者 Mindhikers',
            'description' => '心行者 Mindhikers is a bilingual brand home for product experiments, writing, and a quieter long-form creative practice.',
        ];
    }

    return [
        'title'       => '心行者 Mindhikers',
        'description' => '心行者 Mindhikers 是一个双语品牌主页，用来承载内容、产品实验、博客输出与长期创作协作。',
    ];
}

/**
 * Get default product section data for a locale.
 *
 * @return array{title: string, description: string, headline: string}
 */
function m1_get_product_section(string $locale): array
{
    if ($locale === 'en') {
        return [
            'title'       => 'Product',
            'description' => 'Start with one concrete release at the center of the page, then let the broader system grow around it.',
            'headline'    => 'The middle of the homepage should feel like a live program block, not a spec sheet.',
        ];
    }

    return [
        'title'       => 'Product',
        'description' => '先把一个足够真实的产品入口放在首页中央，再围绕它挂出内容、工作流和后续生长点。',
        'headline'    => '首页中段应该像一个正在播出的栏目，而不是说明书。',
    ];
}

/**
 * Get default blog section data for a locale.
 *
 * @return array{title: string, description: string, headline: string, cta: array{href: string, label: string}, emptyLabel: string, readArticleLabel: string}
 */
function m1_get_blog_section(string $locale): array
{
    if ($locale === 'en') {
        return [
            'title'            => 'Blog',
            'description'      => 'Writing will sit closer to the front of the brand. The homepage surfaces a small selection, while the archive lives in the full blog.',
            'headline'         => 'Bring recent writing onto the homepage instead of hiding the thinking deeper in the site.',
            'cta'              => [
                'href'  => '/blog',
                'label' => 'Browse all posts',
            ],
            'emptyLabel'       => 'The first wave of writing is still being curated.',
            'readArticleLabel' => 'Read article',
        ];
    }

    return [
        'title'            => 'Blog',
        'description'      => '这里会逐步积累方法、写作和产品思考。首页先展示最近几篇，完整归档放在博客页里。',
        'headline'         => '让首页直接露出最近的写作，而不是把内容藏在站点深处。',
        'cta'              => [
            'href'  => '/blog',
            'label' => '查看全部文章',
        ],
        'emptyLabel'       => '博客内容还在整理中，很快会补上第一批文章。',
        'readArticleLabel' => 'Read article',
    ];
}

/**
 * Get default hero static fields (not in Carbon Fields) for a locale.
 *
 * @return array{highlights: string[], statusLabel: string, statusValue: string, availabilityLabel: string, availabilityValue: string, panelTitle: string}
 */
function m1_get_hero_static(string $locale): array
{
    if ($locale === 'en') {
        return [
            'highlights'         => ['Bilingual entry point', 'Product experiments', 'Research and publishing'],
            'panelTitle'         => 'Quick Links',
            'quickLinks'         => [
                ['label' => 'Golden Crucible', 'href' => '/en/golden-crucible', 'tag' => 'Product'],
                ['label' => 'Latest blog posts', 'href' => '/en/blog', 'tag' => 'Content'],
            ],
        ];
    }

    return [
        'highlights'         => ['双语品牌入口', '产品化实验', '长期写作与研究'],
        'panelTitle'         => 'Quick Links',
        'quickLinks'         => [
            ['label' => '黄金坩埚', 'href' => '/golden-crucible', 'tag' => '产品'],
            ['label' => '博客最新文章', 'href' => '/blog', 'tag' => '内容'],
        ],
    ];
}

/**
 * Get default about static fields for a locale.
 *
 * @return array{intro: string, paragraphs: string[], notes: string[]}
 */
function m1_get_about_static(string $locale): array
{
    if ($locale === 'en') {
        return [
            'intro'      => '心行者 Mindhikers is not meant to read like a resume page. It is a brand homepage for making, thinking, and publishing in public with more intention.',
            'paragraphs' => [
                'The goal is to make room for product entries, recent writing, research threads, and future collaborations without collapsing everything into a single static summary.',
                'This refresh leans toward the feeling of an actively edited studio homepage: sections behave like columns, entry points feel like programming blocks, and motion supports reading instead of distracting from it.',
            ],
            'notes'      => [
                'Remove the template-like self-introduction',
                'Keep motion light but visible',
                'Make product, blog, and contact pathways obvious',
            ],
        ];
    }

    return [
        'intro'      => '心行者 Mindhikers 不是一张展示履历的页面，而是一个兼顾思考、制作与对外发布的品牌主页。',
        'paragraphs' => [
            '我们希望首页既能承接产品入口，也能容纳博客、研究线索和下一步动作，而不是把所有信息压成一页静态介绍。',
            '这次改版会更靠近一种"持续编排中的工作室主页"气质：内容像栏目，入口像节目单，动效和节奏帮助信息呼吸，而不是成为噱头。',
        ],
        'notes'      => [
            '去掉模板味的自我介绍',
            '保留轻量但明确的动效层次',
            '让产品、博客、联系入口一眼可见',
        ],
    ];
}

/**
 * Get default productDetail data for a locale.
 *
 * @return array{eyebrow: string, title: string, summary: string, bullets: string[], stageLabel: string, stageValue: string, returnHome: array{href: string, label: string}, switchLanguage: array{href: string, label: string}}
 */
function m1_get_product_detail_defaults(string $locale): array
{
    if ($locale === 'en') {
        return [
            'eyebrow'        => 'Featured Product',
            'title'          => 'Golden Crucible',
            'summary'        => 'Golden Crucible is the first public product entry under 心行者 Mindhikers, designed to hold early experiments around research, expression, and creator workflows.',
            'bullets'        => [
                'Organize research threads into durable topic assets',
                'Turn expression work into reusable scripts and structures',
                'Gradually shape creator workflows into productized tools',
            ],
            'stageLabel'     => 'Current stage',
            'stageValue'     => 'The branded entry page is live first. More product detail and richer assets will follow.',
            'returnHome'     => [
                'href'  => '/en',
                'label' => 'Back to homepage',
            ],
            'switchLanguage' => [
                'href'  => '/golden-crucible',
                'label' => '查看中文版',
            ],
        ];
    }

    return [
        'eyebrow'        => 'Featured Product',
        'title'          => '黄金坩埚',
        'summary'        => '黄金坩埚是 心行者 Mindhikers 当前最先对外承接的产品入口，用来容纳研究、表达与创作者工作流的第一批产品化尝试。',
        'bullets'        => [
            '把研究线索整理成可延续的主题资产',
            '把表达过程沉淀成可复用的脚本与结构',
            '把创作者工作流逐步变成工具化入口',
        ],
        'stageLabel'     => '当前阶段',
        'stageValue'     => '品牌入口页已建立，后续会继续补充产品能力与具体素材。',
        'returnHome'     => [
            'href'  => '/',
            'label' => '返回首页',
        ],
        'switchLanguage' => [
            'href'  => '/en/golden-crucible',
            'label' => 'View in English',
        ],
    ];
}

/**
 * Get default contact static fields for a locale.
 *
 * @return array{headline: string, emailLabel: string, locationLabel: string, availabilityLabel: string, availability: string, links: array<int, array{href: string, label: string, note: string}>}
 */
function m1_get_contact_static(string $locale): array
{
    $email = m1_get_theme_option('contact_email') ?: 'contactmindhiker@gmail.com';

    if ($locale === 'en') {
        return [
            'headline'         => 'Let contact feel like a continuation of the page, not a mandatory form block at the bottom.',
            'emailLabel'       => 'Email',
            'email'            => $email,
            'locationLabel'    => 'Base',
            'availabilityLabel' => 'Open to',
            'availability'     => 'Editorial collaboration, product experiments, thoughtful internet projects',
            'links'            => [
                [
                    'href'  => 'mailto:' . $email,
                    'label' => 'Send email',
                    'note'  => 'The fastest way to start a conversation',
                ],
                [
                    'href'  => '/',
                    'label' => 'Chinese home',
                    'note'  => 'Switch back to the Chinese entry',
                ],
                [
                    'href'  => '/blog',
                    'label' => 'Recent writing',
                    'note'  => 'Start with the blog to understand the practice',
                ],
            ],
        ];
    }

    return [
        'headline'         => '把联系入口做得像一段自然的续篇，而不是页面底部的表单义务。',
        'emailLabel'       => 'Email',
        'email'            => $email,
        'locationLabel'    => 'Base',
        'availabilityLabel' => 'Open to',
        'availability'     => 'Editorial collaboration, product experiments, thoughtful internet projects',
        'links'            => [
            [
                'href'  => 'mailto:' . $email,
                'label' => '发邮件',
                'note'  => '最快的合作入口',
            ],
            [
                'href'  => '/en',
                'label' => 'English home',
                'note'  => '查看英文版入口',
            ],
            [
                'href'  => '/blog',
                'label' => 'Recent writing',
                'note'  => '先从文章理解我们的工作方式',
            ],
        ],
    ];
}

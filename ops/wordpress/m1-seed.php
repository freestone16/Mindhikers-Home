<?php
/**
 * M1 Content Seeder — 创建 mh_homepage post 作为唯一数据源
 * 执行方式：php /opt/wp-bundle/seed/m1-seed.php
 */

require_once '/var/www/html/wp-load.php';

function m1_build_homepage_payload(string $locale): array
{
    $isEn = $locale === 'en';

    $heroQuickLinks = [
        [
            'href'  => '/golden-crucible',
            'label' => $isEn ? 'Golden Crucible' : '黄金坩埚',
            'tag'   => $isEn ? 'Product' : '产品',
        ],
        [
            'href'  => '/blog',
            'label' => $isEn ? 'Carbon-Silicon Evolution' : '碳硅进化论',
            'tag'   => $isEn ? 'Content' : '文章',
        ],
    ];

    $contactLinks = [
        [
            'href'    => 'mailto:hello@mindhikers.com',
            'label'   => $isEn ? 'Email' : '发邮件',
            'note'    => '',
            'qrImage' => '',
        ],
        [
            'href'    => $isEn ? '/' : '/en',
            'label'   => $isEn ? 'Chinese home' : 'English home',
            'note'    => '',
            'qrImage' => '',
        ],
        [
            'href'    => '/blog',
            'label'   => $isEn ? 'Blog' : '碳硅进化论',
            'note'    => '',
            'qrImage' => '',
        ],
    ];

    return [
        'locale'     => $locale,
        'metadata'   => [
            'title'       => '心行者 MindHikers',
            'description' => '',
        ],
        'navigation' => [
            'brand'          => '心行者 MindHikers',
            'links'          => [],
            'switchLanguage' => $isEn
                ? ['href' => '/', 'label' => '中文']
                : ['href' => '/en', 'label' => 'EN'],
        ],
        'hero'       => [
            'eyebrow'            => 'MindHikers',
            'title'              => $isEn
                ? 'A brand home for research, products, and writing that still feels alive.'
                : '心行者 MindHikers',
            'description'        => $isEn
                ? ''
                : '研究复杂问题 · 制作清晰表达 · 实验产品化路径',
            'primaryAction'      => [
                'href'  => '#product',
                'label' => $isEn ? '' : '查看产品',
            ],
            'secondaryAction'    => [
                'href'  => '/blog',
                'label' => $isEn ? '' : '阅读博客',
            ],
            'highlights'         => [],
            'statusLabel'        => '',
            'statusValue'        => '',
            'availabilityLabel'  => '',
            'availabilityValue'  => '',
            'panelTitle'         => 'Quick Links',
            'quickLinks'         => $heroQuickLinks,
        ],
        'about'      => [
            'title'      => 'About',
            'intro'      => $isEn
                ? ''
                : "<p>MindHikers 是一间一人工作室，主营两件事：</p>\n<p><strong>做内容：</strong>在 YouTube / Bilibili 上研究并讲述复杂议题，面向中文世界的知性探索者。</p>\n<p><strong>做产品：</strong>把创作工作流和研究方法沉淀成工具，先自用，再分享。</p>",
            'paragraphs' => [],
            'notes'      => [],
        ],
        'product'    => [
            'title'       => 'Product',
            'description' => $isEn
                ? 'A product experiment around research, writing, expression, and creator workflows.'
                : '一个围绕研究、写作、表达与创作者工作流展开的产品实验。',
            'headline'    => '',
            'featured'    => [],
            'items'       => [],
        ],
        'blog'       => [
            'title'            => $isEn ? 'Carbon-Silicon Evolution' : '碳硅进化论',
            'description'      => $isEn
                ? 'Three articles on "Carbon-Silicon Evolution" are now live, discussing AI-era education, embodied experience, and ethical growth.'
                : '三篇「碳硅进化论」文章已经上线，讨论 AI 时代的教育、肉身经验与伦理成长。',
            'headline'         => '',
            'cta'              => [
                'href'  => '/blog',
                'label' => $isEn ? 'Browse all posts' : '查看全部文章',
            ],
            'emptyLabel'       => '',
            'readArticleLabel' => 'Read article',
        ],
        'contact'    => [
            'title'             => 'Contact',
            'description'       => $isEn
                ? 'Have a collaboration idea, or just want to chat?'
                : '有合作想法，或者单纯想聊聊？',
            'headline'          => '',
            'emailLabel'        => 'Email',
            'email'             => 'hello@mindhikers.com',
            'locationLabel'     => 'Base',
            'location'          => 'Shanghai / Remote',
            'availabilityLabel' => 'Open to',
            'availability'      => '',
            'links'             => $contactLinks,
        ],
        'productDetail' => [
            'eyebrow'        => '',
            'title'          => '',
            'summary'        => '',
            'bullets'        => [],
            'stageLabel'     => '',
            'stageValue'     => '',
            'returnHome'     => [
                'href'  => $isEn ? '/en' : '/',
                'label' => $isEn ? 'Back to homepage' : '返回首页',
            ],
            'switchLanguage' => [
                'href'  => $isEn ? '/golden-crucible' : '/en/golden-crucible',
                'label' => $isEn ? '查看中文版' : 'View in English',
            ],
        ],
    ];
}

$locales = ['zh', 'en'];

foreach ($locales as $locale) {
    $payload = m1_build_homepage_payload($locale);
    $jsonPayload = wp_json_encode($payload);

    $existing = get_posts([
        'post_type'      => 'mh_homepage',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => 'mindhikers_locale',
        'meta_value'     => $locale,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ]);

    if (!empty($existing)) {
        $postId = $existing[0]->ID;
        update_post_meta($postId, 'mindhikers_homepage_payload', $jsonPayload);
        echo "Updated mh_homepage post for {$locale}: {$postId}\n";
    } else {
        $postId = wp_insert_post([
            'post_type'   => 'mh_homepage',
            'post_title'  => $locale === 'zh' ? 'Homepage ZH' : 'Homepage EN',
            'post_status' => 'publish',
            'post_name'   => "homepage-{$locale}",
        ]);
        if ($postId && !is_wp_error($postId)) {
            update_post_meta($postId, 'mindhikers_locale', $locale);
            update_post_meta($postId, 'mindhikers_homepage_payload', $jsonPayload);
            echo "Created mh_homepage post for {$locale}: {$postId}\n";
        } else {
            echo "Failed to create mh_homepage post for {$locale}\n";
        }
    }
}

echo "Homepage posts seeded.\n";

$zh_product_id = wp_insert_post([
    'post_type'    => 'mh_product',
    'post_title'   => '黄金坩埚',
    'post_content' => '一个围绕研究、写作、表达与创作者工作流展开的产品实验。2026年5月待开放：AI 辅助深度写作工作流、知识管理模板、创作者效率工具集。',
    'post_excerpt' => '一个围绕研究、写作、表达与创作者工作流展开的产品实验。',
    'post_status'  => 'publish',
    'post_name'    => 'golden-crucible',
]);

if ($zh_product_id && !is_wp_error($zh_product_id)) {
    if (function_exists('carbon_set_post_meta')) {
        carbon_set_post_meta($zh_product_id, 'product_subtitle', '已上线');
        carbon_set_post_meta($zh_product_id, 'product_status', 'live');
        carbon_set_post_meta($zh_product_id, 'product_entry_url', '/golden-crucible');
        carbon_set_post_meta($zh_product_id, 'product_is_featured', true);
    }
    echo "ZH Product created: {$zh_product_id}\n";

    if (function_exists('pll_set_post_language')) {
        pll_set_post_language($zh_product_id, 'zh');
    }
} else {
    echo "Failed to create ZH product\n";
    $zh_product_id = 0;
}

$en_product_id = wp_insert_post([
    'post_type'    => 'mh_product',
    'post_title'   => 'Golden Crucible',
    'post_content' => 'A product experiment around research, writing, expression, and creator workflows. Coming May 2026: AI-assisted deep writing workflow, knowledge management templates, creator efficiency toolkit.',
    'post_excerpt' => 'A product experiment around research, writing, expression, and creator workflows.',
    'post_status'  => 'publish',
    'post_name'    => 'golden-crucible',
]);

if ($en_product_id && !is_wp_error($en_product_id)) {
    if (function_exists('carbon_set_post_meta')) {
        carbon_set_post_meta($en_product_id, 'product_subtitle', 'Live now');
        carbon_set_post_meta($en_product_id, 'product_status', 'live');
        carbon_set_post_meta($en_product_id, 'product_entry_url', '/en/golden-crucible');
        carbon_set_post_meta($en_product_id, 'product_is_featured', false);
    }
    echo "EN Product created: {$en_product_id}\n";

    if (function_exists('pll_set_post_language')) {
        pll_set_post_language($en_product_id, 'en');
    }

    if ($zh_product_id && function_exists('pll_save_post_translations')) {
        pll_save_post_translations([
            'zh' => $zh_product_id,
            'en' => $en_product_id,
        ]);
        echo "Polylang translation linked.\n";
    }
} else {
    echo "Failed to create EN product\n";
}

echo "M1 seed completed.\n";

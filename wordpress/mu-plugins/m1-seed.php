<?php
/**
 * M1 Content Seeder for Staging
 * Run: php /tmp/m1-seed.php
 */

require_once '/var/www/html/wp-load.php';
require_once '/var/www/html/wp-content/plugins/carbon-fields/carbon-fields-plugin.php';

\Carbon_Fields\Carbon_Fields::boot();

// ============================================
// 1. Carbon Fields Theme Options
// ============================================

// Hero (ZH)
carbon_set_theme_option('hero_eyebrow_zh', 'Editorial homepage');
carbon_set_theme_option('hero_title_zh', '把研究、产品与表达，排成一个有呼吸感的品牌入口。');
carbon_set_theme_option('hero_desc_zh', '心行者 Mindhikers 正在把长期创作、内容实验与产品化尝试收拢成一个更完整的首页。它不想像简历，也不想像模板，而是像一个持续更新的工作现场。');
carbon_set_theme_option('hero_cta_primary_text_zh', '查看当前产品入口');
carbon_set_theme_option('hero_cta_primary_url', '#product');
carbon_set_theme_option('hero_cta_secondary_text_zh', '进入博客');
carbon_set_theme_option('hero_cta_secondary_url', '/blog');

// Hero (EN)
carbon_set_theme_option('hero_eyebrow_en', 'Editorial homepage');
carbon_set_theme_option('hero_title_en', 'A brand home for research, products, and writing that still feels alive.');
carbon_set_theme_option('hero_desc_en', '心行者 Mindhikers is becoming a quieter but more expressive front page for long-form creation, product experiments, and the kind of internet work that benefits from rhythm, not clutter.');
carbon_set_theme_option('hero_cta_primary_text_en', 'See the current product entry');
carbon_set_theme_option('hero_cta_primary_text_zh', '查看当前产品入口'); // duplicate line from above, ignore
carbon_set_theme_option('hero_cta_primary_url', '#product');
carbon_set_theme_option('hero_cta_secondary_text_en', 'Open the blog');
carbon_set_theme_option('hero_cta_secondary_url', '/blog');

// About (ZH) — 使用 PRD 品牌定位原文
carbon_set_theme_option('about_title_zh', '关于心行者');
carbon_set_theme_option('about_content_zh', "<p>欢迎来到 心行者 MindHikers。</p>
<p>老卢深信在 AI 时代人类需要保有自己的黄金精神，找到碳硅共生的边界。</p>
<p>这是一个以 AI 技术、碳硅共生哲学、脑神经科学（Neuroscience）为支点，撬动人生智慧的知识成长频道。我们专为『知性探索者』设计，致力于将艰深的认知科学、神经科学前沿成果，转化为可执行的行动指南，助你真正实现从'知道'到'做到'的跨越。</p>");

// About (EN)
carbon_set_theme_option('about_title_en', 'About Mindhikers');
carbon_set_theme_option('about_content_en', "<p>Welcome to 心行者 MindHikers.</p>
<p>Mindhikers believes that in the age of AI, humans need to preserve their own golden spirit and find the boundary of carbon-silicon symbiosis.</p>
<p>This is a knowledge growth channel that leverages AI technology, carbon-silicon symbiosis philosophy, and neuroscience to unlock life wisdom. Designed for the intellectually curious, we transform cutting-edge cognitive and neuroscience research into actionable guides—helping you truly bridge the gap from knowing to doing.</p>");

// Contact (ZH/EN)
carbon_set_theme_option('contact_email', 'ops@mindhikers.com');
carbon_set_theme_option('contact_location_zh', '上海 / 远程');
carbon_set_theme_option('contact_location_en', 'Shanghai / Remote');
carbon_set_theme_option('contact_title_zh', '联系');
carbon_set_theme_option('contact_title_en', 'Contact');
carbon_set_theme_option('contact_desc_zh', '如果你想讨论品牌、内容系统、产品实验，或者只是想交换一个更清晰的切题方式，这里是最直接的入口。');
carbon_set_theme_option('contact_desc_en', 'If you want to talk about brand, editorial systems, product experiments, or a more thoughtful corner of the internet, this is the cleanest place to start.');

// Social Matrix
$social_matrix = [
    [
        'platform_name_zh' => 'Twitter / X',
        'platform_name_en' => 'Twitter / X',
        'platform_url' => 'https://x.com/mindhikers',
    ],
    [
        'platform_name_zh' => 'Bilibili',
        'platform_name_en' => 'Bilibili',
        'platform_url' => 'https://space.bilibili.com/mindhikers',
    ],
    [
        'platform_name_zh' => '微信公众号',
        'platform_name_en' => 'WeChat Official Account',
        'platform_url' => '#',
    ],
];
carbon_set_theme_option('contact_social_matrix', $social_matrix);

// Product section titles
carbon_set_theme_option('product_title_zh', '产品');
carbon_set_theme_option('product_title_en', 'Product');
carbon_set_theme_option('product_desc_zh', '先把一个足够真实的产品入口放在首页中央，再围绕它挂出内容、工作流和后续生长点。');
carbon_set_theme_option('product_desc_en', 'Start with one concrete release at the center of the page, then let the broader system grow around it.');

// Blog section titles
carbon_set_theme_option('blog_title_zh', '博客');
carbon_set_theme_option('blog_title_en', 'Blog');
carbon_set_theme_option('blog_desc_zh', '这里会逐步积累方法、写作和产品思考。首页先展示最近几篇，完整归档放在博客页里。');
carbon_set_theme_option('blog_desc_en', 'Writing will sit closer to the front of the brand. The homepage surfaces a small selection, while the archive lives in the full blog.');

echo "Theme options seeded.\n";

// ============================================
// 2. Product CPT: 黄金坩埚 ZH + EN
// ============================================

$zh_product_id = wp_insert_post([
    'post_type'   => 'mh_product',
    'post_title'  => '黄金坩埚',
    'post_content' => '围绕研究、写作、表达与创作者工作流展开的首个产品入口。它承担的不只是一个页面，而是 Mindhikers 第一批品牌化实验的落点。',
    'post_excerpt' => '围绕研究、写作、表达与创作者工作流展开的首个产品入口。',
    'post_status' => 'publish',
    'post_name'   => 'golden-crucible',
]);

if ($zh_product_id && !is_wp_error($zh_product_id)) {
    carbon_set_post_meta($zh_product_id, 'product_subtitle', '你的个人 AI 战略伙伴');
    carbon_set_post_meta($zh_product_id, 'product_status', 'idea');
    carbon_set_post_meta($zh_product_id, 'product_entry_url', '/golden-crucible');
    carbon_set_post_meta($zh_product_id, 'product_is_featured', true);
    echo "ZH Product created: {$zh_product_id}\n";

    // Polylang language
    if (function_exists('pll_set_post_language')) {
        pll_set_post_language($zh_product_id, 'zh');
    }
} else {
    echo "Failed to create ZH product\n";
    $zh_product_id = 0;
}

$en_product_id = wp_insert_post([
    'post_type'   => 'mh_product',
    'post_title'  => 'Golden Crucible',
    'post_content' => 'The first product entry under Mindhikers, built around research, writing, expression, and creator workflows. It is both a page and a signal of where the brand is heading.',
    'post_excerpt' => 'The first product entry under Mindhikers, built around research, writing, expression, and creator workflows.',
    'post_status' => 'publish',
    'post_name'   => 'golden-crucible',
]);

if ($en_product_id && !is_wp_error($en_product_id)) {
    carbon_set_post_meta($en_product_id, 'product_subtitle', 'Your Personal AI Strategy Partner');
    carbon_set_post_meta($en_product_id, 'product_status', 'idea');
    carbon_set_post_meta($en_product_id, 'product_entry_url', '/en/golden-crucible');
    carbon_set_post_meta($en_product_id, 'product_is_featured', false);
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

// ============================================
// 3. Blog Categories (3 main + 4 sub each)
// ============================================

$main_cats = [
    'zh' => ['AI 技术', '碳硅共生', '脑神经科学'],
    'en' => ['AI Technology', 'Carbon-Silicon Symbiosis', 'Neuroscience'],
];

$sub_cats = [
    'zh' => ['深度', '速记', '视频', '工具'],
    'en' => ['Deep Dive', 'Notes', 'Video', 'Tools'],
];

$cat_map = [];

foreach ($main_cats['zh'] as $idx => $zh_name) {
    $en_name = $main_cats['en'][$idx];

    $zh_term = wp_insert_term($zh_name, 'category', ['slug' => sanitize_title($zh_name)]);
    if (function_exists('pll_set_term_language') && !is_wp_error($zh_term)) {
        pll_set_term_language($zh_term['term_id'], 'zh');
    }

    $en_term = wp_insert_term($en_name, 'category', ['slug' => sanitize_title($en_name . '-en')]);
    if (function_exists('pll_set_term_language') && !is_wp_error($en_term)) {
        pll_set_term_language($en_term['term_id'], 'en');
    }

    if (!is_wp_error($zh_term) && !is_wp_error($en_term) && function_exists('pll_save_term_translations')) {
        pll_save_term_translations([
            'zh' => $zh_term['term_id'],
            'en' => $en_term['term_id'],
        ]);
    }

    $parent_zh = is_wp_error($zh_term) ? null : $zh_term['term_id'];
    $parent_en = is_wp_error($en_term) ? null : $en_term['term_id'];

    foreach ($sub_cats['zh'] as $sub_idx => $sub_zh_name) {
        $sub_en_name = $sub_cats['en'][$sub_idx];

        $zh_sub = wp_insert_term($sub_zh_name, 'category', [
            'slug'   => sanitize_title($zh_name . '-' . $sub_zh_name),
            'parent' => $parent_zh,
        ]);
        if (function_exists('pll_set_term_language') && !is_wp_error($zh_sub)) {
            pll_set_term_language($zh_sub['term_id'], 'zh');
        }

        $en_sub = wp_insert_term($sub_en_name, 'category', [
            'slug'   => sanitize_title($en_name . '-' . $sub_en_name . '-en'),
            'parent' => $parent_en,
        ]);
        if (function_exists('pll_set_term_language') && !is_wp_error($en_sub)) {
            pll_set_term_language($en_sub['term_id'], 'en');
        }

        if (!is_wp_error($zh_sub) && !is_wp_error($en_sub) && function_exists('pll_save_term_translations')) {
            pll_save_term_translations([
                'zh' => $zh_sub['term_id'],
                'en' => $en_sub['term_id'],
            ]);
        }
    }
}

echo "Categories created.\n";

// ============================================
// 4. Assign existing 3 posts to categories
// ============================================

$posts = get_posts(['post_type' => 'post', 'posts_per_page' => 3, 'orderby' => 'date', 'order' => 'DESC']);
$ai_tech = get_term_by('name', 'AI 技术', 'category');
$deep = get_term_by('name', '深度', 'category');

if ($ai_tech && $deep) {
    foreach ($posts as $idx => $post) {
        // Assign first post to AI 技术 + 深度, others to same for simplicity
        wp_set_post_categories($post->ID, [$ai_tech->term_id, $deep->term_id]);
        if (function_exists('pll_set_post_language')) {
            pll_set_post_language($post->ID, 'zh');
        }
    }
    echo "Posts categorized.\n";
} else {
    echo "Could not find categories for post assignment.\n";
}

echo "M1 seed completed.\n";

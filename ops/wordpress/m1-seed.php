<?php
/**
 * M1 Content Seeder — 从生产环境 mindhikers.com 爬取的内容
 * 执行方式：php /opt/wp-bundle/seed/m1-seed.php
 */

require_once '/var/www/html/wp-load.php';
require_once '/var/www/html/wp-content/plugins/carbon-fields/carbon-fields-plugin.php';

\Carbon_Fields\Carbon_Fields::boot();

// ============================================
// 1. Carbon Fields Theme Options (ZH)
// ============================================

carbon_set_theme_option('hero_eyebrow_zh', 'MindHikers');
carbon_set_theme_option('hero_title_zh', '心行者 MindHikers');
carbon_set_theme_option('hero_desc_zh', '研究复杂问题 · 制作清晰表达 · 实验产品化路径');
carbon_set_theme_option('hero_cta_primary_text_zh', '查看产品');
carbon_set_theme_option('hero_cta_primary_url', '#product');
carbon_set_theme_option('hero_cta_secondary_text_zh', '阅读博客');
carbon_set_theme_option('hero_cta_secondary_url', '/blog');

// Quick Links
carbon_set_theme_option('hero_quick_links', [
    [
        'link_label_zh' => '黄金坩埚',
        'link_label_en' => 'Golden Crucible',
        'link_url' => '/golden-crucible',
        'link_tag_zh' => '产品',
        'link_tag_en' => 'Product',
    ],
    [
        'link_label_zh' => '碳硅进化论',
        'link_label_en' => 'Carbon-Silicon Evolution',
        'link_url' => '/blog',
        'link_tag_zh' => '文章',
        'link_tag_en' => 'Content',
    ],
]);

// About
carbon_set_theme_option('about_title_zh', 'About');
carbon_set_theme_option('about_content_zh', "<p>MindHikers 是一间一人工作室，主营两件事：</p>
<p><strong>做内容：</strong>在 YouTube / Bilibili 上研究并讲述复杂议题，面向中文世界的知性探索者。</p>
<p><strong>做产品：</strong>把创作工作流和研究方法沉淀成工具，先自用，再分享。</p>");

// Contact
carbon_set_theme_option('contact_email', 'hello@mindhikers.com');
carbon_set_theme_option('contact_location_zh', 'Shanghai / Remote');
carbon_set_theme_option('contact_location_en', 'Shanghai / Remote');
carbon_set_theme_option('contact_title_zh', 'Contact');
carbon_set_theme_option('contact_title_en', 'Contact');
carbon_set_theme_option('contact_desc_zh', '有合作想法，或者单纯想聊聊？');
carbon_set_theme_option('contact_desc_en', 'Have a collaboration idea, or just want to chat?');

// Social Matrix
carbon_set_theme_option('contact_social_matrix', [
    [
        'platform_name_zh' => '发邮件',
        'platform_name_en' => 'Email',
        'platform_url' => 'mailto:hello@mindhikers.com',
    ],
    [
        'platform_name_zh' => 'English home',
        'platform_name_en' => 'English home',
        'platform_url' => '/en',
    ],
    [
        'platform_name_zh' => '碳硅进化论',
        'platform_name_en' => 'Blog',
        'platform_url' => '/blog',
    ],
]);

// Product section titles
carbon_set_theme_option('product_title_zh', 'Product');
carbon_set_theme_option('product_title_en', 'Product');
carbon_set_theme_option('product_desc_zh', '一个围绕研究、写作、表达与创作者工作流展开的产品实验。');
carbon_set_theme_option('product_desc_en', 'A product experiment around research, writing, expression, and creator workflows.');

// Blog section titles
carbon_set_theme_option('blog_title_zh', '碳硅进化论');
carbon_set_theme_option('blog_title_en', 'Carbon-Silicon Evolution');
carbon_set_theme_option('blog_desc_zh', '三篇「碳硅进化论」文章已经上线，讨论 AI 时代的教育、肉身经验与伦理成长。');
carbon_set_theme_option('blog_desc_en', 'Three articles on "Carbon-Silicon Evolution" are now live, discussing AI-era education, embodied experience, and ethical growth.');

echo "Theme options seeded (ZH).\n";

// ============================================
// 2. Product CPT: 黄金坩埚 ZH + EN
// ============================================

$zh_product_id = wp_insert_post([
    'post_type'   => 'mh_product',
    'post_title'  => '黄金坩埚',
    'post_content' => '一个围绕研究、写作、表达与创作者工作流展开的产品实验。2026年5月待开放：AI 辅助深度写作工作流、知识管理模板、创作者效率工具集。',
    'post_excerpt' => '一个围绕研究、写作、表达与创作者工作流展开的产品实验。',
    'post_status' => 'publish',
    'post_name'   => 'golden-crucible',
]);

if ($zh_product_id && !is_wp_error($zh_product_id)) {
    carbon_set_post_meta($zh_product_id, 'product_subtitle', '已上线');
    carbon_set_post_meta($zh_product_id, 'product_status', 'live');
    carbon_set_post_meta($zh_product_id, 'product_entry_url', '/golden-crucible');
    carbon_set_post_meta($zh_product_id, 'product_is_featured', true);
    echo "ZH Product created: {$zh_product_id}\n";

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
    'post_content' => 'A product experiment around research, writing, expression, and creator workflows. Coming May 2026: AI-assisted deep writing workflow, knowledge management templates, creator efficiency toolkit.',
    'post_excerpt' => 'A product experiment around research, writing, expression, and creator workflows.',
    'post_status' => 'publish',
    'post_name'   => 'golden-crucible',
]);

if ($en_product_id && !is_wp_error($en_product_id)) {
    carbon_set_post_meta($en_product_id, 'product_subtitle', 'Live now');
    carbon_set_post_meta($en_product_id, 'product_status', 'live');
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

echo "M1 seed completed.\n";

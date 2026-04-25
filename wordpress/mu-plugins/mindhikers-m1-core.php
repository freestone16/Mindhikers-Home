<?php

/**
 * Plugin Name: Mindhikers M1 Core
 * Description: M1 内容模型核心 — Product CPT、Carbon Fields 字段定义、Hero/About/Contact 后台管理
 * Author: Mindhikers
 * Version: 1.0.0
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;

add_action('init', 'm1_register_product_cpt');

function m1_register_product_cpt(): void
{
    $labels = [
        'name'                  => _x('产品', 'Post type general name', 'mindhikers-m1'),
        'singular_name'         => _x('产品', 'Post type singular name', 'mindhikers-m1'),
        'menu_name'             => _x('产品', 'Admin Menu text', 'mindhikers-m1'),
        'add_new'               => __('新建', 'mindhikers-m1'),
        'add_new_item'          => __('新建产品', 'mindhikers-m1'),
        'edit_item'             => __('编辑产品', 'mindhikers-m1'),
        'new_item'              => __('新产品', 'mindhikers-m1'),
        'view_item'             => __('查看产品', 'mindhikers-m1'),
        'search_items'          => __('搜索产品', 'mindhikers-m1'),
        'not_found'             => __('未找到产品', 'mindhikers-m1'),
        'not_found_in_trash'    => __('回收站中没有产品', 'mindhikers-m1'),
    ];

    $args = [
        'labels'                => $labels,
        'public'                => true,
        'has_archive'           => true,
        'show_in_rest'          => true,
        'supports'              => ['title', 'editor', 'thumbnail', 'excerpt'],
        'rewrite'               => ['slug' => 'product'],
        'menu_icon'             => 'dashicons-products',
        'menu_position'         => 25,
    ];

    register_post_type('mh_product', $args);
}

add_action('carbon_fields_register_fields', 'm1_register_carbon_fields');

function m1_register_carbon_fields(): void
{
    // Hero 管理
    Container::make('theme_options', __('Hero 管理', 'mindhikers-m1'))
        ->set_page_menu_position(30)
        ->set_icon('dashicons-megaphone')
        ->add_fields([
            Field::make('text', 'hero_eyebrow_zh', __('眉标 (ZH)', 'mindhikers-m1')),
            Field::make('text', 'hero_eyebrow_en', __('眉标 (EN)', 'mindhikers-m1')),
            Field::make('text', 'hero_title_zh', __('标题 (ZH)', 'mindhikers-m1')),
            Field::make('text', 'hero_title_en', __('标题 (EN)', 'mindhikers-m1')),
            Field::make('textarea', 'hero_desc_zh', __('描述 (ZH)', 'mindhikers-m1')),
            Field::make('textarea', 'hero_desc_en', __('描述 (EN)', 'mindhikers-m1')),
            Field::make('text', 'hero_cta_primary_text_zh', __('主按钮文字 (ZH)', 'mindhikers-m1')),
            Field::make('text', 'hero_cta_primary_text_en', __('主按钮文字 (EN)', 'mindhikers-m1')),
            Field::make('text', 'hero_cta_primary_url', __('主按钮链接', 'mindhikers-m1')),
            Field::make('text', 'hero_cta_secondary_text_zh', __('次按钮文字 (ZH)', 'mindhikers-m1')),
            Field::make('text', 'hero_cta_secondary_text_en', __('次按钮文字 (EN)', 'mindhikers-m1')),
            Field::make('text', 'hero_cta_secondary_url', __('次按钮链接', 'mindhikers-m1')),
            Field::make('image', 'hero_image', __('配图', 'mindhikers-m1')),
            Field::make('complex', 'hero_quick_links', __('Quick Links', 'mindhikers-m1'))
                ->add_fields([
                    Field::make('text', 'link_label_zh', __('链接文字 (ZH)', 'mindhikers-m1')),
                    Field::make('text', 'link_label_en', __('链接文字 (EN)', 'mindhikers-m1')),
                    Field::make('text', 'link_url', __('链接地址', 'mindhikers-m1')),
                    Field::make('text', 'link_tag_zh', __('标签 (ZH)', 'mindhikers-m1'))
                        ->set_help_text(__('如：产品、内容、服务', 'mindhikers-m1')),
                    Field::make('text', 'link_tag_en', __('标签 (EN)', 'mindhikers-m1'))
                        ->set_help_text(__('如：Product、Content、Service', 'mindhikers-m1')),
                ])
                ->set_header_template('<% if (link_label_zh) { %> <%- link_label_zh %> <% } %>')
                ->set_help_text(__('首页右侧 Quick Links 面板内容，可添加多个链接', 'mindhikers-m1')),
        ]);

    // About 管理
    Container::make('theme_options', __('About 管理', 'mindhikers-m1'))
        ->set_page_menu_position(31)
        ->set_icon('dashicons-info')
        ->add_fields([
            Field::make('text', 'about_title_zh', __('标题 (ZH)', 'mindhikers-m1')),
            Field::make('text', 'about_title_en', __('标题 (EN)', 'mindhikers-m1')),
            Field::make('rich_text', 'about_content_zh', __('内容 (ZH)', 'mindhikers-m1')),
            Field::make('rich_text', 'about_content_en', __('内容 (EN)', 'mindhikers-m1')),
            Field::make('image', 'about_image', __('配图', 'mindhikers-m1')),
        ]);

    // Contact 管理
    Container::make('theme_options', __('Contact 管理', 'mindhikers-m1'))
        ->set_page_menu_position(32)
        ->set_icon('dashicons-email')
        ->add_fields([
            Field::make('text', 'contact_email', __('邮箱', 'mindhikers-m1')),
            Field::make('text', 'contact_location_zh', __('位置 (ZH)', 'mindhikers-m1')),
            Field::make('text', 'contact_location_en', __('位置 (EN)', 'mindhikers-m1')),
            Field::make('text', 'contact_title_zh', __('区块标题 (ZH)', 'mindhikers-m1')),
            Field::make('text', 'contact_title_en', __('区块标题 (EN)', 'mindhikers-m1')),
            Field::make('textarea', 'contact_desc_zh', __('区块描述 (ZH)', 'mindhikers-m1')),
            Field::make('textarea', 'contact_desc_en', __('区块描述 (EN)', 'mindhikers-m1')),
            Field::make('complex', 'contact_social_matrix', __('社交矩阵', 'mindhikers-m1'))
                ->add_fields([
                    Field::make('text', 'platform_name_zh', __('平台名称 (ZH)', 'mindhikers-m1')),
                    Field::make('text', 'platform_name_en', __('平台名称 (EN)', 'mindhikers-m1')),
                    Field::make('text', 'platform_url', __('链接', 'mindhikers-m1')),
                    Field::make('image', 'platform_qr_image', __('二维码图片', 'mindhikers-m1'))
                        ->set_help_text(__('用于微信公众号等需要展示二维码的平台', 'mindhikers-m1')),
                ])
                ->set_header_template('<% if (platform_name_zh) { %> <%- platform_name_zh %> <% } %>'),
        ]);

    // Product 管理（post meta）
    Container::make('post_meta', __('产品信息', 'mindhikers-m1'))
        ->where('post_type', '=', 'mh_product')
        ->add_fields([
            Field::make('text', 'product_subtitle', __('副标题 / 一句话定位', 'mindhikers-m1')),
            Field::make('select', 'product_status', __('状态', 'mindhikers-m1'))
                ->add_options([
                    'idea'      => __('构思中', 'mindhikers-m1'),
                    'dev'       => __('开发中', 'mindhikers-m1'),
                    'beta'      => __('公测', 'mindhikers-m1'),
                    'live'      => __('正式发布', 'mindhikers-m1'),
                    'sunset'    => __('已下线', 'mindhikers-m1'),
                ]),
            Field::make('text', 'product_entry_url', __('产品入口链接', 'mindhikers-m1')),
            Field::make('checkbox', 'product_is_featured', __('Featured 产品', 'mindhikers-m1')),
        ]);

    // Revalidate 配置
    Container::make('theme_options', __('Revalidate 配置', 'mindhikers-m1'))
        ->set_page_menu_position(35)
        ->set_icon('dashicons-update')
        ->add_fields([
            Field::make('text', 'mh_nextjs_revalidate_url', __('Next.js Revalidate URL', 'mindhikers-m1'))
                ->set_help_text(__('Example: https://www.mindhikers.com/api/revalidate — 必须与 Next.js 部署地址一致', 'mindhikers-m1'))
                ->set_default_value('https://www.mindhikers.com/api/revalidate'),
            Field::make('text', 'mh_revalidate_secret', __('Revalidate Secret', 'mindhikers-m1'))
                ->set_help_text(__('Must match REVALIDATE_SECRET in Next.js .env — 缺失时 webhook 静默跳过并记录 error_log', 'mindhikers-m1')),
        ]);
}

// M1-R REST Endpoints
add_action('rest_api_init', 'm1_register_rest_routes');

function m1_register_rest_routes(): void
{
    $m1_rest_dir = WP_PLUGIN_DIR . '/m1-rest';
    require_once $m1_rest_dir . '/helpers.php';
    require_once $m1_rest_dir . '/homepage.php';
    require_once $m1_rest_dir . '/product.php';
    require_once $m1_rest_dir . '/blog.php';

    register_rest_route('mindhikers/v1', '/homepage/(?P<locale>zh|en)', [
        'methods'             => 'GET',
        'callback'            => 'm1_rest_homepage',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('mindhikers/v1', '/product/(?P<slug>[a-zA-Z0-9_-]+)', [
        'methods'             => 'GET',
        'callback'            => 'm1_rest_product',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('mindhikers/v1', '/blog', [
        'methods'             => 'GET',
        'callback'            => 'm1_rest_blog_list',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('mindhikers/v1', '/blog/(?P<slug>[a-zA-Z0-9_-]+)', [
        'methods'             => 'GET',
        'callback'            => 'm1_rest_blog_detail',
        'permission_callback' => '__return_true',
    ]);
}

// M1-R Revalidate Webhooks
if (is_dir(WP_PLUGIN_DIR . '/m1-rest')) {
    require_once WP_PLUGIN_DIR . '/m1-rest/revalidate.php';
}

add_action('admin_init', 'm1_check_carbon_fields');

function m1_check_carbon_fields(): void
{
    if (!class_exists('Carbon_Fields\Carbon_Fields')) {
        add_action('admin_notices', function (): void {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>Mindhikers M1 Core:</strong> 
                    Carbon Fields 插件未激活。请安装并激活 Carbon Fields 以使用 Hero / About / Contact 管理功能。
                </p>
            </div>
            <?php
        });
    }
}

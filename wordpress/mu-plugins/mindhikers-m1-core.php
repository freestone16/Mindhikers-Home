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

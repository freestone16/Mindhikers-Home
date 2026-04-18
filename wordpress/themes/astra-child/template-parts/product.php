<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product 区块模板
 *
 * @var array $args
 * @var string $lang
 */
$lang = isset($args['lang']) && is_string($args['lang']) ? $args['lang'] : 'zh';

$products = new WP_Query([
    'post_type' => 'mh_product',
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'order' => 'ASC',
]);

// Manual Polylang filtering for CPT
$filtered_products = [];
if ($products->have_posts()) {
    while ($products->have_posts()) {
        $products->the_post();
        $post_lang = function_exists('pll_get_post_language') ? pll_get_post_language(get_the_ID()) : 'zh';
        if ($post_lang === $lang) {
            $filtered_products[] = get_post();
        }
    }
    wp_reset_postdata();
}
?>

<section class="mh-product-section" id="product">
    <div class="mh-container">
        <div class="mh-section-header mh-section-header--center">
            <h2 class="mh-product-title"><?php echo esc_html(carbon_get_theme_option("product_title_{$lang}") ?: ''); ?></h2>
            <p class="mh-product-description"><?php echo esc_html(carbon_get_theme_option("product_desc_{$lang}") ?: ''); ?></p>
        </div>
        
        <?php if (!empty($filtered_products)) : ?>
            <div class="mh-product-grid">
                <?php foreach ($filtered_products as $product_post) : ?>
                    <?php
                    setup_postdata($product_post);
                    $subtitle = carbon_get_the_post_meta('product_subtitle');
                    $status = carbon_get_the_post_meta('product_status');
                    $status_labels_zh = [
                        'idea' => '构思中',
                        'dev' => '开发中',
                        'beta' => '公测',
                        'live' => '正式发布',
                        'sunset' => '已下线',
                    ];
                    $status_labels_en = [
                        'idea' => 'Ideating',
                        'dev' => 'In Development',
                        'beta' => 'Public Beta',
                        'live' => 'Live',
                        'sunset' => 'Sunset',
                    ];
                    $status_map = $lang === 'en' ? $status_labels_en : $status_labels_zh;
                    $status_label = $status_map[$status] ?? '';
                    $entry_url = carbon_get_the_post_meta('product_entry_url');
                    ?>
                    
                    <article class="mh-product-card">
                        <?php if ($subtitle) : ?>
                            <span class="mh-product-card-eyebrow"><?php echo esc_html($subtitle); ?></span>
                        <?php endif; ?>
                        
                        <h3 class="mh-product-card-title"><?php echo esc_html(get_the_title($product_post)); ?></h3>
                        <p class="mh-product-card-description"><?php echo esc_html(get_the_excerpt($product_post)); ?></p>
                        
                        <?php if ($status_label) : ?>
                            <span class="mh-product-card-status <?php echo $status === 'live' ? 'mh-product-card-status--active' : ''; ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($entry_url) : ?>
                            <a href="<?php echo esc_url($entry_url); ?>" class="mh-product-card-cta">
                                <?php echo $lang === 'en' ? 'Learn more →' : '了解更多 →'; ?>
                            </a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="mh-text-center"><?php echo $lang === 'en' ? 'No products available yet.' : '暂无产品。'; ?></p>
        <?php endif; ?>
        
        <?php wp_reset_postdata(); ?>
    </div>
</section>

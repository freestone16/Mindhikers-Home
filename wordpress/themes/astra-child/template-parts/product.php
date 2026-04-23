<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product 区块模板
 *
 * @var array $args
 */
$payload = $args['payload'] ?? [];
$product = $payload['product'] ?? [];
$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh';

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
            <?php if (!empty($product['title'])) : ?>
                <h2 class="mh-product-title"><?php echo esc_html($product['title']); ?></h2>
            <?php endif; ?>
            <?php if (!empty($product['description'])) : ?>
                <p class="mh-product-description"><?php echo esc_html($product['description']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($filtered_products)) : ?>
            <div class="mh-product-grid">
                <?php foreach ($filtered_products as $product_post) : ?>
                    <?php
                    $subtitle = get_post_meta($product_post->ID, 'product_subtitle', true);
                    $status = get_post_meta($product_post->ID, 'product_status', true);
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
                    $status_label = is_string($status) && isset($status_map[$status]) ? $status_map[$status] : '';
                    $entry_url = get_post_meta($product_post->ID, 'product_entry_url', true);
                    ?>
                    
                    <article class="mh-product-card">
                        <?php if ($subtitle) : ?>
                            <span class="mh-product-card-eyebrow"><?php echo esc_html($subtitle); ?></span>
                        <?php endif; ?>
                        
                        <h3 class="mh-product-card-title"><?php echo esc_html(get_the_title($product_post)); ?></h3>
                        <p class="mh-product-card-description"><?php echo esc_html(get_the_excerpt($product_post)); ?></p>
                        
                        <?php if ($status_label) : ?>
                            <span class="mh-product-card-status <?php echo esc_attr($status === 'live' ? 'mh-product-card-status--active' : ''); ?>">
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

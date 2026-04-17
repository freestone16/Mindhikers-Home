<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Blog 区块模板
 *
 * @var array $args
 * @var string $lang
 */
$lang = isset($args['lang']) && is_string($args['lang']) ? $args['lang'] : 'zh';

$raw_posts = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 10,
    'orderby' => 'date',
    'order' => 'DESC',
]);

// Manual Polylang filtering (same pattern as product.php)
$filtered_posts = [];
if ($raw_posts->have_posts()) {
    while ($raw_posts->have_posts()) {
        $raw_posts->the_post();
        $post_lang = function_exists('pll_get_post_language') ? pll_get_post_language(get_the_ID()) : 'zh';
        if ($post_lang === $lang) {
            $filtered_posts[] = get_post();
        }
    }
    wp_reset_postdata();
}
?>

<section class="mh-blog-section" id="blog">
    <div class="mh-container">
        <div class="mh-section-header mh-section-header--center">
            <h2 class="mh-blog-title"><?php echo esc_html(carbon_get_theme_option("blog_title_{$lang}") ?: ''); ?></h2>
            <p class="mh-blog-description"><?php echo esc_html(carbon_get_theme_option("blog_desc_{$lang}") ?: ''); ?></p>
        </div>
        
        <?php if (!empty($filtered_posts)) : ?>
            <div class="mh-blog-grid">
                <?php foreach (array_slice($filtered_posts, 0, 3) as $post_item) : ?>
                    <?php
                    setup_postdata($post_item);
                    $categories = get_the_category($post_item->ID);
                    $primary_category = $categories[0] ?? null;
                    ?>
                    
                    <article class="mh-blog-card">
                        <?php if (has_post_thumbnail($post_item->ID)) : ?>
                            <?php echo get_the_post_thumbnail($post_item->ID, 'medium_large', ['class' => 'mh-blog-card-image']); ?>
                        <?php endif; ?>
                        
                        <div class="mh-blog-card-content">
                            <?php if ($primary_category) : ?>
                                <span class="mh-blog-card-category"><?php echo esc_html($primary_category->name); ?></span>
                            <?php endif; ?>
                            
                            <h3 class="mh-blog-card-title"><a href="<?php echo get_permalink($post_item->ID); ?>"><?php echo get_the_title($post_item->ID); ?></a></h3>
                            <p class="mh-blog-card-excerpt"><?php echo esc_html(get_the_excerpt($post_item)); ?></p>
                            <span class="mh-blog-card-meta"><?php echo get_the_date('', $post_item->ID); ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="mh-text-center"><?php echo $lang === 'en' ? 'No articles yet.' : '暂无文章。'; ?></p>
        <?php endif; ?>
        
        <?php wp_reset_postdata(); ?>
    </div>
</section>

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

$posts = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC',
    'lang' => $lang,
]);
?>

<section class="mh-blog-section" id="blog">
    <div class="mh-container">
        <div class="mh-section-header mh-section-header--center">
            <h2 class="mh-blog-title"><?php echo esc_html(carbon_get_theme_option("blog_title_{$lang}") ?: ''); ?></h2>
            <p class="mh-blog-description"><?php echo esc_html(carbon_get_theme_option("blog_desc_{$lang}") ?: ''); ?></p>
        </div>
        
        <?php if ($posts->have_posts()) : ?>
            <div class="mh-blog-grid">
                <?php while ($posts->have_posts()) : $posts->the_post(); ?>
                    <?php
                    $categories = get_the_category();
                    $primary_category = $categories[0] ?? null;
                    ?>
                    
                    <article class="mh-blog-card">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium_large', ['class' => 'mh-blog-card-image']); ?>
                        <?php endif; ?>
                        
                        <div class="mh-blog-card-content">
                            <?php if ($primary_category) : ?>
                                <span class="mh-blog-card-category"><?php echo esc_html($primary_category->name); ?></span>
                            <?php endif; ?>
                            
                            <h3 class="mh-blog-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p class="mh-blog-card-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
                            <span class="mh-blog-card-meta"><?php echo get_the_date(); ?></span>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p class="mh-text-center"><?php echo $lang === 'en' ? 'No articles yet.' : '暂无文章。'; ?></p>
        <?php endif; ?>
        
        <?php wp_reset_postdata(); ?>
    </div>
</section>

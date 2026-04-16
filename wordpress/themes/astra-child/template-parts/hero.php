<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hero 区块模板
 *
 * @var array $args
 * @var string $lang
 */
$lang = isset($args['lang']) && is_string($args['lang']) ? $args['lang'] : 'zh';
?>

<section class="mh-hero-section" id="hero">
    <div class="mh-container">
        <div class="mh-hero-content">
            <span class="mh-hero-eyebrow"><?php echo esc_html(carbon_get_theme_option("hero_eyebrow_{$lang}") ?: ''); ?></span>
            <h1 class="mh-hero-title"><?php echo esc_html(carbon_get_theme_option("hero_title_{$lang}") ?: ''); ?></h1>
            <p class="mh-hero-description"><?php echo esc_html(carbon_get_theme_option("hero_desc_{$lang}") ?: ''); ?></p>
            
            <div class="mh-hero-actions">
                <?php
                $primary_text = carbon_get_theme_option("hero_cta_primary_text_{$lang}");
                $primary_url = carbon_get_theme_option('hero_cta_primary_url');
                if ($primary_text && $primary_url) :
                    ?>
                    <a href="<?php echo esc_url($primary_url); ?>" class="mh-hero-cta-primary"><?php echo esc_html($primary_text); ?></a>
                <?php endif; ?>
                
                <?php
                $secondary_text = carbon_get_theme_option("hero_cta_secondary_text_{$lang}");
                $secondary_url = carbon_get_theme_option('hero_cta_secondary_url');
                if ($secondary_text && $secondary_url) :
                    ?>
                    <a href="<?php echo esc_url($secondary_url); ?>" class="mh-hero-cta-secondary"><?php echo esc_html($secondary_text); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

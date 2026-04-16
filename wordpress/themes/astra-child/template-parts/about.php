<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * About 区块模板
 *
 * @var array $args
 * @var string $lang
 */
$lang = isset($args['lang']) && is_string($args['lang']) ? $args['lang'] : 'zh';
?>

<section class="mh-about-section" id="about">
    <div class="mh-container">
        <h2 class="mh-about-title"><?php echo esc_html(carbon_get_theme_option("about_title_{$lang}") ?: ''); ?></h2>
        <div class="mh-about-content">
            <?php echo wp_kses_post(carbon_get_theme_option("about_content_{$lang}") ?: ''); ?>
        </div>
    </div>
</section>

<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contact 区块模板
 *
 * @var array $args
 * @var string $lang
 */
$lang = isset($args['lang']) && is_string($args['lang']) ? $args['lang'] : 'zh';

$email = carbon_get_theme_option('contact_email') ?: '';
$location = carbon_get_theme_option("contact_location_{$lang}") ?: '';
$social_links = carbon_get_theme_option('contact_social_matrix') ?: [];
?>

<section class="mh-contact-section" id="contact">
    <div class="mh-container">
        <div class="mh-section-header mh-section-header--center">
            <h2 class="mh-contact-title"><?php echo esc_html(carbon_get_theme_option("contact_title_{$lang}") ?: ''); ?></h2>
            <p class="mh-contact-description"><?php echo esc_html(carbon_get_theme_option("contact_desc_{$lang}") ?: ''); ?></p>
        </div>
        
        <div class="mh-contact-grid">
            <?php if ($email) : ?>
                <div class="mh-contact-card">
                    <div class="mh-contact-card-label"><?php echo $lang === 'en' ? 'Email' : '邮箱'; ?></div>
                    <div class="mh-contact-card-value"><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></div>
                </div>
            <?php endif; ?>
            
            <?php if ($location) : ?>
                <div class="mh-contact-card">
                    <div class="mh-contact-card-label"><?php echo $lang === 'en' ? 'Location' : '位置'; ?></div>
                    <div class="mh-contact-card-value"><?php echo esc_html($location); ?></div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($social_links)) : ?>
            <div class="mh-social-links">
                <?php foreach ($social_links as $link) : ?>
                    <?php
                    $platform_url = $link['platform_url'] ?? '';
                    $platform_name = $link["platform_name_{$lang}"] ?? ($link['platform_name_zh'] ?? '');
                    if (!$platform_url) {
                        continue;
                    }
                    ?>
                    <a href="<?php echo esc_url($platform_url); ?>" class="mh-social-link" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr($platform_name); ?>">
                        <?php echo esc_html(mb_substr($platform_name, 0, 2)); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

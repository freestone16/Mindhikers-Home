<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contact 区块模板
 *
 * @var array $args
 */
$payload = $args['payload'] ?? [];
$contact = $payload['contact'] ?? [];
$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh';
?>

<section class="mh-contact-section" id="contact">
    <div class="mh-container">
        <div class="mh-section-header mh-section-header--center">
            <?php if (!empty($contact['title'])) : ?>
                <h2 class="mh-contact-title"><?php echo esc_html($contact['title']); ?></h2>
            <?php endif; ?>
            <?php if (!empty($contact['description'])) : ?>
                <p class="mh-contact-description"><?php echo esc_html($contact['description']); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="mh-contact-grid">
            <?php if (!empty($contact['email'])) : ?>
                <div class="mh-contact-card">
                    <div class="mh-contact-card-label"><?php echo esc_html($contact['emailLabel'] ?? ($lang === 'en' ? 'Email' : '邮箱')); ?></div>
                    <div class="mh-contact-card-value">
                        <a href="mailto:<?php echo esc_url($contact['email']); ?>"><?php echo esc_html($contact['email']); ?></a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($contact['location'])) : ?>
                <div class="mh-contact-card">
                    <div class="mh-contact-card-label"><?php echo esc_html($contact['locationLabel'] ?? ($lang === 'en' ? 'Location' : '位置')); ?></div>
                    <div class="mh-contact-card-value"><?php echo esc_html($contact['location']); ?></div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($contact['availability'])) : ?>
                <div class="mh-contact-card">
                    <div class="mh-contact-card-label"><?php echo esc_html($contact['availabilityLabel'] ?? ($lang === 'en' ? 'Availability' : '可联系时间')); ?></div>
                    <div class="mh-contact-card-value"><?php echo esc_html($contact['availability']); ?></div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($contact['links'])) : ?>
            <div class="mh-social-links">
                <?php foreach ($contact['links'] as $link) : ?>
                    <?php
                    $href = $link['href'] ?? '';
                    $label = $link['label'] ?? '';
                    if (!$href || !$label) {
                        continue;
                    }
                    ?>
                    <a href="<?php echo esc_url($href); ?>" class="mh-social-link" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr($label); ?>">
                        <?php echo esc_html(mb_substr($label, 0, 2)); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hero 区块模板
 *
 * @var array $args
 */
$payload = $args['payload'] ?? [];
$hero = $payload['hero'] ?? [];
?>

<section class="mh-hero-section" id="hero">
    <div class="mh-container">
        <div class="mh-hero-content">
            <?php if (!empty($hero['eyebrow'])) : ?>
                <span class="mh-hero-eyebrow"><?php echo esc_html($hero['eyebrow']); ?></span>
            <?php endif; ?>
            
            <?php if (!empty($hero['title'])) : ?>
                <h1 class="mh-hero-title"><?php echo esc_html($hero['title']); ?></h1>
            <?php endif; ?>
            
            <?php if (!empty($hero['description'])) : ?>
                <p class="mh-hero-description"><?php echo esc_html($hero['description']); ?></p>
            <?php endif; ?>
            
            <div class="mh-hero-actions">
                <?php if (!empty($hero['primaryAction']['label']) && !empty($hero['primaryAction']['href'])) : ?>
                    <a href="<?php echo esc_url($hero['primaryAction']['href']); ?>" class="mh-hero-cta-primary">
                        <?php echo esc_html($hero['primaryAction']['label']); ?>
                    </a>
                <?php endif; ?>
                
                <?php if (!empty($hero['secondaryAction']['label']) && !empty($hero['secondaryAction']['href'])) : ?>
                    <a href="<?php echo esc_url($hero['secondaryAction']['href']); ?>" class="mh-hero-cta-secondary">
                        <?php echo esc_html($hero['secondaryAction']['label']); ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($hero['highlights'])) : ?>
                <ul class="mh-hero-highlights">
                    <?php foreach ($hero['highlights'] as $highlight) : ?>
                        <li><?php echo esc_html($highlight); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>

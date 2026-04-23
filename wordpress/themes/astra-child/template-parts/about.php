<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * About 区块模板
 *
 * @var array $args
 */
$payload = $args['payload'] ?? [];
$about = $payload['about'] ?? [];
?>

<section class="mh-about-section" id="about">
    <div class="mh-container">
        <?php if (!empty($about['title'])) : ?>
            <h2 class="mh-about-title"><?php echo esc_html($about['title']); ?></h2>
        <?php endif; ?>
        
        <div class="mh-about-content">
            <?php if (!empty($about['intro'])) : ?>
                <p class="mh-about-intro"><?php echo esc_html($about['intro']); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($about['paragraphs'])) : ?>
                <?php foreach ($about['paragraphs'] as $paragraph) : ?>
                    <p><?php echo esc_html($paragraph); ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (!empty($about['notes'])) : ?>
                <ul class="mh-about-notes">
                    <?php foreach ($about['notes'] as $note) : ?>
                        <li><?php echo esc_html($note); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_notices', static function (): void {
    if (class_exists('Carbon_Fields\Carbon_Fields')) {
        return;
    }
    ?>
    <div class="notice notice-warning">
        <p><strong>Mindhikers Astra Child:</strong> Carbon Fields 插件尚未激活。请安装并激活 Carbon Fields 以使用 Hero / About / Contact 管理功能。</p>
    </div>
    <?php
});

<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh';
$locale = $lang === 'en' ? 'en' : 'zh';

$payload = mindhikers_get_homepage_data($locale);
if (!is_array($payload)) {
    $payload = [];
}

get_header();

get_template_part('template-parts/hero', null, ['payload' => $payload]);
get_template_part('template-parts/about', null, ['payload' => $payload]);
get_template_part('template-parts/product', null, ['payload' => $payload]);
get_template_part('template-parts/blog', null, ['payload' => $payload]);
get_template_part('template-parts/contact', null, ['payload' => $payload]);

get_footer();

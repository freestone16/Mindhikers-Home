<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh';

get_header();

get_template_part('template-parts/hero', null, ['lang' => $lang]);
get_template_part('template-parts/about', null, ['lang' => $lang]);
get_template_part('template-parts/product', null, ['lang' => $lang]);
get_template_part('template-parts/blog', null, ['lang' => $lang]);
get_template_part('template-parts/contact', null, ['lang' => $lang]);

get_footer();

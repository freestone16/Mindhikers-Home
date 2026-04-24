<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

add_action('plugins_loaded', 'mindhikers_register_m1_rest_compat_functions', 20);

function mindhikers_register_m1_rest_compat_functions(): void
{
    if (!function_exists('mindhikers_m1_compat_homepage_payload')) {
        function mindhikers_m1_compat_homepage_payload(string $locale): array
        {
            if (function_exists('mindhikers_get_homepage_data')) {
                $payload = mindhikers_get_homepage_data($locale);
                if (is_array($payload) && $payload !== []) {
                    return $payload;
                }
            }

            return [
                'locale' => $locale,
                'metadata' => [
                    'title' => '心行者 Mindhikers',
                    'description' => $locale === 'en'
                        ? '心行者 Mindhikers is a bilingual brand home for product experiments, writing, and a quieter long-form creative practice.'
                        : '心行者 Mindhikers 是一个双语品牌主页，用来承载内容、产品实验、博客输出与长期创作协作。',
                ],
                'navigation' => [
                    'brand' => '心行者 Mindhikers',
                    'links' => $locale === 'en'
                        ? [
                            ['href' => '/en#about', 'label' => 'About'],
                            ['href' => '/en#product', 'label' => 'Product'],
                            ['href' => '/en#blog', 'label' => 'Blog'],
                            ['href' => '/en#contact', 'label' => 'Contact'],
                        ]
                        : [
                            ['href' => '/#about', 'label' => 'About'],
                            ['href' => '/#product', 'label' => 'Product'],
                            ['href' => '/#blog', 'label' => 'Blog'],
                            ['href' => '/#contact', 'label' => 'Contact'],
                        ],
                    'switchLanguage' => $locale === 'en'
                        ? ['href' => '/', 'label' => '中文']
                        : ['href' => '/en', 'label' => 'EN'],
                ],
                'hero' => m1_build_hero($locale),
                'about' => ['title' => 'About', 'intro' => '', 'paragraphs' => [], 'notes' => []],
                'product' => ['title' => 'Product', 'description' => '', 'headline' => '', 'featured' => [], 'items' => []],
                'blog' => [
                    'title' => 'Blog',
                    'description' => '',
                    'headline' => '',
                    'cta' => ['href' => '/blog', 'label' => $locale === 'en' ? 'Browse all posts' : '查看全部文章'],
                    'emptyLabel' => '',
                    'readArticleLabel' => 'Read article',
                ],
                'contact' => ['title' => 'Contact', 'description' => '', 'headline' => '', 'emailLabel' => 'Email', 'email' => 'contactmindhiker@gmail.com', 'locationLabel' => 'Base', 'location' => 'Shanghai / Remote', 'availabilityLabel' => 'Open to', 'availability' => '', 'links' => []],
                'productDetail' => ['eyebrow' => '', 'title' => '', 'summary' => '', 'bullets' => [], 'stageLabel' => '', 'stageValue' => '', 'returnHome' => ['href' => $locale === 'en' ? '/en' : '/', 'label' => $locale === 'en' ? 'Back to homepage' : '返回首页'], 'switchLanguage' => ['href' => $locale === 'en' ? '/' : '/en', 'label' => $locale === 'en' ? '查看中文版' : 'View in English']],
            ];
        }
    }

    if (!function_exists('m1_validate_locale')) {
        function m1_validate_locale(string $locale): string|WP_Error
        {
            if (!in_array($locale, ['zh', 'en'], true)) {
                return new WP_Error(
                    'm1_invalid_locale',
                    sprintf('Invalid locale "%s". Allowed values: zh, en.', $locale),
                    ['status' => 400]
                );
            }

            return $locale;
        }
    }

    if (!function_exists('m1_rest_homepage')) {
        function m1_rest_homepage(WP_REST_Request $request): WP_REST_Response|WP_Error
        {
            $validated = m1_validate_locale((string) $request->get_param('locale'));
            if (is_wp_error($validated)) {
                return $validated;
            }

            return rest_ensure_response(mindhikers_m1_compat_homepage_payload($validated));
        }
    }

    if (!function_exists('m1_get_theme_option')) {
        function m1_get_theme_option(string $key): string
        {
            if (!function_exists('carbon_get_theme_option')) {
                return '';
            }

            $value = carbon_get_theme_option($key);
            return is_array($value) ? '' : (string) $value;
        }
    }

    if (!function_exists('m1_get_theme_option_complex')) {
        function m1_get_theme_option_complex(string $key): array
        {
            if (!function_exists('carbon_get_theme_option')) {
                return [];
            }

            $value = carbon_get_theme_option($key);
            return is_array($value) ? $value : [];
        }
    }

    if (!function_exists('m1_get_hero_static')) {
        function m1_get_hero_static(string $locale): array
        {
            if ($locale === 'en') {
                return [
                    'highlights' => ['Bilingual entry point', 'Product experiments', 'Research and publishing'],
                    'panelTitle' => 'Quick Links',
                    'quickLinks' => [
                        ['label' => 'Golden Crucible', 'href' => '/en/golden-crucible', 'tag' => 'Product'],
                        ['label' => 'Latest blog posts', 'href' => '/en/blog', 'tag' => 'Content'],
                    ],
                ];
            }

            return [
                'highlights' => ['双语品牌入口', '产品化实验', '长期写作与研究'],
                'panelTitle' => 'Quick Links',
                'quickLinks' => [
                    ['label' => '黄金坩埚', 'href' => '/golden-crucible', 'tag' => '产品'],
                    ['label' => '博客最新文章', 'href' => '/blog', 'tag' => '内容'],
                ],
            ];
        }
    }

    if (!function_exists('m1_build_hero')) {
        function m1_build_hero(string $locale): array
        {
            if (function_exists('mindhikers_get_homepage_data')) {
                $payload = mindhikers_get_homepage_data($locale);
                if (isset($payload['hero']) && is_array($payload['hero'])) {
                    return $payload['hero'];
                }
            }

            $static = m1_get_hero_static($locale);

            $primaryText = m1_get_theme_option("hero_cta_primary_text_{$locale}");
            $primaryUrl = m1_get_theme_option('hero_cta_primary_url') ?: '#product';
            $secondaryText = m1_get_theme_option("hero_cta_secondary_text_{$locale}");
            $secondaryUrl = m1_get_theme_option('hero_cta_secondary_url') ?: '/blog';

            $quickLinks = m1_get_theme_option_complex('hero_quick_links');
            $quickLinksData = [];

            foreach ($quickLinks as $link) {
                if (!is_array($link)) {
                    continue;
                }

                $label = (string) ($link["link_label_{$locale}"] ?? '');
                $url = (string) ($link['link_url'] ?? '');
                $tag = (string) ($link["link_tag_{$locale}"] ?? '');

                if ($label !== '' && $url !== '') {
                    $quickLinksData[] = [
                        'label' => $label,
                        'href' => $url,
                        'tag' => $tag,
                    ];
                }
            }

            if ($quickLinksData === []) {
                $quickLinksData = $static['quickLinks'];
            }

            return [
                'eyebrow' => m1_get_theme_option("hero_eyebrow_{$locale}") ?: 'Editorial homepage',
                'title' => m1_get_theme_option("hero_title_{$locale}") ?: ($locale === 'en'
                    ? 'A brand home for research, products, and writing that still feels alive.'
                    : '把研究、产品与表达，排成一个有呼吸感的品牌入口。'),
                'description' => m1_get_theme_option("hero_desc_{$locale}") ?: '',
                'primaryAction' => [
                    'href' => $primaryUrl,
                    'label' => $primaryText ?: ($locale === 'en' ? 'See the current product entry' : '查看当前产品入口'),
                ],
                'secondaryAction' => [
                    'href' => $secondaryUrl,
                    'label' => $secondaryText ?: ($locale === 'en' ? 'Open the blog' : '进入博客'),
                ],
                'highlights' => $static['highlights'],
                'panelTitle' => $static['panelTitle'],
                'quickLinks' => $quickLinksData,
            ];
        }
    }

    if (!function_exists('m1_build_metadata')) {
        function m1_build_metadata(string $locale): array
        {
            $payload = mindhikers_m1_compat_homepage_payload($locale);
            return is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];
        }
    }

    if (!function_exists('m1_get_navigation')) {
        function m1_get_navigation(string $locale): array
        {
            $payload = mindhikers_m1_compat_homepage_payload($locale);
            return is_array($payload['navigation'] ?? null) ? $payload['navigation'] : [];
        }
    }

    if (!function_exists('m1_build_about')) {
        function m1_build_about(string $locale): array
        {
            $payload = mindhikers_m1_compat_homepage_payload($locale);
            return is_array($payload['about'] ?? null) ? $payload['about'] : [];
        }
    }

    if (!function_exists('m1_build_product_section')) {
        function m1_build_product_section(string $locale): array
        {
            $payload = mindhikers_m1_compat_homepage_payload($locale);
            return is_array($payload['product'] ?? null) ? $payload['product'] : [];
        }
    }

    if (!function_exists('m1_get_blog_section')) {
        function m1_get_blog_section(string $locale): array
        {
            $payload = mindhikers_m1_compat_homepage_payload($locale);
            return is_array($payload['blog'] ?? null) ? $payload['blog'] : [];
        }
    }

    if (!function_exists('m1_build_contact')) {
        function m1_build_contact(string $locale): array
        {
            $payload = mindhikers_m1_compat_homepage_payload($locale);
            return is_array($payload['contact'] ?? null) ? $payload['contact'] : [];
        }
    }

    if (!function_exists('m1_build_product_detail_defaults')) {
        function m1_build_product_detail_defaults(string $locale): array
        {
            $payload = mindhikers_m1_compat_homepage_payload($locale);
            return is_array($payload['productDetail'] ?? null) ? $payload['productDetail'] : [];
        }
    }
}

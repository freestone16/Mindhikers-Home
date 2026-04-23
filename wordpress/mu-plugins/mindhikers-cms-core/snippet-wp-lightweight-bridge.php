<?php
/**
 * Mindhikers WP Lightweight Customization — Data Bridge Snippet
 * 
 * 这个 Snippet 为现有 CMS Core 添加主题数据桥接层，让 WordPress 主题能直接
 * 读取 CMS Core 的 JSON 数据并渲染首页。
 * 
 * 安装方式：
 * 1. WP Admin → Snippets → Add New
 * 2. 标题：mhs04-wp-lightweight-bridge
 * 3. 粘贴以下代码
 * 4. Run snippet everywhere
 * 5. Save Changes and Activate
 * 
 * 前置条件：
 * - 已安装并激活 Astra Child 主题（上传 astra-child-wp-lightweight.zip）
 * - 已有 mh_homepage post type（由 mindhikers-cms-core 提供）
 */

if (!defined('ABSPATH')) {
    exit;
}

// 防止重复加载
if (function_exists('mindhikers_get_homepage_data')) {
    return;
}

/**
 * 获取首页数据（带缓存）
 * 
 * @param string $locale 'zh' 或 'en'
 * @return array 规范化的首页数据
 */
function mindhikers_get_homepage_data(string $locale = 'zh'): array
{
    $locale = in_array(strtolower(trim($locale)), ['zh', 'en'], true) 
        ? strtolower(trim($locale)) 
        : 'zh';
    
    $cache_key = "mindhikers_homepage_data_{$locale}";
    
    // 检查缓存
    $cached = get_transient($cache_key);
    if (is_array($cached)) {
        return $cached;
    }
    
    // 查询 mh_homepage post
    $posts = get_posts([
        'post_type' => 'mh_homepage',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_key' => 'mindhikers_locale',
        'meta_value' => $locale,
        'orderby' => 'modified',
        'order' => 'DESC',
    ]);
    
    if (empty($posts)) {
        // 返回默认空结构
        $data = mindhikers_get_default_homepage_payload($locale);
        set_transient($cache_key, $data, 6 * HOUR_IN_SECONDS);
        return $data;
    }
    
    // 读取并解析 JSON
    $raw_payload = (string) get_post_meta($posts[0]->ID, 'mindhikers_homepage_payload', true);
    $payload = json_decode($raw_payload, true);
    
    if (!is_array($payload)) {
        $data = mindhikers_get_default_homepage_payload($locale);
        set_transient($cache_key, $data, 6 * HOUR_IN_SECONDS);
        return $data;
    }
    
    // 规范化数据（简化版，只提取主题需要的字段）
    $data = mindhikers_normalize_homepage_payload($payload, $locale);
    
    // 写入缓存
    set_transient($cache_key, $data, 6 * HOUR_IN_SECONDS);
    
    return $data;
}

/**
 * 清除首页缓存
 * 
 * @param string $locale 'zh' 或 'en'
 */
function mindhikers_clear_homepage_cache(string $locale = 'zh'): void
{
    $locale = in_array(strtolower(trim($locale)), ['zh', 'en'], true) 
        ? strtolower(trim($locale)) 
        : 'zh';
    
    delete_transient("mindhikers_homepage_data_{$locale}");
}

/**
 * 默认首页数据结构
 */
function mindhikers_get_default_homepage_payload(string $locale): array
{
    $is_en = $locale === 'en';
    
    return [
        'locale' => $locale,
        'metadata' => [
            'title' => $is_en ? 'MindHikers' : '心行者 Mindhikers',
            'description' => '',
        ],
        'navigation' => [
            'brand' => $is_en ? 'MindHikers' : '心行者 Mindhikers',
            'links' => [],
            'switchLanguage' => ['href' => '', 'label' => ''],
        ],
        'hero' => [
            'eyebrow' => '',
            'title' => '',
            'description' => '',
            'primaryAction' => ['href' => '', 'label' => ''],
            'secondaryAction' => ['href' => '', 'label' => ''],
            'highlights' => [],
            'panelTitle' => '',
            'quickLinks' => [],
        ],
        'about' => [
            'title' => '',
            'intro' => '',
            'paragraphs' => [],
            'notes' => [],
        ],
        'product' => [
            'title' => '',
            'description' => '',
            'headline' => '',
            'featured' => [],
            'items' => [],
        ],
        'blog' => [
            'title' => '',
            'description' => '',
            'headline' => '',
            'cta' => ['href' => '', 'label' => ''],
            'emptyLabel' => $is_en ? 'No articles yet.' : '暂无文章。',
            'readArticleLabel' => '',
        ],
        'contact' => [
            'title' => '',
            'description' => '',
            'headline' => '',
            'emailLabel' => $is_en ? 'Email' : '邮箱',
            'email' => '',
            'locationLabel' => $is_en ? 'Location' : '位置',
            'location' => '',
            'availabilityLabel' => $is_en ? 'Availability' : '可联系时间',
            'availability' => '',
            'links' => [],
        ],
    ];
}

/**
 * 规范化首页数据（简化版）
 */
function mindhikers_normalize_homepage_payload(array $payload, string $locale): array
{
    $default = mindhikers_get_default_homepage_payload($locale);
    
    // 合并传入数据和默认值
    return array_replace_recursive($default, $payload);
}

/**
 * 缓存清除钩子
 * 在保存 mh_homepage 时自动清除对应 locale 的缓存
 */
add_action('save_post_mh_homepage', function ($post_id) {
    $locale = (string) get_post_meta($post_id, 'mindhikers_locale', true);
    mindhikers_clear_homepage_cache($locale);
}, 20);

/**
 * 站点设置更新时清除所有缓存
 */
add_action('updated_option', function ($option, $old_value, $value) {
    if ($option !== 'mindhikers_site_settings_payload') {
        return;
    }
    
    if (serialize($old_value) === serialize($value)) {
        return;
    }
    
    mindhikers_clear_homepage_cache('zh');
    mindhikers_clear_homepage_cache('en');
}, 10, 3);

/**
 * 让 mh_homepage 可被主题查询（如果还没设置）
 * 这个 hook 在 register_post_type 之后运行，确保修改生效
 */
add_action('init', function () {
    global $wp_post_types;
    
    if (isset($wp_post_types['mh_homepage'])) {
        // 只修改查询相关属性，不改写其他设置
        $wp_post_types['mh_homepage']->public = true;
        $wp_post_types['mh_homepage']->publicly_queryable = false;
        $wp_post_types['mh_homepage']->has_archive = false;
        $wp_post_types['mh_homepage']->rewrite = false;
    }
}, 20);

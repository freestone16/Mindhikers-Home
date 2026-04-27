<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (class_exists('Mindhikers_Cms_Core')) {
    return;
}

require_once __DIR__ . '/src/compat/m1-rest-functions.php';

final class Mindhikers_Cms_Core
{
    private static ?self $instance = null;
    private string $homepagePostType = 'mh_homepage';
    private string $homepagePayloadMeta = 'mindhikers_homepage_payload';
    private string $localeMeta = 'mindhikers_locale';
    private string $siteSettingsOption = 'mindhikers_site_settings_payload';
    private string $restNamespace = 'mindhikers/v1';
    private string $homepageNonceAction = 'mindhikers_homepage_payload_save';
    private string $homepageNonceName = 'mindhikers_homepage_payload_nonce';

    public static function boot(): void
    {
        if (self::$instance instanceof self) {
            return;
        }

        self::$instance = new self();
        self::$instance->registerHooks();
        self::$instance->registerCoreState();
    }

    private function registerHooks(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerMeta']);
        add_action('add_meta_boxes', [$this, 'registerMetaBoxes']);
        add_action("save_post_{$this->homepagePostType}", [$this, 'saveHomepageMeta'], 10, 3);
        add_action('admin_menu', [$this, 'registerAdminPages']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
        add_filter('rest_pre_serve_request', [$this, 'serveMindhikersRestResponse'], 10, 4);
        add_action('updated_option', [$this, 'handleUpdatedOption'], 10, 3);
        add_action('template_redirect', [$this, 'redirectManageDomainRoot']);
    }

    /**
     * Redirect the management domain root path to /wp-admin/.
     *
     * Only fires when ALL of these conditions are true:
     *  - Request method is GET or HEAD
     *  - Request URI is exactly "/" (root path)
     *  - HTTP_HOST matches the configured management domain
     *  - Not a CLI, cron, AJAX, or REST request
     */
    public function redirectManageDomainRoot(): void
    {
        if (defined('WP_CLI') && WP_CLI) {
            return;
        }
        if (defined('DOING_CRON') && DOING_CRON) {
            return;
        }
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        if (!in_array($method, ['GET', 'HEAD'], true)) {
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        // Only match exact root: "/" or "/?..." (with optional query string)
        if ($requestUri !== '/' && strpos($requestUri, '/?') !== 0) {
            return;
        }

        // Derive the management domain from WP_HOME (already set to the
        // canonical management URL). This avoids hardcoding the hostname.
        $manageDomain = wp_parse_url(home_url(), PHP_URL_HOST);
        if (!$manageDomain) {
            return;
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';
        // Strip port if present for comparison
        $hostWithoutPort = strtolower(explode(':', $host)[0]);

        if ($hostWithoutPort !== strtolower($manageDomain)) {
            return;
        }

        wp_safe_redirect(admin_url(), 302);
        exit;
    }

    private function registerCoreState(): void
    {
        $this->registerPostType();
        $this->registerMeta();
    }

    /**
     * Get homepage data for theme rendering with caching support.
     *
     * @param string $locale 'zh' or 'en'
     * @return array Normalized homepage payload
     */
    public function getHomepageDataForTheme(string $locale = 'zh'): array
    {
        $locale = $this->sanitizeLocale($locale);
        $cacheKey = "mindhikers_homepage_data_{$locale}";

        $cached = get_transient($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $post = $this->findHomepagePostByLocale($locale);

        if (!$post instanceof WP_Post) {
            $data = $this->getDefaultHomepagePayload($locale);
            set_transient($cacheKey, $data, 6 * HOUR_IN_SECONDS);
            return $data;
        }

        $rawPayload = (string) get_post_meta($post->ID, $this->homepagePayloadMeta, true);
        $payload = $this->decodeJsonPayload($rawPayload);
        $data = $this->normalizeHomepagePayload(is_array($payload) ? $payload : [], $locale);

        set_transient($cacheKey, $data, 6 * HOUR_IN_SECONDS);

        return $data;
    }

    /**
     * Clear homepage transient cache for a specific locale.
     *
     * @param string $locale 'zh' or 'en'
     */
    public function clearHomepageTransient(string $locale = 'zh'): void
    {
        $locale = $this->sanitizeLocale($locale);
        delete_transient("mindhikers_homepage_data_{$locale}");
    }

    public function registerPostType(): void
    {
        register_post_type($this->homepagePostType, [
            'label' => __('Mindhikers Homepages', 'mindhikers-cms-core'),
            'labels' => [
                'name' => __('Mindhikers Homepages', 'mindhikers-cms-core'),
                'singular_name' => __('Mindhikers Homepage', 'mindhikers-cms-core'),
                'add_new_item' => __('Add Homepage Entry', 'mindhikers-cms-core'),
                'edit_item' => __('Edit Homepage Entry', 'mindhikers-cms-core'),
                'view_item' => __('View Homepage Entry', 'mindhikers-cms-core'),
            ],
            'public' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-layout',
            'supports' => ['title', 'revisions'],
            'has_archive' => false,
            'rewrite' => false,
            'capability_type' => 'page',
            'map_meta_cap' => true,
        ]);
    }

    public function registerMeta(): void
    {
        register_post_meta($this->homepagePostType, $this->localeMeta, [
            'single' => true,
            'type' => 'string',
            'show_in_rest' => false,
            'sanitize_callback' => [$this, 'sanitizeLocale'],
            'auth_callback' => static fn () => current_user_can('edit_posts'),
        ]);

        register_post_meta($this->homepagePostType, $this->homepagePayloadMeta, [
            'single' => true,
            'type' => 'string',
            'show_in_rest' => false,
            'sanitize_callback' => [$this, 'sanitizeJsonPayload'],
            'auth_callback' => static fn () => current_user_can('edit_posts'),
        ]);
    }

    public function registerMetaBoxes(): void
    {
        add_meta_box(
            'mindhikers-homepage-content',
            __('Homepage Structured Content', 'mindhikers-cms-core'),
            [$this, 'renderHomepageMetaBox'],
            $this->homepagePostType,
            'normal',
            'high'
        );
    }

    public function registerAdminPages(): void
    {
        add_options_page(
            __('Mindhikers CMS Settings', 'mindhikers-cms-core'),
            __('Mindhikers CMS', 'mindhikers-cms-core'),
            'manage_options',
            'mindhikers-cms-core',
            [$this, 'renderSettingsPage']
        );
    }

    public function registerSettings(): void
    {
        register_setting('mindhikers_cms_core', $this->siteSettingsOption, [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitizeJsonPayload'],
            'default' => $this->encodeJson($this->getDefaultSiteSettings()),
        ]);
    }

    public function registerRestRoutes(): void
    {
        register_rest_route($this->restNamespace, '/site-settings', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getSiteSettings'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($this->restNamespace, '/homepage/(?P<locale>zh|en)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'getHomepageByLocale'],
            'permission_callback' => '__return_true',
            'args' => [
                'locale' => [
                    'required' => true,
                    'sanitize_callback' => [$this, 'sanitizeLocale'],
                ],
            ],
        ]);
    }

    public function renderHomepageMetaBox(WP_Post $post): void
    {
        wp_nonce_field($this->homepageNonceAction, $this->homepageNonceName);

        $locale = $this->sanitizeLocale((string) get_post_meta($post->ID, $this->localeMeta, true));
        $rawPayload = (string) get_post_meta($post->ID, $this->homepagePayloadMeta, true);
        $payload = $this->decodeJsonPayload($rawPayload);

        if (!is_array($payload)) {
            $payload = $this->getDefaultHomepagePayload($locale ?: 'zh');
        }

        if ($locale === '') {
            $locale = 'zh';
        }
        ?>
        <p>
            <label for="mindhikers-homepage-locale"><strong><?php esc_html_e('Locale', 'mindhikers-cms-core'); ?></strong></label>
        </p>
        <p>
            <select id="mindhikers-homepage-locale" name="mindhikers_homepage_locale">
                <option value="zh" <?php selected($locale, 'zh'); ?>>zh</option>
                <option value="en" <?php selected($locale, 'en'); ?>>en</option>
            </select>
        </p>
        <p class="description">
            <?php esc_html_e('建议 slug 固定为 homepage-zh / homepage-en；这里的 payload 必须保持结构化 JSON。', 'mindhikers-cms-core'); ?>
        </p>
        <?php $this->renderBlogPilotFields($payload); ?>
        <p>
            <label for="mindhikers-homepage-payload"><strong><?php esc_html_e('Homepage Payload JSON', 'mindhikers-cms-core'); ?></strong></label>
        </p>
        <textarea
            id="mindhikers-homepage-payload"
            name="mindhikers_homepage_payload"
            rows="28"
            style="width:100%;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;"
        ><?php echo esc_textarea($this->encodeJson($payload)); ?></textarea>
        <p class="description">
            <?php esc_html_e('推荐先从仓库现有 site-content 结构复制内容，再逐步优化后台编辑体验。', 'mindhikers-cms-core'); ?>
        </p>
        <?php
    }

    public function renderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $rawPayload = get_option($this->siteSettingsOption, '');
        $payload = $this->decodeJsonPayload(is_string($rawPayload) ? $rawPayload : '');

        if (!is_array($payload)) {
            $payload = $this->getDefaultSiteSettings();
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Mindhikers CMS Settings', 'mindhikers-cms-core'); ?></h1>
            <p>
                <?php esc_html_e('这里维护站点级共享设置。当前阶段先用结构化 JSON，后续再逐步升级成更友好的字段表单。', 'mindhikers-cms-core'); ?>
            </p>
            <form action="options.php" method="post">
                <?php settings_fields('mindhikers_cms_core'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="mindhikers-site-settings-payload"><?php esc_html_e('Site Settings JSON', 'mindhikers-cms-core'); ?></label>
                        </th>
                        <td>
                            <textarea
                                id="mindhikers-site-settings-payload"
                                name="<?php echo esc_attr($this->siteSettingsOption); ?>"
                                rows="18"
                                class="large-text code"
                            ><?php echo esc_textarea($this->encodeJson($payload)); ?></textarea>
                            <p class="description">
                                <?php esc_html_e('如需触发前台刷新，请为 WordPress 服务配置 MINDHIKERS_REVALIDATE_ENDPOINT 与 MINDHIKERS_REVALIDATE_SECRET。', 'mindhikers-cms-core'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Save Mindhikers Settings', 'mindhikers-cms-core')); ?>
            </form>
        </div>
        <?php
    }

    public function saveHomepageMeta(int $postId, WP_Post $post, bool $update): void
    {
        unset($post, $update);

        if (!$this->canSaveHomepageMeta($postId)) {
            return;
        }

        $locale = $this->sanitizeLocale((string) ($_POST['mindhikers_homepage_locale'] ?? 'zh'));
        $rawPayload = isset($_POST['mindhikers_homepage_payload']) ? wp_unslash((string) $_POST['mindhikers_homepage_payload']) : '';
        $payload = $this->decodeJsonPayload($rawPayload);

        if ($rawPayload !== '' && !is_array($payload)) {
            wp_die(
                esc_html__('Homepage payload 必须是合法 JSON。', 'mindhikers-cms-core'),
                esc_html__('Invalid Homepage Payload', 'mindhikers-cms-core'),
                ['response' => 400, 'back_link' => true]
            );
        }

        $payloadWithPilotOverrides = $this->applyHomepagePilotOverrides(is_array($payload) ? $payload : []);
        $normalizedPayload = $this->normalizeHomepagePayload($payloadWithPilotOverrides, $locale);

        update_post_meta($postId, $this->localeMeta, $locale);
        update_post_meta($postId, $this->homepagePayloadMeta, $this->encodeJson($normalizedPayload));

        if (wp_is_post_revision($postId)) {
            return;
        }

        $this->clearHomepageTransient($locale);

        $this->triggerRevalidate([
            'entity' => 'homepage',
            'locale' => $locale,
            'path' => $locale === 'en' ? '/en' : '/',
        ]);
    }

    public function handleUpdatedOption(string $option, mixed $oldValue, mixed $value): void
    {
        if ($option !== $this->siteSettingsOption) {
            return;
        }

        if (serialize($oldValue) === serialize($value)) {
            return;
        }

        $this->triggerRevalidate([
            'entity' => 'site-settings',
        ]);

        $this->clearHomepageTransient('zh');
        $this->clearHomepageTransient('en');
    }

    public function getSiteSettings(WP_REST_Request $request): WP_REST_Response
    {
        unset($request);

        $rawPayload = get_option($this->siteSettingsOption, '');
        $payload = $this->decodeJsonPayload(is_string($rawPayload) ? $rawPayload : '');

        return rest_ensure_response($this->normalizeSiteSettings(is_array($payload) ? $payload : []));
    }

    public function getHomepageByLocale(WP_REST_Request $request): WP_REST_Response
    {
        $locale = $this->sanitizeLocale((string) $request->get_param('locale'));
        $post = $this->findHomepagePostByLocale($locale);

        if (!$post instanceof WP_Post) {
            return rest_ensure_response($this->getDefaultHomepagePayload($locale));
        }

        $rawPayload = (string) get_post_meta($post->ID, $this->homepagePayloadMeta, true);
        $payload = $this->decodeJsonPayload($rawPayload);

        return rest_ensure_response($this->normalizeHomepagePayload(is_array($payload) ? $payload : [], $locale));
    }

    public function serveMindhikersRestResponse(bool $served, WP_HTTP_Response $result, WP_REST_Request $request, WP_REST_Server $server): bool
    {
        if ($served) {
            return true;
        }

        $route = $request->get_route();
        if (!str_starts_with($route, '/' . $this->restNamespace . '/')) {
            return false;
        }

        $payload = wp_json_encode($result->get_data(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return false;
        }

        foreach ($result->get_headers() as $header => $value) {
            if (strtolower((string) $header) === 'content-type') {
                continue;
            }
            $server->send_header((string) $header, (string) $value);
        }

        $server->send_header('Content-Type', 'application/json; charset=' . get_option('blog_charset'));

        $status = $result->get_status();
        if ($status > 0) {
            status_header($status);
        }

        echo $payload;
        return true;
    }

    public function sanitizeLocale(string $value): string
    {
        $value = strtolower(trim($value));
        return in_array($value, ['zh', 'en'], true) ? $value : 'zh';
    }

    public function sanitizeJsonPayload(mixed $value): string
    {
        $type = gettype($value);
        $len = is_string($value) ? strlen($value) : 0;
        $preview = is_string($value) ? substr($value, 0, 100) : '';
        error_log("sanitizeJsonPayload called: type={$type}, len={$len}, preview={$preview}");

        if (is_array($value)) {
            return $this->encodeJson($value);
        }

        $decoded = $this->decodeJsonPayload(is_string($value) ? $value : '');
        $decodedOk = is_array($decoded);
        error_log("sanitizeJsonPayload decode result: is_array=" . var_export($decodedOk, true));
        if ($decodedOk) {
            return $this->encodeJson($decoded);
        }

        return $this->encodeJson([]);
    }

    private function canSaveHomepageMeta(int $postId): bool
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if (!isset($_POST[$this->homepageNonceName])) {
            return false;
        }

        if (!wp_verify_nonce((string) $_POST[$this->homepageNonceName], $this->homepageNonceAction)) {
            return false;
        }

        if (!current_user_can('edit_post', $postId)) {
            return false;
        }

        return true;
    }

    private function renderBlogPilotFields(array $payload): void
    {
        $blog = is_array($payload['blog'] ?? null) ? $payload['blog'] : [];
        ?>
        <div style="margin: 20px 0 24px; padding: 16px 18px; border: 1px solid #dcdcde; border-radius: 8px; background: #f6f7f7;">
            <h2 style="margin: 0 0 8px;"><?php esc_html_e('Blog Section Pilot', 'mindhikers-cms-core'); ?></h2>
            <p class="description" style="margin-bottom: 16px;">
                <?php esc_html_e('这是 Homepage GUI pilot。当前先把 Blog 区做成可编辑表单；保存时会自动回写到下方 JSON 的 blog 段。', 'mindhikers-cms-core'); ?>
            </p>
            <table class="form-table" role="presentation" style="margin-top: 0;">
                <tr>
                    <th scope="row">
                        <label for="mindhikers-homepage-blog-title"><?php esc_html_e('Section Title', 'mindhikers-cms-core'); ?></label>
                    </th>
                    <td>
                        <input
                            id="mindhikers-homepage-blog-title"
                            name="mindhikers_homepage_blog_title"
                            type="text"
                            class="regular-text"
                            value="<?php echo esc_attr((string) ($blog['title'] ?? '')); ?>"
                        />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mindhikers-homepage-blog-headline"><?php esc_html_e('Headline', 'mindhikers-cms-core'); ?></label>
                    </th>
                    <td>
                        <textarea
                            id="mindhikers-homepage-blog-headline"
                            name="mindhikers_homepage_blog_headline"
                            rows="3"
                            class="large-text"
                        ><?php echo esc_textarea((string) ($blog['headline'] ?? '')); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mindhikers-homepage-blog-description"><?php esc_html_e('Description', 'mindhikers-cms-core'); ?></label>
                    </th>
                    <td>
                        <textarea
                            id="mindhikers-homepage-blog-description"
                            name="mindhikers_homepage_blog_description"
                            rows="4"
                            class="large-text"
                        ><?php echo esc_textarea((string) ($blog['description'] ?? '')); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mindhikers-homepage-blog-cta-label"><?php esc_html_e('CTA Label', 'mindhikers-cms-core'); ?></label>
                    </th>
                    <td>
                        <input
                            id="mindhikers-homepage-blog-cta-label"
                            name="mindhikers_homepage_blog_cta_label"
                            type="text"
                            class="regular-text"
                            value="<?php echo esc_attr((string) ($blog['cta']['label'] ?? '')); ?>"
                        />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mindhikers-homepage-blog-cta-href"><?php esc_html_e('CTA Link', 'mindhikers-cms-core'); ?></label>
                    </th>
                    <td>
                        <input
                            id="mindhikers-homepage-blog-cta-href"
                            name="mindhikers_homepage_blog_cta_href"
                            type="text"
                            class="regular-text"
                            value="<?php echo esc_attr((string) ($blog['cta']['href'] ?? '')); ?>"
                        />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mindhikers-homepage-blog-empty-label"><?php esc_html_e('Empty State Label', 'mindhikers-cms-core'); ?></label>
                    </th>
                    <td>
                        <textarea
                            id="mindhikers-homepage-blog-empty-label"
                            name="mindhikers_homepage_blog_empty_label"
                            rows="3"
                            class="large-text"
                        ><?php echo esc_textarea((string) ($blog['emptyLabel'] ?? '')); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mindhikers-homepage-blog-read-article-label"><?php esc_html_e('Read Article Label', 'mindhikers-cms-core'); ?></label>
                    </th>
                    <td>
                        <input
                            id="mindhikers-homepage-blog-read-article-label"
                            name="mindhikers_homepage_blog_read_article_label"
                            type="text"
                            class="regular-text"
                            value="<?php echo esc_attr((string) ($blog['readArticleLabel'] ?? '')); ?>"
                        />
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    private function applyHomepagePilotOverrides(array $payload): array
    {
        $payload['blog'] = [
            'title' => $this->normalizeText($_POST['mindhikers_homepage_blog_title'] ?? ''),
            'description' => $this->normalizeText($_POST['mindhikers_homepage_blog_description'] ?? ''),
            'headline' => $this->normalizeText($_POST['mindhikers_homepage_blog_headline'] ?? ''),
            'cta' => [
                'label' => $this->normalizeText($_POST['mindhikers_homepage_blog_cta_label'] ?? ''),
                'href' => esc_url_raw((string) ($_POST['mindhikers_homepage_blog_cta_href'] ?? '')),
            ],
            'emptyLabel' => $this->normalizeText($_POST['mindhikers_homepage_blog_empty_label'] ?? ''),
            'readArticleLabel' => $this->normalizeText($_POST['mindhikers_homepage_blog_read_article_label'] ?? ''),
        ];

        return $payload;
    }

    private function findHomepagePostByLocale(string $locale): ?WP_Post
    {
        $posts = get_posts([
            'post_type' => $this->homepagePostType,
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_key' => $this->localeMeta,
            'meta_value' => $locale,
            'orderby' => 'modified',
            'order' => 'DESC',
            'suppress_filters' => true,
        ]);

        return $posts[0] ?? null;
    }

    private function decodeJsonPayload(string $rawPayload): ?array
    {
        $rawPayload = trim($rawPayload);
        if ($rawPayload === '') {
            return [];
        }

        $decoded = json_decode($rawPayload, true);
        $isArr = is_array($decoded);
        if (!$isArr) {
            error_log("decodeJsonPayload failed: len=" . strlen($rawPayload) . ", json_err=" . json_last_error_msg() . ", preview=" . substr($rawPayload, 0, 200));
        }
        return $isArr ? $decoded : null;
    }

    private function encodeJson(array $value): string
    {
        return (string) wp_json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function triggerRevalidate(array $payload): void
    {
        $endpoint = trim((string) getenv('MINDHIKERS_REVALIDATE_ENDPOINT'));
        $secret = trim((string) getenv('MINDHIKERS_REVALIDATE_SECRET'));

        if ($endpoint === '' || $secret === '') {
            return;
        }

        $body = wp_json_encode($payload);
        if ($body === false) {
            return;
        }

        $response = wp_remote_post($endpoint, [
            'timeout' => 10,
            'headers' => [
                'Content-Type' => 'application/json',
                'x-revalidate-secret' => $secret,
            ],
            'body' => $body,
        ]);

        if (is_wp_error($response)) {
            error_log('Mindhikers CMS Core: revalidate failed: ' . $response->get_error_message());
        } elseif (wp_remote_retrieve_response_code($response) >= 400) {
            error_log('Mindhikers CMS Core: revalidate returned HTTP ' . wp_remote_retrieve_response_code($response));
        }
    }

    private function getDefaultSiteSettings(): array
    {
        return [
            'brandName' => '心行者 Mindhikers',
            'defaultSeoTitle' => '心行者 Mindhikers',
            'defaultSeoDescription' => '',
            'siteUrl' => 'https://www.mindhikers.com',
            'contactEmail' => 'contactmindhiker@gmail.com',
            'defaultOgImage' => '',
            'copyrightText' => '',
            'socialLinks' => [],
        ];
    }

    private function normalizeSiteSettings(array $payload): array
    {
        $socialLinks = [];
        foreach (($payload['socialLinks'] ?? []) as $link) {
            if (!is_array($link)) {
                continue;
            }
            $socialLinks[] = $this->normalizeLink($link);
        }

        return [
            'brandName' => $this->normalizeText($payload['brandName'] ?? '心行者 Mindhikers'),
            'defaultSeoTitle' => $this->normalizeText($payload['defaultSeoTitle'] ?? '心行者 Mindhikers'),
            'defaultSeoDescription' => $this->normalizeText($payload['defaultSeoDescription'] ?? ''),
            'siteUrl' => esc_url_raw((string) ($payload['siteUrl'] ?? 'https://www.mindhikers.com')),
            'contactEmail' => sanitize_email((string) ($payload['contactEmail'] ?? 'contactmindhiker@gmail.com')),
            'defaultOgImage' => esc_url_raw((string) ($payload['defaultOgImage'] ?? '')),
            'copyrightText' => $this->normalizeText($payload['copyrightText'] ?? ''),
            'socialLinks' => $socialLinks,
        ];
    }

    private function getDefaultHomepagePayload(string $locale): array
    {
        return $this->normalizeHomepagePayload([], $locale);
    }

    private function normalizeHomepagePayload(array $payload, string $locale): array
    {
        return [
            'locale' => $locale,
            'metadata' => [
                'title' => $this->normalizeText($payload['metadata']['title'] ?? '心行者 Mindhikers'),
                'description' => $this->normalizeText($payload['metadata']['description'] ?? ''),
            ],
            'navigation' => [
                'brand' => $this->normalizeText($payload['navigation']['brand'] ?? '心行者 Mindhikers'),
                'links' => $this->normalizeLinks($payload['navigation']['links'] ?? []),
                'switchLanguage' => $this->normalizeLink($payload['navigation']['switchLanguage'] ?? []),
            ],
            'hero' => [
                'eyebrow' => $this->normalizeText($payload['hero']['eyebrow'] ?? ''),
                'title' => $this->normalizeText($payload['hero']['title'] ?? ''),
                'description' => $this->normalizeText($payload['hero']['description'] ?? ''),
                'primaryAction' => $this->normalizeLink($payload['hero']['primaryAction'] ?? []),
                'secondaryAction' => $this->normalizeLink($payload['hero']['secondaryAction'] ?? []),
                'highlights' => $this->normalizeStringList($payload['hero']['highlights'] ?? []),
                'statusLabel' => $this->normalizeText($payload['hero']['statusLabel'] ?? ''),
                'statusValue' => $this->normalizeText($payload['hero']['statusValue'] ?? ''),
                'availabilityLabel' => $this->normalizeText($payload['hero']['availabilityLabel'] ?? ''),
                'availabilityValue' => $this->normalizeText($payload['hero']['availabilityValue'] ?? ''),
                'panelTitle' => $this->normalizeText($payload['hero']['panelTitle'] ?? ''),
                'quickLinks' => $this->normalizeQuickLinks($payload['hero']['quickLinks'] ?? []),
            ],
            'about' => [
                'title' => $this->normalizeText($payload['about']['title'] ?? ''),
                'intro' => $this->normalizeText($payload['about']['intro'] ?? ''),
                'paragraphs' => $this->normalizeStringList($payload['about']['paragraphs'] ?? []),
                'notes' => $this->normalizeStringList($payload['about']['notes'] ?? []),
            ],
            'product' => [
                'title' => $this->normalizeText($payload['product']['title'] ?? ''),
                'description' => $this->normalizeText($payload['product']['description'] ?? ''),
                'headline' => $this->normalizeText($payload['product']['headline'] ?? ''),
                'featured' => $this->normalizeEntryCard($payload['product']['featured'] ?? []),
                'items' => $this->normalizeEntryCards($payload['product']['items'] ?? []),
            ],
            'blog' => [
                'title' => $this->normalizeText($payload['blog']['title'] ?? ''),
                'description' => $this->normalizeText($payload['blog']['description'] ?? ''),
                'headline' => $this->normalizeText($payload['blog']['headline'] ?? ''),
                'cta' => $this->normalizeLink($payload['blog']['cta'] ?? []),
                'emptyLabel' => $this->normalizeText($payload['blog']['emptyLabel'] ?? ''),
                'readArticleLabel' => $this->normalizeText($payload['blog']['readArticleLabel'] ?? ''),
            ],
            'contact' => [
                'title' => $this->normalizeText($payload['contact']['title'] ?? ''),
                'description' => $this->normalizeText($payload['contact']['description'] ?? ''),
                'headline' => $this->normalizeText($payload['contact']['headline'] ?? ''),
                'emailLabel' => $this->normalizeText($payload['contact']['emailLabel'] ?? ''),
                'email' => sanitize_email((string) ($payload['contact']['email'] ?? '')),
                'locationLabel' => $this->normalizeText($payload['contact']['locationLabel'] ?? ''),
                'location' => $this->normalizeText($payload['contact']['location'] ?? ''),
                'availabilityLabel' => $this->normalizeText($payload['contact']['availabilityLabel'] ?? ''),
                'availability' => $this->normalizeText($payload['contact']['availability'] ?? ''),
                'links' => $this->normalizeContactLinks($payload['contact']['links'] ?? []),
            ],
            'productDetail' => [
                'eyebrow' => $this->normalizeText($payload['productDetail']['eyebrow'] ?? ''),
                'title' => $this->normalizeText($payload['productDetail']['title'] ?? ''),
                'summary' => $this->normalizeText($payload['productDetail']['summary'] ?? ''),
                'bullets' => $this->normalizeStringList($payload['productDetail']['bullets'] ?? []),
                'stageLabel' => $this->normalizeText($payload['productDetail']['stageLabel'] ?? ''),
                'stageValue' => $this->normalizeText($payload['productDetail']['stageValue'] ?? ''),
                'returnHome' => $this->normalizeLink($payload['productDetail']['returnHome'] ?? []),
                'switchLanguage' => $this->normalizeLink($payload['productDetail']['switchLanguage'] ?? []),
            ],
        ];
    }

    private function normalizeLinks(array $links): array
    {
        $normalized = [];
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }
            $normalized[] = $this->normalizeLink($link);
        }

        return $normalized;
    }

    private function normalizeContactLinks(array $links): array
    {
        $normalized = [];
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }

            $normalized[] = [
                'href' => esc_url_raw((string) ($link['href'] ?? '')),
                'label' => $this->normalizeText($link['label'] ?? ''),
                'note' => $this->normalizeText($link['note'] ?? ''),
                'qrImage' => esc_url_raw((string) ($link['qrImage'] ?? '')),
            ];
        }

        return $normalized;
    }

    private function normalizeQuickLinks(array $links): array
    {
        $normalized = [];
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }

            $normalized[] = [
                'href' => esc_url_raw((string) ($link['href'] ?? '')),
                'label' => $this->normalizeText($link['label'] ?? ''),
                'tag' => $this->normalizeText($link['tag'] ?? ''),
            ];
        }

        return $normalized;
    }

    private function normalizeEntryCards(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $normalized[] = $this->normalizeEntryCard($item);
        }

        return $normalized;
    }

    private function normalizeEntryCard(array $item): array
    {
        return [
            'eyebrow' => $this->normalizeText($item['eyebrow'] ?? ''),
            'title' => $this->normalizeText($item['title'] ?? ''),
            'description' => $this->normalizeText($item['description'] ?? ''),
            'href' => esc_url_raw((string) ($item['href'] ?? '')),
            'ctaLabel' => $this->normalizeText($item['ctaLabel'] ?? ''),
            'meta' => $this->normalizeText($item['meta'] ?? ''),
        ];
    }

    private function normalizeLink(array $link): array
    {
        return [
            'href' => esc_url_raw((string) ($link['href'] ?? '')),
            'label' => $this->normalizeText($link['label'] ?? ''),
        ];
    }

    private function normalizeStringList(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $value = $this->normalizeText((string) $item);
            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    private function normalizeText(mixed $value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return trim(wp_strip_all_tags((string) $value));
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }
}

/**
 * Global helper: Get homepage data for theme rendering.
 *
 * @param string $locale 'zh' or 'en'
 * @return array Normalized homepage payload
 */
if (!function_exists('mindhikers_get_homepage_data')) {
    function mindhikers_get_homepage_data(string $locale = 'zh'): array
    {
        $core = Mindhikers_Cms_Core::getInstance();
        if (!$core instanceof Mindhikers_Cms_Core) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                _doing_it_wrong(
                    __FUNCTION__,
                    'Mindhikers_Cms_Core has not been booted. Ensure the mu-plugin is loaded.',
                    '1.0.0'
                );
            }
            return [];
        }
        return $core->getHomepageDataForTheme($locale);
    }
}

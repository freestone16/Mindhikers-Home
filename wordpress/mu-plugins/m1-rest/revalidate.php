<?php

/**
 * M1-R Revalidate Webhook — triggers Next.js ISR revalidation when WP content changes.
 *
 * Tag convention (must match Next.js cache tags exactly):
 *   homepage-zh       — Chinese homepage data
 *   homepage-en       — English homepage data
 *   blog-posts        — All blog list + detail pages (single collection tag)
 *   product-{slug}    — Individual product detail page (slug-based, NOT post ID)
 *
 * Hooks:
 * - carbon_fields_theme_options_container_saved → homepage-zh, homepage-en
 * - save_post_mh_product                        → homepage-zh, homepage-en, product-{slug}
 * - save_post_post (published)                  → blog-posts
 *
 * Observability:
 * - Config missing (URL or secret) → error_log warning with specifics
 * - Every dispatch → error_log with context + tags
 * - WP_Error from wp_remote_post → error_log with error message
 * - Define MH_REVALIDATE_DEBUG as true for blocking mode + full HTTP response logging
 */

defined('ABSPATH') || exit;

/** Set to true for verbose logging (blocking requests, HTTP status codes). Default false. */
if (!defined('MH_REVALIDATE_DEBUG')) {
    define('MH_REVALIDATE_DEBUG', false);
}

/**
 * Trigger Next.js revalidation for given cache tags.
 *
 * Non-blocking by default: 3s timeout, failures logged but never block WP save.
 * Set MH_REVALIDATE_DEBUG to true for blocking mode with full response logging.
 *
 * @param string[] $tags    Cache tags to revalidate (must match Next.js tag names).
 * @param string   $context Human-readable context for log messages (e.g. "save_post_post/42").
 */
function mh_trigger_revalidate(array $tags, string $context = ''): void
{
    $url = get_option('mh_nextjs_revalidate_url');
    $secret = get_option('mh_revalidate_secret');

    if (empty($url) || empty($secret)) {
        error_log(sprintf(
            '[M1-R Revalidate] SKIP — config missing. url=%s secret=%s context=%s',
            $url ? 'set' : 'EMPTY',
            $secret ? 'set' : 'EMPTY',
            $context ?: 'unknown'
        ));
        return;
    }

    $blocking = MH_REVALIDATE_DEBUG;

    error_log(sprintf(
        '[M1-R Revalidate] DISPATCH context=%s tags=[%s] blocking=%s',
        $context ?: 'unknown',
        implode(', ', $tags),
        $blocking ? 'true' : 'false'
    ));

    foreach ($tags as $tag) {
        $start = microtime(true);

        $response = wp_remote_post($url, [
            'headers' => [
                'x-revalidate-secret' => $secret,
                'Content-Type' => 'application/json',
            ],
            'body'    => wp_json_encode(['tag' => $tag]),
            'timeout' => 3,
            'blocking' => $blocking,
        ]);

        $elapsed = round((microtime(true) - $start) * 1000);

        if (is_wp_error($response)) {
            error_log(sprintf(
                '[M1-R Revalidate] FAIL tag="%s" context=%s error="%s" elapsed=%dms',
                $tag,
                $context ?: 'unknown',
                $response->get_error_message(),
                (int) $elapsed
            ));
        } elseif ($blocking && is_array($response)) {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            error_log(sprintf(
                '[M1-R Revalidate] %s tag="%s" context=%s http=%d body=%s elapsed=%dms',
                ($code >= 200 && $code < 300) ? 'OK' : 'WARN',
                $tag,
                $context ?: 'unknown',
                $code,
                $body,
                (int) $elapsed
            ));
        }
    }
}

// Hook: Carbon Fields theme options saved (Hero, About, Contact management)
add_action('carbon_fields_theme_options_container_saved', function ($container_id) {
    mh_trigger_revalidate(
        ['homepage-zh', 'homepage-en'],
        'carbon_fields_saved/' . $container_id
    );
});

// Hook: Product CPT saved
add_action('save_post_mh_product', function ($post_id) {
    // Skip autosaves and revisions
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    $post = get_post($post_id);
    $slug = $post ? $post->post_name : '';

    $tags = ['homepage-zh', 'homepage-en'];
    if ($slug) {
        $tags[] = 'product-' . $slug;
    }

    mh_trigger_revalidate($tags, 'save_post_mh_product/' . $post_id);
});

// Hook: Blog post saved
add_action('save_post_post', function ($post_id) {
    // Skip autosaves and revisions
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    // Only trigger for published posts
    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish') {
        return;
    }

    mh_trigger_revalidate(
        ['blog-posts'],
        'save_post_post/' . $post_id
    );
});

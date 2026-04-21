import type { BlogSourceMode } from "./types";

// ---------------------------------------------------------------------------
// Cache tags — single source of truth
// ---------------------------------------------------------------------------
// These tag values MUST match what the WP webhook sends.  Changing a tag here
// requires updating the corresponding WP PHP side as well.

/** Tag for the global blog post list (WP sends this on any post change). */
export const CACHE_TAG_BLOG = "blog-posts" as const;

/** Tag prefix for homepage locale pages. Use `getHomepageCacheTag(locale)`. */
export const CACHE_TAG_HOMEPAGE_PREFIX = "homepage" as const;

/** Tag prefix for product detail pages. Use `getProductCacheTag(slug)`. */
export const CACHE_TAG_PRODUCT_PREFIX = "product" as const;

/** Derive the cache tag for a homepage locale, e.g. `"homepage-zh"`. */
export function getHomepageCacheTag(locale: string): string {
  return `${CACHE_TAG_HOMEPAGE_PREFIX}-${locale}`;
}

/** Derive the cache tag for a product page, e.g. `"product-golden-crucible"`. */
export function getProductCacheTag(slug: string): string {
  return `${CACHE_TAG_PRODUCT_PREFIX}-${slug}`;
}

/**
 * Canonical set of allowed cache tags for the revalidation webhook.
 * Only tags that match a known prefix are accepted — this prevents callers
 * from purging arbitrary ISR entries.
 */
export const ALLOWED_CACHE_TAG_PREFIXES = [
  CACHE_TAG_BLOG,
  CACHE_TAG_HOMEPAGE_PREFIX,
  CACHE_TAG_PRODUCT_PREFIX,
] as const;

/** Check whether a tag value is recognised as a valid revalidation target. */
export function isValidCacheTag(tag: string): boolean {
  if (tag === CACHE_TAG_BLOG) return true;
  return ALLOWED_CACHE_TAG_PREFIXES.some(
    (prefix) => prefix !== CACHE_TAG_BLOG && tag.startsWith(`${prefix}-`),
  );
}

// ---------------------------------------------------------------------------
// ISR revalidation interval
// ---------------------------------------------------------------------------
export const BLOG_REVALIDATE_SECONDS = 300;

// ---------------------------------------------------------------------------
// Blog source mode
// ---------------------------------------------------------------------------
const BLOG_SOURCE_MODES = new Set<BlogSourceMode>(["mdx", "wordpress", "hybrid"]);

export function getBlogSourceMode(): BlogSourceMode {
  const source = process.env.BLOG_SOURCE?.trim().toLowerCase();

  if (source && BLOG_SOURCE_MODES.has(source as BlogSourceMode)) {
    return source as BlogSourceMode;
  }

  return "wordpress";
}

// ---------------------------------------------------------------------------
// WordPress connection helpers
// ---------------------------------------------------------------------------
export function getWordPressSiteUrl() {
  return process.env.WORDPRESS_API_URL?.trim().replace(/\/+$/, "") ?? "";
}

export function isWordPressConfigured() {
  return getWordPressSiteUrl().length > 0;
}

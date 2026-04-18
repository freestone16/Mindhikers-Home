import type { BlogSourceMode } from "./types";

export const BLOG_CACHE_TAG = "blog-posts";
export const BLOG_REVALIDATE_SECONDS = 300;

const BLOG_SOURCE_MODES = new Set<BlogSourceMode>(["mdx", "wordpress", "hybrid"]);

export function getBlogSourceMode(): BlogSourceMode {
  const source = process.env.BLOG_SOURCE?.trim().toLowerCase();

  if (source && BLOG_SOURCE_MODES.has(source as BlogSourceMode)) {
    return source as BlogSourceMode;
  }

  return "mdx";
}

export function getWordPressSiteUrl() {
  return process.env.WORDPRESS_API_URL?.trim().replace(/\/+$/, "") ?? "";
}

export function isWordPressConfigured() {
  return getWordPressSiteUrl().length > 0;
}

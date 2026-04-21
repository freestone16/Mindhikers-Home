import { normalizePublishedDate } from "@/lib/posts";
import { CACHE_TAG_BLOG, BLOG_REVALIDATE_SECONDS, getWordPressSiteUrl } from "./constants";
import { htmlToPlainText, sanitizeWordPressHtml, truncateText } from "./html";
import type { CmsPost, CmsPostSummary } from "./types";

// ---------------------------------------------------------------------------
// Custom m1-rest endpoint types
// ---------------------------------------------------------------------------

type M1BlogItemBase = {
  slug: string;
  title: string;
  excerpt: string;
  date: string;
  coverImage: string;
  categories: { slug: string; name: string }[];
};

type M1BlogItem = M1BlogItemBase & { href: string };

type M1BlogListResponse = {
  items: M1BlogItem[];
  total: number;
  page: number;
  perPage: number;
  totalPages: number;
};

type M1BlogDetail = M1BlogItemBase & {
  content: string;
  author: { name: string; avatar: string };
};

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

const WORDPRESS_POSTS_PER_PAGE = 50;

function getPublishedDate(value: string | undefined) {
  if (!value) {
    return undefined;
  }

  try {
    return normalizePublishedDate(value);
  } catch {
    return undefined;
  }
}

function mapCategoryNames(categories: { slug: string; name: string }[]): string[] {
  return categories.map((c) => c.name);
}

function toSummary(item: M1BlogItemBase): CmsPostSummary {
  return {
    categories: mapCategoryNames(item.categories),
    coverImage: item.coverImage || undefined,
    id: item.slug,
    publishedAt: getPublishedDate(item.date) ?? normalizePublishedDate(item.date),
    slug: item.slug,
    source: "wordpress",
    summary: truncateText(htmlToPlainText(item.excerpt || ""), 220),
    tags: [],
    title: item.title || "Untitled",
  };
}

function toPost(detail: M1BlogDetail): CmsPost {
  return {
    ...toSummary(detail),
    contentHtml: sanitizeWordPressHtml(detail.content ?? ""),
    contentType: "html",
  };
}

// ---------------------------------------------------------------------------
// Fetch wrapper
// ---------------------------------------------------------------------------

async function fetchM1Blog<T>(path: string, searchParams?: URLSearchParams) {
  const siteUrl = getWordPressSiteUrl();

  if (!siteUrl) {
    throw new Error("WORDPRESS_API_URL is not configured");
  }

  const url = new URL(path, `${siteUrl}/`);
  if (searchParams) {
    url.search = searchParams.toString();
  }

  const response = await fetch(url, {
    headers: {
      Accept: "application/json",
    },
    next: {
      revalidate: BLOG_REVALIDATE_SECONDS,
      tags: [CACHE_TAG_BLOG],
    },
  });

  if (!response.ok) {
    throw new Error(`M1-R blog request failed: ${response.status} ${response.statusText}`);
  }

  return response.json() as Promise<T>;
}

// ---------------------------------------------------------------------------
// Public API
// ---------------------------------------------------------------------------

export async function listWordPressPosts(): Promise<CmsPostSummary[]> {
  const allItems: M1BlogItem[] = [];
  let page = 1;
  let totalPages = 1;

  do {
    const params = new URLSearchParams({
      lang: "zh",
      page: String(page),
      per_page: String(WORDPRESS_POSTS_PER_PAGE),
    });

    const data = await fetchM1Blog<M1BlogListResponse>("/wp-json/mindhikers/v1/blog", params);

    allItems.push(...data.items);
    totalPages = data.totalPages;
    page += 1;
  } while (page <= totalPages);

  return allItems.map(toSummary);
}

export async function getWordPressPostBySlug(slug: string): Promise<CmsPost | null> {
  const params = new URLSearchParams({ lang: "zh" });

  try {
    const detail = await fetchM1Blog<M1BlogDetail>(
      `/wp-json/mindhikers/v1/blog/${encodeURIComponent(slug)}`,
      params,
    );
    return toPost(detail);
  } catch (error) {
    // 404 or other fetch errors — treat as "not found"
    console.warn(`M1-R blog: failed to fetch post "${slug}":`, error);
    return null;
  }
}

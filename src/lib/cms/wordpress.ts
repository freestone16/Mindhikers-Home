import { normalizePublishedDate } from "@/lib/posts";
import { BLOG_CACHE_TAG, BLOG_REVALIDATE_SECONDS, getWordPressSiteUrl } from "./constants";
import { htmlToPlainText, sanitizeWordPressHtml, truncateText } from "./html";
import type { CmsPost, CmsPostSummary } from "./types";

type WordPressRenderedField = {
  rendered: string;
};

type WordPressTerm = {
  id: number;
  name: string;
  taxonomy: string;
};

type WordPressMedia = {
  source_url?: string;
};

type WordPressPost = {
  id: number;
  slug: string;
  date: string;
  date_gmt?: string;
  modified: string;
  modified_gmt?: string;
  title: WordPressRenderedField;
  excerpt: WordPressRenderedField;
  content: WordPressRenderedField;
  _embedded?: {
    "wp:featuredmedia"?: WordPressMedia[];
    "wp:term"?: WordPressTerm[][];
  };
};

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

function getFeaturedMedia(post: WordPressPost) {
  return post._embedded?.["wp:featuredmedia"]?.[0]?.source_url;
}

function getTerms(post: WordPressPost, taxonomy: "category" | "post_tag") {
  return (
    post._embedded?.["wp:term"]
      ?.flat()
      .filter((term) => term.taxonomy === taxonomy)
      .map((term) => term.name) ?? []
  );
}

function toSummary(post: WordPressPost): CmsPostSummary {
  const summarySource = post.excerpt.rendered || post.content.rendered || "";

  return {
    categories: getTerms(post, "category"),
    coverImage: getFeaturedMedia(post),
    id: String(post.id),
    publishedAt: getPublishedDate(post.date_gmt ?? post.date) ?? normalizePublishedDate(post.date),
    slug: post.slug,
    source: "wordpress",
    summary: truncateText(htmlToPlainText(summarySource), 220),
    tags: getTerms(post, "post_tag"),
    title: htmlToPlainText(post.title.rendered) || "Untitled",
    updatedAt: getPublishedDate(post.modified_gmt ?? post.modified),
  };
}

function toPost(post: WordPressPost): CmsPost {
  return {
    ...toSummary(post),
    contentHtml: sanitizeWordPressHtml(post.content.rendered ?? ""),
    contentType: "html",
  };
}

async function fetchWordPress<T>(path: string, searchParams?: URLSearchParams) {
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
      tags: [BLOG_CACHE_TAG],
    },
  });

  if (!response.ok) {
    throw new Error(`WordPress request failed: ${response.status} ${response.statusText}`);
  }

  return response as Response & {
    json(): Promise<T>;
  };
}

export async function listWordPressPosts(): Promise<CmsPostSummary[]> {
  const posts: WordPressPost[] = [];
  let page = 1;
  let totalPages = 1;

  do {
    const searchParams = new URLSearchParams({
      _embed: "wp:featuredmedia,wp:term",
      page: String(page),
      per_page: String(WORDPRESS_POSTS_PER_PAGE),
      status: "publish",
    });
    const response = await fetchWordPress<WordPressPost[]>("/wp-json/wp/v2/posts", searchParams);
    const batch = await response.json();

    posts.push(...batch);
    totalPages = Number.parseInt(response.headers.get("x-wp-totalpages") ?? "1", 10);
    page += 1;
  } while (page <= totalPages);

  return posts.map(toSummary);
}

export async function getWordPressPostBySlug(slug: string): Promise<CmsPost | null> {
  const searchParams = new URLSearchParams({
    _embed: "wp:featuredmedia,wp:term",
    slug,
    status: "publish",
  });
  const response = await fetchWordPress<WordPressPost[]>("/wp-json/wp/v2/posts", searchParams);
  const posts = await response.json();
  const post = posts[0];

  return post ? toPost(post) : null;
}

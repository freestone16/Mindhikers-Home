import { sortPostsByPublishedDateDesc } from "@/lib/posts";
import { getBlogSourceMode, isWordPressConfigured } from "./constants";
import { getMdxPostBySlug, listMdxPosts } from "./mdx";
import type { CmsPost, CmsPostSummary } from "./types";
import { getWordPressPostBySlug, listWordPressPosts } from "./wordpress";

function mergePostSummaries(wordPressPosts: CmsPostSummary[], mdxPosts: CmsPostSummary[]) {
  const postsBySlug = new Map<string, CmsPostSummary>();

  for (const post of mdxPosts) {
    postsBySlug.set(post.slug, post);
  }

  for (const post of wordPressPosts) {
    postsBySlug.set(post.slug, post);
  }

  return sortPostsByPublishedDateDesc(Array.from(postsBySlug.values()));
}

async function listWordPressPostsSafely() {
  if (!isWordPressConfigured()) {
    return [];
  }

  try {
    return await listWordPressPosts();
  } catch (error) {
    console.error("Failed to list WordPress posts:", error);
    return [];
  }
}

async function getWordPressPostBySlugSafely(slug: string) {
  if (!isWordPressConfigured()) {
    return null;
  }

  try {
    return await getWordPressPostBySlug(slug);
  } catch (error) {
    console.error(`Failed to fetch WordPress post "${slug}":`, error);
    return null;
  }
}

export async function listPosts(): Promise<CmsPostSummary[]> {
  const sourceMode = getBlogSourceMode();

  if (sourceMode === "mdx") {
    return listMdxPosts();
  }

  if (sourceMode === "wordpress") {
    const posts = await listWordPressPostsSafely();
    return posts.length > 0 ? posts : listMdxPosts();
  }

  const [wordPressPosts, mdxPosts] = await Promise.all([
    listWordPressPostsSafely(),
    Promise.resolve(listMdxPosts()),
  ]);

  return mergePostSummaries(wordPressPosts, mdxPosts);
}

export async function getPostBySlug(slug: string): Promise<CmsPost | null> {
  const sourceMode = getBlogSourceMode();

  if (sourceMode === "mdx") {
    return getMdxPostBySlug(slug);
  }

  if (sourceMode === "wordpress") {
    return (await getWordPressPostBySlugSafely(slug)) ?? getMdxPostBySlug(slug);
  }

  return (await getWordPressPostBySlugSafely(slug)) ?? getMdxPostBySlug(slug);
}

export async function getRecentPosts(limit: number) {
  const posts = await listPosts();
  return posts.slice(0, limit);
}

export async function listPostSlugs() {
  const posts = await listPosts();
  return posts.map((post) => post.slug);
}

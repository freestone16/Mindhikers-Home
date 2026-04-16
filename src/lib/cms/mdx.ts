import { allPosts } from "content-collections";
import { sortPostsByPublishedDateDesc } from "@/lib/posts";
import type { CmsPost, CmsPostSummary } from "./types";

function getSlug(path: string) {
  return path.replace(/\.mdx$/, "");
}

function mapMdxPost(post: (typeof allPosts)[number]): CmsPost {
  return {
    categories: [],
    contentType: "mdx",
    coverImage: post.image,
    id: post._meta.path,
    mdxCode: post.mdx,
    publishedAt: post.publishedAt,
    slug: getSlug(post._meta.path),
    source: "mdx",
    summary: post.summary,
    tags: [],
    title: post.title,
    updatedAt: post.updatedAt,
  };
}

export function listMdxPosts(): CmsPostSummary[] {
  return sortPostsByPublishedDateDesc(allPosts).map(mapMdxPost);
}

export function getMdxPostBySlug(slug: string): CmsPost | null {
  const post = allPosts.find((candidate) => getSlug(candidate._meta.path) === slug);
  return post ? mapMdxPost(post) : null;
}

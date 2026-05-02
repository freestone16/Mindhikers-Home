import { listPosts } from "@/lib/cms";
import { SITE_URL } from "@/lib/metadata";
import type { MetadataRoute } from "next";

const STATIC_ROUTES = [
  "/",
  "/en",
  "/blog",
  "/golden-crucible",
  "/en/golden-crucible",
];

function siteUrl(path: string) {
  return new URL(path, SITE_URL).toString();
}

function toDate(value: string | undefined) {
  if (!value) {
    return undefined;
  }

  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? undefined : date;
}

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const now = new Date();
  const posts = await listPosts();

  return [
    ...STATIC_ROUTES.map((path) => ({
      url: siteUrl(path),
      lastModified: now,
    })),
    ...posts.map((post) => ({
      url: siteUrl(`/blog/${post.slug}`),
      lastModified: toDate(post.updatedAt ?? post.publishedAt) ?? now,
    })),
  ];
}

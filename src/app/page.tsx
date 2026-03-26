import { HomePage } from "@/components/home-page";
import { getHomeContent } from "@/data/site-content";
import { allPosts } from "content-collections";
import { sortPostsByPublishedDateDesc } from "@/lib/posts";
import type { Metadata } from "next";

const content = getHomeContent("zh");
const recentPosts = sortPostsByPublishedDateDesc(allPosts)
  .slice(0, 3)
  .map((post) => ({
    slug: post._meta.path.replace(/\.mdx$/, ""),
    title: post.title,
    publishedAt: post.publishedAt,
    summary: post.summary,
  }));

export const metadata: Metadata = {
  title: content.metadata.title,
  description: content.metadata.description,
};

export default function Page() {
  return <HomePage content={content} recentPosts={recentPosts} />;
}

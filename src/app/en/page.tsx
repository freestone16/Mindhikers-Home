import { HomePage } from "@/components/home-page";
import { getRecentPosts } from "@/lib/cms";
import { getManagedHomeContent } from "@/lib/cms/homepage";
import { buildPageMetadata } from "@/lib/metadata";
import type { Metadata } from "next";

export async function generateMetadata(): Promise<Metadata> {
  const content = await getManagedHomeContent("en");
  return buildPageMetadata({
    title: content.metadata.title,
    description: content.metadata.description,
    path: "/en",
    locale: "en_US",
    alternateLocale: "zh_CN",
    languages: {
      "zh-Hans": "/",
      en: "/en",
      "x-default": "/",
    },
  });
}

export default async function EnglishPage() {
  const content = await getManagedHomeContent("en");
  const recentPosts = await getRecentPosts(3);
  return <HomePage content={content} recentPosts={recentPosts} />;
}

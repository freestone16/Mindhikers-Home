import { HomePage } from "@/components/home-page";
import { getRecentPosts } from "@/lib/cms";
import { getManagedHomeContent } from "@/lib/cms/homepage";
import type { Metadata } from "next";

export async function generateMetadata(): Promise<Metadata> {
  const content = await getManagedHomeContent("en");
  return {
    title: content.metadata.title,
    description: content.metadata.description,
    alternates: {
      languages: {
        "zh-Hans": "/",
        en: "/en",
      },
    },
    openGraph: {
      locale: "en_US",
      alternateLocale: ["zh_CN"],
    },
  };
}

export default async function EnglishPage() {
  const content = await getManagedHomeContent("en");
  const recentPosts = await getRecentPosts(3);
  return <HomePage content={content} recentPosts={recentPosts} />;
}

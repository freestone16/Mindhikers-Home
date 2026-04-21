import { HomePage } from "@/components/home-page";
import { getRecentPosts } from "@/lib/cms";
import { getManagedHomeContent } from "@/lib/cms/homepage";
import type { Metadata } from "next";

export async function generateMetadata(): Promise<Metadata> {
  const content = await getManagedHomeContent("zh");
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
      locale: "zh_CN",
      alternateLocale: ["en_US"],
    },
  };
}

export default async function Page() {
  const content = await getManagedHomeContent("zh");
  const recentPosts = await getRecentPosts(3);
  return <HomePage content={content} recentPosts={recentPosts} />;
}

import { HomePage } from "@/components/home-page";
import { getHomeContent } from "@/data/site-content";
import type { Metadata } from "next";

const content = getHomeContent("en");

export const metadata: Metadata = {
  title: content.metadata.title,
  description: content.metadata.description,
};

export default function EnglishPage() {
  return <HomePage content={content} />;
}

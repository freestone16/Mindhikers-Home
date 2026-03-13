import { ProductPage } from "@/components/product-page";
import { getHomeContent } from "@/data/site-content";
import type { Metadata } from "next";

const content = getHomeContent("en");

export const metadata: Metadata = {
  title: content.productDetail.title,
  description: content.productDetail.summary,
};

export default function EnglishGoldenCruciblePage() {
  return <ProductPage content={content} />;
}

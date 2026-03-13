import { ProductPage } from "@/components/product-page";
import { getHomeContent } from "@/data/site-content";
import type { Metadata } from "next";

const content = getHomeContent("zh");

export const metadata: Metadata = {
  title: content.productDetail.title,
  description: content.productDetail.summary,
};

export default function GoldenCruciblePage() {
  return <ProductPage content={content} />;
}

import { ProductPage } from "@/components/product-page";
import { getHomeContent } from "@/data/site-content";
import { buildPageMetadata } from "@/lib/metadata";
import type { Metadata } from "next";

const content = getHomeContent("zh");

export const metadata: Metadata = {
  ...buildPageMetadata({
    title: content.productDetail.title,
    description: content.productDetail.summary,
    path: "/golden-crucible",
    locale: "zh_CN",
    alternateLocale: "en_US",
    languages: {
      "zh-Hans": "/golden-crucible",
      en: "/en/golden-crucible",
      "x-default": "/golden-crucible",
    },
  }),
};

export default function GoldenCruciblePage() {
  return <ProductPage content={content} />;
}

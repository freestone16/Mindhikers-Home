import { ProductPage } from "@/components/product-page";
import { getHomeContent } from "@/data/site-content";
import { buildPageMetadata } from "@/lib/metadata";
import type { Metadata } from "next";

const content = getHomeContent("en");

export const metadata: Metadata = {
  ...buildPageMetadata({
    title: content.productDetail.title,
    description: content.productDetail.summary,
    path: "/en/golden-crucible",
    locale: "en_US",
    alternateLocale: "zh_CN",
    languages: {
      "zh-Hans": "/golden-crucible",
      en: "/en/golden-crucible",
      "x-default": "/golden-crucible",
    },
  }),
};

export default function EnglishGoldenCruciblePage() {
  return <ProductPage content={content} />;
}

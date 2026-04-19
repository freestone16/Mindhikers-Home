import { ProductPage } from "@/components/product-page";
import { getHomeContent } from "@/data/site-content";
import { getProductBySlug } from "@/lib/cms/products";
import type { Metadata } from "next";
import { notFound } from "next/navigation";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  const wpProduct = await getProductBySlug(slug, "en");

  if (wpProduct) {
    return {
      title: wpProduct.title,
      description: wpProduct.description || wpProduct.subtitle,
    };
  }

  if (slug !== "golden-crucible") {
    return {};
  }

  const fallback = getHomeContent("en");
  return {
    title: fallback.productDetail.title,
    description: fallback.productDetail.summary,
  };
}

export default async function EnProductSlugPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const wpProduct = await getProductBySlug(slug, "en");

  if (wpProduct) {
    const fallback = getHomeContent("en");
    const content = {
      ...fallback,
      productDetail: {
        ...fallback.productDetail,
        title: wpProduct.title,
        summary: wpProduct.description || wpProduct.subtitle || fallback.productDetail.summary,
        switchLanguage: {
          href: wpProduct.switchLanguage.href,
          label: wpProduct.switchLanguage.label,
        },
      },
    };
    return <ProductPage content={content} />;
  }

  if (slug === "golden-crucible") {
    const content = getHomeContent("en");
    return <ProductPage content={content} />;
  }

  notFound();
}

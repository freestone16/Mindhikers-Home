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
  const wpProduct = await getProductBySlug(slug, "zh");

  if (wpProduct) {
    return {
      title: wpProduct.title,
      description: wpProduct.description || wpProduct.subtitle,
    };
  }

  // Fallback: only support golden-crucible statically
  if (slug !== "golden-crucible") {
    return {};
  }

  const fallback = getHomeContent("zh");
  return {
    title: fallback.productDetail.title,
    description: fallback.productDetail.summary,
  };
}

export default async function ProductSlugPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const wpProduct = await getProductBySlug(slug, "zh");

  if (wpProduct) {
    const fallback = getHomeContent("zh");
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

  // Static fallback for golden-crucible
  if (slug === "golden-crucible") {
    const content = getHomeContent("zh");
    return <ProductPage content={content} />;
  }

  notFound();
}

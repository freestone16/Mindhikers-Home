import type { Locale } from "@/data/site-content";
import { BLOG_REVALIDATE_SECONDS, getProductCacheTag, getWordPressSiteUrl, isWordPressConfigured } from "./constants";

export type WpProductDetail = {
  slug: string;
  title: string;
  subtitle: string;
  description: string;
  content: string;
  status: string;
  statusLabel: string;
  entryUrl: string;
  coverImage: string;
  permalink: string;
  switchLanguage: {
    href: string;
    label: string;
  };
};

async function fetchProductFromWp(slug: string, locale: Locale): Promise<WpProductDetail | null> {
  if (!isWordPressConfigured()) {
    return null;
  }

  const siteUrl = getWordPressSiteUrl();
  const endpoint = new URL(`/wp-json/mindhikers/v1/product/${slug}`, `${siteUrl}/`);
  endpoint.searchParams.set("lang", locale);

  const response = await fetch(endpoint, {
    headers: {
      Accept: "application/json",
    },
    next: {
      revalidate: BLOG_REVALIDATE_SECONDS,
      tags: [getProductCacheTag(slug)],
    },
  });

  if (!response.ok) {
    if (response.status === 404) {
      return null;
    }
    throw new Error(`Product fetch failed: ${response.status} ${response.statusText}`);
  }

  return response.json() as Promise<WpProductDetail>;
}

export async function getProductBySlug(
  slug: string,
  locale: Locale,
): Promise<WpProductDetail | null> {
  try {
    return await fetchProductFromWp(slug, locale);
  } catch (error) {
    console.warn(`Failed to fetch product "${slug}" from WordPress:`, error);
    return null;
  }
}

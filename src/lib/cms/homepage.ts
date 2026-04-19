import { getHomeContent, type HomeContent, type Locale } from "@/data/site-content";
import { BLOG_REVALIDATE_SECONDS, getHomepageCacheTag, getWordPressSiteUrl, isWordPressConfigured } from "./constants";

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === "object" && value !== null;
}

function isLink(value: unknown): boolean {
  return isRecord(value) && typeof value.href === "string" && typeof value.label === "string";
}

/**
 * Runtime type guard for HomeContent. Validates all top-level sections
 * so that WP field structure anomalies auto-degrade to fallback instead
 * of surfacing broken partial data.
 */
function isHomeContentReady(payload: unknown, locale: Locale): payload is HomeContent {
  if (!isRecord(payload)) {
    return false;
  }

  if (payload.locale !== locale) {
    return false;
  }

  const { metadata, navigation, hero, about, product, blog, contact } = payload;

  if (
    !isRecord(metadata) ||
    typeof metadata.title !== "string" ||
    metadata.title.length === 0 ||
    typeof metadata.description !== "string"
  ) {
    return false;
  }

  if (
    !isRecord(navigation) ||
    typeof navigation.brand !== "string" ||
    !Array.isArray(navigation.links) ||
    !isLink(navigation.switchLanguage)
  ) {
    return false;
  }

  if (
    !isRecord(hero) ||
    typeof hero.title !== "string" ||
    hero.title.length === 0 ||
    typeof hero.description !== "string" ||
    !isLink(hero.primaryAction) ||
    !isLink(hero.secondaryAction)
  ) {
    return false;
  }

  if (!isRecord(about) || typeof about.title !== "string") {
    return false;
  }

  if (
    !isRecord(product) ||
    typeof product.title !== "string" ||
    !isRecord(product.featured) ||
    !Array.isArray(product.items)
  ) {
    return false;
  }

  if (
    !isRecord(blog) ||
    typeof blog.title !== "string" ||
    !isRecord(blog.cta) ||
    typeof blog.cta.href !== "string" ||
    typeof blog.cta.label !== "string"
  ) {
    return false;
  }

  if (
    !isRecord(contact) ||
    typeof contact.email !== "string" ||
    !Array.isArray(contact.links)
  ) {
    return false;
  }

  return true;
}

async function fetchWordPressHomepage(locale: Locale): Promise<HomeContent | null> {
  if (!isWordPressConfigured()) {
    return null;
  }

  const siteUrl = getWordPressSiteUrl();
  if (!siteUrl) {
    return null;
  }

  const endpoint = new URL(`/wp-json/mindhikers/v1/homepage/${locale}`, `${siteUrl}/`);

  const response = await fetch(endpoint, {
    headers: {
      Accept: "application/json",
    },
    signal: AbortSignal.timeout(5000),
    next: {
      revalidate: BLOG_REVALIDATE_SECONDS,
      tags: [getHomepageCacheTag(locale)],
    },
  });

  if (!response.ok) {
    throw new Error(`Homepage request failed: ${response.status} ${response.statusText}`);
  }

  const payload = (await response.json()) as unknown;
  return isHomeContentReady(payload, locale) ? payload : null;
}

export async function getManagedHomeContent(locale: Locale): Promise<HomeContent> {
  const fallback = getHomeContent(locale);

  try {
    const payload = await fetchWordPressHomepage(locale);
    return payload ?? fallback;
  } catch (error) {
    console.warn(`Failed to fetch homepage content for locale "${locale}":`, error);
    return fallback;
  }
}

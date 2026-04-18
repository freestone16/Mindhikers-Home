import { getHomeContent, type HomeContent, type Locale } from "@/data/site-content";
import { BLOG_REVALIDATE_SECONDS, getWordPressSiteUrl, isWordPressConfigured } from "./constants";

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === "object" && value !== null;
}

function isLink(value: unknown) {
  return isRecord(value) && typeof value.href === "string" && typeof value.label === "string";
}

function isHomeContentReady(payload: unknown, locale: Locale): payload is HomeContent {
  if (!isRecord(payload)) {
    return false;
  }

  const metadata = payload.metadata;
  const navigation = payload.navigation;
  const hero = payload.hero;

  return (
    payload.locale === locale &&
    isRecord(metadata) &&
    typeof metadata.title === "string" &&
    metadata.title.length > 0 &&
    typeof metadata.description === "string" &&
    isRecord(navigation) &&
    typeof navigation.brand === "string" &&
    Array.isArray(navigation.links) &&
    isLink(navigation.switchLanguage) &&
    isRecord(hero) &&
    typeof hero.title === "string" &&
    hero.title.length > 0 &&
    typeof hero.description === "string" &&
    isLink(hero.primaryAction) &&
    isLink(hero.secondaryAction)
  );
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
    next: {
      revalidate: BLOG_REVALIDATE_SECONDS,
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
    console.error(`Failed to fetch homepage content for locale "${locale}":`, error);
    return fallback;
  }
}

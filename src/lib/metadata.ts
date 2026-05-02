import type { Metadata } from "next";

export const SITE_URL = "https://www.mindhikers.com";
export const SITE_NAME = "心行者 Mindhikers";

const DEFAULT_IMAGE = "/opengraph-image";

export function absoluteUrl(path: string) {
  return new URL(path, SITE_URL).toString();
}

type PageMetadataInput = {
  title: string;
  description: string;
  path: string;
  locale: "zh_CN" | "en_US";
  alternateLocale: "zh_CN" | "en_US";
  languages: NonNullable<Metadata["alternates"]>["languages"];
  imagePath?: string;
};

export function buildPageMetadata({
  title,
  description,
  path,
  locale,
  alternateLocale,
  languages,
  imagePath = DEFAULT_IMAGE,
}: PageMetadataInput): Metadata {
  return {
    title,
    description,
    alternates: {
      canonical: path,
      languages,
    },
    openGraph: {
      title,
      description,
      url: absoluteUrl(path),
      siteName: SITE_NAME,
      type: "website",
      locale,
      alternateLocale: [alternateLocale],
      images: [
        {
          url: imagePath,
          width: 1200,
          height: 630,
          alt: SITE_NAME,
        },
      ],
    },
    twitter: {
      card: "summary_large_image",
      title,
      description,
      images: [imagePath],
    },
  };
}

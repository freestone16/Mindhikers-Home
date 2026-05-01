import type { MetadataRoute } from "next";

export default function robots(): MetadataRoute.Robots {
  const railwayEnvironment =
    process.env.RAILWAY_ENVIRONMENT_NAME ?? process.env.RAILWAY_ENVIRONMENT;
  const appEnvironment = process.env.NEXT_PUBLIC_ENV;
  const isProduction =
    railwayEnvironment === "production" || appEnvironment === "production";

  if (!isProduction) {
    return {
      rules: {
        userAgent: "*",
        disallow: "/",
      },
    };
  }

  return {
    rules: {
      userAgent: "*",
      allow: "/",
    },
    sitemap: "https://www.mindhikers.com/sitemap.xml",
  };
}

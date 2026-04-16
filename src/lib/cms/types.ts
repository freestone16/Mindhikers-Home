export type BlogSource = "mdx" | "wordpress";
export type BlogSourceMode = BlogSource | "hybrid";

export type CmsPostSummary = {
  id: string;
  slug: string;
  title: string;
  summary: string;
  publishedAt: string;
  updatedAt?: string;
  coverImage?: string;
  tags: string[];
  categories: string[];
  source: BlogSource;
};

export type CmsPost =
  | (CmsPostSummary & {
      contentType: "mdx";
      mdxCode: string;
    })
  | (CmsPostSummary & {
      contentType: "html";
      contentHtml: string;
    });

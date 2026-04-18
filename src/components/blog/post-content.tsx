import { mdxComponents } from "@/mdx-components";
import type { CmsPost } from "@/lib/cms/types";
import { sanitizeWordPressHtml } from "@/lib/cms/html";
import { MDXContent } from "@content-collections/mdx/react";

export function PostContent({ post }: { post: CmsPost }) {
  if (post.contentType === "mdx") {
    return <MDXContent code={post.mdxCode} components={mdxComponents} />;
  }

  return <div dangerouslySetInnerHTML={{ __html: sanitizeWordPressHtml(post.contentHtml) }} />;
}

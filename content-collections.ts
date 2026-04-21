import { defineCollection, defineConfig } from "@content-collections/core";
import { compileMDX } from "@content-collections/mdx";
import remarkGfm from "remark-gfm";
import { z } from "zod";
import { remarkCodeMeta } from "./src/lib/remark-code-meta";
import { isValidPublishedDate } from "./src/lib/posts";

const posts = defineCollection({
    name: "posts",
    directory: "content",
    include: "**/*.mdx",
    exclude: [
        "remote-work-productivity.mdx",
        "typescript-best-practices.mdx",
        "api-design-principles.mdx",
        "nextjs-performance-tips.mdx",
        "testing-react-apps.mdx",
        "building-design-systems.mdx",
        "git-workflow-guide.mdx",
    ],
    schema: z.object({
        title: z.string(),
        publishedAt: z.string().refine(isValidPublishedDate, {
            message: "publishedAt must use YYYY-MM-DD format",
        }),
        updatedAt: z
            .string()
            .refine(isValidPublishedDate, {
                message: "updatedAt must use YYYY-MM-DD format",
            })
            .optional(),
        author: z.string().optional(),
        summary: z.string(),
        image: z.string().optional(),
        content: z.string(),
    }),
    transform: async (document, context) => {
        const mdx = await compileMDX(context, document, {
            remarkPlugins: [remarkGfm, remarkCodeMeta],
        });
        return {
        ...document,
            mdx,
        };
    },
});

export default defineConfig({
    collections: [posts],
});

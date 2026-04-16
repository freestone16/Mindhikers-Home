import { PostContent } from "@/components/blog/post-content";
import { formatDate } from "@/lib/utils";
import { DATA } from "@/data/resume";
import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Link from "next/link";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { getPostBySlug, listPostSlugs, listPosts } from "@/lib/cms";

function toAbsoluteImageUrl(imagePath: string) {
  if (imagePath.startsWith("http://") || imagePath.startsWith("https://")) {
    return imagePath;
  }

  return `${DATA.url}${imagePath.startsWith("/") ? imagePath : `/${imagePath}`}`;
}

function toIsoTimestamp(date: string) {
  return new Date(`${date}T00:00:00.000Z`).toISOString();
}

export async function generateStaticParams() {
  const slugs = await listPostSlugs();
  return slugs.map((slug) => ({ slug }));
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{
    slug: string;
  }>;
}): Promise<Metadata | undefined> {
  const { slug } = await params;
  const post = await getPostBySlug(slug);

  if (!post) {
    return undefined;
  }

  const {
    title,
    publishedAt: publishedTime,
    summary: description,
    coverImage,
  } = post;

  const imageUrl = coverImage ? toAbsoluteImageUrl(coverImage) : undefined;

  return {
    title,
    description,
    openGraph: {
      title,
      description,
      type: "article",
      publishedTime: toIsoTimestamp(publishedTime),
      url: `${DATA.url}/blog/${slug}`,
      ...(imageUrl && {
        images: [
          {
            url: imageUrl,
          },
        ],
      }),
    },
    twitter: {
      card: "summary_large_image",
      title,
      description,
      ...(imageUrl && {
        images: [imageUrl],
      }),
    },
  };
}

export default async function Blog({
  params,
}: {
  params: Promise<{
    slug: string;
  }>;
}) {
  const { slug } = await params;
  const [post, sortedPosts] = await Promise.all([getPostBySlug(slug), listPosts()]);
  const currentIndex = sortedPosts.findIndex(
    (candidate) => candidate.slug === slug
  );

  if (!post || currentIndex === -1) {
    notFound();
  }

  const previousPost = currentIndex > 0 ? sortedPosts[currentIndex - 1] : null;
  const nextPost = currentIndex < sortedPosts.length - 1 ? sortedPosts[currentIndex + 1] : null;

  const jsonLdContent = JSON.stringify({
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    headline: post.title,
    datePublished: toIsoTimestamp(post.publishedAt),
    dateModified: toIsoTimestamp(post.updatedAt ?? post.publishedAt),
    description: post.summary,
    image: post.coverImage
      ? toAbsoluteImageUrl(post.coverImage)
      : `${DATA.url}/blog/${slug}/opengraph-image`,
    url: `${DATA.url}/blog/${slug}`,
    author: {
      "@type": "Person",
      name: DATA.name,
    },
  }).replace(/</g, "\\u003c");

  return (
    <section id="blog">
      <script
        type="application/ld+json"
        suppressHydrationWarning
        dangerouslySetInnerHTML={{
          __html: jsonLdContent,
        }}
      />
      <div className="flex justify-start gap-4 items-center">
        <Link href="/blog" className="text-sm text-muted-foreground hover:text-foreground transition-colors border border-border rounded-lg px-2 py-1 inline-flex items-center gap-1 mb-6 group" aria-label="Back to Blog">
          <ChevronLeft className="size-3 group-hover:-translate-x-px transition-transform" />
          Back to Blog
        </Link>
      </div>
      <div className="flex flex-col gap-4">
        <h1 className="title font-semibold text-3xl md:text-4xl tracking-tighter leading-tight">
          {post.title}
        </h1>
        <p className="text-sm text-muted-foreground">
          {formatDate(post.publishedAt)}
        </p>
      </div>
      <div className="my-6 flex w-full items-center">
        <div
          className="flex-1 h-px bg-border"
          style={{
            maskImage:
              "linear-gradient(90deg, transparent, black 8%, black 92%, transparent)",
            WebkitMaskImage:
              "linear-gradient(90deg, transparent, black 8%, black 92%, transparent)",
          }}
        />
      </div>
      <article className="prose max-w-full text-pretty font-sans leading-relaxed text-muted-foreground dark:prose-invert">
        <PostContent post={post} />
      </article>

      <nav className="mt-12 pt-8 max-w-2xl">
        <div className="flex flex-col sm:flex-row justify-between gap-4">
          {previousPost ? (
            <Link
              href={`/blog/${previousPost.slug}`}
              className="group flex-1 flex flex-col gap-1 p-4 rounded-lg border border-border hover:bg-accent/50 transition-colors"
            >
              <span className="flex items-center gap-1 text-xs text-muted-foreground">
                <ChevronLeft className="size-3" />
                Previous
              </span>
              <span className="text-sm font-medium group-hover:text-foreground transition-colors whitespace-normal wrap-break-word">
                {previousPost.title}
              </span>
            </Link>
          ) : (
            <div className="hidden sm:block flex-1" />
          )}

          {nextPost ? (
            <Link
              href={`/blog/${nextPost.slug}`}
              className="group flex-1 flex flex-col gap-1 p-4 rounded-lg border border-border hover:bg-accent/50 transition-colors text-right"
            >
              <span className="flex items-center justify-end gap-1 text-xs text-muted-foreground">
                Next
                <ChevronRight className="size-3" />
              </span>
              <span className="text-sm font-medium group-hover:text-foreground transition-colors whitespace-normal wrap-break-word">
                {nextPost.title}
              </span>
            </Link>
          ) : (
            <div className="hidden sm:block flex-1" />
          )}
        </div>
      </nav>
    </section>
  );
}

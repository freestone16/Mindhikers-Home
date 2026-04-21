import { CACHE_TAG_BLOG, isValidCacheTag } from "@/lib/cms/constants";
import { revalidatePath, revalidateTag } from "next/cache";
import { NextRequest, NextResponse } from "next/server";

type RevalidatePayload = {
  path?: string;
  secret?: string;
  slug?: string;
  tag?: string;
};

const LEGACY_REVALIDATE_PATHS = new Set(["/", "/en", "/blog"]);

function isAllowedLegacyPath(path: string): boolean {
  if (LEGACY_REVALIDATE_PATHS.has(path)) return true;
  return path.startsWith("/blog/");
}

export async function POST(request: NextRequest) {
  const expectedSecret = process.env.REVALIDATE_SECRET?.trim();

  if (!expectedSecret) {
    return NextResponse.json(
      { message: "REVALIDATE_SECRET is not configured", revalidated: false },
      { status: 500 },
    );
  }

  let payload: RevalidatePayload;
  try {
    payload = (await request.json()) as RevalidatePayload;
  } catch {
    return NextResponse.json(
      { message: "Invalid JSON body", revalidated: false },
      { status: 400 },
    );
  }

  const providedSecret =
    request.headers.get("x-revalidate-secret") ?? payload.secret;

  if (providedSecret !== expectedSecret) {
    return NextResponse.json(
      { message: "Invalid revalidation secret", revalidated: false },
      { status: 401 },
    );
  }

  // Tag-based revalidation — only accept tags on the allowlist
  if (typeof payload.tag === "string" && payload.tag.length > 0) {
    if (!isValidCacheTag(payload.tag)) {
      return NextResponse.json(
        { message: `Unknown cache tag: ${payload.tag}`, revalidated: false },
        { status: 400 },
      );
    }

    revalidateTag(payload.tag, "default");
    return NextResponse.json({ ok: true, revalidated: true, tag: payload.tag });
  }

  // Legacy path-based fallback (backward compatible with old WP integration)
  revalidateTag(CACHE_TAG_BLOG, "default");
  revalidatePath("/");
  revalidatePath("/en");
  revalidatePath("/blog");

  if (payload.slug) {
    revalidatePath(`/blog/${payload.slug}`);
  }

  if (payload.path) {
    if (!isAllowedLegacyPath(payload.path)) {
      return NextResponse.json(
        { message: `Unsupported legacy revalidate path: ${payload.path}`, revalidated: false },
        { status: 400 },
      );
    }
    revalidatePath(payload.path);
  }

  return NextResponse.json({
    path: payload.path ?? null,
    revalidated: true,
    slug: payload.slug ?? null,
    tag: CACHE_TAG_BLOG,
  });
}

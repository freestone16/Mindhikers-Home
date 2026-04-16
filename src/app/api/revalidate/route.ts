import { BLOG_CACHE_TAG } from "@/lib/cms/constants";
import { revalidatePath, revalidateTag } from "next/cache";
import { NextRequest, NextResponse } from "next/server";

type RevalidatePayload = {
  path?: string;
  secret?: string;
  slug?: string;
};

function getProvidedSecret(request: NextRequest, payload: RevalidatePayload) {
  // Secret must come from the x-revalidate-secret header (preferred) or POST
  // request body. Query param is intentionally not supported to prevent the
  // secret from appearing in server access logs and browser history.
  return request.headers.get("x-revalidate-secret") ?? payload.secret;
}

async function readPayload(request: NextRequest): Promise<RevalidatePayload> {
  if (request.method === "POST") {
    try {
      return (await request.json()) as RevalidatePayload;
    } catch {
      return {};
    }
  }

  return {
    path: request.nextUrl.searchParams.get("path") ?? undefined,
    slug: request.nextUrl.searchParams.get("slug") ?? undefined,
  };
}

async function handleRevalidate(request: NextRequest) {
  const expectedSecret = process.env.REVALIDATE_SECRET?.trim();

  if (!expectedSecret) {
    return NextResponse.json(
      {
        message: "REVALIDATE_SECRET is not configured",
        revalidated: false,
      },
      { status: 500 }
    );
  }

  const payload = await readPayload(request);
  const providedSecret = getProvidedSecret(request, payload);

  if (providedSecret !== expectedSecret) {
    return NextResponse.json(
      {
        message: "Invalid revalidation secret",
        revalidated: false,
      },
      { status: 401 }
    );
  }

  revalidateTag(BLOG_CACHE_TAG, "max");
  revalidatePath("/");
  revalidatePath("/en");
  revalidatePath("/blog");

  if (payload.slug) {
    revalidatePath(`/blog/${payload.slug}`);
  }

  if (payload.path) {
    revalidatePath(payload.path);
  }

  return NextResponse.json({
    path: payload.path ?? null,
    revalidated: true,
    slug: payload.slug ?? null,
    tag: BLOG_CACHE_TAG,
  });
}

export async function GET(request: NextRequest) {
  return handleRevalidate(request);
}

export async function POST(request: NextRequest) {
  return handleRevalidate(request);
}

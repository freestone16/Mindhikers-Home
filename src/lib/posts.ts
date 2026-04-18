const ISO_DATE_PATTERN = /^\d{4}-\d{2}-\d{2}$/;

export function isValidPublishedDate(date: string) {
  if (!ISO_DATE_PATTERN.test(date)) {
    return false;
  }

  const parsed = new Date(`${date}T00:00:00.000Z`);
  return !Number.isNaN(parsed.getTime()) && parsed.toISOString().startsWith(date);
}

export function parsePublishedDate(date: string) {
  if (!isValidPublishedDate(date)) {
    throw new Error(`Invalid publishedAt value: ${date}`);
  }

  return new Date(`${date}T00:00:00.000Z`);
}

export function normalizePublishedDate(date: string | Date) {
  const parsed = date instanceof Date ? date : new Date(date);

  if (Number.isNaN(parsed.getTime())) {
    throw new Error(`Invalid date value: ${String(date)}`);
  }

  return parsed.toISOString().slice(0, 10);
}

type PostWithPublishedDate = {
  publishedAt: string;
  _meta?: {
    path: string;
  };
  slug?: string;
};

export function sortPostsByPublishedDateDesc<T extends PostWithPublishedDate>(posts: T[]) {
  return [...posts].sort((a, b) => {
    const timeDelta =
      parsePublishedDate(b.publishedAt).getTime() -
      parsePublishedDate(a.publishedAt).getTime();

    if (timeDelta !== 0) {
      return timeDelta;
    }

    const aTieBreaker = a._meta?.path ?? a.slug ?? "";
    const bTieBreaker = b._meta?.path ?? b.slug ?? "";
    return aTieBreaker.localeCompare(bTieBreaker);
  });
}

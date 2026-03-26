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

type PostWithPublishedDate = {
  publishedAt: string;
  _meta: {
    path: string;
  };
};

export function sortPostsByPublishedDateDesc<T extends PostWithPublishedDate>(posts: T[]) {
  return [...posts].sort((a, b) => {
    const timeDelta =
      parsePublishedDate(b.publishedAt).getTime() -
      parsePublishedDate(a.publishedAt).getTime();

    if (timeDelta !== 0) {
      return timeDelta;
    }

    return a._meta.path.localeCompare(b._meta.path);
  });
}

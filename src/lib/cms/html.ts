import sanitizeHtml from "sanitize-html";

const HTML_ENTITY_MAP: Record<string, string> = {
  amp: "&",
  apos: "'",
  gt: ">",
  lt: "<",
  nbsp: " ",
  quot: '"',
};

const allowedTags = [
  ...sanitizeHtml.defaults.allowedTags,
  "figure",
  "figcaption",
  "h1",
  "h2",
  "h3",
  "h4",
  "h5",
  "h6",
  "img",
  "span",
  "table",
  "tbody",
  "td",
  "tfoot",
  "th",
  "thead",
  "tr",
];

const allowedAttributes = {
  ...sanitizeHtml.defaults.allowedAttributes,
  a: ["href", "name", "target", "rel"],
  code: ["class"],
  img: ["alt", "height", "loading", "src", "srcset", "title", "width"],
  p: ["class"],
  span: ["class"],
  table: ["class"],
  td: ["colspan", "rowspan"],
  th: ["colspan", "rowspan", "scope"],
};

export function decodeHtmlEntities(value: string) {
  return value
    .replace(/&#(\d+);/g, (_, decimal: string) =>
      String.fromCodePoint(Number.parseInt(decimal, 10))
    )
    .replace(/&#x([0-9a-fA-F]+);/g, (_, hexadecimal: string) =>
      String.fromCodePoint(Number.parseInt(hexadecimal, 16))
    )
    .replace(/&([a-zA-Z]+);/g, (entity, name: string) => HTML_ENTITY_MAP[name] ?? entity);
}

export function sanitizeWordPressHtml(html: string) {
  return sanitizeHtml(html, {
    allowedAttributes,
    allowedSchemes: ["http", "https", "mailto"],
    allowedSchemesByTag: {
      img: ["http", "https", "data"],
    },
    allowedTags,
    enforceHtmlBoundary: true,
    nonBooleanAttributes: ["target"],
    transformTags: {
      a: sanitizeHtml.simpleTransform("a", {
        rel: "noopener noreferrer",
      }),
    },
  });
}

export function htmlToPlainText(html: string) {
  const stripped = sanitizeHtml(html, {
    allowedAttributes: {},
    allowedTags: [],
  });

  return decodeHtmlEntities(stripped).replace(/\s+/g, " ").trim();
}

export function truncateText(text: string, maxLength = 220) {
  if (text.length <= maxLength) {
    return text;
  }

  return `${text.slice(0, maxLength).trimEnd()}...`;
}

/* eslint-disable @next/next/no-img-element */

interface MediaContainerProps {
  src: string;
  alt?: string;
  type?: "image" | "video";
  className?: string;
}

function getSafeMediaSource(src: string) {
  if (src.startsWith("/")) {
    return src;
  }

  try {
    const url = new URL(src);
    return url.protocol === "https:" ? url.toString() : null;
  } catch {
    return null;
  }
}

export function MediaContainer({
  src,
  alt = "",
  type = "image",
  className = "",
}: MediaContainerProps) {
  const safeSrc = getSafeMediaSource(src);

  return (
    <div className={`ring-4 ring-muted w-full h-[300px] rounded-lg overflow-hidden flex items-center justify-center ${className}`}>
      {!safeSrc ? (
        <div className="flex h-full w-full items-center justify-center bg-muted px-4 text-center text-sm text-muted-foreground">
          Unsupported media source
        </div>
      ) : type === "image" ? (
        <img
          src={safeSrc}
          alt={alt}
          className="w-full h-full object-cover object-center max-w-full max-h-full"
          loading="lazy"
          referrerPolicy="no-referrer"
        />
      ) : (
        <video
          src={safeSrc}
          className="w-full h-full object-cover object-center max-w-full max-h-full"
          controls
          playsInline
          preload="metadata"
        />
      )}
    </div>
  );
}

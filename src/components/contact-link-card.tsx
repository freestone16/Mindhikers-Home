"use client";

import Image from "next/image";
import { useState } from "react";
import { ArrowUpRight, X } from "lucide-react";

export function ContactLinkCard({
  href,
  label,
  note,
  qrImage,
}: {
  href: string;
  label: string;
  note: string;
  qrImage?: string;
}) {
  const [showQr, setShowQr] = useState(false);

  if (!qrImage) {
    return (
      <a
        href={href}
        target="_blank"
        rel="noopener noreferrer"
        className="group flex items-center justify-between rounded-[1.0rem] border border-border/70 bg-white/68 px-5 py-4 backdrop-blur transition-transform hover:-translate-y-0.5"
      >
        <div>
          <p className="text-sm font-medium text-foreground">{label}</p>
          {note && <p className="mt-1 text-[13px] text-foreground/62">{note}</p>}
        </div>
        <ArrowUpRight className="size-4 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
      </a>
    );
  }

  return (
    <>
      <button
        type="button"
        onClick={() => setShowQr(true)}
        className="group flex w-full items-center justify-between rounded-[1.0rem] border border-border/70 bg-white/68 px-5 py-4 backdrop-blur transition-transform hover:-translate-y-0.5 text-left"
      >
        <div>
          <p className="text-sm font-medium text-foreground">{label}</p>
          {note && <p className="mt-1 text-[13px] text-foreground/62">{note}</p>}
        </div>
        <span className="rounded-full border border-border/80 bg-accent/50 px-2.5 py-1 text-[10px] tracking-wide text-foreground/70">
          QR
        </span>
      </button>

      {showQr && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
          onClick={() => setShowQr(false)}
          role="dialog"
          aria-modal="true"
          aria-label={`${label} QR code`}
        >
          <div
            className="relative rounded-2xl border border-border/70 bg-white p-5 shadow-xl"
            onClick={(e) => e.stopPropagation()}
          >
            <button
              type="button"
              onClick={() => setShowQr(false)}
              className="absolute right-3 top-3 rounded-full p-1 text-muted-foreground hover:text-foreground transition-colors"
              aria-label="Close"
            >
              <X className="size-4" />
            </button>
            <p className="mb-3 text-sm font-medium text-foreground">{label}</p>
            <div className="relative size-52 overflow-hidden rounded-xl border border-border/50">
              <Image
                src={qrImage}
                alt={`${label} QR code`}
                fill
                className="object-contain"
                sizes="208px"
              />
            </div>
          </div>
        </div>
      )}
    </>
  );
}

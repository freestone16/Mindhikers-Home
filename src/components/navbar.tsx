"use client";

import { Button } from "@/components/ui/button";
import { getLocaleFromPathname, SITE_CONTENT } from "@/data/site-content";
import { cn } from "@/lib/utils";
import Image from "next/image";
import Link from "next/link";
import { usePathname } from "next/navigation";

export default function Navbar() {
  const pathname = usePathname();
  const locale = getLocaleFromPathname(pathname);
  const content = SITE_CONTENT[locale];

  return (
    <header className="pointer-events-none fixed inset-x-0 top-[3px] z-40 px-4 sm:px-6">
      <div className="pointer-events-auto mx-auto flex max-w-6xl items-center justify-between gap-3 rounded-[1.35rem] border border-border/70 bg-background/88 px-3 py-2.5 shadow-[0_12px_40px_rgba(20,24,22,0.08)] backdrop-blur-md sm:px-4">
        <Link
          href={locale === "en" ? "/en" : "/"}
          className="flex items-center gap-3"
        >
          <span className="relative size-8 overflow-hidden rounded-md border border-border bg-background sm:size-9">
            <Image
              src="/MindHikers.png"
              alt="心行者 Mindhikers"
              fill
              className="object-cover"
              sizes="36px"
              priority
            />
          </span>
          <span className="text-base font-semibold tracking-tight text-foreground sm:text-lg">
            {content.navigation.brand}
          </span>
        </Link>
        <nav className="hidden items-center gap-1 md:flex">
          {content.navigation.links.map((item) => {
            const active = pathname === item.href;

            return (
              <Button
                key={item.href}
                asChild
                variant="ghost"
                className={cn(
                  "h-8 rounded-full px-3 text-[12px] text-muted-foreground hover:bg-accent hover:text-foreground",
                  active && "bg-accent text-foreground"
                )}
              >
                <Link href={item.href}>{item.label}</Link>
              </Button>
            );
          })}
        </nav>
        <Button
          asChild
          variant="outline"
          className="h-8 rounded-full border-border bg-background/85 px-3 text-[12px]"
        >
          <Link href={content.navigation.switchLanguage.href}>
            {content.navigation.switchLanguage.label}
          </Link>
        </Button>
      </div>
    </header>
  );
}

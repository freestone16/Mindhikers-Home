"use client";

import { Button } from "@/components/ui/button";
import { getLocaleFromPathname, SITE_CONTENT } from "@/data/site-content";
import { cn } from "@/lib/utils";
import Link from "next/link";
import { usePathname } from "next/navigation";

export default function Navbar() {
  const pathname = usePathname();
  const locale = getLocaleFromPathname(pathname);
  const content = SITE_CONTENT[locale];

  return (
    <header className="pointer-events-none fixed inset-x-0 top-0 z-40 px-4 py-4 sm:px-6">
      <div className="pointer-events-auto mx-auto flex max-w-6xl items-center justify-between gap-4 rounded-full border border-border/70 bg-background/88 px-4 py-3 shadow-[0_24px_70px_-45px_rgba(97,68,39,0.65)] backdrop-blur-xl sm:px-6">
        <Link
          href={locale === "en" ? "/en" : "/"}
          className="font-display text-lg tracking-tight text-foreground"
        >
          {content.navigation.brand}
        </Link>
        <nav className="hidden items-center gap-1 md:flex">
          {content.navigation.links.map((item) => {
            const active =
              pathname === item.href ||
              (item.href.includes("#") && pathname === item.href.split("#")[0]);

            return (
              <Button
                key={item.href}
                asChild
                variant="ghost"
                className={cn(
                  "rounded-full px-4 text-sm text-muted-foreground hover:bg-accent/70 hover:text-foreground",
                  active && "bg-accent/80 text-foreground"
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
          className="rounded-full border-border/80 bg-background/80 px-4"
        >
          <Link href={content.navigation.switchLanguage.href}>
            {content.navigation.switchLanguage.label}
          </Link>
        </Button>
      </div>
    </header>
  );
}

import BlurFade from "@/components/magicui/blur-fade";
import { Button } from "@/components/ui/button";
import { HomeContent } from "@/data/site-content";
import { ArrowUpRight, Sparkles } from "lucide-react";
import Link from "next/link";

const BLUR_FADE_DELAY = 0.06;

export function ProductPage({ content }: { content: HomeContent }) {
  const detail = content.productDetail;

  return (
    <main className="space-y-14 pb-12 pt-8 sm:space-y-16 sm:pt-10">
      <section className="space-y-5">
        <BlurFade delay={BLUR_FADE_DELAY}>
          <p className="text-[10px] uppercase tracking-[0.14em] text-muted-foreground">
            {detail.eyebrow}
          </p>
        </BlurFade>
        <BlurFade delay={BLUR_FADE_DELAY * 2}>
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_320px] lg:items-start">
            <div className="space-y-3.5">
              <h1 className="text-3xl font-semibold leading-tight tracking-tight text-foreground sm:text-4xl">
                {detail.title}
              </h1>
              <p className="max-w-3xl text-[14px] leading-7 text-muted-foreground sm:text-[15px]">
                {detail.summary}
              </p>
              <div className="flex flex-wrap gap-3">
                <Button asChild className="h-9 rounded-md px-3.5 text-[13px]">
                  <Link href={detail.returnHome.href}>{detail.returnHome.label}</Link>
                </Button>
                <Button
                  asChild
                  variant="outline"
                  className="h-9 rounded-md border-border bg-background px-3.5 text-[13px]"
                >
                  <Link href={detail.switchLanguage.href}>
                    {detail.switchLanguage.label}
                    <ArrowUpRight className="ml-2 size-4" />
                  </Link>
                </Button>
              </div>
            </div>
            <div className="rounded-xl border border-border bg-card p-4 shadow-none">
              <div className="mb-3 flex size-8 items-center justify-center rounded-md bg-accent text-primary">
                <Sparkles className="size-4" />
              </div>
              <p className="text-[10px] uppercase tracking-[0.14em] text-muted-foreground">
                {detail.stageLabel}
              </p>
              <p className="mt-2.5 text-[13px] leading-6 text-muted-foreground">
                {detail.stageValue}
              </p>
            </div>
          </div>
        </BlurFade>
      </section>

      <section className="grid gap-4 md:grid-cols-3">
        {detail.bullets.map((item, index) => (
          <BlurFade key={item} delay={BLUR_FADE_DELAY * (3 + index)}>
            <div className="rounded-xl border border-border bg-card p-4 shadow-none">
              <p className="text-[10px] uppercase tracking-[0.14em] text-muted-foreground">
                {content.locale === "zh" ? `方向 ${index + 1}` : `Direction ${index + 1}`}
              </p>
              <p className="mt-2.5 text-[13px] leading-6 text-foreground/88">{item}</p>
            </div>
          </BlurFade>
        ))}
      </section>
    </main>
  );
}

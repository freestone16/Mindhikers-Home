import BlurFade from "@/components/magicui/blur-fade";
import { Button } from "@/components/ui/button";
import { HomeContent } from "@/data/site-content";
import { ArrowUpRight, Sparkles } from "lucide-react";
import Link from "next/link";

const BLUR_FADE_DELAY = 0.06;

export function ProductPage({ content }: { content: HomeContent }) {
  const detail = content.productDetail;

  return (
    <main className="space-y-20 pb-16 pt-12 sm:space-y-24">
      <section className="space-y-8">
        <BlurFade delay={BLUR_FADE_DELAY}>
          <p className="text-sm uppercase tracking-[0.24em] text-primary/85">
            {detail.eyebrow}
          </p>
        </BlurFade>
        <BlurFade delay={BLUR_FADE_DELAY * 2}>
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_320px] lg:items-start">
            <div className="space-y-6">
              <h1 className="font-display text-5xl leading-[0.95] tracking-[-0.04em] text-foreground sm:text-6xl">
                {detail.title}
              </h1>
              <p className="max-w-3xl text-lg leading-8 text-muted-foreground sm:text-xl">
                {detail.summary}
              </p>
              <div className="flex flex-wrap gap-3">
                <Button asChild size="lg" className="rounded-full px-6">
                  <Link href={detail.returnHome.href}>{detail.returnHome.label}</Link>
                </Button>
                <Button
                  asChild
                  size="lg"
                  variant="outline"
                  className="rounded-full border-border/80 bg-background/80 px-6"
                >
                  <Link href={detail.switchLanguage.href}>
                    {detail.switchLanguage.label}
                    <ArrowUpRight className="ml-2 size-4" />
                  </Link>
                </Button>
              </div>
            </div>
            <div className="rounded-[30px] border border-border/70 bg-card/90 p-6 shadow-[0_30px_90px_-55px_rgba(97,68,39,0.65)]">
              <div className="mb-4 flex size-11 items-center justify-center rounded-full bg-primary/12 text-primary">
                <Sparkles className="size-5" />
              </div>
              <p className="text-sm uppercase tracking-[0.22em] text-primary/80">
                {detail.stageLabel}
              </p>
              <p className="mt-3 text-sm leading-7 text-muted-foreground sm:text-base">
                {detail.stageValue}
              </p>
            </div>
          </div>
        </BlurFade>
      </section>

      <section className="grid gap-5 md:grid-cols-3">
        {detail.bullets.map((item, index) => (
          <BlurFade key={item} delay={BLUR_FADE_DELAY * (3 + index)}>
            <div className="rounded-[28px] border border-border/70 bg-card/90 p-6 shadow-[0_20px_60px_-45px_rgba(97,68,39,0.45)]">
              <p className="text-sm uppercase tracking-[0.22em] text-primary/80">
                {content.locale === "zh" ? `方向 ${index + 1}` : `Direction ${index + 1}`}
              </p>
              <p className="mt-4 text-base leading-8 text-foreground/88">{item}</p>
            </div>
          </BlurFade>
        ))}
      </section>
    </main>
  );
}

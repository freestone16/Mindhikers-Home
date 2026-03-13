import BlurFade from "@/components/magicui/blur-fade";
import { Button } from "@/components/ui/button";
import { HomeContent } from "@/data/site-content";
import { ArrowUpRight, Compass, Languages, Sparkles } from "lucide-react";
import Link from "next/link";
import type { ReactNode } from "react";

const BLUR_FADE_DELAY = 0.05;

function SectionHeader({
  eyebrow,
  title,
  description,
}: {
  eyebrow: string;
  title: string;
  description: string;
}) {
  return (
    <div className="max-w-2xl space-y-3">
      <p className="text-sm uppercase tracking-[0.22em] text-primary/80">
        {eyebrow}
      </p>
      <h2 className="font-display text-3xl tracking-tight text-foreground sm:text-4xl">
        {title}
      </h2>
      <p className="text-base leading-7 text-muted-foreground sm:text-lg">
        {description}
      </p>
    </div>
  );
}

function Card({
  item,
  icon,
}: {
  item: HomeContent["products"]["items"][number];
  icon: ReactNode;
}) {
  return (
    <div className="group flex h-full flex-col justify-between rounded-[28px] border border-border/70 bg-card/90 p-6 shadow-[0_20px_60px_-40px_rgba(97,68,39,0.45)] transition-transform duration-300 hover:-translate-y-1">
      <div className="space-y-4">
        <div className="flex items-center justify-between gap-3">
          <span className="text-xs uppercase tracking-[0.22em] text-primary/80">
            {item.eyebrow}
          </span>
          <span className="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary">
            {icon}
          </span>
        </div>
        <div className="space-y-3">
          <div className="flex items-start justify-between gap-3">
            <h3 className="font-display text-2xl tracking-tight text-foreground">
              {item.title}
            </h3>
            {item.meta ? (
              <span className="rounded-full bg-background px-3 py-1 text-xs text-muted-foreground">
                {item.meta}
              </span>
            ) : null}
          </div>
          <p className="text-sm leading-7 text-muted-foreground sm:text-base">
            {item.description}
          </p>
        </div>
      </div>
      {item.href && item.ctaLabel ? (
        <div className="pt-6">
          <Button
            asChild
            variant="ghost"
            className="h-auto rounded-full px-0 text-sm text-foreground hover:bg-transparent"
          >
            <Link href={item.href}>
              {item.ctaLabel}
              <ArrowUpRight className="ml-2 size-4" />
            </Link>
          </Button>
        </div>
      ) : null}
    </div>
  );
}

export function HomePage({ content }: { content: HomeContent }) {
  const labels =
    content.locale === "zh"
      ? {
          about: "品牌",
          featured: "核心入口",
          infrastructure: "基础设施",
          methods: "工作方法",
          next: "下一步",
        }
      : {
          about: "Brand",
          featured: "Featured",
          infrastructure: "Infrastructure",
          methods: "Working methods",
          next: "Next step",
        };

  return (
    <main className="space-y-24 pb-16 pt-8 sm:space-y-28 sm:pt-12">
      <section className="grid gap-10 lg:grid-cols-[minmax(0,1.35fr)_minmax(280px,0.85fr)] lg:items-end">
        <div className="space-y-8">
          <BlurFade delay={BLUR_FADE_DELAY}>
            <p className="text-sm uppercase tracking-[0.24em] text-primary/85">
              {content.hero.eyebrow}
            </p>
          </BlurFade>
          <BlurFade delay={BLUR_FADE_DELAY * 2}>
            <div className="space-y-6">
              <h1 className="max-w-4xl font-display text-5xl leading-[0.95] tracking-[-0.04em] text-foreground sm:text-6xl lg:text-7xl">
                {content.hero.title}
              </h1>
              <p className="max-w-2xl text-lg leading-8 text-muted-foreground sm:text-xl">
                {content.hero.description}
              </p>
            </div>
          </BlurFade>
          <BlurFade delay={BLUR_FADE_DELAY * 3}>
            <div className="flex flex-wrap gap-3">
              <Button
                asChild
                size="lg"
                className="rounded-full bg-primary px-6 text-primary-foreground shadow-[0_20px_40px_-24px_rgba(140,95,53,0.95)] hover:bg-primary/90"
              >
                <Link href={content.hero.primaryAction.href}>
                  {content.hero.primaryAction.label}
                </Link>
              </Button>
              <Button
                asChild
                size="lg"
                variant="outline"
                className="rounded-full border-border/80 bg-background/80 px-6"
              >
                <Link href={content.hero.secondaryAction.href}>
                  <Languages className="mr-2 size-4" />
                  {content.hero.secondaryAction.label}
                </Link>
              </Button>
            </div>
          </BlurFade>
          <BlurFade delay={BLUR_FADE_DELAY * 4}>
            <div className="flex flex-wrap gap-2">
              {content.hero.highlights.map((item) => (
                <span
                  key={item}
                  className="rounded-full border border-border/80 bg-background/80 px-4 py-2 text-sm text-muted-foreground"
                >
                  {item}
                </span>
              ))}
            </div>
          </BlurFade>
        </div>
        <BlurFade delay={BLUR_FADE_DELAY * 5}>
          <aside className="rounded-[32px] border border-border/70 bg-[linear-gradient(180deg,rgba(255,250,242,0.98),rgba(246,238,228,0.92))] p-7 shadow-[0_32px_80px_-48px_rgba(97,68,39,0.55)]">
            <div className="space-y-6">
              <div className="flex size-12 items-center justify-center rounded-full bg-primary/12 text-primary">
                <Compass className="size-5" />
              </div>
              <div className="space-y-3">
                <p className="text-sm uppercase tracking-[0.22em] text-primary/80">
                  {content.hero.focusCard.title}
                </p>
                <div className="space-y-3">
                  {content.hero.focusCard.items.map((item) => (
                    <div
                      key={item}
                      className="rounded-2xl border border-white/70 bg-white/70 px-4 py-4 text-sm leading-6 text-foreground/85"
                    >
                      {item}
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </aside>
        </BlurFade>
      </section>

      <section id="about" className="space-y-8">
        <BlurFade delay={BLUR_FADE_DELAY * 6}>
          <SectionHeader
            eyebrow={labels.about}
            title={content.about.title}
            description={content.about.paragraphs[0]}
          />
        </BlurFade>
        <div className="grid gap-6 md:grid-cols-2">
          {content.about.paragraphs.map((paragraph, index) => (
            <BlurFade key={paragraph} delay={BLUR_FADE_DELAY * (7 + index)}>
              <div className="rounded-[28px] border border-border/70 bg-card/90 p-6 leading-8 text-muted-foreground shadow-[0_20px_60px_-45px_rgba(97,68,39,0.45)]">
                {paragraph}
              </div>
            </BlurFade>
          ))}
        </div>
      </section>

      <section id="products" className="space-y-10">
        <BlurFade delay={BLUR_FADE_DELAY * 9}>
          <SectionHeader
            eyebrow={labels.featured}
            title={content.products.title}
            description={content.products.description}
          />
        </BlurFade>
        <div className="grid gap-5 lg:grid-cols-1">
          {content.products.items.map((item, index) => (
            <BlurFade key={item.title} delay={BLUR_FADE_DELAY * (10 + index)}>
              <Card item={item} icon={<Sparkles className="size-4" />} />
            </BlurFade>
          ))}
        </div>
      </section>

      <section id="projects" className="space-y-10">
        <BlurFade delay={BLUR_FADE_DELAY * 11}>
          <SectionHeader
            eyebrow={labels.infrastructure}
            title={content.projects.title}
            description={content.projects.description}
          />
        </BlurFade>
        <div className="grid gap-5 md:grid-cols-3">
          {content.projects.items.map((item, index) => (
            <BlurFade key={item.title} delay={BLUR_FADE_DELAY * (12 + index)}>
              <Card item={item} icon={<Compass className="size-4" />} />
            </BlurFade>
          ))}
        </div>
      </section>

      <section id="tools" className="space-y-10">
        <BlurFade delay={BLUR_FADE_DELAY * 15}>
          <SectionHeader
            eyebrow={labels.methods}
            title={content.tools.title}
            description={content.tools.description}
          />
        </BlurFade>
        <div className="grid gap-5 md:grid-cols-3">
          {content.tools.items.map((item, index) => (
            <BlurFade key={item.title} delay={BLUR_FADE_DELAY * (16 + index)}>
              <Card item={item} icon={<Languages className="size-4" />} />
            </BlurFade>
          ))}
        </div>
      </section>

      <section className="rounded-[32px] border border-border/70 bg-[linear-gradient(180deg,rgba(255,252,247,0.94),rgba(243,235,224,0.98))] px-6 py-10 shadow-[0_32px_90px_-60px_rgba(97,68,39,0.8)] sm:px-10 sm:py-12">
        <BlurFade delay={BLUR_FADE_DELAY * 19}>
          <div className="space-y-6">
            <p className="text-sm uppercase tracking-[0.22em] text-primary/80">
              {labels.next}
            </p>
            <div className="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_auto] lg:items-end">
              <div className="space-y-4">
                <h2 className="max-w-3xl font-display text-3xl tracking-tight text-foreground sm:text-4xl">
                  {content.closing.title}
                </h2>
                <p className="max-w-2xl text-base leading-8 text-muted-foreground sm:text-lg">
                  {content.closing.description}
                </p>
              </div>
              <div className="flex flex-wrap gap-3">
                <Button
                  asChild
                  size="lg"
                  className="rounded-full bg-primary px-6 text-primary-foreground"
                >
                  <Link href={content.closing.primaryAction.href}>
                    {content.closing.primaryAction.label}
                  </Link>
                </Button>
                <Button
                  asChild
                  size="lg"
                  variant="outline"
                  className="rounded-full border-border/80 bg-background/80 px-6"
                >
                  <Link href={content.closing.secondaryAction.href}>
                    {content.closing.secondaryAction.label}
                  </Link>
                </Button>
              </div>
            </div>
          </div>
        </BlurFade>
      </section>
    </main>
  );
}

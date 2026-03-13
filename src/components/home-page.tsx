import BlurFade from "@/components/magicui/blur-fade";
import { Button } from "@/components/ui/button";
import { HomeContent } from "@/data/site-content";
import { ArrowUpRight, Compass, Languages, Sparkles } from "lucide-react";
import Image from "next/image";
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
    <div className="max-w-2xl space-y-2">
      <p className="text-[10px] uppercase tracking-[0.16em] text-muted-foreground">
        {eyebrow}
      </p>
      <h2 className="text-xl font-semibold tracking-tight text-foreground sm:text-2xl">
        {title}
      </h2>
      <p className="text-[13px] leading-6 text-muted-foreground sm:text-sm">
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
    <div className="flex h-full flex-col justify-between rounded-xl border border-border bg-card p-4 shadow-none transition-colors hover:bg-accent/25">
      <div className="space-y-3">
        <div className="flex items-center justify-between gap-3">
          <span className="text-[10px] uppercase tracking-[0.14em] text-muted-foreground">
            {item.eyebrow}
          </span>
          <span className="flex size-7 items-center justify-center rounded-md bg-accent text-primary">
            {icon}
          </span>
        </div>
        <div className="space-y-2.5">
          <div className="flex items-start justify-between gap-3">
            <h3 className="text-[15px] font-medium tracking-tight text-foreground">
              {item.title}
            </h3>
            {item.meta ? (
              <span className="rounded-md bg-accent px-2 py-1 text-[10px] text-muted-foreground">
                {item.meta}
              </span>
            ) : null}
          </div>
          <p className="text-[13px] leading-6 text-muted-foreground">
            {item.description}
          </p>
        </div>
      </div>
      {item.href && item.ctaLabel ? (
        <div className="pt-5">
          <Button
            asChild
            variant="ghost"
            className="h-auto rounded-md px-0 text-[13px] text-foreground hover:bg-transparent"
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
    <main className="space-y-16 pb-12 pt-8 sm:space-y-18 sm:pt-10">
      <section className="grid gap-8 lg:grid-cols-[minmax(0,1.3fr)_minmax(260px,0.8fr)] lg:items-start">
        <div className="space-y-5">
          <BlurFade delay={BLUR_FADE_DELAY}>
            <p className="text-[10px] uppercase tracking-[0.14em] text-muted-foreground">
              {content.hero.eyebrow}
            </p>
          </BlurFade>
          <BlurFade delay={BLUR_FADE_DELAY * 2}>
            <div className="space-y-4">
              <h1 className="max-w-3xl text-3xl font-semibold leading-tight tracking-tight text-foreground sm:text-4xl">
                {content.hero.title}
              </h1>
              <p className="max-w-2xl text-[14px] leading-7 text-muted-foreground sm:text-[15px]">
                {content.hero.description}
              </p>
            </div>
          </BlurFade>
          <BlurFade delay={BLUR_FADE_DELAY * 3}>
            <div className="flex flex-wrap gap-3">
              <Button
                asChild
                className="h-9 rounded-md bg-primary px-3.5 text-[13px] text-primary-foreground hover:bg-primary/90"
              >
                <Link href={content.hero.primaryAction.href}>
                  {content.hero.primaryAction.label}
                </Link>
              </Button>
              <Button
                asChild
                variant="outline"
                className="h-9 rounded-md border-border bg-background px-3.5 text-[13px]"
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
                  className="rounded-md border border-border bg-card px-2.5 py-1.5 text-[11px] text-muted-foreground"
                >
                  {item}
                </span>
              ))}
            </div>
          </BlurFade>
        </div>
        <BlurFade delay={BLUR_FADE_DELAY * 5}>
          <aside className="rounded-xl border border-border bg-card p-4">
            <div className="space-y-3">
              <div className="flex items-center gap-3">
                <div className="relative size-10 overflow-hidden rounded-md border border-border bg-background">
                  <Image
                    src="/MindHikers.png"
                    alt="心行者 Mindhikers"
                    fill
                    className="object-cover"
                    sizes="40px"
                    priority
                  />
                </div>
                <div className="min-w-0">
                  <p className="text-[10px] uppercase tracking-[0.14em] text-muted-foreground">
                    {content.hero.focusCard.title}
                  </p>
                  <p className="text-[13px] font-medium text-foreground">
                    心行者 Mindhikers
                  </p>
                </div>
              </div>
              <div className="divide-y divide-border rounded-lg border border-border bg-background">
                {content.hero.focusCard.items.map((item) => (
                  <div
                    key={item}
                    className="px-3 py-3 text-[13px] leading-6 text-foreground/85"
                  >
                    {item}
                  </div>
                ))}
              </div>
            </div>
          </aside>
        </BlurFade>
      </section>

      <section id="about" className="space-y-6">
        <BlurFade delay={BLUR_FADE_DELAY * 6}>
          <SectionHeader
            eyebrow={labels.about}
            title={content.about.title}
            description={content.about.paragraphs[0]}
          />
        </BlurFade>
        <div className="grid gap-4 md:grid-cols-2">
          {content.about.paragraphs.map((paragraph, index) => (
            <BlurFade key={paragraph} delay={BLUR_FADE_DELAY * (7 + index)}>
              <div className="rounded-xl border border-border bg-card p-4 text-[13px] leading-6 text-muted-foreground shadow-none">
                {paragraph}
              </div>
            </BlurFade>
          ))}
        </div>
      </section>

      <section id="products" className="space-y-6">
        <BlurFade delay={BLUR_FADE_DELAY * 9}>
          <SectionHeader
            eyebrow={labels.featured}
            title={content.products.title}
            description={content.products.description}
          />
        </BlurFade>
        <div className="grid gap-4 lg:grid-cols-1">
          {content.products.items.map((item, index) => (
            <BlurFade key={item.title} delay={BLUR_FADE_DELAY * (10 + index)}>
              <Card item={item} icon={<Sparkles className="size-4" />} />
            </BlurFade>
          ))}
        </div>
      </section>

      <section id="projects" className="space-y-6">
        <BlurFade delay={BLUR_FADE_DELAY * 11}>
          <SectionHeader
            eyebrow={labels.infrastructure}
            title={content.projects.title}
            description={content.projects.description}
          />
        </BlurFade>
        <div className="grid gap-4 md:grid-cols-3">
          {content.projects.items.map((item, index) => (
            <BlurFade key={item.title} delay={BLUR_FADE_DELAY * (12 + index)}>
              <Card item={item} icon={<Compass className="size-4" />} />
            </BlurFade>
          ))}
        </div>
      </section>

      <section id="tools" className="space-y-6">
        <BlurFade delay={BLUR_FADE_DELAY * 15}>
          <SectionHeader
            eyebrow={labels.methods}
            title={content.tools.title}
            description={content.tools.description}
          />
        </BlurFade>
        <div className="grid gap-4 md:grid-cols-3">
          {content.tools.items.map((item, index) => (
            <BlurFade key={item.title} delay={BLUR_FADE_DELAY * (16 + index)}>
              <Card item={item} icon={<Languages className="size-4" />} />
            </BlurFade>
          ))}
        </div>
      </section>

      <section className="rounded-xl border border-border bg-card px-4 py-6 shadow-none sm:px-5 sm:py-7">
        <BlurFade delay={BLUR_FADE_DELAY * 19}>
          <div className="space-y-4">
            <p className="text-[10px] uppercase tracking-[0.14em] text-muted-foreground">
              {labels.next}
            </p>
            <div className="grid gap-4 lg:grid-cols-[minmax(0,1.1fr)_auto] lg:items-end">
              <div className="space-y-2.5">
                <h2 className="max-w-3xl text-xl font-semibold tracking-tight text-foreground sm:text-2xl">
                  {content.closing.title}
                </h2>
                <p className="max-w-2xl text-[13px] leading-6 text-muted-foreground sm:text-sm">
                  {content.closing.description}
                </p>
              </div>
              <div className="flex flex-wrap gap-3">
                <Button
                  asChild
                  className="h-9 rounded-md bg-primary px-3.5 text-[13px] text-primary-foreground"
                >
                  <Link href={content.closing.primaryAction.href}>
                    {content.closing.primaryAction.label}
                  </Link>
                </Button>
                <Button
                  asChild
                  variant="outline"
                  className="h-9 rounded-md border-border bg-background px-3.5 text-[13px]"
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

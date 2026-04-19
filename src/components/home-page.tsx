import BlurFade from "@/components/magicui/blur-fade";
import { Button } from "@/components/ui/button";
import { ContactLinkCard } from "@/components/contact-link-card";
import { HomeContent } from "@/data/site-content";
import {
  ArrowUpRight,
  ChevronRight,
  CircleDot,
  Mail,
  MapPinned,
  Sparkles,
} from "lucide-react";
import Image from "next/image";
import Link from "next/link";

const BLUR_FADE_DELAY = 0.06;

type HomePost = {
  slug: string;
  title: string;
  publishedAt: string;
  summary: string;
};

function SectionEyebrow({ children }: { children: React.ReactNode }) {
  return (
    <p className="text-[10px] uppercase tracking-[0.22em] text-muted-foreground">
      {children}
    </p>
  );
}

function InfoPill({
  label,
  value,
}: {
  label: string;
  value: string;
}) {
  return (
    <div className="rounded-full border border-border/80 bg-white/70 px-3 py-1.5 text-[11px] text-foreground/75 backdrop-blur">
      <span className="mr-2 text-muted-foreground">{label}</span>
      <span>{value}</span>
    </div>
  );
}

export function HomePage({
  content,
  recentPosts,
}: {
  content: HomeContent;
  recentPosts: HomePost[];
}) {
  return (
    <main className="space-y-16 pb-16 pt-6 sm:space-y-20 sm:pt-10">
      <section className="grid gap-8 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.8fr)] lg:gap-10">
        <div className="space-y-8">
          <div className="space-y-5">
            <BlurFade delay={BLUR_FADE_DELAY}>
              <SectionEyebrow>{content.hero.eyebrow}</SectionEyebrow>
            </BlurFade>
            <BlurFade delay={BLUR_FADE_DELAY * 2}>
              <h1 className="max-w-4xl text-4xl leading-[0.98] font-semibold tracking-[-0.04em] text-foreground sm:text-5xl xl:text-[4.5rem]">
                {content.hero.title}
              </h1>
            </BlurFade>
            <BlurFade delay={BLUR_FADE_DELAY * 3}>
              <p className="max-w-2xl text-[15px] leading-8 text-foreground/72 sm:text-[16px]">
                {content.hero.description}
              </p>
            </BlurFade>
          </div>

          <BlurFade delay={BLUR_FADE_DELAY * 4}>
            <div className="flex flex-wrap gap-3">
              <Button
                asChild
                className="h-11 rounded-full bg-primary px-5 text-[13px] text-primary-foreground shadow-[0_12px_30px_rgba(64,111,93,0.18)] hover:bg-primary/90"
              >
                <Link href={content.hero.primaryAction.href}>
                  {content.hero.primaryAction.label}
                </Link>
              </Button>
              <Button
                asChild
                variant="outline"
                className="h-11 rounded-full border-border/80 bg-white/80 px-5 text-[13px] backdrop-blur"
              >
                <Link href={content.hero.secondaryAction.href}>
                  {content.hero.secondaryAction.label}
                  <ArrowUpRight className="ml-2 size-4" />
                </Link>
              </Button>
            </div>
          </BlurFade>

          <BlurFade delay={BLUR_FADE_DELAY * 5}>
            <div className="flex flex-wrap gap-2.5">
              {content.hero.highlights.map((item) => (
                <span
                  key={item}
                  className="rounded-full border border-border/80 bg-white/72 px-3 py-1.5 text-[11px] text-foreground/70 backdrop-blur"
                >
                  {item}
                </span>
              ))}
            </div>
          </BlurFade>
        </div>

        <div className="grid gap-4">
          <BlurFade delay={BLUR_FADE_DELAY * 6}>
            <aside className="overflow-hidden rounded-[1.35rem] border border-border/70 bg-white/78 p-5 shadow-[0_20px_60px_rgba(26,34,31,0.06)] backdrop-blur-xl">
              <div className="flex items-center gap-3 border-b border-border/70 pb-4">
                <div className="relative size-11 overflow-hidden rounded-2xl border border-border/80 bg-background">
                  <Image
                    src="/MindHikers.png"
                    alt="心行者 Mindhikers"
                    fill
                    className="object-cover"
                    sizes="44px"
                    priority
                  />
                </div>
                <div className="min-w-0">
                  <p className="text-[10px] uppercase tracking-[0.18em] text-muted-foreground">
                    Mindhikers
                  </p>
                  <p className="text-sm font-medium text-foreground">
                    {content.navigation.brand}
                  </p>
                </div>
              </div>

              <div className="mt-5 space-y-5">
                <div className="grid gap-2.5">
                  <InfoPill
                    label={content.hero.statusLabel}
                    value={content.hero.statusValue}
                  />
                  <InfoPill
                    label={content.hero.availabilityLabel}
                    value={content.hero.availabilityValue}
                  />
                </div>

                <div className="rounded-[1.35rem] bg-[linear-gradient(180deg,rgba(241,245,243,0.92),rgba(255,255,255,0.82))] p-4">
                  <p className="text-[10px] uppercase tracking-[0.18em] text-muted-foreground">
                    {content.hero.panelTitle}
                  </p>
                  <div className="mt-3 grid gap-2">
                    {content.navigation.links.map((item) => (
                      <Link
                        key={item.href}
                        href={item.href}
                        className="group flex items-center justify-between rounded-[0.8rem] border border-transparent bg-white/80 px-3 py-3 text-sm text-foreground/80 transition-all hover:border-border/80 hover:bg-white"
                      >
                        <span>{item.label}</span>
                        <ChevronRight className="size-4 text-muted-foreground transition-transform group-hover:translate-x-0.5" />
                      </Link>
                    ))}
                  </div>
                </div>
              </div>
            </aside>
          </BlurFade>
        </div>
      </section>

      <section
        id="about"
        className="scroll-mt-[9.5rem] grid gap-5 lg:grid-cols-[minmax(0,1.05fr)_minmax(280px,0.95fr)] sm:scroll-mt-[10rem] lg:scroll-mt-[10.5rem]"
      >
        <BlurFade delay={BLUR_FADE_DELAY * 7}>
          <div className="rounded-[1.35rem] border border-border/70 bg-white/70 p-6 shadow-[0_18px_55px_rgba(26,34,31,0.05)] backdrop-blur">
            <SectionEyebrow>{content.about.title}</SectionEyebrow>
            <p className="mt-4 max-w-2xl text-2xl leading-tight font-semibold tracking-[-0.03em] text-foreground sm:text-[2rem]">
              {content.about.intro}
            </p>
            <div className="mt-6 grid gap-4 md:grid-cols-2">
              {content.about.paragraphs.map((paragraph) => (
                <p
                  key={paragraph}
                  className="text-[14px] leading-7 text-foreground/72"
                >
                  {paragraph}
                </p>
              ))}
            </div>
          </div>
        </BlurFade>

        <div className="grid gap-4">
          {content.about.notes.map((note, index) => (
            <BlurFade key={note} delay={BLUR_FADE_DELAY * (8 + index)}>
              <div className="flex min-h-28 items-start gap-4 rounded-[1.35rem] border border-border/70 bg-white/65 p-5 backdrop-blur">
                <span className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                  <CircleDot className="size-4" />
                </span>
                <p className="text-[14px] leading-7 text-foreground/78">{note}</p>
              </div>
            </BlurFade>
          ))}
        </div>
      </section>

      <section
        id="product"
        className="scroll-mt-[5.1rem] space-y-5 sm:scroll-mt-[5.2rem] lg:scroll-mt-[5.3rem]"
      >
        <BlurFade delay={BLUR_FADE_DELAY * 11}>
          <div className="max-w-5xl space-y-3">
            <SectionEyebrow>{content.product.title}</SectionEyebrow>
            <h2 className="max-w-6xl text-3xl font-semibold tracking-[-0.04em] text-foreground sm:text-[2.35rem] lg:text-[4.1rem] lg:leading-[0.98]">
              {content.product.headline}
            </h2>
            <p className="text-[15px] leading-7 text-foreground/70">
              {content.product.description}
            </p>
          </div>
        </BlurFade>

        <div className="grid gap-4 lg:grid-cols-[minmax(0,1.25fr)_minmax(280px,0.75fr)]">
          <BlurFade delay={BLUR_FADE_DELAY * 12}>
            <div className="group rounded-[1.35rem] border border-border/70 bg-white/80 p-6 shadow-[0_18px_55px_rgba(26,34,31,0.06)] transition-transform hover:-translate-y-1">
              <div className="flex items-start justify-between gap-4">
                <div>
                  <SectionEyebrow>{content.product.featured.eyebrow}</SectionEyebrow>
                  <h3 className="mt-3 text-[1.65rem] font-semibold tracking-[-0.03em] text-foreground">
                    {content.product.featured.title}
                  </h3>
                </div>
                <span className="rounded-full border border-border/80 bg-background/80 px-3 py-1 text-[11px] text-foreground/72">
                  {content.product.featured.meta}
                </span>
              </div>
              <p className="mt-5 max-w-2xl text-[15px] leading-8 text-foreground/72">
                {content.product.featured.description}
              </p>
              <div className="mt-8">
                <Button
                  asChild
                  variant="ghost"
                  className="h-auto rounded-[2.2rem] px-0 text-sm text-foreground hover:bg-transparent"
                >
                  <Link href={content.product.featured.href ?? "/"}>
                    {content.product.featured.ctaLabel}
                    <ArrowUpRight className="ml-2 size-4" />
                  </Link>
                </Button>
              </div>
            </div>
          </BlurFade>

          <div className="grid gap-4">
            {content.product.items.map((item, index) => (
              <BlurFade key={item.title} delay={BLUR_FADE_DELAY * (13 + index)}>
                <div className="rounded-[1.35rem] border border-border/70 bg-white/68 p-5 backdrop-blur">
                  <SectionEyebrow>{item.eyebrow}</SectionEyebrow>
                  <h3 className="mt-3 text-lg font-semibold tracking-[-0.02em] text-foreground">
                    {item.title}
                  </h3>
                  <p className="mt-2 text-[14px] leading-7 text-foreground/70">
                    {item.description}
                  </p>
                </div>
              </BlurFade>
            ))}
          </div>
        </div>
      </section>

      <section
        id="blog"
        className="scroll-mt-[5.1rem] space-y-5 sm:scroll-mt-[5.2rem] lg:scroll-mt-[5.3rem]"
      >
        <BlurFade delay={BLUR_FADE_DELAY * 16}>
          <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div className="max-w-3xl space-y-3">
              <SectionEyebrow>{content.blog.title}</SectionEyebrow>
              <h2 className="text-3xl font-semibold tracking-[-0.04em] text-foreground sm:text-[2.35rem]">
                {content.blog.headline}
              </h2>
              <p className="text-[15px] leading-7 text-foreground/70">
                {content.blog.description}
              </p>
            </div>
            <Button
              asChild
              variant="outline"
              className="h-10 rounded-[0.9rem] border-border/80 bg-white/70 px-4 text-[13px] backdrop-blur"
            >
              <Link href={content.blog.cta.href}>
                {content.blog.cta.label}
                <ArrowUpRight className="ml-2 size-4" />
              </Link>
            </Button>
          </div>
        </BlurFade>

        {recentPosts.length > 0 ? (
          <div className="grid gap-4 lg:grid-cols-3">
            {recentPosts.map((post, index) => (
              <BlurFade key={post.slug} delay={BLUR_FADE_DELAY * (17 + index)}>
                <Link
                  href={`/blog/${post.slug}`}
                  className="group flex h-full flex-col justify-between rounded-[1.35rem] border border-border/70 bg-white/70 p-5 backdrop-blur transition-transform hover:-translate-y-1"
                >
                  <div className="space-y-3">
                    <div className="flex items-center justify-between text-[11px] uppercase tracking-[0.18em] text-muted-foreground">
                      <span>{post.publishedAt}</span>
                      <Sparkles className="size-3.5" />
                    </div>
                    <h3 className="text-xl font-semibold leading-tight tracking-[-0.03em] text-foreground">
                      {post.title}
                    </h3>
                    <p className="text-[14px] leading-7 text-foreground/70">
                      {post.summary}
                    </p>
                  </div>
                  <div className="mt-8 flex items-center text-sm text-foreground/82">
                    {content.blog.readArticleLabel}
                    <ChevronRight className="ml-1 size-4 transition-transform group-hover:translate-x-0.5" />
                  </div>
                </Link>
              </BlurFade>
            ))}
          </div>
        ) : (
          <BlurFade delay={BLUR_FADE_DELAY * 17}>
            <div className="rounded-[1.35rem] border border-border/70 bg-white/70 p-6 text-[14px] text-foreground/68 backdrop-blur">
              {content.blog.emptyLabel}
            </div>
          </BlurFade>
        )}
      </section>

      <section
        id="contact"
        className="scroll-mt-[5.1rem] grid gap-4 lg:grid-cols-[minmax(0,1.2fr)_minmax(300px,0.8fr)] sm:scroll-mt-[5.2rem] lg:scroll-mt-[5.3rem]"
      >
        <BlurFade delay={BLUR_FADE_DELAY * 20}>
          <div className="rounded-[1.35rem] border border-border/70 bg-[linear-gradient(180deg,rgba(248,250,248,0.95),rgba(255,255,255,0.88))] p-6 shadow-[0_18px_55px_rgba(26,34,31,0.06)]">
            <SectionEyebrow>{content.contact.title}</SectionEyebrow>
            <h2 className="mt-4 max-w-3xl text-3xl font-semibold tracking-[-0.04em] text-foreground sm:text-[2.4rem]">
              {content.contact.headline}
            </h2>
            <p className="mt-4 max-w-2xl text-[15px] leading-8 text-foreground/70">
              {content.contact.description}
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Button
                asChild
                className="h-11 rounded-full bg-primary px-5 text-[13px] text-primary-foreground hover:bg-primary/90"
              >
                <Link href={`mailto:${content.contact.email}`}>
                  <Mail className="mr-2 size-4" />
                  {content.contact.email}
                </Link>
              </Button>
            </div>
          </div>
        </BlurFade>

        <div className="grid gap-4">
          <BlurFade delay={BLUR_FADE_DELAY * 21}>
            <div className="rounded-[1.35rem] border border-border/70 bg-white/72 p-5 backdrop-blur">
              <div className="flex items-center gap-2 text-[11px] uppercase tracking-[0.18em] text-muted-foreground">
                <MapPinned className="size-3.5" />
                {content.contact.locationLabel}
              </div>
              <p className="mt-3 text-base font-medium text-foreground">
                {content.contact.location}
              </p>
              <p className="mt-5 text-[11px] uppercase tracking-[0.18em] text-muted-foreground">
                {content.contact.availabilityLabel}
              </p>
              <p className="mt-3 text-[14px] leading-7 text-foreground/72">
                {content.contact.availability}
              </p>
            </div>
          </BlurFade>

          {content.contact.links.map((item, index) => (
            <BlurFade key={item.href} delay={BLUR_FADE_DELAY * (22 + index)}>
              <ContactLinkCard
                href={item.href}
                label={item.label}
                note={item.note}
                qrImage={item.qrImage}
              />
            </BlurFade>
          ))}
        </div>
      </section>
    </main>
  );
}

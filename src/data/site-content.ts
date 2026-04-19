export type Locale = "zh" | "en";

type ActionLink = {
  href: string;
  label: string;
};

type NavigationLink = {
  href: string;
  label: string;
};

type EntryCard = {
  eyebrow: string;
  title: string;
  description: string;
  href?: string;
  ctaLabel?: string;
  meta?: string;
};

type ContactLink = {
  href: string;
  label: string;
  note: string;
  qrImage?: string;
};

type ProductDetail = {
  eyebrow: string;
  title: string;
  summary: string;
  bullets: string[];
  stageLabel: string;
  stageValue: string;
  returnHome: ActionLink;
  switchLanguage: ActionLink;
};

export type HomeContent = {
  locale: Locale;
  metadata: {
    title: string;
    description: string;
  };
  navigation: {
    brand: string;
    links: NavigationLink[];
    switchLanguage: ActionLink;
  };
  hero: {
    eyebrow: string;
    title: string;
    description: string;
    primaryAction: ActionLink;
    secondaryAction: ActionLink;
    highlights: string[];
    statusLabel: string;
    statusValue: string;
    availabilityLabel: string;
    availabilityValue: string;
    panelTitle: string;
  };
  about: {
    title: string;
    intro: string;
    paragraphs: string[];
    notes: string[];
  };
  product: {
    title: string;
    description: string;
    headline: string;
    featured: EntryCard;
    items: EntryCard[];
  };
  blog: {
    title: string;
    description: string;
    headline: string;
    cta: ActionLink;
    emptyLabel: string;
    readArticleLabel: string;
  };
  contact: {
    title: string;
    description: string;
    headline: string;
    emailLabel: string;
    email: string;
    locationLabel: string;
    location: string;
    availabilityLabel: string;
    availability: string;
    links: ContactLink[];
  };
  productDetail: ProductDetail;
};

const BRAND = "心行者 Mindhikers";

const zhBase = "";
const enBase = "/en";

function withBase(base: string, hashOrPath = "") {
  if (!hashOrPath) {
    return base || "/";
  }

  if (hashOrPath.startsWith("#")) {
    return `${base || "/"}${hashOrPath}`;
  }

  return `${base}${hashOrPath}`;
}

export const SITE_CONTENT: Record<Locale, HomeContent> = {
  zh: {
    locale: "zh",
    metadata: {
      title: BRAND,
      description:
        "心行者 Mindhikers 是一个双语品牌主页，用来承载内容、产品实验、博客输出与长期创作协作。",
    },
    navigation: {
      brand: BRAND,
      links: [
        { href: withBase(zhBase, "#about"), label: "About" },
        { href: withBase(zhBase, "#product"), label: "Product" },
        { href: withBase(zhBase, "#blog"), label: "Blog" },
        { href: withBase(zhBase, "#contact"), label: "Contact" },
      ],
      switchLanguage: {
        href: "/en",
        label: "EN",
      },
    },
    hero: {
      eyebrow: "Editorial homepage",
      title: "把研究、产品与表达，排成一个有呼吸感的品牌入口。",
      description:
        "心行者 Mindhikers 正在把长期创作、内容实验与产品化尝试收拢成一个更完整的首页。它不想像简历，也不想像模板，而是像一个持续更新的工作现场。",
      primaryAction: {
        href: "#product",
        label: "查看当前产品入口",
      },
      secondaryAction: {
        href: "/blog",
        label: "进入博客",
      },
      highlights: ["双语品牌入口", "产品化实验", "长期写作与研究"],
      statusLabel: "Current focus",
      statusValue: "Homepage refresh in progress",
      availabilityLabel: "Working rhythm",
      availabilityValue: "Research, build, write, publish",
      panelTitle: "Homepage blocks",
    },
    about: {
      title: "About",
      intro:
        "心行者 Mindhikers 不是一张展示履历的页面，而是一个兼顾思考、制作与对外发布的品牌主页。",
      paragraphs: [
        "我们希望首页既能承接产品入口，也能容纳博客、研究线索和下一步动作，而不是把所有信息压成一页静态介绍。",
        "这次改版会更靠近一种“持续编排中的工作室主页”气质：内容像栏目，入口像节目单，动效和节奏帮助信息呼吸，而不是成为噱头。",
      ],
      notes: [
        "去掉模板味的自我介绍",
        "保留轻量但明确的动效层次",
        "让产品、博客、联系入口一眼可见",
      ],
    },
    product: {
      title: "Product",
      description:
        "先把一个足够真实的产品入口放在首页中央，再围绕它挂出内容、工作流和后续生长点。",
      headline: "首页中段应该像一个正在播出的栏目，而不是说明书。",
      featured: {
        eyebrow: "Featured release",
        title: "黄金坩埚",
        description:
          "围绕研究、写作、表达与创作者工作流展开的首个产品入口。它承担的不只是一个页面，而是 Mindhikers 第一批品牌化实验的落点。",
        href: "/golden-crucible",
        ctaLabel: "打开产品页",
        meta: "Live now",
      },
      items: [
        {
          eyebrow: "Brand system",
          title: "双语首页结构",
          description:
            "首页会同时承担中文与英文入口，让不同受众都能快速找到切入点。",
        },
        {
          eyebrow: "Content flow",
          title: "博客与研究栏目",
          description:
            "后续会把 blog 与研究内容接入首页，让站点像持续更新的出版物，而不只是产品单页。",
        },
        {
          eyebrow: "Contact surface",
          title: "合作与联系窗口",
          description:
            "把联系入口做得更自然，既能留住潜在合作，也不破坏整体节奏。",
        },
      ],
    },
    blog: {
      title: "Blog",
      description:
        "这里会逐步积累方法、写作和产品思考。首页先展示最近几篇，完整归档放在博客页里。",
      headline: "让首页直接露出最近的写作，而不是把内容藏在站点深处。",
      cta: {
        href: "/blog",
        label: "查看全部文章",
      },
      emptyLabel: "博客内容还在整理中，很快会补上第一批文章。",
      readArticleLabel: "Read article",
    },
    contact: {
      title: "Contact",
      description:
        "如果你想讨论品牌、内容、产品实验，或者只是想交换一个更清晰的切题方式，这里是最直接的入口。",
      headline:
        "把联系入口做得像一段自然的续篇，而不是页面底部的表单义务。",
      emailLabel: "Email",
      email: "contactmindhiker@gmail.com",
      locationLabel: "Base",
      location: "Shanghai / Remote",
      availabilityLabel: "Open to",
      availability: "Editorial collaboration, product experiments, thoughtful internet projects",
      links: [
        {
          href: "mailto:contactmindhiker@gmail.com",
          label: "发邮件",
          note: "最快的合作入口",
        },
        {
          href: "/en",
          label: "English home",
          note: "查看英文版入口",
        },
        {
          href: "/blog",
          label: "Recent writing",
          note: "先从文章理解我们的工作方式",
        },
      ],
    },
    productDetail: {
      eyebrow: "Featured Product",
      title: "黄金坩埚",
      summary:
        "黄金坩埚是 心行者 Mindhikers 当前最先对外承接的产品入口，用来容纳研究、表达与创作者工作流的第一批产品化尝试。",
      bullets: [
        "把研究线索整理成可延续的主题资产",
        "把表达过程沉淀成可复用的脚本与结构",
        "把创作者工作流逐步变成工具化入口",
      ],
      stageLabel: "当前阶段",
      stageValue: "品牌入口页已建立，后续会继续补充产品能力与具体素材。",
      returnHome: {
        href: "/",
        label: "返回首页",
      },
      switchLanguage: {
        href: "/en/golden-crucible",
        label: "View in English",
      },
    },
  },
  en: {
    locale: "en",
    metadata: {
      title: BRAND,
      description:
        "心行者 Mindhikers is a bilingual brand home for product experiments, writing, and a quieter long-form creative practice.",
    },
    navigation: {
      brand: BRAND,
      links: [
        { href: withBase(enBase, "#about"), label: "About" },
        { href: withBase(enBase, "#product"), label: "Product" },
        { href: withBase(enBase, "#blog"), label: "Blog" },
        { href: withBase(enBase, "#contact"), label: "Contact" },
      ],
      switchLanguage: {
        href: "/",
        label: "中文",
      },
    },
    hero: {
      eyebrow: "Editorial homepage",
      title: "A brand home for research, products, and writing that still feels alive.",
      description:
        "心行者 Mindhikers is becoming a quieter but more expressive front page for long-form creation, product experiments, and the kind of internet work that benefits from rhythm, not clutter.",
      primaryAction: {
        href: "#product",
        label: "See the current product entry",
      },
      secondaryAction: {
        href: "/blog",
        label: "Open the blog",
      },
      highlights: ["Bilingual entry point", "Product experiments", "Research and publishing"],
      statusLabel: "Current focus",
      statusValue: "Homepage refresh in progress",
      availabilityLabel: "Working rhythm",
      availabilityValue: "Research, build, write, publish",
      panelTitle: "Homepage blocks",
    },
    about: {
      title: "About",
      intro:
        "心行者 Mindhikers is not meant to read like a resume page. It is a brand homepage for making, thinking, and publishing in public with more intention.",
      paragraphs: [
        "The goal is to make room for product entries, recent writing, research threads, and future collaborations without collapsing everything into a single static summary.",
        "This refresh leans toward the feeling of an actively edited studio homepage: sections behave like columns, entry points feel like programming blocks, and motion supports reading instead of distracting from it.",
      ],
      notes: [
        "Remove the template-like self-introduction",
        "Keep motion light but visible",
        "Make product, blog, and contact pathways obvious",
      ],
    },
    product: {
      title: "Product",
      description:
        "Start with one concrete release at the center of the page, then let the broader system grow around it.",
      headline:
        "The middle of the homepage should feel like a live program block, not a spec sheet.",
      featured: {
        eyebrow: "Featured release",
        title: "Golden Crucible",
        description:
          "The first product entry under Mindhikers, built around research, writing, expression, and creator workflows. It is both a page and a signal of where the brand is heading.",
        href: "/en/golden-crucible",
        ctaLabel: "Open product page",
        meta: "Live now",
      },
      items: [
        {
          eyebrow: "Brand system",
          title: "Bilingual homepage structure",
          description:
            "A calm bilingual structure gives Chinese and English readers their own clear point of entry.",
        },
        {
          eyebrow: "Content flow",
          title: "Blog and research columns",
          description:
            "The homepage will gradually connect to writing and research so the site feels like a publication, not a frozen launch page.",
        },
        {
          eyebrow: "Contact surface",
          title: "Collaboration window",
          description:
            "Contact should feel natural and visible without breaking the visual rhythm of the homepage.",
        },
      ],
    },
    blog: {
      title: "Blog",
      description:
        "Writing will sit closer to the front of the brand. The homepage surfaces a small selection, while the archive lives in the full blog.",
      headline:
        "Bring recent writing onto the homepage instead of hiding the thinking deeper in the site.",
      cta: {
        href: "/blog",
        label: "Browse all posts",
      },
      emptyLabel: "The first wave of writing is still being curated.",
      readArticleLabel: "Read article",
    },
    contact: {
      title: "Contact",
      description:
        "If you want to talk about brand, editorial systems, product experiments, or a more thoughtful corner of the internet, this is the cleanest place to start.",
      headline:
        "Let contact feel like a continuation of the page, not a mandatory form block at the bottom.",
      emailLabel: "Email",
      email: "contactmindhiker@gmail.com",
      locationLabel: "Base",
      location: "Shanghai / Remote",
      availabilityLabel: "Open to",
      availability: "Editorial collaboration, product experiments, thoughtful internet projects",
      links: [
        {
          href: "mailto:contactmindhiker@gmail.com",
          label: "Send email",
          note: "The fastest way to start a conversation",
        },
        {
          href: "/",
          label: "Chinese home",
          note: "Switch back to the Chinese entry",
        },
        {
          href: "/blog",
          label: "Recent writing",
          note: "Start with the blog to understand the practice",
        },
      ],
    },
    productDetail: {
      eyebrow: "Featured Product",
      title: "Golden Crucible",
      summary:
        "Golden Crucible is the first public product entry under 心行者 Mindhikers, designed to hold early experiments around research, expression, and creator workflows.",
      bullets: [
        "Organize research threads into durable topic assets",
        "Turn expression work into reusable scripts and structures",
        "Gradually shape creator workflows into productized tools",
      ],
      stageLabel: "Current stage",
      stageValue:
        "The branded entry page is live first. More product detail and richer assets will follow.",
      returnHome: {
        href: "/en",
        label: "Back to homepage",
      },
      switchLanguage: {
        href: "/golden-crucible",
        label: "查看中文版",
      },
    },
  },
};

export function getHomeContent(locale: Locale) {
  return SITE_CONTENT[locale];
}

export function getLocaleFromPathname(pathname: string): Locale {
  return pathname.startsWith("/en") ? "en" : "zh";
}

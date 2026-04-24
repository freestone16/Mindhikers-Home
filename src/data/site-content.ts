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
    panelTitle: string;
    quickLinks: { label: string; href: string; tag: string }[];
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
        "心行者 MindHikers：研究复杂问题，制作清晰表达，实验产品化路径。",
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
        label: "English",
      },
    },
    hero: {
      eyebrow: "MindHikers",
      title: "心行者 MindHikers",
      description: "研究复杂问题 · 制作清晰表达 · 实验产品化路径",
      primaryAction: {
        href: "#product",
        label: "查看产品",
      },
      secondaryAction: {
        href: "/blog",
        label: "阅读博客",
      },
      highlights: ["研究复杂问题", "制作清晰表达", "实验产品化路径"],
      panelTitle: "Quick links",
      quickLinks: [
        { label: "黄金坩埚", href: "/golden-crucible", tag: "产品" },
        { label: "碳硅进化论", href: "/blog", tag: "文章" },
      ],
    },
    about: {
      title: "About",
      intro: "MindHikers 是一间一人工作室，主营两件事：",
      paragraphs: [
        "做内容：在 YouTube / Bilibili 上研究并讲述复杂议题，面向中文世界的知性探索者。",
        "做产品：把创作工作流和研究方法沉淀成工具，先自用，再分享。",
      ],
      notes: [
        "AI 替你写、替你画、替你思考的时代，人类最稀缺的不是效率，是知道自己是谁。",
        "痛感是真实的标准，摩擦是生长的时刻。",
        "先锚定，后攀爬。",
      ],
    },
    product: {
      title: "Product",
      description: "一个围绕研究、写作、表达与创作者工作流展开的产品实验。",
      headline: "黄金坩埚",
      featured: {
        eyebrow: "Featured release",
        title: "黄金坩埚",
        description: "一个围绕研究、写作、表达与创作者工作流展开的产品实验。2026年5月待开放：AI 辅助深度写作工作流、知识管理模板、创作者效率工具集。",
        href: "/golden-crucible",
        ctaLabel: "打开产品页",
        meta: "已上线",
      },
      items: [
        {
          eyebrow: "Workflow",
          title: "AI 辅助深度写作工作流",
          description: "把研究、提纲、草稿、修改和发布拆成可以复用的创作流程。",
        },
        {
          eyebrow: "Templates",
          title: "知识管理模板",
          description: "把长期研究线索整理成可检索、可复盘、可继续生长的主题资产。",
        },
        {
          eyebrow: "Toolkit",
          title: "创作者效率工具集",
          description: "围绕选题、表达、资料管理和发布节奏沉淀轻量工具。",
        },
      ],
    },
    blog: {
      title: "Content flow",
      description: "三篇「碳硅进化论」文章已经上线，讨论 AI 时代的教育、肉身经验与伦理成长。",
      headline: "碳硅进化论",
      cta: {
        href: "/blog",
        label: "查看全部文章",
      },
      emptyLabel: "碳硅进化论系列文章正在上线中。",
      readArticleLabel: "阅读全文",
    },
    contact: {
      title: "Contact",
      description: "我们欢迎内容共创、产品化合作，以及 thoughtful internet projects。",
      headline: "有合作想法，或者单纯想聊聊？",
      emailLabel: "Email",
      email: "hello@mindhikers.com",
      locationLabel: "Base",
      location: "Shanghai / Remote",
      availabilityLabel: "Open to",
      availability: "内容共创（访谈、联名研究、播客） · 产品化合作（工具、模板、课程） · thoughtful internet projects",
      links: [
        {
          href: "mailto:hello@mindhikers.com",
          label: "发邮件",
          note: "hello@mindhikers.com",
        },
        {
          href: "/en",
          label: "English home",
          note: "查看英文版入口",
        },
        {
          href: "/blog",
          label: "碳硅进化论",
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
        "MindHikers researches complex questions, creates clear expression, and experiments with productized creative workflows.",
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
      eyebrow: "MindHikers",
      title: "MindHikers",
      description: "Research. Create. Productize.",
      primaryAction: {
        href: "#product",
        label: "Explore Products",
      },
      secondaryAction: {
        href: "/blog",
        label: "Read Blog",
      },
      highlights: ["Research complex questions", "Create clear expression", "Productize workflows"],
      panelTitle: "Quick links",
      quickLinks: [
        { label: "Golden Crucible", href: "/en/golden-crucible", tag: "Product" },
        { label: "Carbon-Silicon Evolution", href: "/blog", tag: "Writing" },
      ],
    },
    about: {
      title: "About",
      intro: "MindHikers is a one-person studio doing two things:",
      paragraphs: [
        "Content: deep-dive research and storytelling on YouTube and Bilibili for intellectually curious audiences navigating the AI era.",
        "Products: turning creative workflows and research methods into tools. Built for ourselves first, then shared.",
      ],
      notes: [
        "In an age where AI writes, draws, and thinks for you, the scarcest human quality is knowing who you are.",
        "Pain is the standard of truth; friction is where growth happens.",
        "Anchor first, then climb.",
      ],
    },
    product: {
      title: "Product",
      description: "A product experiment around research, writing, expression, and creator workflows.",
      headline: "The Crucible",
      featured: {
        eyebrow: "Featured release",
        title: "The Crucible",
        description: "A product experiment around research, writing, expression, and creator workflows. Currently open: AI-assisted deep writing workflow, knowledge management templates, and a creator productivity toolkit.",
        href: "/en/golden-crucible",
        ctaLabel: "Open product page",
        meta: "Live now",
      },
      items: [
        {
          eyebrow: "Workflow",
          title: "AI-assisted deep writing workflow",
          description: "A reusable process for research, outlining, drafting, revision, and publishing.",
        },
        {
          eyebrow: "Templates",
          title: "Knowledge management templates",
          description: "Durable systems for collecting, reviewing, and extending long-term research threads.",
        },
        {
          eyebrow: "Toolkit",
          title: "Creator productivity toolkit",
          description: "Lightweight tools for topics, expression, source management, and publishing rhythm.",
        },
      ],
    },
    blog: {
      title: "Content flow",
      description: "Three Carbon-Silicon Evolution essays are live, exploring education, embodied experience, and moral growth in the AI age.",
      headline: "Carbon-Silicon Evolution",
      cta: {
        href: "/blog",
        label: "Browse all posts",
      },
      emptyLabel: "The first wave of writing is still being curated.",
      readArticleLabel: "Read article",
    },
    contact: {
      title: "Contact",
      description: "We welcome content partnerships, product collaborations, and thoughtful internet projects.",
      headline: "Have a collaboration idea, or just want to chat?",
      emailLabel: "Email",
      email: "hello@mindhikers.com",
      locationLabel: "Base",
      location: "Shanghai / Remote",
      availabilityLabel: "Open to",
      availability: "Content partnerships · Product collaborations · Thoughtful internet projects",
      links: [
        {
          href: "mailto:hello@mindhikers.com",
          label: "Send email",
          note: "hello@mindhikers.com",
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

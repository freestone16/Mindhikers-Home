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
    focusCard: {
      title: string;
      items: string[];
    };
  };
  about: {
    title: string;
    paragraphs: string[];
  };
  products: {
    title: string;
    description: string;
    items: EntryCard[];
  };
  projects: {
    title: string;
    description: string;
    items: EntryCard[];
  };
  tools: {
    title: string;
    description: string;
    items: EntryCard[];
  };
  closing: {
    title: string;
    description: string;
    primaryAction: ActionLink;
    secondaryAction: ActionLink;
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
        "心行者 Mindhikers 是一个双语品牌主页，用来承载内容、方法、产品实验与创作者工具入口。",
    },
    navigation: {
      brand: BRAND,
      links: [
        { href: withBase(zhBase, "#about"), label: "关于" },
        { href: withBase(zhBase, "#products"), label: "产品" },
        { href: withBase(zhBase, "#tools"), label: "工具" },
      ],
      switchLanguage: {
        href: "/en",
        label: "EN",
      },
    },
    hero: {
      eyebrow: "Bilingual brand home",
      title: "把复杂问题，做成清晰可用的作品。",
      description:
        "心行者 Mindhikers 用内容、方法和产品实验，把抽象议题拆成可理解、可执行、可持续迭代的路径。",
      primaryAction: {
        href: "/golden-crucible",
        label: "查看黄金坩埚",
      },
      secondaryAction: {
        href: "/en",
        label: "切换到英文版",
      },
      highlights: ["品牌主页", "产品入口", "双语表达"],
      focusCard: {
        title: "当前建设重点",
        items: [
          "去掉模板的简历叙事",
          "先挂出黄金坩埚入口",
          "建立 / 与 /en 的轻量双语结构",
        ],
      },
    },
    about: {
      title: "关于 心行者 Mindhikers",
      paragraphs: [
        "心行者 Mindhikers 不是一张个人简历页，而是一个面向长期创作与产品实验的品牌门户。它承接我们正在推进的内容、方法论和工具化尝试。",
        "首版首页会保持节制而清晰的结构：先让用户理解品牌在做什么，再给出具体产品入口，最后逐步沉淀项目与工具能力。",
      ],
    },
    products: {
      title: "产品",
      description:
        "先从一个真实入口开始，用最小但完整的结构承接品牌的第一批产品化表达。",
      items: [
        {
          eyebrow: "Featured",
          title: "黄金坩埚",
          description:
            "一个围绕研究、写作、表达与创作者工作流而展开的产品入口，用来承接 心行者 Mindhikers 的第一批品牌化实验。",
          href: "/golden-crucible",
          ctaLabel: "进入产品页",
          meta: "首批挂载入口",
        },
      ],
    },
    projects: {
      title: "项目",
      description:
        "除了单点产品，我们也在构建更完整的品牌基础设施，让表达、协作和发布能够逐渐形成体系。",
      items: [
        {
          eyebrow: "Brand System",
          title: "双语品牌首页",
          description:
            "用轻量路由而不是重型国际化方案，先把中文与英文两个入口跑顺。",
        },
        {
          eyebrow: "Content Flow",
          title: "内容生产工作流",
          description:
            "把选题、研究、脚本、审校和交付拆成可协作的稳定模块，而不是临时堆砌。",
        },
        {
          eyebrow: "Distribution",
          title: "品牌化入口矩阵",
          description:
            "围绕官网、产品页和后续内容分发节点，逐步搭出一致的品牌落点。",
        },
      ],
    },
    tools: {
      title: "工具",
      description:
        "工具不是目的，工具是让复杂工作反复复用、降低摩擦、放大长期价值的方式。",
      items: [
        {
          eyebrow: "Research",
          title: "结构化研究",
          description: "把零散输入整理成可继续推演的知识骨架。",
        },
        {
          eyebrow: "Script",
          title: "脚本与表达提炼",
          description: "把想法打磨成可发布、可复用、可审校的表达单元。",
        },
        {
          eyebrow: "Execution",
          title: "研发与落地协同",
          description: "让内容策划、页面承载和产品化入口在同一套节奏里推进。",
        },
      ],
    },
    closing: {
      title: "先把一个入口做完整，再把系统慢慢搭出来。",
      description:
        "这是 心行者 Mindhikers 首页的第一轮品牌化版本。它的重点不是堆信息，而是建立一个足够干净、足够稳定的起点。",
      primaryAction: {
        href: "/golden-crucible",
        label: "继续看黄金坩埚",
      },
      secondaryAction: {
        href: "/en",
        label: "查看英文版",
      },
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
        "心行者 Mindhikers is a bilingual brand home for thoughtful content, repeatable workflows, and product experiments.",
    },
    navigation: {
      brand: BRAND,
      links: [
        { href: withBase(enBase, "#about"), label: "About" },
        { href: withBase(enBase, "#products"), label: "Products" },
        { href: withBase(enBase, "#tools"), label: "Tools" },
      ],
      switchLanguage: {
        href: "/",
        label: "中文",
      },
    },
    hero: {
      eyebrow: "Bilingual brand home",
      title: "Turn complex questions into clear, usable work.",
      description:
        "心行者 Mindhikers brings together content, methods, and product experiments to make difficult ideas easier to understand, apply, and evolve.",
      primaryAction: {
        href: "/en/golden-crucible",
        label: "Explore Golden Crucible",
      },
      secondaryAction: {
        href: "/",
        label: "Switch to Chinese",
      },
      highlights: ["Brand home", "Product gateway", "Bilingual structure"],
      focusCard: {
        title: "Current focus",
        items: [
          "Remove the resume-like template narrative",
          "Publish Golden Crucible as the first product entry",
          "Establish a clean / and /en routing structure",
        ],
      },
    },
    about: {
      title: "About 心行者 Mindhikers",
      paragraphs: [
        "心行者 Mindhikers is not meant to feel like a personal resume page. It is a brand portal for long-form creation, product thinking, and practical experimentation.",
        "This first release keeps the structure intentional and calm: explain what the brand does, show a concrete product entry, and leave space for projects and tools to grow over time.",
      ],
    },
    products: {
      title: "Products",
      description:
        "We start with one real entry point so the brand has something concrete to stand on from day one.",
      items: [
        {
          eyebrow: "Featured",
          title: "Golden Crucible",
          description:
            "A product entry centered on research, writing, expression, and creator workflows. It is the first public-facing experiment under the 心行者 Mindhikers brand.",
          href: "/en/golden-crucible",
          ctaLabel: "Open product page",
          meta: "First featured entry",
        },
      ],
    },
    projects: {
      title: "Projects",
      description:
        "Beyond a single product, we are building the brand infrastructure that makes expression, collaboration, and publishing feel consistent.",
      items: [
        {
          eyebrow: "Brand System",
          title: "Bilingual homepage",
          description:
            "A light routing approach that gives Chinese and English their own clear entry points without heavy i18n overhead.",
        },
        {
          eyebrow: "Content Flow",
          title: "Content production workflow",
          description:
            "A more stable system for topic development, research, scripting, review, and delivery.",
        },
        {
          eyebrow: "Distribution",
          title: "Branded entry matrix",
          description:
            "A growing set of brand touchpoints across the homepage, product pages, and future distribution surfaces.",
        },
      ],
    },
    tools: {
      title: "Tools",
      description:
        "Tools matter because they turn difficult work into something reusable, lower-friction, and durable.",
      items: [
        {
          eyebrow: "Research",
          title: "Structured research",
          description: "Turn scattered inputs into a knowledge skeleton worth building on.",
        },
        {
          eyebrow: "Script",
          title: "Script and expression refinement",
          description: "Shape ideas into outputs that are publishable, reusable, and reviewable.",
        },
        {
          eyebrow: "Execution",
          title: "Build-and-delivery coordination",
          description: "Keep content planning, page delivery, and product entry points moving in one rhythm.",
        },
      ],
    },
    closing: {
      title: "Build one complete entry point first, then grow the system around it.",
      description:
        "This is the first branded version of the 心行者 Mindhikers homepage. The goal is not volume. The goal is a calm, durable starting point.",
      primaryAction: {
        href: "/en/golden-crucible",
        label: "Continue to Golden Crucible",
      },
      secondaryAction: {
        href: "/",
        label: "Open Chinese version",
      },
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

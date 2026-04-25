# MindHikers 主页内容填充指导手册

> **文档性质**：内容规格书（Content Spec）  
> **面向对象**：前端开发 / 内容运营  
> **版本**：v1.1  
> **原则**：格局不变，只填空。所有文案可直接复制使用。

---

## 全局原则

1. **双语并行**：每个文本区块需同时提供中文与英文版本，由语言切换器控制显隐。
2. **去元叙事**：删除所有"解释为什么这样设计"的文案（如"不是履历页面""持续编排中的工作室"等），只保留用户需要知道的信息。
3. **行动导向**：每个区块至少有一个明确的 CTA（Call to Action）。
4. **当前状态优先**：Blog 已有 3 篇「碳硅进化论」系列文章，应直接展示真实内容卡片，不再使用空窗期过渡方案。

---

## Block 1: Hero 首屏

### 位置
页面最顶部，全屏或近全屏高度，视觉焦点区。

### 当前问题
- 副文案"把研究、产品与表达，排成一个有呼吸感的品牌入口"是内部语言，用户无感。
- CTA 不够明确。

### 填充内容

| 元素 | 中文文案 | 英文文案 |
|------|----------|----------|
| **主标题** | 心行者 MindHikers | MindHikers |
| **副标题** | 研究复杂问题 · 制作清晰表达 · 实验产品化路径 | Research. Create. Productize. |
| **CTA 主按钮** | 查看产品 | Explore Products |
| **CTA 次按钮** | 阅读博客 | Read Blog |
| **CTA 第三按钮** | English | 中文 |

### 文案规格
- 副标题：三个动词短语，用间隔号「·」或英文句点分隔。总长度控制在 20 个中文字符或 50 个英文字符以内。
- 语气：简洁、有力、自信。不要形容词堆砌。

### 注意事项
- 英文版副标题不要直译中文，用更地道的表达（如 "Research. Create. Productize." 或 "Think deeply. Speak clearly. Ship tools."）
- "English / 中文" 按钮用于语言切换，当前显示的是目标语言（即当前中文页显示 "English"，英文页显示 "中文"）。

---

## Block 2: About 关于

### 位置
Hero 下方第一屏，通常是两栏布局（左文右图或纯文本）。

### 当前问题
- 大段否定式解释（"不是履历页面""兼顾思考、制作与对外发布"），显得防御且啰嗦。

### 填充内容

**中文版：**

```
About

MindHikers 是一间一人工作室，主营两件事：

1. 做内容 —— 在 YouTube / Bilibili 上研究并讲述复杂议题，面向中文世界的"知性探索者"
2. 做产品 —— 把创作工作流和研究方法沉淀成工具，先自用，再分享

我们的精神内核叫「黄金精神」——AI替你写、替你画、替你思考的时代，人类最稀缺的不是效率，是知道自己是谁。痛感是真实的标准，摩擦是生长的时刻。先锚定，后攀爬。

[了解更多 →]
```

**英文版：**

```
About

MindHikers is a one-person studio doing two things:

1. Content — Deep-dive research and storytelling on YouTube and Bilibili for intellectually curious audiences navigating the AI era
2. Products — Turning creative workflows and research methods into tools. Built for ourselves first, then shared

We call it the Golden Spirit — in an age where AI writes, draws, and thinks for you, the scarcest human quality isn't efficiency. It's knowing who you are. Pain is the standard of truth; friction is where growth happens. Anchor first, then climb.

[Learn more →]
```

### 文案规格
- 结构：标题 + 两段式定义（做内容 / 做产品）+ 价值主张 + CTA
- 语气：直接、有态度、略带锋芒。用短句。
- 字数：中文 120-150 字，英文 80-100 词。

### 黄金精神放置建议

「黄金精神 Human Golden Spirit」的完整版文本（见 `contents/黄金精神 Human Golden Spirit.md`）建议如下放置：

| 层级 | 放置位置 | 展示形式 | 说明 |
|------|----------|----------|------|
| **精炼版** | About 区块（上方文案已包含） | 2-3 句话融入价值主张段落 | 首页首屏即见，让访客秒懂品牌灵魂 |
| **完整版** | 独立 `/about` 页面（建议新建） | 全文 + 视觉排版 | "了解更多 →" 链接指向此页，承载完整哲学叙事 |
| **呼应版** | 黄金坩埚产品页 `/golden-crucible` | 引用 1-2 句 + 链接回 About | "黄金坩埚" 名字来自 "黄金精神"，需要精神血脉关联 |

> **实施优先级**：先把精炼版融入 About（已完成），再创建 `/about` 独立页面承载完整版，最后在产品页做呼应引用。

### 中英差异说明
- 中文版用"面向中文世界的'知性探索者'"——有态度、有圈层感。
- 英文版用 "intellectually curious audiences navigating the AI era"——更 accessible，强调 AI 时代语境而非族群标签。
- 中文版价值主张直接说"碳硅共生"，英文版展开为 "carbon-silicon coexistence"——这个概念对英语读者不常见，需要点明。
- 英文版增加 Bilibili 平台提及，匹配中文版。

### 注意事项
- "了解更多"链接目前可指向同一个 About 页面（若有独立页面）或页面锚点。若暂无独立 About 页，此按钮可暂时隐藏或改为锚点到 Contact 区。
- 英文版中 "intellectually curious" 对应中文"知性探索者"，不要直译成 "intellectual explorer"（太生硬）。

---

## Block 3: Product 产品（黄金坩埚）

### 位置
About 下方，页面视觉中心，通常占据较大版面（Feature 区块）。

### 当前问题
- 文案偏向内部说明（"首个产品入口""品牌化实验的落点"），缺用户利益点。
- 缺少"即时价值"和新鲜度提示。

### 填充内容

**中文版：**

```
Featured release

黄金坩埚

🟢 已上线

一个围绕研究、写作、表达与创作者工作流展开的产品实验。

2026年5月待开放：
• AI 辅助深度写作工作流
• 知识管理模板
• 创作者效率工具集

[打开产品页 →]

最近更新：增加了 Notion 研究数据库模板（免费下载）
```

**英文版：**

```
Featured release

The Crucible

🟢 Live now

A product experiment around research, writing, expression, and creator workflows.

Currently open:
• AI-assisted deep writing workflow
• Knowledge management templates
• Creator productivity toolkit

[Open product page →]

Latest: Notion research database template added (free download)
```

### 文案规格
- 标签："Featured release" + 产品名 + 状态指示器（🟢）
- 描述：一句话定义 + 三点当前内容（用项目符号）
- CTA：明确动作（打开/进入/查看）
- 更新提示：一行小字，制造新鲜感和即时性
- 语气：产品化、实验感、不夸大。

### 注意事项
- 若黄金坩埚实际内容与此处列出的三项不符，请按实际情况替换，但保持"三点列表"格式。
- "最近更新"必须真实。若近期无更新，可改为"首批内容包括："并列出。
- 英文产品名 "The Crucible" 是 "黄金坩埚" 的对译，若产品有既定英文名请使用既定名。

---

## Block 4: Content Flow 内容流（博客/研究）

### 位置
Product 下方，通常是通栏或卡片式布局。

### 当前问题
- 文案还是"后续会接入"的空头支票，没有当下可用的内容。

### 填充内容

**当前状态：Blog 已有 3 篇文章（碳硅进化论系列），直接使用方案 A。**

**中文版：**

```
Content flow

碳硅进化论

[文章卡片 1: AI时代，别把孩子养成一台过时的机器]
[文章卡片 2: 从台北101到你家楼下——AI时代的肉身哲学]
[文章卡片 3: 伦理无菌室——当AI的"永远赞同"废掉了孩子的关怀能力]

[查看全部文章 →]
```

每张卡片需包含：
| 字段 | 文章 1 | 文章 2 | 文章 3 |
|------|--------|--------|--------|
| **标题** | AI时代，别把孩子养成一台过时的机器 | 从台北101到你家楼下——AI时代的肉身哲学 | 伦理无菌室——当AI的"永远赞同"废掉了孩子的关怀能力 |
| **副标题** | 碳硅进化论 EP01 | 碳硅进化论 · 特别篇 | 碳硅进化论 EP03 |
| **摘要** | 用脑神经科学和AI训练原理，锻造孩子的四种"反脆弱"能力：脑、胆、身、心 | 在AI可以模拟一切的时代，什么才是人类真正无可替代的东西？ | AI不是在教坏孩子，而是在悄悄替代他们成长中最珍贵的挣扎 |

**英文版：**

```
Content flow

Carbon-Silicon Evolution

[Card 1: Don't Raise Your Child Into an Obsolete Machine]
[Card 2: From Taipei 101 to Your Doorstep — Embodied Philosophy in the AI Age]
[Card 3: The Ethics Cleanroom — How AI's Constant Approval Erodes Children's Moral Growth]

[Browse all articles →]
```

| 字段 | Article 1 | Article 2 | Article 3 |
|------|-----------|-----------|-----------|
| **Title** | Don't Raise Your Child Into an Obsolete Machine | From Taipei 101 to Your Doorstep — Embodied Philosophy in the AI Age | The Ethics Cleanroom — How AI's Constant Approval Erodes Children's Moral Growth |
| **Eyebrow** | Carbon-Silicon Evolution EP01 | Carbon-Silicon Evolution · Special | Carbon-Silicon Evolution EP03 |
| **Summary** | Using neuroscience and AI training principles to forge four "anti-fragile" capacities in children | In an age where AI can simulate everything, what remains truly irreplaceable about being human? | AI isn't corrupting children — it's quietly replacing the struggles that make them grow |

### 文案规格
- 栏目标题使用系列名「碳硅进化论」/ "Carbon-Silicon Evolution"，不再用泛化的"博客与研究栏目"。
- 三张文章卡片对应 `contents/` 目录下的三篇已完成稿件，内容真实可上线。
- 英文标题不是逐字直译，而是针对英语读者重新提炼的表达。
- 语气：研究质感、有态度、不学术腔。

### 注意事项
- 三篇文章已完成，源文件在 `contents/` 目录：`01 AI时代，别把孩子养成一台过时的机器.md`、`02 从台北101到你家楼下——AI时代的肉身哲学.md`、`03 伦理无菌室——当AI的"永远赞同"废掉了孩子的关怀能力.md`。
- 发布时需将 Markdown 内容导入 WordPress CMS 或转换为 MDX 放入 `content/` 目录。
- 若后续新增文章，保持"最新 3 篇"的卡片展示逻辑。
- 英文版摘要应独立撰写，不是中文摘要的机翻——要让英语读者在无中文语境下也能产生兴趣。

**备用方案（文章临时不可访问时的降级）：**

```
Content flow

碳硅进化论系列文章正在上线中。

在此之前，你可以在 YouTube 频道看到我们最新的研究与表达。

[前往 YouTube →]（链接：https://youtube.com/@mindhikers）
```

---

## Block 5: Contact 联系

### 位置
页面中下部或底部，通常是独立区块。

### 当前问题
- 文案像设计说明（"把联系入口做得像一段自然的续篇"），不是对外沟通。
- 缺少合作类型的具体说明，询盘质量难以把控。

### 填充内容

**中文版：**

```
Contact

有合作想法，或者单纯想聊聊？

我们欢迎：
• 内容共创（访谈、联名研究、播客）
• 产品化合作（工具、模板、课程）
• thoughtful internet projects（有想法的项目，不限形式）

📧 hello@mindhikers.com

🌍 Shanghai / Remote
```

**英文版：**

```
Contact

Have a collaboration idea, or just want to chat?

We welcome:
• Content partnerships (interviews, co-research, podcasts)
• Product collaborations (tools, templates, courses)
• Thoughtful internet projects (ideas-first, format-agnostic)

📧 hello@mindhikers.com

🌍 Shanghai / Remote
```

### 文案规格
- 结构：标题 + 开放邀请 + 三类合作清单 + 联系方式 + 地点
- 语气：友好但有筛选感。"thoughtful internet projects" 是质量过滤器。
- 字数：中文 80-100 字，英文 60-80 词。

### 注意事项
- 邮箱地址请确认已配置好收发功能。
- 若希望增加预约功能，可在邮箱下方增加：
  - "预约 15 分钟聊聊 →" 链接到 Calendly / 飞书预约 / 腾讯会议
- 英文版 "format-agnostic" 意为"不限形式"，若觉得太生僻可改为 "open to all formats"。

---

## Block 6: Recent Writing 最近文章（底部预览）

### 位置
页面底部，通常是 3 列卡片布局，展示最新文章。

### 当前状态
- Blog 已有 3 篇碳硅进化论系列文章，应直接展示真实内容。
- 旧的"博客内容还在整理中"空货架文案必须删除。

### 填充内容

**中文版（当前适用）：**

```
Recent writing

[卡片 1]
碳硅进化论 EP01 · 2026-04-24
AI时代，别把孩子养成一台过时的机器
用脑神经科学和AI训练原理，锻造孩子的四种"反脆弱"能力
[阅读全文 →]

[卡片 2]
碳硅进化论 · 特别篇 · 2026-04-24
从台北101到你家楼下——AI时代的肉身哲学
在AI可以模拟一切的时代，什么才是人类真正无可替代的东西？
[阅读全文 →]

[卡片 3]
碳硅进化论 EP03 · 2026-04-24
伦理无菌室——当AI的"永远赞同"废掉了孩子的关怀能力
AI不是在教坏孩子，而是在悄悄替代他们成长中最珍贵的挣扎
[阅读全文 →]
```

**英文版：**

```
Recent writing

[Card 1]
Carbon-Silicon Evolution EP01 · 2026-04-24
Don't Raise Your Child Into an Obsolete Machine
Four "anti-fragile" capacities every child needs — through the lens of neuroscience and AI training
[Read article →]

[Card 2]
Carbon-Silicon Evolution · Special · 2026-04-24
From Taipei 101 to Your Doorstep — Embodied Philosophy in the AI Age
In an age where AI can simulate everything, what remains truly irreplaceable about being human?
[Read article →]

[Card 3]
Carbon-Silicon Evolution EP03 · 2026-04-24
The Ethics Cleanroom — How AI's Constant Approval Erodes Children's Moral Growth
AI isn't corrupting children — it's quietly replacing the struggles that make them grow
[Read article →]
```

### 注意事项
- 每张卡片包含：系列标签、发布日期、标题（≤25 字）、摘要（≤40 字）、阅读链接。
- 文章卡片由 CMS 动态渲染（WordPress 或 MDX），此处文案仅供内容校对参考。
- 若后续文章超过 3 篇，此区块自动展示最新 3 篇，旧文章归入 `/blog` 归档页。
- 英文摘要独立撰写，确保对英语母语读者自然有吸引力。

---

## Block 7: Brand System 双语入口（Footer 或全局）

### 位置
通常位于 Footer 或页面角落，语言切换器。

### 填充内容

- 中文页 Footer 增加：「[English →]」链接到英文版首页
- 英文页 Footer 增加：「[中文 →]」链接到中文版首页

### 注意事项
- 语言切换建议用子路径实现，如 `mindhikers.com/en` 或 `mindhikers.com/zh`
- 切换后应保留当前页面上下文（如在 Product 区切换语言，应仍在 Product 区）

---

## 执行检查清单

交付前请逐项确认：

- [ ] Hero 副标题已替换，不再有"有呼吸感"等内部用语
- [ ] About 区已删除"不是履历页面"等否定式解释，黄金精神价值主张已融入
- [ ] Product 区列出了三项具体内容，且至少一项可免费获取
- [ ] Content Flow 区已展示 3 篇碳硅进化论真实文章卡片（非占位符）
- [ ] Recent Writing 区已展示 3 篇真实文章，标题 / 摘要 / 日期 / 链接齐全
- [ ] Contact 区列出了三类合作方向
- [ ] 所有 CTA 按钮都有可点击的链接目标
- [ ] 英文版标题和摘要独立撰写，非中文直译，符合英语表达习惯
- [ ] 中英文内容在语气和侧重点上有适度差异（中文更有态度，英文更 accessible）
- [ ] 3 篇文章已导入 CMS（WordPress 或 MDX）并可正常访问
- [ ] 全站邮箱链接可正常点击唤起邮件客户端

---

## 附：语气与用词规范

| 不要使用 ❌ | 请使用 ✅ | 原因 |
|-------------|----------|------|
| 有呼吸感的品牌入口 | 研究 · 制作 · 产品化 | 内部用语，用户无感 |
| 不是一张展示履历的页面 | （直接说是什么） | 否定式开篇显得防御 |
| 持续编排中的工作室主页 | （删除） | 元叙事，不要解释设计 |
| 还在整理中 / Coming soon | 碳硅进化论系列（直接展示真实文章） | Blog 已有内容，不再需要过渡文案 |
| 首个产品入口 | Featured release | 产品化表达，不自我解释 |
| 敬请期待 | （删除，给出替代内容） | 空货架文案 |
| thinking clearly is valuable | carbon-silicon coexistence / Golden Spirit | 英文版要保留核心概念，不要泛化 |

---

*手册版本：v1.1*  
*更新日期：2026-04-24*  
*v1.1 变更：3 篇碳硅进化论文章已就绪，替换所有占位符；黄金精神融入 About 区；英文版独立润色*  
*如有内容更新需求，联系老张*

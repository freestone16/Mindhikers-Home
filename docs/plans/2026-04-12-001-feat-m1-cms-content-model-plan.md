---
title: "feat: M1 — CMS 内容模型 + 后台可用性 + 多语言数据层"
type: feat
status: active
date: 2026-04-12
origin: docs/brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md
---

# M1 — CMS 内容模型 + 后台可用性 + 多语言数据层

## Overview

将 Mindhikers Homepage 的 staging 站点从"Elementor 模板硬编码"升级为"WordPress 原生 CMS 后台管理"。完成后，老卢能从 WP Admin 后台独立管理首页五大区块（Hero / About / Product / Blog / Contact）的内容，双语 `/` 和 `/en` 严格对等，不再依赖 Elementor 编辑器。

视觉打底层（Logo / 配色 / 字体 / 主文案 / Smoke R2）已于 2026-04-12 完成（Unit 1–7），本计划不重复该工作。

## Problem Frame

当前 staging 站点的品牌视觉已对齐线上，但内容仍嵌在 Elementor 数据层（`_elementor_data` JSON）中。老卢每次改一段文字都需要进 Elementor 编辑器 → 找到 widget → 点开文字 — 4 步流程。Product 区没有真正的内容模型，About / Contact 没有后台字段，Blog 没有分类体系，双语靠独立页 + CSS/JS 隐藏实现，不存在真正的翻译关系。

M1 要解决这三个根本问题：
1. 建立真正的内容模型（Product CPT + Blog 分类 + Hero/About/Contact 后台字段）
2. 让老卢能 1-click 进入后台编辑界面，5 分钟完成一次内容更新
3. 用 Polylang 建立 ZH↔EN 翻译关系，替代当前的 CSS/JS 隐藏过渡方案

(see origin: `docs/brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md` §2, §6)

## Requirements Trace

- R1. Hero 后台可编辑，字段集中在一个表单视图，支持 ZH+EN（origin R1-R4）
- R2. About 后台可编辑，使用品牌定位原文打底（origin R5-R8）
- R3. Product 以 CPT 实现，每个产品独立条目，黄金坩埚入库（origin R9-R15）
- R4. Blog 双层分类（3 主分类 × 4 次级分类），3 篇文章归类（origin R16-R21）
- R5. Contact 静态信息 + 社交矩阵后台可管理（origin R22-R27）
- R6. 导航 + Footer 双语正确（origin R28-R29）
- R7. 后台编辑入口清晰，1-click 进入，语义字段命名（origin R30-R34）
- R8. 双语 `/` 和 `/en` 五大区块严格对等（origin §5）
- R9. 老卢能在 staging 上独立完成"修改 Hero + 新增一个测试产品 + 改一个博客分类"的端到端操作（origin §9.1 完成定义）

## Scope Boundaries

- **不做**：联系表单 / 订阅功能（M2）
- **不做**：生产域名切换（M2）
- **不做**：邮件服务接入（M3+）
- **不做**：视觉精修（Hero 人像图、背景轮播、区块重排）— 留给后续
- **不做**：Elementor Pro 升级（origin §12.2 明确排除）
- **不做**：自定义 admin 界面（origin §12.2，使用 WordPress 原生 + 插件）
- **保留**：视觉打底层成果（Logo / 配色 / 字体 / Additional CSS）迁移到 child theme
- **保留**：staging `noindex` 状态

## Context & Research

### Relevant Code and Patterns

- `wordpress/mu-plugins/mindhikers-cms-core.php` + `bootstrap.php` — 已有 mu-plugin 模式，注册了 `mh_homepage` CPT 和 REST 端点。这是为旧的 headless 方案（Next.js 前台 + WP REST API）设计的，M1 不直接复用其 CPT 定义，但复用 mu-plugin 目录结构和注册模式
- `ops/wordpress/homepage-seeds/homepage-zh.json` + `homepage-en.json` — 已有内容种子文件，可作为 M1 内容迁移的数据源
- `src/data/site-content.ts` — 完整的中英文内容基线，Product / Blog / Contact 字段值的权威来源
- `docs/plans/MIN-110_Additional_CSS_Draft.css` — 当前注入 staging 的 Additional CSS，需迁移到 child theme

### Institutional Learnings

- SureRank REST API 处理 SEO 元数据但有缓存/刷新问题（`docs/dev_logs/HANDOFF.md`）
- Elementor 直接 JSON 编辑 `_elementor_data` 可行但需要 Regenerate CSS（`docs/lessons.md`）
- WordPress Customizer `customize_save` 比 Widgets REST 更稳定（dev log 2026-04-06）
- `GET /surerank/v1/post/settings` 会触发致命错误 — 不要调用
- 域名治理：任何强语义子域名需要跨项目核查（`docs/rules.md`）

### External References

- Polylang Free 与 Astra Free 兼容性确认：无重大冲突，支持 `/en/` 目录 URL + 隐藏默认语言前缀
- ACF Free 不支持 Options Pages 和 Repeater（Pro only）— 排除 ACF Free 作为主力字段插件
- Carbon Fields（完全免费）支持 Theme Options 容器 + Complex/Repeater 字段 — 满足所有 M1 需求
- Astra Child Theme `front-page.php` 可完全绕过 Elementor 渲染首页

## Key Technical Decisions

### D1. 自定义字段插件：Carbon Fields（而非 ACF Free / Meta Box）

**决策**：使用 Carbon Fields 作为 M1 的自定义字段插件。

**理由**：
- Carbon Fields 完全免费，支持 Theme Options 容器（ACF Free 不支持 Options Pages）
- Carbon Fields 支持 Complex 字段（类似 Repeater）— 社交矩阵需要（ACF Free 不支持 Repeater）
- Carbon Fields 支持自定义 Admin Menu 入口 — 满足"1-click 进入编辑界面"需求（R7）
- 不引入 Pro 付费墙，符合全 Free 栈约束

**排除选项**：
- ACF Free：缺 Options Pages + Repeater，M1 核心需求无法满足
- ACF Pro：收费，与 origin §12.2 "不升级付费插件"方向冲突
- Meta Box Free：功能与 Carbon Fields 相当，但社区和文档不如 Carbon Fields 活跃
- 原生 Custom Fields + `add_menu_page()`：可行但 UX 粗糙，不满足"5 分钟更新"要求

### D2. 单例数据管理方式：Carbon Fields Theme Options + 语言前缀字段

**决策**：Hero / About / Contact 三个单例模块的双语数据，统一存放在 Carbon Fields Theme Options 容器中，字段名以语言后缀区分（如 `hero_title_zh` / `hero_title_en`）。

**理由**：
- 管理员看到一个"Hero 管理"菜单，页面内同时展示 ZH 和 EN 字段 — 无需切换语言，自然强制双语对等
- 存储在 `wp_options`，不依赖页面 ID 或 Polylang 翻译关系 — 架构更简单
- 模板层通过 `pll_current_language()` 读取对应语言字段 — 渲染逻辑清晰
- 满足 R7"1-click 进入"和 R33"切换语言版本不超过 1 次点击"（实际无需切换）

**排除选项**：
- 每个模块创建一对 ZH/EN 页面 + Polylang 翻译关系：管理员需要在两个页面间切换，增加操作步骤
- WordPress Customizer API：实时预览能力好，但 Customizer 的分区 UI 不适合多字段表单，字段数量多时 UX 差

### D3. Product CPT：mu-plugin 代码注册（而非 CPT UI 插件）

**决策**：Product CPT 通过 mu-plugin PHP 代码注册，不使用 CPT UI 插件。

**理由**：
- mu-plugin 不可被意外停用，CPT 注册更稳固
- 已有 `wordpress/mu-plugins/` 目录结构和代码模式（`mindhikers-cms-core.php`）
- 老卢不需要自行新增 CPT 类型（PRD 只定义了 Product 一种 CPT）
- 减少插件依赖

### D4. 模板渲染方式：Astra Child Theme + `front-page.php`（而非 Elementor）

**决策**：创建 Astra Child Theme，用 `front-page.php` 自定义模板渲染首页五大区块，完全绕过 Elementor 的 `_elementor_data` 渲染。

**理由**：
- 彻底解除首页对 Elementor 编辑器的依赖（origin R3/R7/R30 核心需求）
- `front-page.php` 在 WordPress 模板层级中优先于 `page.php`，只要设置了静态首页就自动启用
- 各区块数据从 Carbon Fields Theme Options（Hero/About/Contact）和 CPT 查询（Product/Blog）拉取
- 当前 Additional CSS 迁移到 child theme `style.css`，不丢失视觉打底成果
- Astra 的 `get_header()` / `get_footer()` 保留原有 Header/Footer 设计

**Elementor 处置**：
- 不卸载 Elementor — 其他页面（如独立的产品详情页）可能仍需要
- 首页的 Elementor 数据保留但不再被模板加载
- 新的 `front-page.php` 接管首页渲染

### D5. 双语策略：Polylang（多实例数据）+ 语言前缀字段（单例数据）混合

**决策**：
- Product CPT / Blog Posts：使用 Polylang 原生的 post-level 翻译（每个翻译是独立 post，通过 Polylang 关联）
- Hero / About / Contact（单例）：使用 Carbon Fields 语言前缀字段（D2），不走 Polylang 翻译
- 导航菜单：使用 Polylang 的 menu-per-language 功能（Free 版支持）
- URL 结构：中文 `/`（默认语言，隐藏前缀）+ 英文 `/en/`（目录前缀）

**理由**：
- 多实例数据（Product/Blog）天然适合 Polylang 的 post-level 翻译模型
- 单例数据用 Polylang 管理会引入不必要的"翻译页面"概念，语言前缀字段更直接
- 混合方案兼顾了操作简便性和架构清晰度

### D6. 既有 mu-plugin 处置

**决策**：保留 `wordpress/mu-plugins/mindhikers-cms-core.php` 文件但标记为 legacy。M1 新建 `mindhikers-m1-core.php` 作为新的入口。

**理由**：
- 旧 mu-plugin 的 `mh_homepage` CPT、REST 端点、revalidation webhook 都是为 headless 方案设计
- 直接修改旧文件风险高（可能影响仍在运行的功能）
- 新建文件可以干净起步，后续验证旧文件无用后再移除

## Open Questions

### Resolved During Planning

- **Q1: Polylang Free 能否在 Astra + Elementor Free 环境下正常运行？**
  Resolution: 外部调研确认兼容，无重大冲突。但仍需 Unit 0 在 staging 实际验证。

- **Q2: Product CPT 用 WordPress 原生 register_post_type 还是 CPT UI？**
  Resolution: mu-plugin 代码注册（D3），不用 CPT UI。

- **Q3: 产品的"完整描述"用 Gutenberg 还是 Elementor？**
  Resolution: Gutenberg（`'show_in_rest' => true` + `'supports' => ['editor']`）。贴近 CMS 范式，后台简洁。

- **Q4: 自定义字段用哪个插件？**
  Resolution: Carbon Fields（D1）。

- **Q5: 首页模板从 Elementor 迁出后的实现方式？**
  Resolution: Astra Child Theme `front-page.php`（D4）。

- **Q6: `/en` 页面的实现方式？**
  Resolution: Polylang 接管 URL 路由，child theme 模板通过 `pll_current_language()` 读取对应语言数据（D5）。

### Deferred to Implementation

- **Carbon Fields 与 Polylang 的 `pll_current_language()` 运行时兼容性**：理论上无冲突（Carbon Fields 存 `wp_options`，Polylang 切语言上下文），但需要在 Unit 1 安装后实际验证
- **首页五区块的 HTML/CSS 具体结构**：当前 Elementor 渲染的 DOM 结构需要在实施时分析，child theme 模板的 class 命名和样式继承在编码时确定
- **旧 Elementor 首页数据的清理时机**：先确认新模板稳定后再决定是否删除旧 `_elementor_data`

## High-Level Technical Design

> *This illustrates the intended approach and is directional guidance for review, not implementation specification. The implementing agent should treat it as context, not code to reproduce.*

```
┌─────────────────────────────────────────────────────────────────────┐
│  WP Admin 后台                                                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  侧边栏菜单：                                                       │
│    ▸ Hero 管理     → Carbon Fields Theme Options (ZH+EN 字段)       │
│    ▸ About 管理    → Carbon Fields Theme Options (ZH+EN 字段)       │
│    ▸ Contact 管理  → Carbon Fields Theme Options (ZH+EN 字段)       │
│    ▸ 产品          → CPT: mh_product (Polylang 翻译)               │
│    ▸ 文章          → WP Posts (Polylang 翻译 + 双层分类)            │
│    ▸ 语言          → Polylang 设置                                  │
│                                                                     │
└────────────┬────────────────────────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────────────────────┐
│  数据层                                                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  wp_options:                                                        │
│    hero_title_zh / hero_title_en / hero_desc_zh / hero_desc_en ...  │
│    about_content_zh / about_content_en ...                          │
│    contact_email / contact_location_zh / contact_location_en ...    │
│    contact_social_matrix (complex field: platform + url + qr)       │
│                                                                     │
│  wp_posts (CPT: mh_product):                                       │
│    产品名 / 副标题 / 简介 / 完整描述 / 状态 / Logo / 入口链接      │
│    is_featured (Carbon Fields post meta)                            │
│    Polylang 翻译关系 ZH ↔ EN                                       │
│                                                                     │
│  wp_posts (post_type: post):                                        │
│    标题 / 正文 / 摘要 / 封面图                                      │
│    wp_term_relationships → 主分类 + 次级分类                        │
│    Polylang 翻译关系（可选）                                        │
│                                                                     │
└────────────┬────────────────────────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────────────────────┐
│  前台渲染 (Astra Child Theme)                                        │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  front-page.php:                                                    │
│    $lang = pll_current_language();                                  │
│    ├── Hero 区块     ← carbon_get_theme_option("hero_*_{$lang}")   │
│    ├── About 区块    ← carbon_get_theme_option("about_*_{$lang}")  │
│    ├── Product 区块  ← WP_Query('mh_product') + Polylang 过滤     │
│    ├── Blog 区块     ← WP_Query('post') + Polylang 过滤           │
│    └── Contact 区块  ← carbon_get_theme_option("contact_*")        │
│                                                                     │
│  URL 路由:                                                          │
│    /         → 中文首页 (Polylang default, prefix hidden)           │
│    /en/      → 英文首页 (Polylang EN prefix)                       │
│    /product/<slug>  → 产品详情 (single-mh_product.php)             │
│    /en/product/<slug> → 英文产品详情                                │
│    /blog/    → 博客列表                                             │
│    /en/blog/ → 英文博客列表                                        │
│                                                                     │
│  get_header() / get_footer() → Astra 原生 Header/Footer            │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

## Implementation Units

- [ ] **Unit 0: Polylang 技术验证（Gate）**

**Goal:** 在 staging 上安装 Polylang Free，验证其与 Astra + Elementor Free 的兼容性，建立 ZH/EN 语言结构。验证失败则暂停 M1，回退调整双语策略。

**Requirements:** R8（双语基础设施）

**Dependencies:** None — 这是所有后续 Unit 的前置门

**Files:**
- 无代码文件变更 — 纯 WordPress 后台操作

**Approach:**
- 通过 WP Admin → 插件 → 安装 Polylang Free
- 配置语言：ZH（默认）+ EN
- 配置 URL：目录模式 `/en/`，隐藏默认语言前缀
- 验证：现有首页、博客、文章详情页在安装后仍然正常访问（无 500 / 白屏 / 布局断裂）
- 验证：Polylang 语言切换器可见且功能正常
- 验证：WP Admin 后台仍可正常操作

**Gate 标准:**
- ✅ 通过：所有验证项通过 → 继续 Unit 1
- ❌ 失败：出现不可调和的兼容性问题 → 暂停 M1，记录问题，回退到 origin §13.1 讨论替代方案

**Test scenarios:**
- Happy path: 安装 Polylang 后访问 `/`，页面 200 且内容不变
- Happy path: 访问 `/blog/`，页面 200 且文章列表可见
- Happy path: WP Admin 后台可正常登录和操作
- Edge case: Polylang 语言切换器出现在前台（nav 或 widget）
- Error path: 如果 Polylang 与 Elementor 冲突导致首页白屏，记录错误信息并触发 Gate 失败流程

**Verification:**
- staging 全站无 500 错误
- Polylang 语言设置页显示 ZH（默认）+ EN 两种语言
- URL `/en/` 可访问（即使内容暂时为空或与 `/` 相同）

---

- [ ] **Unit 1: Astra Child Theme + Carbon Fields 基础设施**

**Goal:** 创建 Astra Child Theme，安装 Carbon Fields，将当前 Additional CSS 迁移到 child theme，建立 M1 的模板和字段基础设施。

**Requirements:** R7（后台编辑入口）

**Dependencies:** Unit 0 通过

**Files:**
- Create: `wordpress/themes/astra-child/style.css`
- Create: `wordpress/themes/astra-child/functions.php`
- Create: `wordpress/themes/astra-child/front-page.php`（骨架，后续 Unit 填充）
- Reference: `docs/plans/MIN-110_Additional_CSS_Draft.css`（迁移来源）

**Approach:**
- Child theme `style.css` 的 `Template: astra` 头部声明
- `functions.php` 中 enqueue 父主题样式 + 加载 Carbon Fields
- 将当前 staging 的 Additional CSS（配色 / 字体 / 品牌覆盖）迁移到 child theme `style.css`
- Carbon Fields 通过 Composer 安装到 child theme（或作为独立插件安装 — 实施时选择更稳妥的方式）
- 在 WP Admin 激活 child theme，验证视觉效果不退化

**Patterns to follow:**
- `wordpress/mu-plugins/` 目录结构（mu-plugin 注册模式）
- Astra 官方 child theme 指南

**Test scenarios:**
- Happy path: 激活 child theme 后，首页视觉效果与激活前一致（Logo / 配色 / 字体 / 布局不变）
- Happy path: WP Admin → 外观 → 主题，显示 Astra Child 为当前主题
- Error path: 如果 child theme 激活后样式丢失，检查 `functions.php` 的父主题 enqueue 是否正确
- Edge case: Carbon Fields 安装后 WP Admin 侧边栏出现 Carbon Fields 菜单项

**Verification:**
- Child theme 激活后首页视觉与之前一致
- Carbon Fields 可用（后台无报错）
- Additional CSS 已迁移到 child theme（WP Customizer 的 Additional CSS 可清空）

---

- [ ] **Unit 2: Product CPT 注册 + 黄金坩埚数据入库**

**Goal:** 注册 `mh_product` CPT，定义所有产品字段（Carbon Fields），创建黄金坩埚的 ZH + EN 条目。

**Requirements:** R3（Product CPT），R8（双语对等），R9（端到端操作）

**Dependencies:** Unit 1（Carbon Fields 可用）

**Files:**
- Create: `wordpress/mu-plugins/mindhikers-m1-core.php`（CPT 注册 + Carbon Fields 字段定义）
- Create: `wordpress/themes/astra-child/single-mh_product.php`（产品详情页模板）
- Reference: `src/data/site-content.ts`（黄金坩埚内容基线）

**Approach:**
- 在 `mindhikers-m1-core.php` 中 `register_post_type('mh_product', ...)`：
  - `public => true`, `has_archive => true`, `show_in_rest => true`
  - `supports => ['title', 'editor', 'thumbnail', 'excerpt']`
  - `rewrite => ['slug' => 'product']`
  - `menu_icon => 'dashicons-products'`
  - Labels 中英文（WP Admin 侧边栏显示"产品"）
- Carbon Fields 字段组（attach to `mh_product`）：
  - `product_subtitle`（文本）— 一句话定位
  - `product_status`（下拉选择）— 构思中 / 开发中 / 公测 / 正式发布 / 已下线
  - `product_entry_url`（URL）— 产品入口链接
  - `product_is_featured`（布尔）— 是否 Featured
  - 产品名用 WP 原生 `post_title`
  - 简介用 WP 原生 `excerpt`
  - 完整描述用 WP 原生 `post_content`（Gutenberg 编辑）
  - Logo/主视觉图用 WP 原生 `post_thumbnail`
- Polylang 启用 `mh_product` 翻译
- 创建黄金坩埚 ZH 条目 + EN 条目，Polylang 关联为翻译对
- **重要**：黄金坩埚状态设为"构思中"（origin R12.1 明确要求纠正旧的"Live now"错误标签）

**Test scenarios:**
- Happy path: WP Admin 侧边栏出现"产品"菜单，点击进入产品列表
- Happy path: 新建一个产品，所有字段可编辑（标题、副标题、状态、简介、完整描述、Logo、入口链接、Featured）
- Happy path: 黄金坩埚 ZH 条目存在，状态为"构思中"
- Happy path: 黄金坩埚 EN 条目存在，通过 Polylang 与 ZH 版本关联
- Happy path: 访问 `/product/golden-crucible/` 显示产品详情页
- Edge case: 设置两个产品为 Featured — 验证只有一个生效（或记录此约束需要 UI 层面处理）
- Integration: Polylang 语言切换器在产品详情页正确链接到对应语言版本

**Verification:**
- 黄金坩埚在前台可访问（ZH + EN）
- 产品详情页内容来自 WP 后台字段，不来自 Elementor
- WP Admin 中"产品"入口 1-click 可达

---

- [ ] **Unit 3: Blog 双层分类 + 文章归类**

**Goal:** 建立 Blog 的双层分类体系（3 主分类 × 4 次级分类），将现有 3 篇文章归类。

**Requirements:** R4（Blog 分类法）

**Dependencies:** Unit 0（Polylang 用于分类翻译）

**Files:**
- Modify: `wordpress/mu-plugins/mindhikers-m1-core.php`（分类注册，如果需要自定义 taxonomy）
- 无新模板文件 — 使用 Astra 默认的 category archive

**Approach:**
- 使用 WordPress 原生 Categories 的父子结构（不需要自定义 taxonomy）：
  - 主分类（父级）：AI 技术 / 碳硅共生 / 脑神经科学
  - 次级分类（子级）：深度 / 速记 / 视频 / 工具（挂在每个主分类下）
- Polylang 为每个分类创建 ZH + EN 翻译
- 将现有 3 篇文章各分配一个主分类 + 一个次级分类
- 文章内容不变（本轮不改文章正文）

**Test scenarios:**
- Happy path: WP Admin → 文章 → 分类目录，显示 3 个主分类，每个下有 4 个子分类
- Happy path: 编辑一篇文章，侧边栏可见分类选择器，能同时勾选主分类和子分类
- Happy path: 3 篇现有文章各有明确的主分类 + 次级分类
- Edge case: 分类名的 ZH / EN 翻译通过 Polylang 正确关联
- Integration: 访问 `/category/ai-technology/` 显示该分类下的文章列表

**Verification:**
- 12 个分类存在（3 主 × 4 子，或 3 主 + 4 子如果子分类跨主分类共享 — 实施时确定层级关系）
- 3 篇文章均已归类
- 分类 archive 页面可访问

---

- [ ] **Unit 4: Hero / About / Contact 后台字段**

**Goal:** 用 Carbon Fields Theme Options 为 Hero / About / Contact 三个单例模块建立后台编辑界面，录入第一版内容。

**Requirements:** R1（Hero 后台），R2（About 后台），R5（Contact 后台），R7（1-click 入口），R8（双语）

**Dependencies:** Unit 1（Carbon Fields 可用）

**Files:**
- Create: `wordpress/mu-plugins/mindhikers-m1-fields.php`（Carbon Fields 字段定义 — Hero / About / Contact）
- Reference: `src/data/site-content.ts`（内容基线）
- Reference: `docs/brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md` §1（品牌定位原文）

**Approach:**

三个 Carbon Fields Theme Options 容器，分别注册为 WP Admin 侧边栏的独立菜单项：

**Hero 管理**（菜单图标：`dashicons-megaphone`）
- `hero_eyebrow_zh` / `hero_eyebrow_en`（文本）
- `hero_title_zh` / `hero_title_en`（文本）
- `hero_desc_zh` / `hero_desc_en`（textarea）
- `hero_cta_primary_text_zh` / `hero_cta_primary_text_en`（文本）
- `hero_cta_primary_url`（URL — 语言共享）
- `hero_cta_secondary_text_zh` / `hero_cta_secondary_text_en`（文本）
- `hero_cta_secondary_url`（URL）
- `hero_image`（图片 — 媒体库选择）

**About 管理**（菜单图标：`dashicons-info`）
- `about_title_zh` / `about_title_en`（文本）
- `about_content_zh` / `about_content_en`（rich text / WYSIWYG）
- `about_image`（图片 — 可选）

**Contact 管理**（菜单图标：`dashicons-email`）
- `contact_email`（邮箱 — 语言共享）
- `contact_location_zh` / `contact_location_en`（文本）
- `contact_social_matrix`（Carbon Fields Complex 字段）：
  - 子字段：`platform_name_zh` / `platform_name_en` / `platform_url` / `platform_icon` / `platform_qr_image`（用于微信公众号二维码）
  - 初始数据：Twitter/X + Bilibili + 微信公众号

**内容录入**：
- Hero：从 `src/data/site-content.ts` 的 `hero` 字段迁移
- About：使用 origin §1 品牌定位原文作为底稿
- Contact：主邮箱暂保持 staging 现有值（`ops@mindhikers.com` 的可收信状态待老卢确认后再切换）

**Test scenarios:**
- Happy path: WP Admin 侧边栏显示"Hero 管理" / "About 管理" / "Contact 管理"三个入口
- Happy path: 点击"Hero 管理"，页面内同时显示 ZH 和 EN 字段，可直接编辑并保存
- Happy path: 修改 `hero_title_zh` 并保存，前台 `/` 的 Hero 标题更新（Unit 5 模板完成后验证）
- Happy path: Contact 社交矩阵显示 3 条记录（Twitter / Bilibili / 微信公众号），可新增/删除/排序
- Edge case: 微信公众号条目有 QR 图片字段，其他平台该字段为空
- Error path: 保存空的必填字段 — Carbon Fields 的行为是否有内置验证（记录实际行为，不一定阻塞）

**Verification:**
- 三个管理菜单可 1-click 到达
- ZH + EN 字段在同一页面内，无需切换
- 数据成功保存到 `wp_options`
- 社交矩阵的 Complex 字段可增删改

---

- [ ] **Unit 5: 首页模板（front-page.php 五区块渲染）**

**Goal:** 在 Astra Child Theme 的 `front-page.php` 中实现首页五大区块的完整渲染，数据全部来自 CMS（Carbon Fields + CPT 查询 + Polylang 语言切换）。

**Requirements:** R1-R6（五区块完整），R7（不依赖 Elementor），R8（双语渲染）

**Dependencies:** Unit 1（child theme 骨架），Unit 2（Product CPT），Unit 3（Blog 分类），Unit 4（Hero/About/Contact 字段）

**Files:**
- Modify: `wordpress/themes/astra-child/front-page.php`
- Create: `wordpress/themes/astra-child/template-parts/hero.php`
- Create: `wordpress/themes/astra-child/template-parts/about.php`
- Create: `wordpress/themes/astra-child/template-parts/product.php`
- Create: `wordpress/themes/astra-child/template-parts/blog.php`
- Create: `wordpress/themes/astra-child/template-parts/contact.php`
- Modify: `wordpress/themes/astra-child/style.css`（区块样式）

**Approach:**
- `front-page.php` 结构：`get_header()` → 5 个 `get_template_part()` → `get_footer()`
- 每个 template-part 开头获取当前语言：`$lang = pll_current_language('slug');`
- **Hero**：从 `carbon_get_theme_option("hero_*_{$lang}")` 读取字段，渲染 eyebrow + title + desc + CTA 按钮 + 配图
- **About**：从 `carbon_get_theme_option("about_*_{$lang}")` 读取，渲染标题 + 品牌叙述
- **Product**：`WP_Query(['post_type' => 'mh_product', 'lang' => $lang])` 查询当前语言的产品列表，Featured 大卡片 + 其他小卡片
- **Blog**：`WP_Query(['post_type' => 'post', 'posts_per_page' => 3, 'lang' => $lang])` 最近 3 篇文章，显示标题 + 摘要 + 主分类 + 封面图 + 日期
- **Contact**：Carbon Fields 读取邮箱 + 位置 + 社交矩阵
- CSS：从当前 Additional CSS + Elementor 渲染结果中提取关键样式，适配新的 HTML 结构。不追求像素级还原 Elementor 布局，但保持品牌视觉一致性（配色 / 字体 / 间距气质）
- Polylang 语言切换器通过 `pll_the_languages()` 或导航菜单集成

**Execution note:** 这是 M1 最大工程量的单元。建议先搭骨架（5 个区块的 HTML 结构 + 数据绑定），再逐步调整样式。不追求一次性完美。

**Test scenarios:**
- Happy path: 访问 `/`，五大区块全部可见，内容来自 CMS（不再来自 Elementor）
- Happy path: 访问 `/en/`，五大区块显示英文版内容
- Happy path: 在 WP Admin 修改 Hero 标题 → 前台刷新后标题更新
- Happy path: Product 区显示黄金坩埚卡片，点击进入产品详情页
- Happy path: Blog 区显示最近 3 篇文章，点击标题进入文章详情
- Happy path: Contact 区显示社交矩阵（Twitter / Bilibili / 微信公众号）
- Edge case: 英文 Blog 列表为空（如果 3 篇文章都没有英文翻译）— 应显示空状态提示而非报错
- Edge case: Product 列表只有 1 个产品 — 布局应优雅降级
- Integration: Polylang 语言切换器链接 `/` ↔ `/en/` 双向可用
- Integration: Header/Footer（Astra 原生）在 child theme 下仍正常显示 Logo / 导航 / 版权

**Verification:**
- 首页渲染完全绕过 Elementor（可通过检查页面源码确认无 `elementor` class）
- 五大区块数据全部来自 CMS
- 中英文切换正确
- 品牌视觉（配色 / 字体 / Logo）与 Unit 1–7 的打底成果一致

---

- [ ] **Unit 6: 双语渲染验证 + EN 页面收口**

**Goal:** 验证所有双语场景的端到端正确性，清理旧的 `/en` 独立页过渡方案，确保 Polylang 接管后 EN 页面完整可用。

**Requirements:** R8（双语严格对等），R6（导航双语），R9（端到端操作）

**Dependencies:** Unit 5（首页模板完成）

**Files:**
- Modify: `wordpress/themes/astra-child/front-page.php`（如有双语 edge case 需修复）
- 无新文件 — 主要是验证和配置

**Approach:**
- 验证 Polylang 的导航菜单 per-language 配置：ZH 菜单（关于 / 产品 / 博客 / 联系）+ EN 菜单（About / Product / Blog / Contact）
- 清理旧的 `/en` 独立页面（当前通过 CSS/JS 隐藏中文主题壳的过渡方案）：确认 Polylang 接管 `/en` 路由后，旧页面不再被使用。将旧页面移到 Trash 或标记 Draft
- 验证 Footer 的语言切换入口
- 验证 Product 详情页的双语切换
- 验证 Blog 列表的双语行为（EN 列表只显示有英文翻译的文章）

**Test scenarios:**
- Happy path: `/` 导航显示中文菜单项，`/en/` 导航显示英文菜单项
- Happy path: `/` Footer 显示中文版权 + 中文联系信息，`/en/` Footer 显示英文版
- Happy path: 从 `/` 点击语言切换器跳转到 `/en/`，反之亦然
- Happy path: `/product/golden-crucible/` 显示中文详情，`/en/product/golden-crucible/` 显示英文详情
- Edge case: 访问旧的 `/en` 独立页面路径 — 应该被 Polylang 路由接管或返回 404（不应显示旧的 CSS/JS 隐藏版本）
- Edge case: `/en/blog/` 列表为空时显示友好的空状态消息
- Error path: 某个 Carbon Fields 字段的 EN 版本为空 — 模板应 graceful 降级（显示空或 fallback，不报 PHP 错误）

**Verification:**
- `/` 和 `/en/` 五大区块严格对等
- 旧的 CSS/JS 隐藏过渡方案不再使用
- 所有导航链接和语言切换器功能正常
- 无 PHP 错误或警告

---

- [ ] **Unit 7: M1 端到端验收**

**Goal:** 模拟老卢的完整操作流程，验证 M1 的完成定义（origin §9.1）：从后台修改 Hero + 新增一个测试产品 + 改一个博客分类。

**Requirements:** R9（端到端操作），全部 R1-R8

**Dependencies:** Unit 6（双语收口完成）

**Files:**
- Modify: `docs/dev_logs/HANDOFF.md`（更新交接状态）

**Approach:**
- 执行以下端到端流程（模拟老卢操作）：
  1. 从 WP Admin 侧边栏 → "Hero 管理" → 修改 `hero_title_zh` → 保存 → 前台 `/` 验证更新
  2. 从 WP Admin 侧边栏 → "产品" → "新增" → 填写一个测试产品（ZH + EN 双版本）→ 前台首页 Product 区验证显示
  3. 从 WP Admin → "文章" → 编辑一篇文章 → 修改其分类 → 保存 → 前台分类页验证
- 执行 Smoke 验收（与 Unit 1–7 的 Smoke R2 同级别）：
  - 首页 `/` 和 `/en/` 200 + 五区块完整
  - `/blog/` 200 + 文章列表可见
  - 产品详情页可访问
  - SEO 元信息正确（`<title>` / `meta description`）
  - staging 仍保持 `noindex`
  - 后台仍可登录和操作
- 更新 `docs/dev_logs/HANDOFF.md`：
  - 当前状态更新为 M1 完成
  - 记录新的技术栈（Carbon Fields / Polylang / Child Theme）
  - 记录已知限制和 M2 建议
  - 更新凭据和操作指南（如有变化）

**Test scenarios:**
- Happy path: 完整的"修改 Hero → 新增产品 → 改分类"端到端流程无阻塞完成
- Happy path: 新增的测试产品在首页 Product 区可见
- Happy path: 修改分类后，文章出现在新分类的 archive 页面
- Integration: 所有 Smoke R2 验收项在新架构下仍然通过
- Edge case: 新增产品的 EN 版本不创建时，英文首页 Product 区不显示该产品（Polylang 过滤生效）

**Verification:**
- M1 完成定义全部满足（origin §9.1）
- HANDOFF.md 已更新，下一位同事可直接接手 M2
- staging 全站无 500 错误

## System-Wide Impact

- **Interaction graph:** Polylang 安装后会接管 URL 路由和语言上下文，影响所有前台 `WP_Query` 的默认行为（自动按语言过滤）。Carbon Fields 的 `carbon_get_theme_option()` 不受 Polylang 影响（读 `wp_options`，无语言过滤）
- **Error propagation:** 如果 Carbon Fields 字段读取失败（字段不存在或未填写），模板应 `echo ''` 而非触发 PHP Fatal。Polylang 的 `pll_current_language()` 在未安装 Polylang 时返回 `false` — 模板应有 fallback
- **State lifecycle risks:** 旧的 Elementor 首页数据（`_elementor_data`）仍在数据库中，新 `front-page.php` 不加载它，但 Elementor 仍可能尝试渲染。需确认 WordPress 静态首页设置后 `front-page.php` 优先级高于 Elementor
- **API surface parity:** SureRank 的 SEO 元信息与新模板的关系不变（SureRank 写入 `<head>`，不受 body 模板切换影响）
- **Unchanged invariants:** Header / Footer 仍由 Astra 原生渲染（`get_header()` / `get_footer()`），Logo / 导航 / 版权行不受 `front-page.php` 模板切换影响

## Risks & Dependencies

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Polylang 与 Astra/Elementor 不兼容 | Low | High | Unit 0 作为 gate 验证，失败则暂停 |
| Carbon Fields 与 Polylang 运行时冲突 | Low | Med | Theme Options 存 `wp_options`，理论上不冲突；Unit 1 实际验证 |
| front-page.php 视觉效果与 Elementor 版差异大 | Med | Med | 不追求像素级还原，保持品牌气质一致即可；CSS 从 Additional CSS 迁移 |
| 旧 Elementor 数据干扰新模板渲染 | Low | Med | 确认 WP 模板层级：`front-page.php` 优先于 Elementor 页面模板 |
| 内容迁移遗漏 | Med | Low | 用 `src/data/site-content.ts` 和 seed 文件做 checklist 式迁移 |
| staging 数据库操作失误 | Low | High | 操作前备份关键 options 和 post meta；旧数据保留不删除 |

## Documentation / Operational Notes

- M1 完成后更新 `docs/dev_logs/HANDOFF.md`
- 新增的 child theme 和 mu-plugin 文件需要部署到 staging 的 WordPress 实例（Railway 容器内 `wp-content/` 目录）
- 部署方式：通过 WP Admin 上传（child theme zip）或直接 SSH/SFTP 到 Railway 容器 — 具体方式在实施时确定
- Carbon Fields 的配置存在数据库中（`wp_options`），不随 theme zip 迁移 — 如果重建环境需要重新录入内容

## Sources & References

- **Origin document:** [docs/brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md](docs/brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md)
- Current staging state: [docs/dev_logs/HANDOFF.md](docs/dev_logs/HANDOFF.md)
- Content baseline: [src/data/site-content.ts](src/data/site-content.ts)
- Existing mu-plugin pattern: [wordpress/mu-plugins/mindhikers-cms-core.php](wordpress/mu-plugins/mindhikers-cms-core.php)
- Additional CSS baseline: [docs/plans/MIN-110_Additional_CSS_Draft.css](docs/plans/MIN-110_Additional_CSS_Draft.css)
- Brand positioning: origin document §1
- Related Linear issue: MIN-110

---
title: "feat: WP 轻量定制模式 — CMS Core JSON 数据桥接与主题渲染"
type: feat
status: active
date: 2026-04-23
origin: docs/plans/wp-traditional-mode-assessment.md
---

# feat: WP 轻量定制模式 — CMS Core JSON 数据桥接与主题渲染

## Overview

在保留现有 Headless 架构（Next.js + WP REST API）的前提下，为 Mindhikers-Homepage 引入 **WP 轻量定制** 模式。该模式允许任何 WordPress 主题直接读取 CMS Core 的 JSON 数据并渲染首页，无需迁移数据到标准 WP 页面或 Carbon Fields。

核心思路：**数据零迁移，主题直接读 JSON**。CMS Core 提供统一的数据桥接层，主题通过 PHP API 获取结构化数据，享受 WP 生态（主题切换、插件）的同时，保持现有 JSON 存储架构不变。

## Problem Frame

当前架构存在两套互不兼容的数据系统：

1. **CMS Core（Headless）**：`mh_homepage` post type 存储 JSON payload，通过 REST API 供 Next.js 消费
2. **Carbon Fields（传统主题）**：Astra Child 主题依赖 Carbon Fields 的 theme options，数据存储在 `wp_options`

**核心矛盾**：
- Astra Child 主题无法读取 CMS Core 的 JSON 数据，导致传统 WP 前台渲染为空
- `mh_homepage` post type 设为 `public => false`，主题模板无法直接查询
- Carbon Fields 数据与 CMS Core JSON 完全不互通，切换主题 = 数据丢失

**目标**：在不迁移数据的前提下，让任何 WP 主题都能读取 CMS Core JSON 并渲染首页。

## Requirements Trace

- **R1. 数据桥接层**：提供 PHP API，任何主题可通过函数获取 CMS Core JSON 数据（hero、about、product、blog、contact 等区块）
- **R2. Post Type 可查询**：`mh_homepage` 必须可被主题模板查询（`public => true` 或等效方案）
- **R3. 主题模板重写**：Astra Child 的 5 个 template-parts 改用 JSON 数据渲染，移除 Carbon Fields 依赖
- **R4. 缓存策略**：使用 WP Transients 缓存 JSON 解析结果，避免每次请求重复查询+解析
- **R5. 双语支持**：支持 `zh-CN` / `en-US` 双语内容切换，与现有 `locale` meta 兼容
- **R6. 主题切换安全**：任何主题都能通过桥接层访问数据，不绑定特定主题
- **R7. REST API 兼容**：现有 `mindhikers/v1/homepage/{locale}` 端点继续为 Next.js 服务，行为不变

## Scope Boundaries

- ✅ 在 CMS Core 内新增数据桥接 PHP API
- ✅ 修改 `mh_homepage` post type 使其可被主题查询
- ✅ 重写 Astra Child 5 个 template-parts 使用 JSON 数据
- ✅ 实现 WP Transients 缓存
- ✅ 支持双语（zh/en）
- ❌ **不迁移数据**：不将 JSON 数据导入标准 WP 页面或 Carbon Fields
- ❌ **不改造 Dockerfile**：当前计划不涉及 Railway 容器部署通道（P1 架构债另案处理）
- ❌ **不删除 Carbon Fields**：仅在新模板中停止使用，保留历史数据
- ❌ **不改动 Next.js 前端**：Headless 模式继续运行，不受影响

## Context & Research

### Relevant Code and Patterns

- **CMS Core 主类**：`wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php` — `Mindhikers_Cms_Core` 单例类，负责 post type 注册、meta 注册、REST 路由、数据规范化
- **现有 REST 端点**：`registerRestRoutes()` 注册 `/homepage/(?P<locale>zh|en)`，内部调用 `findHomepagePostByLocale()` + `normalizeHomepagePayload()`
- **数据规范化**：`normalizeHomepagePayload()` 返回标准化数组（hero/about/product/blog/contact/productDetail 等区块）
- **Astra Child 主题**：`wordpress/themes/astra-child/`，当前依赖 `carbon_get_theme_option()` 和 `carbon_get_the_post_meta()`
- **模板结构**：`front-page.php` 加载 5 个 `template-parts/*.php`，通过 `$args['lang']` 传递语言
- **Polylang 集成**：主题使用 `pll_current_language()` 和 `pll_get_post_language()` 做多语言过滤

### Institutional Learnings

- **HANDOFF.md 关键认知**：当前 production WP 容器不通过 git 部署代码，mu-plugins 在 Railway Volume 内。本计划编写的代码需通过 Code Snippets 或手动 ZIP 上传生效（中长期需解决 P1 架构债）
- **mhs02 Snippet 依赖**：production 当前依赖 Code Snippet `mhs02` 提供 REST API，本计划不触碰该 snippet
- **Carbon Fields 仅用于主题**：CMS Core 完全不依赖 Carbon Fields，两者是独立数据层

### External References

- [WordPress Transients API](https://developer.wordpress.org/apis/handbook/transients/)
- [WP_Query - Post Type Parameters](https://developer.wordpress.org/reference/classes/wp_query/#post-type-parameters)
- [register_post_type - public argument](https://developer.wordpress.org/reference/functions/register_post_type/)

## Key Technical Decisions

- **KTD1. 数据桥接层作为 CMS Core 内部方法**：不新建独立插件，在 `Mindhikers_Cms_Core` 类中新增 `getHomepageDataForTheme($locale)` 方法，保持数据逻辑集中
- **KTD2. `public => true` + `has_archive => false`**：让 `mh_homepage` 可被 `get_posts()` 查询，但不生成前端 archive 页面，避免 URL 冲突
- **KTD3. 缓存键按 locale 分离**：`mindhikers_homepage_data_{$locale}`，确保双语缓存互不污染
- **KTD4. 缓存失效绑定 save 钩子**：在 `save_post_mh_homepage` 和 `updated_option`（site settings）时清除对应 locale 的 transient
- **KTD5. 主题模板接收 `$args['payload']`**：`front-page.php` 一次性查询+解析 JSON，通过 `$args` 传递给各 template-parts，避免每个 part 重复查询
- **KTD6. 保留 Carbon Fields 文件但停用**：`lib/carbon-fields.php` 保留但不再 `require`，防止激活报错，历史数据可查阅

## Open Questions

### Resolved During Planning

- **Q1. `public => true` 是否会产生意外前端路由？**
  - 决议：`has_archive => false` + `rewrite => false`（或自定义 slug），不生成公开 URL。即使 `public => true`，没有 rewrite 规则就不会暴露前端页面。
- **Q2. 缓存时间设多长？**
  - 决议：默认 `HOUR_IN_SECONDS * 6`（6 小时）。CMS 编辑频率低，6 小时足够。紧急更新可通过保存 post 立即失效缓存。
- **Q3. 主题切换时如何确保其他主题也能用？**
  - 决议：桥接层提供全局函数 `mindhikers_get_homepage_data($locale = 'zh')`，任何主题调用即可。不依赖 Astra Child 特有逻辑。
- **Q4. Product 区块的 `mh_product` CPT 如何处理？**
  - 决议：`mh_product` 已是 `public => true`（由其他插件注册），当前 `product.php` 的 `WP_Query` 逻辑继续工作，只需把 Carbon Fields 元数据读取改为 JSON 数据或标准 post meta。

### Deferred to Implementation

- **Q5. 具体 JSON 字段名与模板变量映射**：需在重写 template-parts 时对照实际 JSON 结构确认字段路径（如 `$payload['hero']['title']`）
- **Q6. Polylang 与 `mh_homepage` 的兼容性**：`mh_homepage` 目前通过自定义 `locale` meta 区分语言，未使用 Polylang。主题层如需深度集成 Polylang，需现场验证 `pll_current_language()` 返回值与 `locale` meta 的对应关系。
- **Q7. 生产部署方式**：当前 Railway 容器不自动拉取仓库代码，实施完成后需通过 Code Snippets 或手动上传 ZIP 部署到 production（P1 架构债）。

## High-Level Technical Design

> *This illustrates the intended approach and is directional guidance for review, not implementation specification. The implementing agent should treat it as context, not code to reproduce.*

### 数据流（WP 轻量定制模式）

```
┌─────────────────────────────────────────────────────────────┐
│  WP Theme (any)                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │ front-page  │→ │ template-   │→ │  HTML Output        │ │
│  │ .php        │  │ parts/*.php │  │                     │ │
│  └──────┬──────┘  └─────────────┘  └─────────────────────┘ │
│         │                                                   │
│         │ get_posts(['post_type'=>'mh_homepage'])           │
│         ▼                                                   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Mindhikers_Cms_Core::getHomepageDataForTheme()      │   │
│  │  1. Check transient cache                           │   │
│  │  2. If miss: query mh_homepage post by locale       │   │
│  │  3. Decode JSON payload                             │   │
│  │  4. Normalize + cache                               │   │
│  │  5. Return array                                    │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│  CMS Core (existing)                                        │
│  ┌─────────────┐  ┌─────────────────────────────────────┐  │
│  │ mh_homepage │  │ mindhikers_homepage_payload (JSON)  │  │
│  │ post type   │  │ mindhikers_locale (zh/en)           │  │
│  └─────────────┘  └─────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### 缓存生命周期

```
[Theme Request]
    │
    ▼
[Check Transient: mindhikers_homepage_data_zh]
    │
    ├── Hit ──→ Return cached array
    │
    └── Miss ──→ Query DB ──→ Decode JSON ──→ Normalize ──→ Set Transient ──→ Return
                                                    │
                                                    ▼
                                            [save_post_mh_homepage]
                                                    │
                                                    └── Clear Transient
```

## Implementation Units

- [ ] **Unit 1: CMS Core Data Bridge API**

**Goal:** 在 CMS Core 中新增主题可用的数据获取 API，支持缓存和双语

**Requirements:** R1, R4, R5, R6

**Dependencies:** None

**Files:**
- Modify: `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php`
- Test: `wordpress/mu-plugins/mindhikers-cms-core/tests/test-data-bridge.php` (if tests exist, else manual verification)

**Approach:**
- 在 `Mindhikers_Cms_Core` 类中新增 `getHomepageDataForTheme(string $locale): array` 方法
- 方法内部先检查 `get_transient("mindhikers_homepage_data_{$locale}")`
- 缓存 miss 时：调用现有 `findHomepagePostByLocale()` 获取 post，读取 `mindhikers_homepage_payload` meta，decode + normalize，写入 transient（6 小时）
- 新增 `clearHomepageTransient(string $locale): void` 方法，在 `save_post_mh_homepage` 钩子中调用
- 提供全局包装函数 `mindhikers_get_homepage_data(string $locale = 'zh'): array`

**Patterns to follow:**
- 复用现有 `findHomepagePostByLocale()`、`decodeJsonPayload()`、`normalizeHomepagePayload()` 方法
- 缓存键命名：`mindhikers_homepage_data_{$locale}`
- 缓存时间：`6 * HOUR_IN_SECONDS`

**Test scenarios:**
- **Happy path**: 调用 `mindhikers_get_homepage_data('zh')` → 返回规范化数组，包含 hero/about/product/blog/contact 键
- **Happy path**: 第二次调用相同 locale → 从 transient 返回，不触发 DB 查询
- **Edge case**: 无 `mh_homepage` post → 返回 `getDefaultHomepagePayload($locale)`
- **Edge case**: 非法 locale → 按 `sanitizeLocale` 回退到 `'zh'`
- **Integration**: 保存 `mh_homepage` post 后 → 对应 locale 的 transient 被清除，下次请求重新生成

**Verification:**
- `mindhikers_get_homepage_data('zh')` 返回数组且结构与 `normalizeHomepagePayload()` 一致
- WP Admin → 编辑 homepage → 保存后，前台立即显示更新内容（缓存已失效）

---

- [ ] **Unit 2: Make mh_homepage Queryable by Themes**

**Goal:** 修改 `mh_homepage` post type 注册参数，使主题模板可以查询

**Requirements:** R2, R6

**Dependencies:** Unit 1

**Files:**
- Modify: `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php`

**Approach:**
- 修改 `registerPostType()` 中 `register_post_type` 的参数：
  - `'public' => true`（原为 `false`）
  - `'has_archive' => false`（保持）
  - `'rewrite' => false`（避免生成前端 URL）
- 确保 `show_in_rest => true` 不变（REST API 继续工作）
- 验证 `get_posts(['post_type' => 'mh_homepage'])` 在主题中可正常返回结果

**Patterns to follow:**
- 保持其他参数（`show_ui`, `show_in_menu`, `capability_type`, `map_meta_cap`）不变

**Test scenarios:**
- **Happy path**: 主题中 `get_posts(['post_type' => 'mh_homepage'])` 返回已发布的 homepage post
- **Edge case**: 无 published homepage → 返回空数组，不 fatal
- **Integration**: REST API `GET /wp-json/mindhikers/v1/homepage/zh` 继续返回正确 JSON

**Verification:**
- 主题模板能成功查询到 `mh_homepage` post
- REST API 端点响应不变，Next.js 前台不受影响

---

- [ ] **Unit 3: Rewrite Astra Child front-page.php**

**Goal:** 重写首页模板，通过桥接层获取 JSON 数据并传递给 template-parts

**Requirements:** R3, R5

**Dependencies:** Unit 1, Unit 2

**Files:**
- Modify: `wordpress/themes/astra-child/front-page.php`

**Approach:**
- 检测当前语言：`$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh'`
- 调用 `mindhikers_get_homepage_data($lang)` 获取完整 payload
- 将 `$payload` 通过 `$args` 传递给各 template-part：
  - `get_template_part('template-parts/hero', null, ['payload' => $payload])`
  - `get_template_part('template-parts/about', null, ['payload' => $payload])`
  - `get_template_part('template-parts/product', null, ['payload' => $payload])`
  - `get_template_part('template-parts/blog', null, ['payload' => $payload])`
  - `get_template_part('template-parts/contact', null, ['payload' => $payload])`
- 添加空数据兜底：若 payload 为空或关键区块缺失，显示友好提示（不 fatal）

**Patterns to follow:**
- 保持 `get_header()` / `get_footer()` 调用位置
- 保持语言检测逻辑与现有 `pll_current_language()` 一致

**Test scenarios:**
- **Happy path**: 访问首页 → 5 个区块均渲染，内容来自 CMS Core JSON
- **Happy path**: 切换语言（Polylang）→ 显示对应 locale 的 homepage 数据
- **Error path**: `mindhikers_get_homepage_data()` 返回空数组 → 显示 "Homepage data not found" 提示

**Verification:**
- 首页完整渲染，无 fatal error
- 各区块内容与实际 CMS 编辑内容一致

---

- [ ] **Unit 4: Rewrite Hero Template Part**

**Goal:** 将 `template-parts/hero.php` 从 Carbon Fields 切换到 JSON payload

**Requirements:** R3

**Dependencies:** Unit 3

**Files:**
- Modify: `wordpress/themes/astra-child/template-parts/hero.php`

**Approach:**
- 从 `$args['payload']` 读取数据：`$hero = $args['payload']['hero'] ?? []`
- 替换所有 `carbon_get_theme_option()` 调用为数组访问：
  - `carbon_get_theme_option("hero_eyebrow_{$lang}")` → `$hero['eyebrow'] ?? ''`
  - `carbon_get_theme_option("hero_title_{$lang}")` → `$hero['title'] ?? ''`
  - `carbon_get_theme_option("hero_desc_{$lang}")` → `$hero['description'] ?? ''`
  - `carbon_get_theme_option("hero_cta_primary_text_{$lang}")` → `$hero['primaryAction']['label'] ?? ''`
  - `carbon_get_theme_option('hero_cta_primary_url')` → `$hero['primaryAction']['href'] ?? ''`
  - `carbon_get_theme_option("hero_cta_secondary_text_{$lang}")` → `$hero['secondaryAction']['label'] ?? ''`
  - `carbon_get_theme_option('hero_cta_secondary_url')` → `$hero['secondaryAction']['href'] ?? ''`
- 保持所有 HTML 结构和 CSS class 不变

**Patterns to follow:**
- 使用 `??` 提供空字符串兜底
- 继续使用 `esc_html()` / `esc_url()` 做输出转义

**Test scenarios:**
- **Happy path**: Hero 区块渲染 eyebrow、title、description、两个 CTA 按钮
- **Edge case**: payload 中缺少 `hero` 键 → 不渲染内容，不报错
- **Edge case**: `primaryAction.href` 为空 → 不显示主按钮

**Verification:**
- Hero 区块内容与 CMS 编辑的 JSON 中 `hero` 段一致
- 按钮链接和文本正确

---

- [ ] **Unit 5: Rewrite About Template Part**

**Goal:** 将 `template-parts/about.php` 从 Carbon Fields 切换到 JSON payload

**Requirements:** R3

**Dependencies:** Unit 3

**Files:**
- Modify: `wordpress/themes/astra-child/template-parts/about.php`

**Approach:**
- 从 `$args['payload']['about']` 读取数据
- 替换 `carbon_get_theme_option("about_title_{$lang}")` → `$about['title'] ?? ''`
- 替换 `carbon_get_theme_option("about_content_{$lang}")` → 拼接 `$about['intro']` 和 `$about['paragraphs']`
- 注意：JSON 中 about 段结构为 `intro` + `paragraphs[]`，需合并为富文本输出
- 保持 `wp_kses_post()` 过滤

**Patterns to follow:**
- 复用 Hero 模板的数组访问模式

**Test scenarios:**
- **Happy path**: About 区块渲染 title 和多个 paragraph
- **Edge case**: `paragraphs` 为空数组 → 只显示 `intro`
- **Edge case**: `about` 键缺失 → 区块为空

**Verification:**
- About 区块内容与 CMS JSON 一致
- 多段落正确渲染

---

- [ ] **Unit 6: Rewrite Product Template Part**

**Goal:** 将 `template-parts/product.php` 从 Carbon Fields 切换到 JSON payload

**Requirements:** R3

**Dependencies:** Unit 3

**Files:**
- Modify: `wordpress/themes/astra-child/template-parts/product.php`

**Approach:**
- 从 `$args['payload']['product']` 读取区块标题和描述
- 产品列表来源有两种可能：
  - **方案 A（推荐）**：继续使用 `mh_product` CPT 的 `WP_Query`，但把 Carbon Fields 元数据读取改为标准 post meta 或 JSON 数据
  - **方案 B**：如果 `product.items` JSON 数组已包含完整产品信息，可直接遍历 JSON 数组
- 当前 JSON payload 的 `product` 段包含 `featured` 和 `items`（EntryCard 数组），可直接使用
- 替换 `carbon_get_theme_option("product_title_{$lang}")` → `$product['title']`
- 替换 `carbon_get_theme_option("product_desc_{$lang}")` → `$product['description']`
- 产品卡片遍历 `$product['items']` 数组，字段映射：
  - `eyebrow` → 副标题
  - `title` → 产品名
  - `description` → 描述
  - `href` → 链接
  - `ctaLabel` → CTA 文本
  - `meta` → 状态标签

**Patterns to follow:**
- 保持 `mh-product-card-featured` 对第一个 item 的特殊样式
- 保持状态标签颜色逻辑（`meta` 字段包含状态信息）

**Test scenarios:**
- **Happy path**: Product 区块渲染标题、描述、产品卡片网格
- **Happy path**: Featured 产品（第一个 item）有特殊边框样式
- **Edge case**: `product.items` 为空 → 显示 "暂无产品"
- **Edge case**: 单个产品卡片缺少 `href` → 不显示 "了解更多" 链接

**Verification:**
- 产品卡片数量、内容、链接与 CMS JSON 一致
- Featured 产品样式正确

---

- [ ] **Unit 7: Rewrite Blog Template Part**

**Goal:** 将 `template-parts/blog.php` 从 Carbon Fields 切换到 JSON payload

**Requirements:** R3

**Dependencies:** Unit 3

**Files:**
- Modify: `wordpress/themes/astra-child/template-parts/blog.php`

**Approach:**
- 从 `$args['payload']['blog']` 读取区块标题、描述、CTA 配置
- 博客文章列表继续用 `WP_Query(['post_type' => 'post'])` 查询，但最多取 3 篇
- 替换 `carbon_get_theme_option("blog_title_{$lang}")` → `$blog['title']`
- 替换 `carbon_get_theme_option("blog_desc_{$lang}")` → `$blog['description']`
- CTA 按钮使用 `$blog['cta']['label']` 和 `$blog['cta']['href']`
- Empty state 使用 `$blog['emptyLabel']`
- 保留 Polylang 语言过滤逻辑（如果 `pll_get_post_language` 存在）

**Patterns to follow:**
- 复用现有 `WP_Query` + `pll_get_post_language` 过滤模式
- 保持最多显示 3 篇文章的限制

**Test scenarios:**
- **Happy path**: Blog 区块渲染标题、描述、3 篇最新文章卡片
- **Happy path**: CTA 按钮链接到博客列表页
- **Edge case**: 无文章 → 显示 `emptyLabel` 文本
- **Edge case**: `blog` 键缺失 → 区块为空

**Verification:**
- 博客卡片显示正确标题、摘要、日期
- CTA 按钮可点击且链接正确

---

- [ ] **Unit 8: Rewrite Contact Template Part**

**Goal:** 将 `template-parts/contact.php` 从 Carbon Fields 切换到 JSON payload

**Requirements:** R3

**Dependencies:** Unit 3

**Files:**
- Modify: `wordpress/themes/astra-child/template-parts/contact.php`

**Approach:**
- 从 `$args['payload']['contact']` 读取数据
- 替换 `carbon_get_theme_option("contact_title_{$lang}")` → `$contact['title']`
- 替换 `carbon_get_theme_option("contact_desc_{$lang}")` → `$contact['description']`
- 替换 `carbon_get_theme_option('contact_email')` → `$contact['email']`
- 替换 `carbon_get_theme_option("contact_location_{$lang}")` → `$contact['location']`
- 社交链接使用 `$contact['links']` 数组，字段映射：
  - `href` → 链接 URL
  - `label` → 平台名称
  - `note` → 可选备注
- 移除 `carbon_get_theme_option('contact_social_matrix')` 的复杂结构，改用扁平化的 `links` 数组

**Patterns to follow:**
- 复用 Hero 模板的数组访问模式
- 保持社交链接的图标渲染逻辑（取 label 前两个字符）

**Test scenarios:**
- **Happy path**: Contact 区块渲染 title、description、email、location、社交链接
- **Edge case**: `contact.links` 为空 → 不显示社交链接区域
- **Edge case**: `contact.email` 为空 → 不显示 email 卡片

**Verification:**
- Contact 区块内容与 CMS JSON 一致
- 社交链接可点击，图标显示正确

---

- [ ] **Unit 9: Cleanup Carbon Fields Dependency**

**Goal:** 从 Astra Child 主题中移除 Carbon Fields 依赖，保留文件但停用

**Requirements:** R3, R6

**Dependencies:** Unit 4–8

**Files:**
- Modify: `wordpress/themes/astra-child/functions.php`
- Modify: `wordpress/themes/astra-child/lib/carbon-fields.php`

**Approach:**
- 在 `functions.php` 中注释掉或删除 `require_once __DIR__ . '/lib/carbon-fields.php'`
- 保留 `lib/carbon-fields.php` 文件本身（历史数据参考），但使其不再被加载
- 如果 Carbon Fields 插件未激活，当前模板已不依赖它，不会报错
- 更新 `functions.php` 中的 `load_theme_textdomain` 调用，确保文本域正确

**Patterns to follow:**
- 不删除文件，仅移除 `require_once`，保留历史数据可追溯

**Test scenarios:**
- **Happy path**: 主题激活后无 fatal error，即使 Carbon Fields 插件未安装
- **Integration**: 首页完整渲染，无 Carbon Fields 相关 warning

**Verification:**
- WP Admin → 外观 → 主题 → 激活 Astra Child → 无报错
- 前台首页正常显示

---

- [ ] **Unit 10: Cache Invalidation & Bilingual Verification**

**Goal:** 验证缓存失效和双语切换功能完整工作

**Requirements:** R4, R5

**Dependencies:** Unit 1–9

**Files:**
- Modify: `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php`（缓存失效钩子）
- Test: 手动验证

**Approach:**
- 在 `saveHomepageMeta()` 方法末尾调用 `clearHomepageTransient($locale)`
- 在 `handleUpdatedOption()` 中若更新的是 site settings，清除所有 locale 的 transient（或仅清除相关设置缓存）
- 验证双语：
  - 创建/确保存在 `locale=zh` 和 `locale=en` 的两个 `mh_homepage` post
  - 访问 `?lang=zh` 和 `?lang=en`（或通过 Polylang 切换）→ 内容不同
  - 确认两个 locale 的 transient 互相独立

**Patterns to follow:**
- 复用现有 `save_post_mh_homepage` 和 `updated_option` 钩子位置

**Test scenarios:**
- **Happy path**: 编辑中文 homepage → 保存 → 前台中文内容立即更新
- **Happy path**: 编辑英文 homepage → 保存 → 前台英文内容立即更新，中文内容不变
- **Integration**: 6 小时内重复访问 → 从缓存读取，DB 查询次数不增加
- **Edge case**: 同时编辑两个 locale → 两个 transient 均被清除

**Verification:**
- WP Admin 编辑并保存后，前台 5 秒内显示更新内容
- 双语首页内容互不干扰
- Query Monitor 或日志显示缓存命中

## System-Wide Impact

- **Interaction graph**：
  - CMS Core 新增 `getHomepageDataForTheme()` 方法，被主题模板调用
  - `save_post_mh_homepage` 钩子新增缓存清除逻辑
  - `updated_option` 钩子新增 site settings 缓存清除
- **Error propagation**：
  - 若 `json_decode` 失败 → 返回默认 payload（空数组经 normalize 后），不 fatal
  - 若 `get_posts()` 返回空 → 返回默认 payload，模板显示空状态
  - 若 transient 写入失败 → 降级为无缓存，每次请求查 DB，功能正常
- **State lifecycle risks**：
  - 缓存清除必须在 `save_post` 成功后执行，避免保存失败但缓存已清
  - 多 locale 缓存键独立，避免清除一个 locale 影响另一个
- **API surface parity**：
  - REST API `mindhikers/v1/homepage/{locale}` 行为完全不变
  - Next.js 前端不受影响
- **Integration coverage**：
  - 主题切换场景：任何主题调用 `mindhikers_get_homepage_data()` 即可获取数据
  - Polylang 兼容：主题层继续使用 `pll_current_language()`，CMS Core 通过 `locale` meta 查询
- **Unchanged invariants**：
  - `mindhikers/v1/homepage/{locale}` REST 端点响应格式不变
  - `mindhikers/v1/site-settings` 端点不变
  - Next.js 前端构建和渲染逻辑不变
  - `mh_homepage` post type 的 admin UI 和编辑流程不变
  - JSON payload 的结构和规范化逻辑不变

## Risks & Dependencies

| Risk | Mitigation |
|------|------------|
| `public => true` 意外暴露前端路由 | `rewrite => false` + `has_archive => false`，不生成公开 URL |
| JSON 结构变更导致模板报错 | 模板中使用 `??` 空合并运算符提供兜底值 |
| 缓存未失效导致前台显示旧内容 | 在 `save_post` 钩子中强制清除对应 locale transient |
| 多主题切换时数据不兼容 | 桥接层提供全局函数，任何主题均可调用 |
| Production 部署困难（P1 架构债） | 本计划代码需通过 Code Snippets 或 ZIP 上传部署，需单独安排部署窗口 |
| Carbon Fields 停用后历史数据不可见 | 保留 `lib/carbon-fields.php` 文件，仅停止加载，历史数据在 WP Admin 中仍可通过 Carbon Fields 插件查看 |
| Polylang 与 `mh_homepage` locale meta 冲突 | 主题层以 `pll_current_language()` 为准，CMS Core 以 `locale` meta 为准，两者需保持映射一致（zh↔zh-CN, en↔en-US） |

## Documentation / Operational Notes

- **部署方式**：当前 Railway WP 容器不自动拉取仓库代码，实施完成后需：
  1. 将修改后的 `bootstrap.php` 通过 Code Snippets 或 WP Admin 插件上传方式部署到 production
  2. 将修改后的 Astra Child 主题文件通过 WP Admin → 外观 → 主题上传 ZIP 部署
- **回滚方案**：
  - CMS Core：保留原 `bootstrap.php` 备份，通过 Code Snippets 切换
  - 主题：WP Admin → 外观 → 切换回其他主题（如 Astra 父主题）
- **监控**：
  - 部署后检查 `mindhikers/v1/homepage/zh` REST 端点响应是否正常（Next.js 依赖）
  - 检查首页各区块是否渲染完整
- **后续工作**：
  - P1 架构债：建立 git → WP 容器的自动部署通道
  - P2 架构债：评估是否将 m1-rest 逻辑合并到 CMS Core

## Sources & References

- **Origin document:** [docs/plans/wp-traditional-mode-assessment.md](docs/plans/wp-traditional-mode-assessment.md)
- **Related code:**
  - `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php`
  - `wordpress/themes/astra-child/functions.php`
  - `wordpress/themes/astra-child/front-page.php`
  - `wordpress/themes/astra-child/template-parts/*.php`
- **Related handoff:** [docs/dev_logs/HANDOFF.md](docs/dev_logs/HANDOFF.md)
- **Related rules:** [docs/rules.md](docs/rules.md)
- **Related domain boundary:** [docs/domain-boundary.md](docs/domain-boundary.md)

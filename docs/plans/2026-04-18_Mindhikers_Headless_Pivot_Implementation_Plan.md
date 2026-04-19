---
title: "Mindhikers Homepage M1-R 实施方案 — Headless Hybrid Pivot"
type: implementation-plan
status: draft — 待老卢审核
date: 2026-04-18
last_revised: 2026-04-19
milestone: M1-R
target_audience: "后续接手的 AI 编码端（codex / opencode）或工程师"
prd: docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md
origin: docs/brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md
supersedes_section: "docs/plans/2026-04-12-001-feat-m1-cms-content-model-plan.md § Implementation Units 的前台渲染部分"
author: "OldYang 代 CE 团队起草"
reviewer: 老卢
linear: MIN-8 / MIN-110
estimate: 6.5 工作日（M1-R，含 Blog 切 WP）+ 1 工作日（生产切换演练）
---

# Mindhikers Homepage M1-R 实施方案 — Headless Hybrid Pivot

> 本方案面向外包团队与接手工程师。不默认你了解项目历史，所有必要上下文都在本文档或其直接引用中。
> 如遇未定义行为，优先读 `docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md` PRD 修订版，其次问老卢。

---

## 0. 一页读懂本方案

### 0.1 要解决什么

当前 `mindhikers.com` 线上是 Next.js 静态站，视觉与品牌调性到位，但所有内容硬编码在 `src/data/site-content.ts`，主理人老卢**不能自己改**。过去 4 周尝试迁到 WordPress PHP 模板站，但前台视觉简陋，无法达到上线门槛。

### 0.2 方案主线

**Headless Hybrid**：前台保留 Next.js（继承现有线上视觉 100%），后台改用 WordPress（主理人编辑），两者通过 REST API + ISR 缓存打通。

### 0.3 总工时预估

| 阶段 | 工时 |
|---|---|
| Unit 0 前置门（环境/凭据/读代码） | 0.5 天 |
| Unit 1 WP REST endpoint 实装（含 Blog 列表 & 详情） | 1.5 天 |
| Unit 2 Next.js 数据契约对齐 | 0.5 天 |
| Unit 3 前台五区块数据打通 + ISR + 兜底 | 1.0 天 |
| Unit 4 导航补齐 + 产品详情 + **Blog 切 WP Posts** | 1.0 天 |
| Unit 5 Contact 模块打通（含社交矩阵图片） | 0.5 天 |
| Unit 6 双语端到端验证 + MDX 分支封存 | 0.5 天 |
| Unit 7 revalidate webhook 打通（含 Blog hooks） | 0.5 天 |
| Unit 8 M1-R Smoke 验收 + 交付 | 0.5 天 |
| **M1-R 小计** | **6.5 工作日** |
| Unit 9 生产切换演练（M1-R Release，基于现有 Railway） | 1.0 天 |

### 0.4 关键不变量

- **不改** `src/components/home-page.tsx` 的视觉（已是目标基线）
- **不删** 任何现有文件；路线淘汰的代码打 legacy 标签封存
- **不动** 生产 DNS 直到 M1-R 全部验收通过

---

## 1. 目标架构（一图看懂）

```
┌──────────────────────────────────────────────────────────────┐
│ 访客浏览器                                                    │
└──────────────────┬───────────────────────────────────────────┘
                   │ HTTPS
                   ▼
┌──────────────────────────────────────────────────────────────┐
│ Cloudflare CDN                                                │
│   www.mindhikers.com  →  Next.js (Railway or Vercel)          │
│   homepage-manage.mindhikers.com  →  WordPress (Railway)      │
│                                       ↑ Cloudflare Access     │
└──────────────────┬───────────────────────────────────────────┘
                   │
     ┌─────────────┴────────────────┐
     ▼                              ▼
┌──────────────────┐       ┌──────────────────────────┐
│ Next.js 16       │       │ WordPress + Astra        │
│ - React 19       │       │ - Carbon Fields (字段)   │
│ - Tailwind 4     │       │ - Polylang (双语)        │
│ - ISR 缓存 300s  │       │ - mh_product CPT         │
│ - fallback 兜底  │       │ - WP Posts (Blog)        │
│                  │       │ - Media Library          │
│ 渲染 / SEO / 路由│       │ /wp-json/mindhikers/v1/* │
└────────┬─────────┘       └────────┬─────────────────┘
         │                          │
         │  GET /wp-json/            │
         │  mindhikers/v1/homepage/  │
         │  {zh|en}                  │
         ├──────────────────────────>│
         │  ← JSON (HomeContent)     │
         │                           │
         │  POST /api/revalidate     │
         │<──────── webhook ─────────┤
         │  (WP 字段保存后触发)      │
         ▼                           ▼
    构建时若 WP 不可达 → 用 src/data/site-content.ts 作 build-time fallback
    运行时若 WP 5xx   → 继续用上一次成功拉取的 ISR 缓存 / 静态 fallback
```

---

## 2. 代码仓库 / 环境 / 凭据（外包上机第一步）

### 2.1 Git 仓库

- **地址**：`https://github.com/freestone16/Mindhikers-Home`
- **主分支**：`main`
- **当前 HEAD**：本方案基于 commit `bb8635e` 或之后
- **新功能分支命名**：`feat/headless-pivot-<unit-number>-<short-desc>`
- **提交规范**：`refs MIN-110 <变更摘要>`；严禁直接推 `main`，所有变更走 PR

### 2.2 本地开发环境

| 要求 | 版本 |
|---|---|
| Node.js | >= 20.9.0 |
| pnpm | >= 9.x |
| Git | >= 2.40 |
| PHP（读 WP 插件用，不必本地跑 WP） | >= 8.1 |
| Railway CLI（可选） | latest |

**一键启动前台**：
```bash
cd Mindhikers-Homepage
pnpm install
cp .env.example .env.local
# 填好 WORDPRESS_API_URL 与 REVALIDATE_SECRET
pnpm dev
# 打开 http://localhost:3000
```

**不需要本地跑 WordPress**：外包团队直接对接 staging WP（`wordpress-l1ta-staging.up.railway.app`），通过 SSH/SFTP 或 WP Admin 修改主题文件。

### 2.3 凭据清单（由老卢统一下发）

| 凭据 | 用途 | 交付方式 |
|---|---|---|
| WP Admin 账号（`mindhikers_admin` + 密码） | 后台登录 | 1Password / 加密邮件 |
| Railway CLI token | 部署 / 日志 | 同上 |
| Cloudflare API token（DNS + Pages） | DNS 切换 / CDN | 同上 |
| GitHub 协作者权限 | push / PR | 老卢在 GitHub 加人 |
| Cloudflare Access 访问权限 | 后台访问 | 老卢加邮箱到 Access Policy |

**安全要求**：
- 任何凭据不入仓
- `.env.local` 必须在 `.gitignore` 中（已在）
- `REVALIDATE_SECRET` 首次由外包生成（32 字节随机），同步给老卢一份

---

## 3. 技术栈定型（v2 口径）

### 3.1 前台

| 技术 | 版本 | 角色 |
|---|---|---|
| Next.js | 16.x | App Router / SSG + ISR |
| React | 19.x | 视图层 |
| TypeScript | 5.x | 类型系统 |
| Tailwind CSS | 4.x | 样式 |
| Content Collections | latest | MDX blog 源（短期兼容） |
| shadcn/ui + Magic UI | latest | 组件 |
| Lucide Icons | latest | 图标 |

### 3.2 后台

| 技术 | 版本 | 角色 |
|---|---|---|
| WordPress | 6.x | CMS core |
| Astra | latest | 主题框架（仅后台 UI 保留，前台不渲染） |
| Carbon Fields | 3.6.9 | Theme Options + CPT meta |
| Polylang Free | 3.8.2 | 双语关系 |
| Astra Child | 自研 | 字段注册 + endpoint 宿主（本方案不再承担前台渲染） |

### 3.3 部署（2026-04-19 锁定）

| 层 | 方案 | 说明 |
|---|---|---|
| Next.js 前台 staging | **现有 Railway 服务**（已部署） | 不另起炉灶 |
| Next.js 前台 production | **现有 Railway 服务**（已部署） | 不另起炉灶 |
| WordPress 后台 | **现有 Railway 服务**（已部署） | 不另起炉灶 |
| CDN | Cloudflare | 保持 |

**硬约束**：本方案禁止引入新的部署平台（Vercel / 新建 Railway 项目等）。所有改动通过现有 Railway 服务的 `git push` 触发或手动部署。

---

## 4. 实施单元（Units）

> 每个 Unit 自包含：Goal / Dependencies / Files / Approach / Test / Verification / Rollback。
> 外包团队按序执行，严禁跳 Unit。

### Unit 0：前置门 — 环境、凭据、代码理解 Gate

**Goal**：外包团队完成环境搭建、凭据拿到、关键文件读过，能独立跑起 `pnpm dev`，能登录 staging WP 后台。

**Dependencies**：无

**交付物**：
- 外包侧本地 `http://localhost:3000` 可见 `/`（通过 fallback 渲染）
- 外包侧可登录 staging WP 后台
- 外包侧提交 "环境就绪" 简报（邮件 / Linear 评论）含：本地 Node 版本、WP Admin 登录截图、可读取 `src/components/home-page.tsx` 的确认

**Files to read**：
1. `docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md`（PRD）
2. `docs/brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md`（v1 PRD，继承内容）
3. `src/data/site-content.ts`（HomeContent 类型 + 基线数据）
4. `src/components/home-page.tsx`（目标视觉）
5. `src/lib/cms/homepage.ts`（已有拉取逻辑）
6. `src/lib/cms/constants.ts`（WP 配置）
7. `wordpress/mu-plugins/mindhikers-m1-core.php`（字段定义）

**Verification**：
- ✅ `pnpm dev` 无报错
- ✅ WP Admin 看到 Hero 管理 / About 管理 / Contact 管理菜单
- ✅ 本文档理解无 open questions（有问必须在本 Unit 结束前解决）

**工时**：0.5 天

---

### Unit 1：WP 端 REST Endpoint 实装（含 Blog 列表与详情） ⭐

**Goal**：WP 端实现以下 endpoint，全部返回严格对齐 TypeScript 类型的 JSON：

1. `GET /wp-json/mindhikers/v1/homepage/{zh|en}` — 首页五区块聚合
2. `GET /wp-json/mindhikers/v1/product/{slug}?lang=zh|en` — 产品详情
3. `GET /wp-json/mindhikers/v1/blog?lang=zh|en&page=1&per_page=10&category=<slug>` — 博客列表（分页）
4. `GET /wp-json/mindhikers/v1/blog/{slug}?lang=zh|en` — 博客详情（含正文 HTML、封面图、分类、日期）

**Dependencies**：Unit 0 通过

**Files**：
- **修改**：`wordpress/mu-plugins/mindhikers-m1-core.php`（在现有 `m1_register_*` 后追加 REST 路由注册）
- **新建**：`wordpress/mu-plugins/mindhikers-cms-core/rest-homepage.php`（实际 handler 文件，避免主入口膨胀）
- **新建**：`docs/plans/schemas/homepage.schema.json`（JSON Schema，供外包自检）

**Approach**：
```php
add_action('rest_api_init', function() {
    register_rest_route('mindhikers/v1', '/homepage/(?P<locale>zh|en)', [
        'methods'  => 'GET',
        'callback' => 'mh_rest_homepage',
        'permission_callback' => '__return_true',
    ]);
});

function mh_rest_homepage(WP_REST_Request $req) {
    $locale = $req['locale']; // 'zh' | 'en'
    return [
        'locale'     => $locale,
        'metadata'   => mh_build_metadata($locale),
        'navigation' => mh_build_navigation($locale),
        'hero'       => mh_build_hero($locale),
        'about'      => mh_build_about($locale),
        'product'    => mh_build_product_section($locale),
        'blog'       => mh_build_blog_section($locale),
        'contact'    => mh_build_contact($locale),
    ];
}
```

**字段对齐清单**（严格参照 `src/data/site-content.ts` 的 `HomeContent` 类型）：

Hero：`eyebrow / title / description / primaryAction{href,label} / secondaryAction{...} / highlights[] / statusLabel / statusValue / availabilityLabel / availabilityValue / panelTitle`

About：`title / intro / paragraphs[] / notes[]`

Product：`title / description / headline / featured{eyebrow,title,description,href,ctaLabel,meta} / items[]`

Blog：`title / description / headline / cta{href,label} / readArticleLabel / emptyLabel`

Contact：`title / headline / description / email / locationLabel / location / availabilityLabel / availability / links[]{href,label,note}`

Navigation：`brand / links[]{href,label} / switchLanguage{href,label}`

**数据来源映射**：

| 字段 | 来源 | 说明 |
|---|---|---|
| Hero / About / Contact 文本 | `carbon_get_theme_option("<key>_<locale>")` | 已存在 |
| Navigation brand / links / switchLanguage | 硬编码常量（与 `site-content.ts` 同源） | `links` 应生成到 `#about #product #blog #contact` |
| Product featured / items | `WP_Query(['post_type' => 'mh_product', 'lang' => $locale])` | featured 由 `product_is_featured = true` 决定 |
| Blog 列表 | `WP_Query(['post_type' => 'post', 'lang' => $locale, 'posts_per_page' => 3])` | 按 `post_date` 倒序 |
| Contact `links[]` | Carbon `contact_social_matrix` Complex field → `{platform_url, platform_name, note}` | 微信公众号条目的 `href` 允许指向二维码图片 URL |
| Metadata title / description | Carbon Fields 或落盘常量 | 与 site-content.ts 一致 |

**Test scenarios**：
- Happy：`curl https://<wp-host>/wp-json/mindhikers/v1/homepage/zh | jq .hero.title` 返回正确字符串
- Happy：所有字段存在，无 `null` / 缺字段
- Edge：某产品无 EN 翻译 → EN endpoint 该产品不出现
- Error：未知 locale → 400
- Perf：p95 < 500ms（可通过 transient cache 优化）

**Verification**：
- ✅ JSON Schema 校验通过
- ✅ Next.js 本地 `pnpm dev` 切 `WORDPRESS_API_URL=https://wordpress-l1ta-staging.up.railway.app` 后，`/` 首屏文本来自 WP 而非 fallback

**Rollback**：WP 侧回滚 PHP 文件；Next.js 侧自动 fallback 到静态数据，不停机。

**工时**：1.5 天（含 Blog 列表、Blog 详情、分类过滤、Polylang 可选翻译过滤）

---

### Unit 2：Next.js 端数据契约与类型守卫

**Goal**：强化 `src/lib/cms/homepage.ts` 的运行时校验，确保 WP 返回的字段结构异常时自动降级，不抛错。

**Dependencies**：Unit 1 基本形态可用

**Files**：
- 修改：`src/lib/cms/homepage.ts`（增强 `isHomeContentReady` 覆盖 product / about / blog / contact / navigation.links）
- 修改：`src/lib/cms/constants.ts`（增加 WP 超时 5s）
- 新建：`src/lib/cms/__tests__/homepage.test.ts`（可选单元测试）

**Approach**：
- 现有 `isHomeContentReady` 仅校验 metadata / navigation / hero，扩展到所有顶层字段
- fetch 增加 `AbortSignal.timeout(5000)`
- 日志级别：WP 错误用 `console.warn`（不喷 error）

**Test scenarios**：
- Happy：完整 payload → 返回 WP 数据
- Edge：缺字段 / 类型错乱 → fallback 到 `getHomeContent(locale)`
- Edge：WP 超时 → fallback，日志 warn
- Edge：WP 5xx → fallback

**Verification**：
- ✅ 手动 stop 掉 WP staging → 本地 `pnpm dev` 仍渲染首页（用 fallback）
- ✅ 手动改 WP 返回字段残缺 → 前台仍渲染，且从 WP 降级到 fallback

**工时**：0.5 天

---

### Unit 3：前台五区块数据打通 + ISR + 构建期 fallback

**Goal**：`/` 和 `/en` 走 WP endpoint 实装 ISR 缓存，构建期 WP 不可达时使用 build-time fallback 不阻塞构建。

**Dependencies**：Unit 1、Unit 2

**Files**：
- 修改：`src/app/page.tsx`（已调用 `getManagedHomeContent("zh")`，核对 `generateMetadata` 一致）
- 修改：`src/app/en/page.tsx`（同上改为 `"en"`，**若不存在则新建**）
- 修改：`src/lib/cms/homepage.ts` 增加 `next: { revalidate: 300, tags: ['homepage-zh' | 'homepage-en'] }`
- 新建：`src/app/api/revalidate/route.ts`（POST + secret 校验，调用 `revalidateTag`）

**Approach**：
```ts
// src/app/api/revalidate/route.ts
import { revalidateTag } from 'next/cache';
import { NextRequest, NextResponse } from 'next/server';

export async function POST(req: NextRequest) {
  const secret = req.headers.get('x-revalidate-secret');
  if (secret !== process.env.REVALIDATE_SECRET) {
    return NextResponse.json({ ok: false }, { status: 401 });
  }
  const { tag } = await req.json();
  revalidateTag(tag);
  return NextResponse.json({ ok: true, tag });
}
```

**Test scenarios**：
- Happy：`curl -X POST .../api/revalidate -H 'x-revalidate-secret: XXX' -d '{"tag":"homepage-zh"}'` → 下次访问 `/` 重新拉 WP
- Edge：错误 secret → 401
- Edge：构建期 WP 不可达 → `pnpm build` 不失败

**Verification**：
- ✅ 修改 WP Hero 标题 → 等 300s（或触发 revalidate） → 前台更新
- ✅ `pnpm build` 在 WP 离线时仍成功（使用 fallback）

**工时**：1.0 天

---

### Unit 4：导航补齐 + 产品详情 + **Blog 切换到 WP Posts（一次到位）**

**Goal**：
1. 补齐顶部导航 5 项（About / Product / Blog / Contact / EN 切换）
2. 产品详情页接通 WP CPT
3. **Blog 列表与详情一次性切换到 WP Posts，MDX 分支封存**

**Dependencies**：Unit 3，Unit 1 的 Blog endpoint 可用

**Files**：
- 修改：`src/data/site-content.ts` 的 `navigation.links` 改为 5 项
- 核对：`src/components/navbar.tsx` 消费方式不变
- 新建 / 修改：`src/app/product/[slug]/page.tsx` 从 WP `/wp-json/mindhikers/v1/product/{slug}?lang=zh` 拉取
- 新建 / 修改：`src/app/en/product/[slug]/page.tsx` 同上，`lang=en`
- **修改：`src/app/blog/page.tsx`** 从 `/wp-json/mindhikers/v1/blog?lang=zh` 拉取列表（分页、分类过滤）
- **修改：`src/app/en/blog/page.tsx`** 同上，`lang=en`
- **修改：`src/app/blog/[slug]/page.tsx`** 从 `/wp-json/mindhikers/v1/blog/{slug}?lang=zh` 拉取详情
- **修改：`src/app/en/blog/[slug]/page.tsx`** 同上
- **修改：`src/lib/posts.ts`** 新增 `getBlogListFromWP()` / `getBlogPostFromWP()`
- **修改：`src/lib/cms/index.ts`** `getRecentPosts(n)` 切换默认源为 WP，MDX 仅作 fallback
- **修改：`.env.example`** 增加注释：`BLOG_SOURCE=wp`（M1-R 默认）
- **封存**：`content/blog/*.mdx` 文件不删，但首页与列表页不再读取；在文件头顶加 `ARCHIVED: 2026-04-19` 注释

**Approach**：
- 导航 5 项：`/#about`, `/#product`, `/blog`, `/#contact`（中文）；`/en/#about` 等（英文）
- Blog 列表：WP 返回 `{ items: [...], total, page, perPage, categories: [...] }` 结构
- Blog 详情：`post_content` 经 WP 渲染后的 HTML + 主分类 + 次级分类 + 封面图 + 发布时间 + 作者（固定老卢）
- 现有 3 篇 MDX 博客：M1-R 实施开始前由老卢或接手端一次性导入 WP Posts（保留原发布时间 / 分类 / 封面图）
- EN Blog：Polylang 过滤，EN 列表只显示已翻译文章；EN 详情访问未翻译 slug 时 `notFound()`

**Test scenarios**：
- Happy：导航 5 项点击跳转正确
- Happy：`/product/golden-crucible` 显示 WP 中文数据；`/en/product/golden-crucible` 显示 EN
- Happy：`/blog` 显示 WP 博客列表（分页、按发布时间倒序）
- Happy：`/blog/{slug}` 显示 WP 博客详情（含正文、分类、封面、日期）
- Happy：`/en/blog` 只列出有 EN 版本的文章
- Edge：产品 / 博客无 EN 翻译 → EN 页面 `notFound()`
- Edge：WP 不可达 → 列表使用上次 ISR 缓存；详情页若无缓存则兜底 500 页面（非 unhandled error）

**Verification**：
- ✅ 导航 5 项全部可见且功能
- ✅ 产品详情页字段齐全
- ✅ Blog 列表与详情完全不再读取 `content/blog/*.mdx`（可用构建日志或代码扫描确认）
- ✅ 老卢在 WP 后台新发一篇博客 → 30 秒内 `/blog` 出现

**工时**：1.0 天

---

### Unit 5：Contact 模块 + 社交矩阵图片

**Goal**：Contact 区块 email / location / 社交矩阵（含微信二维码图片）通过 WP 拉取并正确渲染。

**Dependencies**：Unit 1、Unit 3

**Files**：
- 修改：`src/components/home-page.tsx` Contact 区块 `contact.links` 消费方式——如果需要支持二维码图片预览，扩展 `ContactLink` 类型加 `qrImage?: string`
- 修改：`src/data/site-content.ts` `HomeContent.contact.links[]` 增加 `qrImage?`
- 修改：`src/lib/cms/homepage.ts` 映射 WP Carbon `contact_social_matrix` 的 `platform_qr_image`

**Approach**：
- 微信条目：`{ href: '#', label: '微信公众号', note: '扫码关注', qrImage: '<url>' }`
- 前台点击微信卡片弹出 QR 图片（modal 或 popover）
- Twitter / Bilibili 无 `qrImage`，照旧外链跳转

**Test scenarios**：
- Happy：3 个社交条目可见
- Happy：微信条目显示"扫码关注" + 点击弹图
- Edge：社交矩阵条目为空 → Contact 右侧区块优雅降级

**Verification**：
- ✅ 三个社交条目按 WP 后台顺序排列
- ✅ 新增 / 删除条目后前台同步

**工时**：0.5 天

---

### Unit 6：双语端到端验证 + SEO 收口 + MDX 分支封存

**Goal**：
1. `/` 和 `/en` 五区块数据严格对等，meta 元素双语正确，`og:locale` 等字段到位
2. 确认 `content/blog/*.mdx` 不再被任何 runtime 代码引用（可留在仓库作为历史）

**Dependencies**：Unit 1–5

**Files**：
- 修改：`src/app/layout.tsx` 的 metadata 基础值
- 核对：`src/app/page.tsx` / `src/app/en/page.tsx` 的 `generateMetadata`
- 核对：`src/app/opengraph-image.tsx`

**Approach**：
- 每个页面 `generateMetadata` 注入 locale-aware 标题 / 描述 / alternates
- `alternates.languages` 声明 zh-Hans / en 双版本
- `og:locale` + `og:locale:alternate`

**Test scenarios**：
- Happy：`view-source:/` 的 `<title>` 是中文
- Happy：`view-source:/en` 的 `<title>` 是英文
- Happy：`alternates` 标签互指
- Edge：Google Rich Results Test 通过

**Verification**：
- ✅ Layer B 视觉对照表打勾（全部）
- ✅ SEO 元素双语正确

**工时**：0.5 天

---

### Unit 7：Revalidate Webhook 打通（WP → Next.js，含 Blog）

**Goal**：主理人在 WP 后台保存 Hero / About / Contact / 产品 / **博客文章** 后，自动触发 Next.js revalidate，前台 30 秒内更新。

**Dependencies**：Unit 3

**Files**：
- 修改：`wordpress/mu-plugins/mindhikers-m1-core.php` 注册 hooks：`carbon_fields_theme_options_container_saved` + `save_post_mh_product` + `save_post` (post)
- 新建 / 修改：`wordpress/mu-plugins/mindhikers-cms-core/revalidate.php`
- Next.js 侧：Unit 3 已实装 `/api/revalidate`

**Approach**：
```php
add_action('carbon_fields_theme_options_container_saved', function($id) {
    mh_trigger_revalidate(['homepage-zh', 'homepage-en']);
});
add_action('save_post_mh_product', function($post_id) {
    mh_trigger_revalidate(['homepage-zh', 'homepage-en', "product-{$post_id}"]);
});
add_action('save_post_post', function($post_id) {
    mh_trigger_revalidate(['homepage-zh', 'homepage-en', 'blog-zh', 'blog-en', "blog-{$post_id}"]);
});

function mh_trigger_revalidate(array $tags) {
    $url = get_option('mh_nextjs_revalidate_url'); // 'https://www.mindhikers.com/api/revalidate'
    $secret = get_option('mh_revalidate_secret');
    foreach ($tags as $tag) {
        wp_remote_post($url, [
            'headers' => ['x-revalidate-secret' => $secret, 'Content-Type' => 'application/json'],
            'body'    => wp_json_encode(['tag' => $tag]),
            'timeout' => 3,
        ]);
    }
}
```

- Secret 存 `wp_options`（由 admin 配置页录入），不硬编码
- 失败不阻塞 WP 保存流程（timeout 3s）

**Test scenarios**：
- Happy：改 Hero 保存 → 30 秒内 `/` 刷新
- Happy：新建产品 → 30 秒内 `/product/...` 可见
- Edge：Next.js 不可达 → WP 保存仍成功，只是前台延迟 300s 自然过期
- Edge：Secret 错误 → Next.js 401，WP 日志记录

**Verification**：
- ✅ 连续 5 次 Hero 修改全部在 60s 内反映
- ✅ WP 错误日志无 warning / error

**工时**：0.5 天

---

### Unit 8：M1-R Smoke 验收 + 文档交付

**Goal**：主理人独立完成 M1-R 验收清单，文档交付齐全。

**Dependencies**：Unit 0–7

**Files**：
- 新建：`docs/testing_reports/2026-<date>_M1R_Smoke_Report.md`
- 修改：`docs/dev_logs/HANDOFF.md`（覆盖写，按治理协议前两行要时间戳 + 分支）
- 修改：`docs/operations-guide.md`（更新内容）
- 新建：`docs/plans/2026-04-18_M1R_Acceptance_Checklist.md`（本方案 §5 的打勾表）

**Approach**：
- 外包 QA 跑完 Layer A（功能）+ Layer B（视觉）所有检查项
- 主理人老卢独立执行一次"改 Hero + 新增产品 + 改分类 + 改社交矩阵"完整流程
- 所有项目通过后外包提交 M1-R 验收报告

**交付物**：
- Layer A + Layer B 全部打勾的 Markdown 表
- staging 5 区块中英文截图各 1 张（共 10 张）
- 线上 mindhikers.com 对照截图各 1 张
- 维护手册：`docs/operations-guide.md` 最新版
- 已知限制与 TODO 清单

**Verification**：
- ✅ 老卢签字确认 "M1-R 通过"

**工时**：0.5 天

---

### Unit 9：生产切换演练（M1-R Release，基于现有 Railway 服务）

**Goal**：将现有 Railway production Next.js 服务切到 Headless 版本（新 commit / 新环境变量），DNS 不变或仅微调，保留 10 分钟回滚能力。

**前提**：本项目 staging 与 production 的 Next.js 与 WordPress 均已在 Railway 部署。切换不涉及迁平台，仅涉及：
- production Next.js 的代码从旧版（`site-content.ts` 硬编码）更新为 Headless 版本
- production Next.js 的环境变量更新（`WORDPRESS_API_URL` 指向 production WP）
- DNS 若已指向 Railway Next.js 服务则不动；否则切到该服务

**Dependencies**：Unit 8 验收通过 + 老卢下达切换口令

**Pre-flight Checklist**（切换前 24 小时）：
- [ ] staging Layer A + B 全部通过
- [ ] DNS TTL 降至 60s 预热
- [ ] 旧 Next.js 静态站仍在线且可 CNAME 切回
- [ ] Cloudflare Access 已配 `homepage-manage.mindhikers.com` 白名单
- [ ] 老卢 + 外包 + 老杨三方在线

**切换步骤**：
1. Railway production Next.js 服务：更新环境变量 `WORDPRESS_API_URL` 指向 production WP + `REVALIDATE_SECRET`
2. Railway 触发部署（`git push` 到 production 分支 / 手动 deploy）
3. 部署完成后清 Cloudflare 缓存（purge everything）
4. 5 分钟 Smoke：`curl https://www.mindhikers.com/`、`/en`、`/blog`、`/product/golden-crucible`、`/api/revalidate` POST
5. 主理人在新前台执行一次"改 Hero 标题"闭环验证
6. 15 分钟静默观察（Railway metrics + Cloudflare Analytics + 错误率）
7. 成功则锁定；出事则执行 Rollback

**Rollback（任何一步失败）**：
1. Railway production 服务：一键回滚到上一个 deployment（Railway 原生支持）
2. Cloudflare purge
3. 通知老卢 + 老杨召开故障会

**Post-flight Checklist**：
- [ ] `robots.txt` 移除 noindex（仅 production）
- [ ] Google Search Console 重新提交 sitemap
- [ ] `docs/dev_logs/HANDOFF.md` 更新
- [ ] DNS TTL 恢复正常（300s / 3600s）

**工时**：1.0 天

---

## 5. M1-R 验收清单（打勾表）

### Layer A：后台功能（外包 QA + 老卢双确认）

- [ ] WP Admin 侧边栏 Hero 管理 / About 管理 / Contact 管理菜单存在
- [ ] Carbon Fields 字段保存后 300s 或手动 revalidate 30s 内生效
- [ ] `mh_product` CPT 可新建 / 编辑 / 删除，5 种状态可选
- [ ] Polylang 中英翻译关系可建立，EN 产品前台独立详情
- [ ] Blog 双层分类可选，文章归类后分类 archive 可访问
- [ ] Contact 社交矩阵可增删排序，微信二维码图片可上传
- [ ] Media Library 正常使用
- [ ] 主理人独立完成"改 Hero + 新增产品 + 改分类 + 改社交矩阵"完整流程 ≤ 15 分钟

### Layer B：前台视觉（对照 `mindhikers.com` 线上基线）

- [ ] Header 左上 Logo + 品牌名
- [ ] Header 玻璃态圆角栏 + 5 项导航 + EN/ZH 切换
- [ ] Hero 左栏 eyebrow + 大标题 + 描述 + 双 CTA + highlights 药丸
- [ ] Hero 右栏 品牌头卡 + 2 个 InfoPill + Homepage blocks 快捷导航
- [ ] About 左卡 + 右 notes 列表
- [ ] Product Featured 大卡片 + 3 个小卡片
- [ ] Blog 3 列卡片 + 空状态兜底
- [ ] Contact 左主卡邮箱 CTA + 右位置 / availability / 社交卡片
- [ ] Footer 版权 + 双语 + 社交矩阵同源
- [ ] 响应式：手机竖屏五区块可读
- [ ] `/` 全中文 / `/en` 全英文，无中英混排
- [ ] `<title>` / `meta description` / `og:*` / `twitter:*` / `alternates` 双语正确

### Layer C：健壮性

- [ ] WP 关机 5 分钟后前台仍可访问（fallback 生效）
- [ ] Next.js 构建期 WP 不可达仍能 build
- [ ] 首页 p95 TTFB < 300ms（CDN 命中）
- [ ] 任何 PHP error / fatal 为 0
- [ ] Next.js error log 为 0

---

## 6. 风险与回退

| 风险 | 缓解 | 回退 |
|---|---|---|
| WP endpoint 字段命名与 Next.js 类型不对齐 | Unit 2 类型守卫 + JSON Schema | fallback 到 `site-content.ts` |
| Cloudflare Access 误拒老卢访问 | 部署前双人验证 + 应急 Cloudflare API token | Access 临时关闭（仅 5 分钟） |
| DNS 切换触发 Cloudflare Universal SSL 复签异常 | 切换前确保 SSL 证书已签发 | DNS 切回旧主机 |
| 生产切换后首屏慢 | ISR 预热脚本（切换前 5 分钟跑一遍 `/` `/en`） | 手动触发 revalidate |
| revalidate webhook 被恶意调用 | Secret 必须 32 字节随机 + 每 90 天轮换 | 轮换 secret |
| 接手端（codex/opencode）交付后维护断层 | 文档交付齐全 + 维护手册 + 老卢录屏 1 次 | 老杨承接治理 |
| Blog 从 MDX 切 WP 期间文章丢失 | Unit 4 前 3 篇 MDX 文章手动导入 WP 并核对后再切；`content/blog/*.mdx` 不删只封存 | 环境变量 `BLOG_SOURCE=mdx` 临时回退 |

---

## 7. 部署 Runbook（外包与主理人共用）

### 7.1 前台部署（Railway — 现有服务）

本项目已有 staging 与 production 两个 Next.js Railway 服务。**不新建 Railway 项目 / 不迁 Vercel**。

```bash
# 查看现有服务
railway login
railway status

# 推代码触发部署
git push origin feat/headless-pivot-unit-N-xxx  # 先合 PR
# PR merge 到 main → Railway 自动部署到对应环境
```

### 7.2 后台部署（WordPress / Railway — 现有服务）

- mu-plugins 改动：SCP / SFTP 到容器 `/var/www/html/wp-content/mu-plugins/`
- Astra Child 改动：同上到 `/wp-content/themes/astra-child/`
- 或通过 Railway volume 挂载 + `railway run`

### 7.3 环境变量

| 变量 | 前台 | 后台 |
|---|---|---|
| `WORDPRESS_API_URL` | `https://homepage-manage.mindhikers.com` | — |
| `REVALIDATE_SECRET` | 32 字节随机 | WP 侧同值 |
| `BLOG_SOURCE` | `wp`（M1-R 锁定，MDX 仅 fallback） | — |
| `NEXT_PUBLIC_SITE_URL` | `https://www.mindhikers.com` | — |
| WP `mh_nextjs_revalidate_url` | — | `https://www.mindhikers.com/api/revalidate` |

---

## 8. 维护手册（给主理人 / 运营）

### 8.1 日常改内容流程

| 场景 | 步骤 |
|---|---|
| 改 Hero 标题 | WP Admin → Hero 管理 → 修改 `hero_title_zh` / `hero_title_en` → 保存 |
| 新增产品 | WP Admin → 产品 → 新建 → 填字段 → 设状态 → 发布 → Polylang 翻译 EN → 发布 |
| **写博客** | **WP Admin → 文章 → 新建 → 写正文（Gutenberg）→ 选主分类 + 次级分类 → 上传封面 → 发布** |
| **翻译博客** | **写完中文后 → Polylang 侧栏 → "+" 创建 EN 翻译 → 写英文版 → 发布** |
| 改社交矩阵 | WP Admin → Contact 管理 → `contact_social_matrix` → 增删行 → 保存 |
| 换 Logo | WP Admin → 外观 → 自定义 → 站点标识 → Logo（同步更新前台 `public/MindHikers.png`） |

### 8.2 紧急操作

- **前台挂了**：Cloudflare DNS 切回旧主机（见 §7 Rollback）
- **后台登录不上**：Cloudflare Access 检查；备用邮箱 `ops@mindhikers.com`
- **revalidate 不生效**：Next.js `/api/revalidate` 手动 curl；检查 secret；检查 WP `mh_nextjs_revalidate_url` 配置

### 8.3 何时找谁

| 问题类型 | 找谁 |
|---|---|
| 前台 React / 样式 | codex / opencode 端承接，老杨审查 |
| 后台 WP / Carbon Fields | codex / opencode 端承接，老杨审查 |
| 内容 / 文案 / 产品信息 | 老卢自决 |
| 域名 / DNS / Cloudflare | 老卢 + 老杨 |
| 治理 / 分支 / 合并 | 老杨 |

---

## 9. 文档交付清单（M1-R 验收必提交）

- [x] 本方案（`docs/plans/2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md`）
- [x] PRD 修订版（`docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md`）
- [ ] JSON Schema（`docs/plans/schemas/homepage.schema.json`）— Unit 1 产出
- [ ] 验收报告（`docs/testing_reports/2026-<date>_M1R_Smoke_Report.md`）— Unit 8 产出
- [ ] 验收清单（`docs/plans/2026-04-18_M1R_Acceptance_Checklist.md`）— Unit 8 产出
- [ ] 运营手册更新（`docs/operations-guide.md`）— Unit 8 产出
- [ ] HANDOFF 更新（`docs/dev_logs/HANDOFF.md`）— Unit 8 产出
- [ ] 生产切换 Runbook 执行记录（`docs/dev_logs/<date>_production_cutover.md`）— Unit 9 产出

---

## 10. 分支与提交策略（硬约束）

- 禁止直接 push `main`
- 每个 Unit 一条功能分支：`feat/headless-pivot-unit-<N>-<short>`
- 每个分支对应一个 PR，必须老杨或老卢 review 通过才 merge
- Commit message：`refs MIN-110 <描述>`
- 治理类变更（本文档、PRD 修订版、HANDOFF、rules）与代码变更**分开独立 commit**，严禁混提
- 在推送任何 commit / merge 前必须显式请示老卢确认
- **接手端（codex / opencode）特别注意**：本项目由 AI 编码端承接实施，每个 Unit 完成后必须在 PR 描述中列出：本 Unit 修改文件清单 / 验收打勾状态 / 已知限制 / 下一 Unit 前置条件

---

## 11. 出坑经验（来自 v1 M1 的 Lessons）

继承 `docs/lessons.md` + `docs/rules.md`。重点提醒：

1. SureRank REST API 不稳定 → 不调用
2. Elementor 直接编辑 `_elementor_data` 需 Regenerate CSS → 本方案不再涉及 Elementor
3. Carbon Fields 保存顺序敏感 → hooks 用 `carbon_fields_theme_options_container_saved`
4. Polylang 默认语言前缀隐藏 → URL `/` = zh，`/en/` = en
5. WordPress 容器内文件写入通过 SSH/SFTP 最稳 → Railway 提供 shell

---

## 12. 附录

### 附录 A. HomeContent 类型摘要（完整见 `src/data/site-content.ts`）

```typescript
export type HomeContent = {
  locale: "zh" | "en";
  metadata: { title: string; description: string; };
  navigation: {
    brand: string;
    links: { href: string; label: string; }[];
    switchLanguage: { href: string; label: string; };
  };
  hero: {
    eyebrow: string; title: string; description: string;
    primaryAction: { href: string; label: string; };
    secondaryAction: { href: string; label: string; };
    highlights: string[];
    statusLabel: string; statusValue: string;
    availabilityLabel: string; availabilityValue: string;
    panelTitle: string;
  };
  about: { title: string; intro: string; paragraphs: string[]; notes: string[]; };
  product: { title: string; description: string; headline: string;
    featured: EntryCard; items: EntryCard[]; };
  blog: { title: string; description: string; headline: string;
    cta: { href: string; label: string; };
    readArticleLabel: string; emptyLabel: string; };
  contact: { title: string; headline: string; description: string;
    email: string; locationLabel: string; location: string;
    availabilityLabel: string; availability: string;
    links: { href: string; label: string; note: string; qrImage?: string; }[]; };
};
```

### 附录 B. 对照资源

- PRD v2：[docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md](2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md)
- PRD v1：[docs/brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md](../brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md)
- v1 实施方案：[docs/plans/2026-04-12-001-feat-m1-cms-content-model-plan.md](2026-04-12-001-feat-m1-cms-content-model-plan.md)
- M1 专家审查：[docs/dev_logs/M1_REVIEW_FOR_EXPERT.md](../dev_logs/M1_REVIEW_FOR_EXPERT.md)
- 域名边界：[docs/domain-boundary.md](../domain-boundary.md)
- 规则：[docs/rules.md](../rules.md)
- 经验：[docs/lessons.md](../lessons.md)
- 运营手册：[docs/operations-guide.md](../operations-guide.md)
- 前台视觉基线：[src/components/home-page.tsx](../../src/components/home-page.tsx)
- 导航基线：[src/components/navbar.tsx](../../src/components/navbar.tsx)
- 内容字典：[src/data/site-content.ts](../../src/data/site-content.ts)
- CMS 接入：[src/lib/cms/homepage.ts](../../src/lib/cms/homepage.ts)
- WP 字段定义：[wordpress/mu-plugins/mindhikers-m1-core.php](../../wordpress/mu-plugins/mindhikers-m1-core.php)

---

*文档结束。外包团队阅读本方案后如有 open questions，Unit 0 前必须解决。*

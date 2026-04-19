🕐 Last updated: 2026-04-19 21:20
🌿 Branch: `staging`（从 `feat/m1r-headless-pivot` 改名，已推远程）
📌 Base commit: `bb8635e`（main HEAD）
🚀 Push status: ✅ 已推送 `origin/staging`

## 交接入口（新会话请从这里开始）

- 工作目录：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- 工作分支：**`staging`**
- 分支策略：`staging` → Railway staging 环境；`main` → Railway production 环境
- Linear 主线：`MIN-8`（网站上线）→ `MIN-110`（CMS 内容模型）→ M1-R（Headless 转向）
- **PRD（必读）**：`docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md`
- **实施方案（必读）**：`docs/plans/2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md`
- **治理日志**：`docs/dev_logs/2026-04-19.md`
- staging 前端：`https://mindhikers-homepage-staging.up.railway.app`
- staging WP：`https://wordpress-l1ta-staging.up.railway.app`

## 当前状态：M1-R 全部代码完成，等待 staging 部署验收 🟡

> Units 0–7 + 治理修复 + Blog 管道统一 = 全部完成并提交。
> 分支已改名为 `staging` 并推送到远程。
> **下一步**：在 Railway Dashboard 绑定 GitHub 仓库 → 自动部署到 staging → Smoke 验收。

## 提交历史（staging 分支，共 9 个 commit ahead of main）

```
9f7e518  docs: update HANDOFF — fix blog decision + staging strategy
320502b  feat(blog): switch to custom m1-rest blog endpoint          ← Blog 管道统一
296736f  docs: update HANDOFF for Plan A handoff
e66cff0  feat(headless): M1-R Units 0-7 implementation               ← 23 files, +1415
95a74cb  fix(governance): headless review fixes + docs                ← 11 files, +444 -126
9f053ce  plan: M1-R headless pivot implementation plan
6259215  prd revision v2.1: headless hybrid pivot
58c4bdc  handoff: pivot to M1-R headless hybrid
bb8635e  ← main HEAD
```

## 已完成的内容

### Units 0–7 功能实现（commit e66cff0）

| Unit | 内容 | 关键文件 |
|------|------|----------|
| 0 | 环境门控 | `.env.example` |
| 1 | WP REST 端点 | `wordpress/mu-plugins/m1-rest/` (helpers, homepage, product, blog) |
| 2 | Next.js 数据契约 | 类型定义在 helpers.php + TS 类型 |
| 3 | ISR + Revalidate Route | `src/app/api/revalidate/route.ts` |
| 4 | 产品详情页 + Blog 切 WP | `src/app/product/[slug]/`, `src/lib/cms/wordpress.ts` |
| 5 | Contact 模块 + QR | `src/components/contact-link-card.tsx` |
| 6 | SEO 元数据 + MDX 归档 | `layout.tsx`, `page.tsx`, `en/page.tsx`, 7 MDX files |
| 7 | WP Revalidate Webhook | `wordpress/mu-plugins/m1-rest/revalidate.php` |

### 治理修复（commit 95a74cb）

1. Cache tag 对齐 — `blog-posts` / `product-{slug}` / `homepage-zh,en`
2. `/api/revalidate` 收口 — POST-only + tag allowlist + path whitelist
3. WP webhook 可观测性 — 配置缺失日志、`MH_REVALIDATE_DEBUG` 开关
4. 图片 CSP 收紧 — `remotePatterns` + `img-src` 显式域名
5. Secret 清理 — 移除 staging 明文密码
6. Homepage ↔ productDetail 解耦 — `isHomeContentReady()` 移除 `productDetail` 强制校验
7. 8 条治理规则写入 `docs/rules.md`

### Blog 数据管道统一（commit 320502b）

- `src/lib/cms/wordpress.ts` 重写：从 WP 原生 `/wp-json/wp/v2/posts` 切到自定义 `/wp-json/mindhikers/v1/blog`
- 删除旧类型 `WordPressPost` 等，新增 `M1BlogItem` / `M1BlogDetail`
- 分页取全、categories 从 `{slug,name}[]` 映射为 `string[]`
- Cache tag `blog-posts` + ISR 300s 保留
- `pnpm build` ✅ | 旧 API 残留零命中 | 无 `as any`

## 下一步

### 1. Railway staging 部署配置（需老卢在 Dashboard 操作）

打开 https://railway.com/project/a8f3b17b-c4b7-4a4f-99a8-3cfec489ad97

**staging Next.js 绑定**：
- staging 环境 → Mindhikers-Homepage 服务 → Settings → Source → Connect Repo
- 仓库：`freestone16/Mindhikers-Home`，分支：`staging`，Root Directory 留空

**production Next.js 绑定（可选，验收通过后用）**：
- production 环境 → 同服务 → Branch：`main`

**绑定前需确认 staging 环境变量**：
- `WORDPRESS_API_URL` = `https://wordpress-l1ta-staging.up.railway.app`
- `BLOG_SOURCE` = `wordpress`
- `REVALIDATE_SECRET` = 需设置（与 staging WP 侧 `mh_revalidate_secret` 对应）

### 2. Smoke 验收（Unit 8）

staging 部署成功后验证：首页 / Blog / Product / Revalidate 全链路。

### 3. 生产切换（Unit 9）

staging 验收通过 → `staging` 合入 `main` → Railway production 自动部署。
**必须老卢明确下令后才能动。**

## 约束与红线

1. ❌ 不在 `main` 直接开发
2. ❌ 未经老卢确认不擅改代码
3. ❌ 每次 commit 必须有 Linear issue（`refs MIN-xx`）
4. ❌ commit / push / merge 前必须显式请示
5. ✅ 治理修复单元（代码 + 对应文档）放在同一个 commit
6. ✅ 功能实现 vs 过程治理文档分开 commit
7. ❌ 不要删 `wordpress/mu-plugins/mindhikers-cms-core.php`（旧 headless 插件，保留不启用）

## Cache Tag 规范

| 内容 | Tag | 来源 |
|------|-----|------|
| Blog（全量） | `blog-posts` | `CACHE_TAG_BLOG` in constants.ts |
| Homepage ZH | `homepage-zh` | `getHomepageCacheTag("zh")` |
| Homepage EN | `homepage-en` | `getHomepageCacheTag("en")` |
| Product | `product-{slug}` | `getProductCacheTag(slug)` |

## Railway 环境

| 环境 | Next.js 服务 | WP 服务 | 域名 |
|------|-------------|---------|------|
| production | `Mindhikers-Homepage` ✅ | `WordPress-L1ta` ✅ | `www.mindhikers.com` + `homepage-manage.mindhikers.com` |
| staging | `Mindhikers-Homepage` ⚠️ 未部署 | `WordPress-L1ta` ✅ | `mindhikers-homepage-staging.up.railway.app` |

- staging Next.js 需要在 Dashboard 绑定 GitHub `staging` 分支后自动部署
- 所有服务当前 `source.repo = null`（无自动部署），绑定后生效

## 技术栈

- Next.js 16.1.7, React 19, TypeScript, Tailwind 4
- `revalidateTag` 在 Next.js 16 需要 2 个参数：`(tag: string, profile: string | CacheLifeConfig)` — 使用 `"default"`

## 后台账号（staging）

- WP Admin：`https://wordpress-l1ta-staging.up.railway.app/wp-admin/`
- 用户名：`mindhikers_admin`
- 密码：不要入仓；通过安全渠道获取

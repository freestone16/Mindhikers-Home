🕐 Last updated: 2026-04-19 19:30
🌿 Branch: feat/m1r-headless-pivot（本次优化分支，未合并 main）
📌 Base commit: `bb8635e`（main HEAD）
🚀 Push status: 待推送

## 交接入口（新会话请从这里开始）

- 工作目录：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- 工作分支：**`feat/m1r-headless-pivot`**（严禁直接动 `main`）
- Linear 主线：`MIN-8`（网站上线）→ `MIN-110`（CMS 内容模型）→ 本次 M1-R（Headless 转向）
- **PRD（必读）**：`docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md`
- **实施方案（必读）**：`docs/plans/2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md`
- **治理日志**：`docs/dev_logs/2026-04-19.md`
- staging 前端：现有 Next.js Railway 服务
- staging WP：`https://wordpress-l1ta-staging.up.railway.app`

## 当前状态：Unit 0–7 + 治理修复已提交，准备进入 Plan A 🟡

> 本分支已完成 Unit 0–7 的全部本地代码实现，并经过代码 review → 治理修复 → 提交。
> **当前工作树干净**，两个功能 commit 已落地。
> 下一步是 Plan A：Blog 数据管道统一 + Smoke 验收。

## 提交历史（本分支）

```
e66cff0  feat(headless): M1-R Units 0-7 implementation       ← 23 files, +1415
95a74cb  fix(governance): headless review fixes + docs        ← 11 files, +444 -126
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
| 4 | 产品详情页 | `src/app/product/[slug]/`, `src/app/en/product/[slug]/`, `src/lib/cms/products.ts` |
| 5 | Contact 模块 + QR | `src/components/contact-link-card.tsx` |
| 6 | SEO 元数据 + MDX 归档 | `layout.tsx`, `page.tsx`, `en/page.tsx`, 7 MDX files |
| 7 | WP Revalidate Webhook | `wordpress/mu-plugins/m1-rest/revalidate.php` |

### 治理修复（commit 95a74cb）

1. **Cache tag 对齐** — 统一为 `blog-posts` / `product-{slug}` / `homepage-zh,en`
2. **`/api/revalidate` 收口** — POST-only + tag allowlist + path whitelist
3. **WP webhook 可观测性** — 配置缺失日志、context 日志、`MH_REVALIDATE_DEBUG` 开关
4. **图片 CSP 收紧** — `remotePatterns` + `img-src` 显式域名
5. **Secret 清理** — 移除 staging 明文密码
6. **8 条治理规则** — 写入 `docs/rules.md`

### 已验证

- `pnpm build` ✅（验证 3 次）
- 旧 tag grep ✅ 无残留
- 明文密码 grep ✅ 清除

## 下一步线头（Plan A）

### P1：Blog 数据管道统一

**当前问题**：Blog 仍走 `src/lib/cms/wordpress.ts` 的旧取数路径（WP 原生 `/wp-json/wp/v2/posts`），而非自定义 `m1-rest/blog.php`。

**需要决策**：
- 方案 A：继续用 WP 原生 `/wp-json/wp/v2/posts` + `blog-posts` cache tag
- 方案 B：切到 `m1-rest/blog.php` 统一 headless 数据面

选择后需要：统一 fetcher / page / cache / doc。

### P1：Homepage ↔ productDetail 解耦

`src/lib/cms/homepage.ts` 仍把 `productDetail` 作为 homepage payload 的校验项。建议分离为独立 fetch。

### P2：Smoke 验收（Unit 8）

验证首页 / Blog / Product / Revalidate 全链路。

### P2：生产切换演练（Unit 9）

**必须老卢明确下令后才能动。**

## 约束与红线

1. ❌ 不在 `main` 直接开发
2. ❌ 未经老卢确认不擅改代码
3. ❌ 每次 commit 必须有 Linear issue（`refs MIN-xx`）
4. ❌ commit / push / merge 前必须显式请示
5. ✅ 治理修复单元（代码 + 对应文档）放在同一个 commit
6. ✅ 功能实现 vs 过程治理文档分开 commit
7. ❌ 不要删 `wordpress/mu-plugins/mindhikers-cms-core.php`（旧 headless 插件，保留不启用）

## Cache Tag 规范（post-fix，canonical）

| 内容 | Tag | 来源 |
|------|-----|------|
| Blog（全量） | `blog-posts` | `CACHE_TAG_BLOG` in constants.ts |
| Homepage ZH | `homepage-zh` | `getHomepageCacheTag("zh")` |
| Homepage EN | `homepage-en` | `getHomepageCacheTag("en")` |
| Product | `product-{slug}` | `getProductCacheTag(slug)` |

## 环境变量

| 变量 | 当前值 | 说明 |
|------|--------|------|
| `WORDPRESS_API_URL` | production WP | `.env.local` 指向生产 WP |
| `BLOG_SOURCE` | `wordpress` | Blog 数据来源 |
| `REVALIDATE_SECRET` | 已设置 | Revalidate 接口密钥 |

## 技术栈

- Next.js 16.1.7, React 19, TypeScript, Tailwind 4
- `revalidateTag` 在 Next.js 16 需要 2 个参数：`(tag: string, profile: string | CacheLifeConfig)` — 使用 `"default"`

## 后台账号（staging）

- WP Admin：`https://wordpress-l1ta-staging.up.railway.app/wp-admin/`
- 用户名：`mindhikers_admin`
- 密码：不要入仓；通过安全渠道获取

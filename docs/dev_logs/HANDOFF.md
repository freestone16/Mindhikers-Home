🕐 Last updated: 2026-04-19 12:56
🌿 Branch: feat/m1r-headless-pivot（本次优化分支，未合并 main）
📌 Base commit: `bb8635e`（main HEAD）
🚀 Push status: 待推送

## 交接入口（codex / opencode 请从这里开始）

- 工作目录：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- 工作分支：**`feat/m1r-headless-pivot`**（严禁直接动 `main`）
- Linear 主线：`MIN-8`（网站上线）→ `MIN-110`（CMS 内容模型）→ 本次 M1-R（Headless 转向）
- **PRD（必读）**：`docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md`
- **实施方案（必读）**：`docs/plans/2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md`
- **本轮治理日志（新增）**：`docs/dev_logs/2026-04-19.md`
- staging 前端：现有 Next.js Railway 服务
- staging WP：`https://wordpress-l1ta-staging.up.railway.app`

## 当前状态：M1-R 已完成到 Unit 7，本轮额外完成 review 问题收口 🟡

> 本分支已完成 Unit 0–7 的本地代码实现，并在本轮会话里对结构/安全 review 提出的阻塞项与高优项做了治理修复。
> 现在的状态不是“从 Unit 0 重新开始”，而是：**Headless 主链已基本成形，接下来优先做复审、验收与剩余主线补齐。**

## 本轮新增修复（2026-04-19）

### 1. revalidate tag 对齐

已统一 WP webhook 与 Next.js 实际 cache tag：

- homepage → `homepage-zh`, `homepage-en`
- blog → `blog-posts`
- product → `product-{slug}`

修复文件：

- `src/lib/cms/constants.ts`
- `src/lib/cms/wordpress.ts`
- `src/lib/cms/products.ts`
- `wordpress/mu-plugins/m1-rest/revalidate.php`

### 2. `/api/revalidate` 收口

- 已改为 **POST-only**
- 已增加 tag allowlist / 前缀校验
- 已清理误导性的 `revalidateTag(..., "max")` 用法

修复文件：

- `src/app/api/revalidate/route.ts`

### 3. WordPress webhook 可观测性增强

- 配置缺失会记录日志
- dispatch 会记录 context + tags
- `WP_Error` 初始化失败会记录日志
- 预留 `MH_REVALIDATE_DEBUG` 调试模式

修复文件：

- `wordpress/mu-plugins/m1-rest/revalidate.php`
- `wordpress/mu-plugins/mindhikers-m1-core.php`

### 4. WordPress 图片来源配置补齐

- 已补 `images.remotePatterns`
- 已收紧 CSP `img-src` 到明确来源模式，而非全量 `https:`

修复文件：

- `next.config.mjs`

### 5. 规则与日志沉淀

- 已把治理结论写入 `docs/rules.md`
- 已新增当日日志 `docs/dev_logs/2026-04-19.md`

## 当前验证状态

1. `pnpm build` ✅ 通过
2. 旧 tag grep 复查：未发现 `blog-zh` / `blog-en` / `product-{postId}` 这类旧约定残留 ✅
3. `/api/revalidate` 构建结果为动态路由 ✅

## 仍未完成 / 下一步线头

### 优先级 P1：先做复审

1. 对本轮修复后的代码跑一轮 `ce:review`
2. 确认没有新的结构/安全明显问题

### 优先级 P1：继续主线补齐

1. **Blog 仍未彻底切到自定义 `m1-rest/blog.php` 管道**
   - 当前 blog 主要仍走 `src/lib/cms/wordpress.ts` 的旧取数路径
   - 下一步应决定：
     - 继续沿用 WP 原生 `/wp-json/wp/v2/posts` + `blog-posts` tag
     - 还是切到 `m1-rest/blog.php` 作为统一 headless 数据面
2. `src/lib/cms/homepage.ts` 仍把 `productDetail` 视为 homepage payload 的校验项，建议后续解耦

### 优先级 P2：验收与交付

1. Unit 8：Smoke 验收 + 文档交付
2. Unit 9：生产切换演练（必须老卢明确下令后再动）

## 当前不要做的事

1. 不要在 `main` 开发；所有 M1-R 工作继续留在 `feat/m1r-headless-pivot`
2. 不要提前 commit / push / merge；需要老卢明确确认
3. 不要重新开 Astra Child 视觉还原支线
4. 不要在生产环境直接验证 webhook 或缓存失效链路

## 后台账号（staging）

- WP Admin：`https://wordpress-l1ta-staging.up.railway.app/wp-admin/`
- 用户名：`mindhikers_admin`
- 密码：不要入仓；通过安全渠道获取

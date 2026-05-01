🕐 Last updated: 2026-05-01 11:45 CST
🌿 Branch: `staging`
📌 Latest commit on staging: `778af1b` docs(handoff): rewrite for deep-acceptance handoff with checklist A-G
🚀 Push status: ✅ Plan B 修复已 push，staging build/deploy SUCCESS（仍稳定）
🎫 Linear 跟踪：[MIN-167](https://linear.app/mindhikers/issue/MIN-167/staging-深度验收-外包-ai-执行)

---

## ⚡ 给外包 AI：1 分钟入场指南

**受众契约**：本轮接手者是**外包 AI**，所有验收项必须用 CLI 工具完成（curl + grep / Lighthouse CLI / WP REST API / Railway CLI），**禁止依赖**人眼截图、浏览器 DevTools、wp-admin GUI 操作。

主方案 v1.1 已按 AI 受众重写：所有命令均机器可执行，证据落到纯文本/JSON 文件。

### 📘 主方案（必读，所有执行细节都在这里）

→ **[`docs/plans/2026-05-01_Staging_Deep_Acceptance_Outsourced_Execution_Plan.md`](../plans/2026-05-01_Staging_Deep_Acceptance_Outsourced_Execution_Plan.md)**

绝对路径：
```
/Users/luzhoua/Mindhikers/Mindhikers-Homepage/docs/plans/2026-05-01_Staging_Deep_Acceptance_Outsourced_Execution_Plan.md
```

主方案包含：
- 易懂总览（先看这一节）
- 红线风险（RED-1 robots.txt SEO 漏水，必须第一个修）
- A–G 七组 43 项验收手册（每项含命令、判定、证据格式、修复 hint）
- 修复执行规范（commit 纪律、升级路径）
- 验收报告模板与交付物清单
- 时间预算（顺风 5–7h / 正常 1.5–2d / 暗坑 2.5–3d）

### ⏱ 入场顺序

1. 跑主方案 4.1 自检脚本，确认环境/凭证齐全（任何必备项 MISSING → 在 MIN-167 留 comment 后停手）
2. 通读主方案"0. 易懂总览"+"5. 已知红线"+"10. 升级路径"
3. `git fetch && git checkout staging && git pull --ff-only` 确认分支正确
4. 按主方案"12.2 推荐执行节奏"推进 A→G
5. 所有 commit message 加 `refs MIN-167`

---

## 当前 baseline（OldYang 已快速扫过的事实，可直接复用）

时间：2026-05-01 02:30 CST，证据来自 curl + 响应头观察。

### ✅ 已绿
| 项 | 现状 |
|---|---|
| 前台 ZH `/` | HTTP 200，HTML 59KB，`<html lang="zh-CN">`，渲染正常 |
| 前台 EN `/en` | HTTP 200，HTML 57KB，`<html lang="en">`，渲染正常 |
| `/health` | 200，JSON `{ok:true, version:"phase-closure"}` |
| `/favicon.ico` | 200，image/x-icon，25KB |
| ZH/EN homepage API | 200，~2KB，<1.5s |
| 安全头基线 | CSP / Referrer-Policy / X-Content-Type-Options / X-Frame-Options DENY / Permissions-Policy 都在 |
| 前台缓存策略 | `cache-control: s-maxage=300, stale-while-revalidate=...`，符合 5m revalidate |

### 🚨 已确认的红线
- **RED-1：`/robots.txt` 返回 HTML 不是 robots** → 主方案 5.1 节有完整修法（推荐 `app/robots.ts` 按环境变量返回不同策略）

### 🟡 已知不动项（本次记录但不修）
- HSTS 头未观察到（YEL-1）
- CSP 含 `'unsafe-inline'` script（YEL-2）

### ❓ 还没扫但主方案有覆盖
- `/sitemap.xml` 是否同样 fallthrough（D6）
- HTML head 里的 metadata 完整性（D1–D4）
- runtime log 健康度（B5）
- `/blog` `/golden-crucible` 路由实际渲染（A3/A5）
- F 段全部（依赖 wp-admin 凭证）

外包 AI 按主方案 A→G 顺序逐一推即可，不要重复 baseline 已扫过的项。

---

## 关键事实（不要重新验证）

- **修复路径**：`package.json` 加 `"packageManager": "pnpm@9.15.9"` 让 Railpack 走 corepack 路径，绕开 pnpm 9.x 版本索引 bug。这是 Plan B（commit `a57bff5`），稳定生效
- **Railway 自动回滚兜底机制**：连续 5 次 build 失败时，Railway 会自动回滚到 9 小时前最近一次 SUCCESS 镜像（之前是 WordPress Dockerfile 镜像）。这导致"前台 502 + service Online"假象。下次 build 失败时记得 `railway logs --deployment` 看实际跑的是不是预期镜像
- **production 当前在跑 4-24 旧镜像**（commit `a7cabf16`），先别动。等 staging 验收完老卢决定推送方案

---

## production 推送（外包不要碰）

外包**只在最终报告里给建议**，不执行。三个备选见主方案 11 节。

⚠️ main 分支最新 commit `8744c4f` 把 builder 切到 RAILPACK 但**没**加 `packageManager` 字段。下次推 main 触发部署会复现之前的 5 次失败。这是定时炸弹，但本次外包不动。

---

## 关键文件速查（详见主方案附录 B）

| 文件 | 作用 |
|---|---|
| `package.json` | 关键修复点（`packageManager` 字段），别动 |
| `railway.json` | 根目录，`builder: "RAILPACK"` |
| `ops/mindhikers-cms-runtime/railway.json` | WordPress 服务专用 |
| `.dockerignore` | G3 待决策项（`pnpm-lock.yaml` 排除规则可能要回退） |
| `app/robots.ts` | RED-1 修复后新建 |
| `app/sitemap.ts` | D6 如做后新建 |

---

## 升级路径

任何卡点超过 20 分钟、改动 > 30 行、跨模块、影响 production —— **立即在 Linear 或微信升级，等老卢拍板**。

升级模板见主方案 10.2 节。

---

## 历史记录（修复路径详见上一版 HANDOFF）

| commit | 说明 |
|---|---|
| `5851cb3` | 上一版 HANDOFF（Plan B success） |
| `a57bff5` | ✅ Plan B 修复（packageManager 字段） |
| `18106e9` | Plan A（删 engines.pnpm） |
| `e01c584` | 再上一版 HANDOFF |
| `125743e` | WordPress 配置分离 |

---

(End of file)

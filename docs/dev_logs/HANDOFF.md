🕐 Last updated: 2026-04-19 00:30
🌿 Branch: feat/m1r-headless-pivot（本次优化分支，未合并 main）
📌 Base commit: `bb8635e`（main HEAD）
🚀 Push status: 待推送

## 交接入口（codex / opencode 请从这里开始）

- 工作目录：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- 工作分支：**`feat/m1r-headless-pivot`**（严禁直接动 `main`）
- Linear 主线：`MIN-8`（网站上线）→ `MIN-110`（CMS 内容模型）→ 本次 M1-R（Headless 转向）
- **PRD（必读）**：`docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md`
- **实施方案（必读）**：`docs/plans/2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md`
- 历史方案（背景）：`docs/plans/2026-04-12-001-feat-m1-cms-content-model-plan.md`
- 专家审查背景：`docs/dev_logs/M1_REVIEW_FOR_EXPERT.md`
- staging 前端：现有 Next.js Railway 服务
- staging WP：`https://wordpress-l1ta-staging.up.railway.app`

## 当前状态：路线已切换至 M1-R Headless Hybrid 🟡

> 上一轮 M1 完成了 CMS 数据层（Carbon Fields + Polylang + Product CPT + 双语 Seed），但 Astra Child 前台视觉简陋无法验收。
>
> 本轮决策：**放弃重写 WP 模板视觉，回到现成的 Next.js 前端 + WordPress Headless**。Next.js 前端已 production-grade，CMS 管道 80% 已搭通，只需补 WP REST + webhook + Blog 切 WP Posts 即可收口。

### 本次会话产出

| 文件 | 用途 |
|------|------|
| `docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md` | PRD v2.1，锁定 Headless Hybrid，取代 2026-04-12 §D4 |
| `docs/plans/2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md` | 给 codex/opencode 的 M1-R 实施手册（Unit 0–9，6.5 人日 + 1 天 cutover） |

### 关键决策（不可回退）

1. **路线**：Next.js 16 + ISR + WP Headless（REST）；不再做 Astra Child 视觉还原
2. **部署**：沿用现有 Railway 服务（Next.js staging/prod + WP staging/prod），**不引入 Vercel 或其他平台**
3. **Blog**：M1-R **一次性**从 MDX 切到 WP Posts，不做两阶段
4. **回退**：如失败，Next.js 回退到 build-time `src/data/site-content.ts` + Railway deployment rollback
5. **受众**：后续开发由 codex / opencode AI 编码端承担，不招人

## 下一窗口起手式（codex / opencode）

1. 拉最新分支：`git fetch && git checkout feat/m1r-headless-pivot && git pull`
2. 读 PRD v2.1 → 读实施方案
3. 从 **Unit 0**（环境准备 + revalidate secret 配置）开始执行
4. 每个 Unit 结束都要按实施方案中的 Test / Verification 自验
5. 提交口径：`refs MIN-110 <Unit N>: <摘要>`
6. 不要合并回 `main`——完成 M1-R 全部 Unit 并经老卢验收后才开 PR

## 执行顺序（Unit 0–9）

| Unit | 名称 | 估时 |
|------|------|------|
| 0 | 环境 + 密钥（REVALIDATE_SECRET / WP_API_BASE） | 0.5d |
| 1 | WP REST 端点：homepage / product / blog 列表 + 详情 | 1.5d |
| 2 | Next.js `lib/cms/*` 补齐（product / blog fetchers） | 1.0d |
| 3 | ISR 接入 + fallback 稳健化 | 0.5d |
| 4 | 导航/产品详情/Blog 一次性切 WP Posts | 1.0d |
| 5 | Cloudflare Access for `homepage-manage.*` | 0.5d |
| 6 | 运营手册重写（WP 后台改→前台 300s 生效） | 0.5d |
| 7 | revalidate webhook（WP save_post → Next.js） | 0.5d |
| 8 | E2E 验证（ZH/EN × 首页/产品/Blog） | 0.5d |
| 9 | Cutover：Railway 环境变量切换 + 上线 | 1.0d |

## 红线（来自 OldYang skill §☠️）

1. ❌ 不在 `main` 直接开发
2. ❌ 未经老卢确认不擅改代码
3. ❌ 每次 commit 必须有 Linear issue（`refs MIN-xx`）
4. ❌ `commit` / `push` / `merge` 前必须显式请示
5. ✅ 治理类（HANDOFF / plans / rules）与代码类必须**独立 commit**
6. ❌ 不要删 `wordpress/mu-plugins/mindhikers-cms-core.php`（旧 headless 插件），保留不启用

## 当前不要做的事

1. 不要重启 Astra Child 视觉还原工作（方案 B 已放弃）
2. 不要在 `main` 开发；所有 M1-R 工作都在 `feat/m1r-headless-pivot`
3. 不要引入 Vercel / Netlify 等新部署平台
4. 不要把 Blog 切换拆成两阶段
5. 不要提前取消 staging 的 `noindex`
6. 不要在生产环境直接试错

## 后台账号（staging）

- WP Admin：`https://wordpress-l1ta-staging.up.railway.app/wp-admin/`
- 用户名：`mindhikers_admin`
- 密码：`IW0pGAFhiydfFg3GC5xxgl+L`

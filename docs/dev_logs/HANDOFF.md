🕐 Last updated: 2026-04-24 08:14 CST
🌿 Branch: `experiment/wp-traditional-mode`
📌 Base commit: `3370c37`
🚀 Push status: ❌ 暂不推远端（本地实验分支；003 文档 commit 已获授权，禁止静默 push）

---

## 🎯 当前状态：WP 单栈迁移执行手册已落盘（003），Linear 归属已确认

**一句话**：003 执行手册已写完（`docs/plans/2026-04-23-003-wp-single-stack-migration-execution-playbook.md`，约 1800 行），本轮主 issue 定为 `MIN-30`，挂在 `MIN-7 网站开发` 下；老卢已授权文档 commit，但仍禁止静默 push。

---

## 📖 问题背景（给新窗口 LLM 的上下文）

### 起点问题
- 上个窗口做的是 **WP 轻量定制实验**（experiment/wp-traditional-mode 分支，Units 1-9 完成），但卡在 Unit 10 验证
- 阻塞：Railway 容器文件系统只读 → 主题 ZIP 上传失败、Code Snippets 插件装不上
- 根因：`ops/mindhikers-cms-runtime/Dockerfile` 只有 5 行，**不 COPY `wordpress/`**，导致仓库改动对容器零影响 → 所有 WP 代码变更只能走 Code Snippets 补丁（不可追溯、不可 revert）

### 老卢的真实诉求演进
1. 最初：解决阻塞，让生产稳定，好灌内容
2. 澄清：**尽快完成多模板改造（WP 外观→主题切换）+ 尽快灌内容**
3. 哲学约束：**第一性原理 + 奥卡姆剃刀**，不打补丁、不凑合
4. 时间线：不并行、不搭临时桥，**顺序**走——先完成 WP 多模板改造，再灌内容
5. 架构终态：**WP 单栈**（替代现在的 Next.js + WP headless 双栈），Next.js 退役归档
6. 执行策略：**老卢找 codex 外包团队执行**，老杨（CE 团队）只负责出详细方案

### 老卢锁定的 7 项决策
| # | 决策 | 值 |
|---|---|---|
| 1 | Linear issue | `MIN-30`，父级/归属：`MIN-7 网站开发` |
| 2 | 主题名 | `mindhikers-main`（不叫 astra-child / mindhikers-child） |
| 3 | 插件策略 | 混合——Carbon Fields + Polylang 打进镜像；其他留 Volume；M1 REST 卸载 |
| 4 | Next.js 处置 | 归档到 `legacy/nextjs-frontend/`（不 `git rm`） |
| 5 | 数据库备份 | 老卢手动做（Railway 面板 + mysqldump） |
| 6 | 动画保真 | 接近即可（不追求像素级） |
| 7 | DNS 切换 | 老卢在 Cloudflare 操作，LLM 给分步指南 |

---

## 📄 已产出的核心文档

### `docs/plans/2026-04-23-003-wp-single-stack-migration-execution-playbook.md`
**状态**：✅ 已落盘磁盘 | ✅ 文档 commit 已获老卢授权
**大小**：约 1800 行
**目标读者**：codex 外包团队 LLM

**结构**：
- TL;DR + 7 条锁定决策（置顶）
- Prerequisites（commit 纪律、🛑 阻断约定、跨阶段依赖）
- Phase 0 备份（6 步原子，全部 🛑 等老卢手动执行）
- Phase 1 Dockerfile + 插件清理（含 image→Volume `sync-bundle.sh` 完整代码，解决 Volume 覆盖 COPY 的难题）
- Phase 2 Code Snippets 退役（含 mhs02 等价性校验）
- Phase 3 主题重建（10 个子阶段，`mindhikers-main` 全量 PHP/JS/CSS，IntersectionObserver 替代 motion/react，原生 JS 替代 next-themes）
- Phase 4 Blog 迁移（TypeScript 脚本 `scripts/migrate-mdx-to-wp.ts`，含 MDX 已归档的前置核查）
- Phase 5 Staging 验证（30+ checkpoint）
- Phase 6 生产切换（Cloudflare DNS 分步指南，老卢可直接照做）
- Phase 7 清理（`src/` → `legacy/nextjs-frontend/`）
- 附录 A–E（验收清单、回滚策略、env 变量表、决策备忘、commit 顺序）

**关键设计亮点**：
- **image→Volume sync 模式**：Dockerfile 把核心文件 stage 到 `/opt/wp-bundle/`，entrypoint 运行 `sync-bundle.sh` 同步到 Volume 挂载的 `wp-content`，绕过 Railway Volume 覆盖镜像 COPY 的限制
- **混合插件策略**：核心插件（Carbon Fields、Polylang）打进镜像锁版本；其他插件留 Volume，WP Admin 可升级
- **DNS 切换保险**：72 小时 Next.js 热备，TTL 24 小时前预降，可秒级回滚

### `docs/plans/2026-04-23-002-wp-single-stack-migration-plan.md`
**状态**：✅ 已落盘 | ✅ 随 003 作为历史对照一并提交
**定位**：003 的前身，决策未锁时的初版（340 行），保留作历史对照

### `docs/plans/2026-04-23-001-feat-wp-lightweight-customization-plan.md`
**状态**：已提交到 `experiment/wp-traditional-mode` 分支（commit `3370c37` 之前）
**定位**：上个窗口的轻量定制方案（Units 1-9 的设计依据），已被 003 取代

---

## 🚦 下一步动作（新窗口开工 checklist）

### ✅ 已确认决策（P0）

1. **003 playbook commit 策略三选一**：
   - ✅ 老卢确认：Linear 编号定为 `MIN-30`
   - ✅ 老卢确认：挂在 `MIN-7 网站开发` 下
   - ✅ 老卢确认：文档可以 commit

2. **提交边界**：只提交文档，不混入 Next.js dark mode、ZIP、截图等非本轮文档资产。

### 🛑 外包团队执行前，等老卢手动动作

3. **Phase 0 数据库备份**（Railway 面板 + mysqldump）
4. **Cloudflare DNS TTL 24h 前预降**（Phase 6 前置）
5. **生产 DNS CNAME 切换**（Phase 6 切换日）

### 当前可推进（无需等老卢）

6. 002 playbook 是否需要归档/删除？（被 003 取代，但保留作历史依据可能更稳）
7. 检查 `experiment/wp-traditional-mode` 分支上 Units 1-9 的代码是否需要在 Phase 3 之前 revert 或保留作参考
8. 检查 `content/*.mdx` 的"ARCHIVED: 2026-04-19"标记，确认 Blog 是否已完成迁移到 WP（Phase 4 有前置核查步骤）

---

## 🔴 生产环境不能动的（红线）

### Production Code Snippets
| 名称 | 状态 | 说明 |
|---|---|---|
| `mhs02` | 🔴 Active | 重注册 homepage REST 路由 override，产线依赖中。**退役条件**：Phase 2 完成（bootstrap.php 接管新 schema）后才能删 |
| `mhs` / `mhs03` | Run Once 已执行 | 可删除但建议留到 Phase 2 一起清 |

### 生产域名架构（切换前）
- Next.js 前台：`https://www.mindhikers.com`（Railway 服务 `Mindhikers-Homepage`）
- WP CMS：`https://homepage-manage.mindhikers.com`（Railway 服务 `WordPress-L1ta`）
- 切换后：`www.mindhikers.com` 直指 WP 容器，Next.js 保留 72 小时热备

---

## 🚧 OldYang 红线（务必遵守）

1. ❌ 不在 `main` 直接开发
2. ❌ 每次 commit 必须 `refs MIN-xx`（等老卢给 issue 号）
3. ❌ commit / push / merge 前**显式请示老卢**
4. ✅ 治理文档与代码分开 commit
5. ✅ **绝不删除 production 的 `mhs02` snippet**（Phase 2 完成后才能删）

---

## 📌 Linear

- 上轮主 issue：[MIN-164](https://linear.app/mindhikers/issue/MIN-164)
- **本轮主 issue**：`MIN-30`（父级/归属：`MIN-7 网站开发`，主题："WP 单栈迁移——多模板改造 + Next.js 归档"）

---

## 📂 关键文件速查

| 路径 | 作用 | 状态 |
|---|---|---|
| `docs/plans/2026-04-23-003-*.md` | **本轮执行手册**（外包团队的 SSOT） | 已落盘未提交 |
| `docs/plans/2026-04-23-002-*.md` | 003 前身，决策未锁时的初版 | 已落盘未提交 |
| `ops/mindhikers-cms-runtime/Dockerfile` | 只有 5 行，**是原罪**（Phase 1 要全面改写） | 未动 |
| `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php` | REST 路由 + 数据层真正注册点 | Units 1-9 已改 |
| `wordpress/themes/astra-child/` | 上轮改的主题 | Phase 3 会**重建**为 `mindhikers-main`，此目录将归档/删除 |
| `src/` | Next.js 整个前台 | Phase 7 会归档到 `legacy/nextjs-frontend/` |
| `content/*.mdx` | 博客源文件 | 已标 ARCHIVED 2026-04-19，Phase 4 前置核查 |

---

## 🗝️ 给新窗口 LLM 的第一行动

1. **先读**：本 HANDOFF + `docs/plans/2026-04-23-003-*.md`
2. **按 `MIN-30` 执行**：确认归属 `MIN-7 网站开发`，所有后续 commit message 使用 `refs MIN-30`
3. **按老杨规矩**：任何 commit / push 前显式请示；代码和文档分开 commit

---

## 📖 上轮（Units 1-9）历史简记（供参考，非当前主线）

> 下面是上个窗口的轻量定制实验进度，现已被 003 全栈迁移方案取代。保留此段仅供对照。

- **分支**：`experiment/wp-traditional-mode`，base commit `3370c37`
- **完成度**：9/10 Units（template-parts hero/about/product/blog/contact 已重写为 JSON 消费）
- **关键改动文件**：
  - `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php`（+`getHomepageDataForTheme()` + 缓存层）
  - `wordpress/themes/astra-child/front-page.php` + 5 个 template-parts
- **阻塞原因**：Railway 容器只读 → 003 Phase 1 的 Dockerfile 改造是**真正的根治方案**
- **Code Snippets 现状**：production `mhs02` Active（不能删），`mhs` / `mhs03` 已执行可删

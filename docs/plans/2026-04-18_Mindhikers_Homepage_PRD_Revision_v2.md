---
title: "Mindhikers Homepage PRD 修订版 v2 — Headless Hybrid"
type: prd-revision
status: draft — 待老卢审核
date: 2026-04-18
last_revised: 2026-04-19
version: v2.1
supersedes_section:
  - "docs/plans/2026-04-12-001-feat-m1-cms-content-model-plan.md § Key Technical Decisions D4（Astra Child front-page.php 渲染主线）"
  - "docs/plans/2026-04-12-001-feat-m1-cms-content-model-plan.md § Scope Boundaries（原明确排除视觉精修）"
origin:
  - "docs/brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md （v1 PRD，继承品牌定位、信息架构、双语策略、后台 UX 要求）"
author: "OldYang 代 CE 团队起草"
reviewer: 老卢
linear: MIN-8 / MIN-110
---

# Mindhikers Homepage PRD 修订版 v2 — Headless Hybrid

## 0. 本修订版的目的与适用范围

2026-04-10 发布的 v1 PRD（brainstorm 产物）已稳固定义了：

- 品牌定位（心行者 / 知识成长频道 / 三大支点）
- 首页五大区块的语义角色
- 双语策略（首页严格对等 / Blog 精选翻译）
- 后台 UX 的 R30–R34 要求
- M1 / M2 的里程碑边界

**v1 的以上内容在 v2 中完全保留，不重做。**

v1 衍生的 2026-04-12 M1 实施方案将前台主线定为 "Astra Child `front-page.php` + 自写 PHP 模板"。2026-04-18 staging 验收时，主理人老卢明确反馈三条不可接受：

1. **前台视觉严重不完整**：左上角无 Logo；导航只有"Home / 开始联系"，无 About / Product / Blog / Contact；无右侧信息面板；无卡片式设计；整体简陋上下堆叠
2. **与线上 `mindhikers.com` 差距过大**：不具备上线验收条件
3. **可接受换技术方案**，但要求：与现有线上调性一致、模块完整、后台维护简单、长期健壮

根因诊断：v1 PRD 正确，但 2026-04-12 方案在 § Scope Boundaries 中明确排除"视觉精修"，导致 M1 完成态与用户验收期望之间形成认知落差。

**v2 本文档只修订 v1 的"技术主线"和"M1 验收硬指标"两项**，其余继承 v1。

---

## 1. 核心变更摘要（一页看懂）

| 维度 | v1 / 2026-04-12 M1 方案 | v2 修订 |
|---|---|---|
| 前台渲染技术 | Astra Child `front-page.php` + PHP 模板 + 原生 CSS | **Next.js 16 + React 19 + Tailwind 4**（继承现有 `src/components/home-page.tsx` 的成品视觉） |
| WordPress 角色 | 后台字段 **+ 前台渲染** | **仅作为 Headless CMS 后端** |
| 数据流 | WP 服务端直接渲染 HTML | WP REST endpoint → Next.js ISR 拉取 → React 端渲染 |
| 上线视觉基线 | 需要从零重写 CSS | **直接沿用 `mindhikers.com` 线上版本**（已满足品牌调性） |
| M1 验收硬指标 | 五大区块字段可后台管理 | 前者 **+ 视觉完整度逐项比对线上** |
| 前台域名 | staging PHP 前台 | `www.mindhikers.com`（Next.js） |
| 后台域名 | staging 共域 | `homepage-manage.mindhikers.com`（仅 WP，Cloudflare Access 保护） |

**里程碑命名变更**：v1 的 M1 / M2 更名为 **M1-R / M2-R**（R = Revision），避免与 v1 方案版本混淆。

---

## 2. 继承自 v1 的内容（不重复）

以下内容请直接读 `docs/brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md`，v2 不重述：

- §1 品牌定位（心行者 / 老卢 / slogan / 核心信念 / 完整品牌叙述原文）
- §3 信息架构（五大区块 + 数据源映射图）
- §4 R1–R29 模块需求（Hero / About / Product / Blog / Contact / 导航 / Footer 字段集）
- §5 双语策略
- §6 R30–R34 后台 UX 要求
- §7 内容运营约束
- §10 Key Decisions（除 D4 被 v2 取代）

---

## 3. v2 新增 / 修订的需求编号

### 3.1 新增 R 编号

| 编号 | 需求 | 验收口径 |
|---|---|---|
| **R35** | 前台视觉完整度必须逐项对齐 `mindhikers.com` 线上版本 | 外包团队必须用对照表逐项打勾，不允许缺项验收 |
| **R36** | 后台更改内容后，前台自动更新（ISR） | 默认 300 秒内生效；带手动触发入口，30 秒内生效 |
| **R37** | WordPress 服务异常时前台 graceful 降级 | WP 5xx 或超时时，前台使用静态 fallback 数据，不显示 500；后台修复后自动恢复 |
| **R38** | 后台登录入口必须位于独立子域并受 Cloudflare Access 保护 | `homepage-manage.mindhikers.com/wp-admin` 仅授权账号可访问；访客前台域名无任何 /wp-admin 暴露 |
| **R39** | 生产切换必须具备一键回滚能力 | 切换到新架构后，10 分钟内可回滚到旧 Next.js 静态站 |
| **R40** | 前台构建必须在 WP 不可达时仍能完成 | Next.js 构建阶段如 WP 不可达，使用 `src/data/site-content.ts` 作为 build-time fallback，不阻塞构建 |

### 3.2 R1–R29 的字段一致性补充（非修订，仅澄清）

既有字段字典保持不变，但 WP REST endpoint 返回的 JSON 结构必须与 Next.js `HomeContent` TypeScript 类型（`src/data/site-content.ts` 已定义）100% 对齐，字段名、层级、数组顺序严格一致。此类型即数据契约。

---

## 4. 前后端职责边界（v2 明确）

### 4.1 前台（Next.js）职责

- 视觉渲染（五大区块布局、卡片、动效、响应式、字体、配色）
- 路由（`/`, `/en`, `/blog`, `/blog/[slug]`, `/product/[slug]`, `/en/product/[slug]` 等）
- ISR 缓存与 fallback 兜底
- SEO 元数据输出（title / description / og:* / twitter:* / canonical）
- 手动 revalidate 入口（`/api/revalidate`）

### 4.2 后台（WordPress）职责

- Carbon Fields Theme Options（Hero / About / Contact 三组字段）
- `mh_product` CPT（产品字段 + Gutenberg 详情）
- WP 原生 Posts（Blog 双层分类）
- Polylang（ZH/EN 翻译关系 + 语言切换）
- WP Media Library（图片 / 二维码统一管理）
- `/wp-json/mindhikers/v1/*` REST endpoint 对外输出
- revalidate webhook：保存字段 → 触发前台刷新

### 4.3 不再由任何一方承担

- ❌ WordPress 不再渲染任何前台 HTML（保留能力但不启用）
- ❌ Astra Child `front-page.php` 和 `template-parts/*.php` 标记为 archived，不删除、不启用
- ❌ Elementor 不参与首页数据（保留能力给未来独立页，不参与 `/`）

---

## 5. 数据契约（WP → Next.js）

### 5.1 端点清单

| 端点 | 返回 | 缓存 |
|---|---|---|
| `GET /wp-json/mindhikers/v1/homepage/zh` | `HomeContent`（zh 版本，聚合 Hero/About/Contact/Product 列表/Blog 列表） | ISR 300s |
| `GET /wp-json/mindhikers/v1/homepage/en` | `HomeContent`（en 版本） | ISR 300s |
| `GET /wp-json/mindhikers/v1/product/{slug}?lang=zh\|en` | 单产品详情（副标题 / 状态 / 完整描述 HTML / Logo URL / Featured） | ISR 300s |
| `GET /wp-json/mindhikers/v1/blog?lang=zh\|en&page=&per_page=&category=` | 博客列表（分页 + 分类过滤，EN 仅含已翻译文章） | ISR 300s |
| `GET /wp-json/mindhikers/v1/blog/{slug}?lang=zh\|en` | 博客详情（标题 / 正文 HTML / 封面 / 主次分类 / 发布时间） | ISR 300s |
| `POST /wp-json/mindhikers/v1/revalidate` | 接受 WP 侧触发的刷新请求，转发至 Next.js `/api/revalidate` | 不缓存 |

### 5.2 权威类型定义位置

- TypeScript：`src/data/site-content.ts` `HomeContent` 类型
- 固定基线（fallback）：同文件的 `SITE_CONTENT` 常量
- JSON Schema（给外包用于 WP 端参考）：本方案实施时由 Unit 0 落盘至 `docs/plans/schemas/homepage.schema.json`

---

## 6. 修订后的 M1-R 验收口径

### 6.1 验收分两层（缺一项不合格）

#### Layer A — 后台内容管理可用性（继承 v1 §8.2）

| 项 | 通过标准 |
|---|---|
| Hero 后台修改 | 改 `hero_title_zh` → 前台 `/` 5 分钟内可见；手动 revalidate 后 30 秒可见 |
| About 后台修改 | Gutenberg 编辑 About 文案 → 前台生效 |
| 新增产品 | 新建 `mh_product` 条目（ZH+EN）→ 首页 Product 区 + `/product/{slug}` 详情页自动生成 |
| **写博客** | **WP 后台新建文章（选主分类 + 次级分类 + 封面 + 正文）→ 前台 `/blog` 列表与 `/blog/{slug}` 详情页自动生成，30 秒内生效** |
| **Blog 双语** | **中文文章可选翻译为英文；EN `/en/blog` 只显示已翻译文章** |
| 双语切换 | `/` ↔ `/en` 五区块对等 |
| 社交矩阵 | 展示 Twitter / Bilibili / 微信公众号（含二维码） |

#### Layer B — 前台视觉完整度（v2 新增，外包必须逐项打勾）

对照 `mindhikers.com` 线上版本，以下模块视觉逐项对齐：

| 模块 | 对齐点 |
|---|---|
| Header | 左上 `/MindHikers.png` Logo + 品牌名 + 顶部圆角玻璃态导航栏 + 右上 EN/ZH 切换按钮 |
| Hero（左栏） | Eyebrow / 大标题（4xl-[4.5rem]）/ 描述段 / 主次 CTA / Highlights 药丸标签 |
| Hero（右栏） | 品牌头卡片 + Current focus InfoPill + Working rhythm InfoPill + Homepage blocks 快捷导航面板 |
| About | 左卡片（eyebrow + intro + 双列 paragraphs）+ 右 notes 列表 |
| Product | 标题区 + Featured 大卡片 + 右侧小卡片网格 |
| Blog | 标题区 + "查看全部"按钮 + 3 列文章卡片 + 空状态兜底 |
| Contact | 左主卡（邮箱 CTA）+ 右侧：位置 + availability + 社交链接卡片 |
| Footer | Astra Footer 或 Next.js 自建，版权 + 双语 + 社交矩阵同源 |

验收方式：外包团队提交对照截图（staging vs production-baseline），主理人老卢确认打勾。

### 6.2 M1-R 完成态（硬边界）

1. `staging.mindhikers.com`（或等价 staging 前台）**Layer A + Layer B 全部通过**
2. 中英双页 5 区块严格对等
3. 后台修改内容后，默认 300 秒内或手动刷新 30 秒内反映到前台
4. WP 异常时前台 fallback 生效，不出现 5xx
5. 文档交付齐全（本方案附录列出）

---

## 7. 域名与环境最终映射

继承 `docs/domain-boundary.md`，v2 明确如下：

| 域名 | 归属 | 用途 | 防护 |
|---|---|---|---|
| 域名 | 指向（现有 Railway 服务） | 用途 | 防护 |
|---|---|---|---|
| `www.mindhikers.com` | Railway Next.js production | 公开访问 | Cloudflare CDN |
| `mindhikers.com` | 301 → `www.mindhikers.com` | apex 规范化 | Cloudflare |
| `homepage-manage.mindhikers.com` | Railway WordPress production | 后台管理 | **Cloudflare Access（必装）** |
| `wordpress-l1ta-staging.up.railway.app` | Railway WordPress staging（现有） | 过渡期 staging | noindex 保持 |
| Next.js staging 域名（待老卢确认现状） | Railway Next.js staging（现有） | M1-R 验收环境 | basic auth 或 Cloudflare Access |

**关键约束（2026-04-19 锁定）**：staging 与 production 的 Next.js / WordPress 均已在 Railway 部署，M1-R 不引入任何新部署平台，不新建 Railway 项目。所有代码变更通过现有服务的 git 推送触发部署。

---

## 8. 范围边界（v2 明确）

### 8.1 v2 范围内

- 本文档 §3 所有新增 R35-R40 需求
- v1 所有保留 R1-R29 + R30-R34
- M1-R（Headless 打通 + 验收）
- M2-R（表单 / 订阅 / 生产切换）

### 8.2 v2 明确不做

- ❌ 不重做 v1 的品牌定位、信息架构、双语策略、字段字典（保持稳定）
- ❌ 不删除 `wordpress/themes/astra-child/` 下的 PHP 模板（archived，封存）
- ❌ 不删除 `wordpress/mu-plugins/mindhikers-cms-core.php`（legacy，封存）
- ❌ 不重写 `src/components/home-page.tsx` 的视觉（已是目标基线）
- ❌ 不引入新的多语言方案（Polylang 已就绪，不改）
- ❌ 不引入新的字段插件（Carbon Fields 已就绪，不改）
- ❌ **不引入新的部署平台**（Vercel / 新 Railway 项目等）；基于现有 Railway staging + production 服务实施
- ❌ **不把 Blog 切换拆两阶段**（M1-R 一次性从 MDX 切到 WP Posts，不留尾巴）

---

## 9. 风险与回退

| 风险 | 概率 | 影响 | 缓解 |
|---|---|---|---|
| WP REST endpoint 返回结构与 `HomeContent` 类型不匹配 | 中 | 中 | Unit 2 做 TypeScript 运行时校验 `isHomeContentReady`（已有），校验失败自动 fallback |
| Polylang 语言前缀与 Next.js i18n 路由冲突 | 中 | 中 | Next.js i18n 不启用中间件；仅以 `/en` 前缀手动区分；WP 端 `?lang=` 参数显式传递 |
| Cloudflare Access 误拒合法访问 | 低 | 高 | 部署前双人验证；保留 Cloudflare API Token 紧急绕行清单 |
| DNS 切换造成用户短时访问旧站 | 低 | 中 | TTL 降低至 60s 预热 24 小时；切换在低峰时段；保留 15 分钟回滚窗口 |
| revalidate 密钥泄露 | 低 | 中 | 使用 `REVALIDATE_SECRET` 环境变量；每 90 天轮换；不入仓 |
| 接手端（codex/opencode）WP PHP 能力参差 | 中 | 中 | endpoint 提供参考实现骨架；验收要求 p95 < 500ms；老杨审查每个 PR |
| MDX → WP Posts 切换时文章丢失 / 链接断裂 | 低 | 高 | Unit 4 前 3 篇现有 MDX 文章手动录入 WP 并保留 slug 不变；`content/blog/*.mdx` 不删只封存 |

### 回退方案（生产切换后出事）

**10 分钟回滚 Runbook**（详见实施方案 §7）：
1. Cloudflare DNS：`www.mindhikers.com` CNAME 切回旧 Next.js 静态站
2. 清 Cloudflare 缓存
3. 通知运营：15 分钟窗口内对外暂停任何后台改动
4. 根因诊断由外包 + 老杨联合排查

---

## 10. 依赖与假设（v2 补充）

继承 v1 §11，补充：

- **假设 1**：现有 `src/components/home-page.tsx` 视觉为"生产基线"，外包不需要重新设计
- **假设 2**：Carbon Fields / Polylang / Astra 已在 staging 稳定运行（已在 M1 验证）
- **假设 3**：Cloudflare Access 配置权限归老卢，外包提供最小权限建议清单
- **假设 4**：Next.js staging 与 production 均已在 Railway 部署，M1-R 基于现有服务实施（2026-04-19 老卢确认）
- **假设 5**：生产 DNS 现由老卢管理
- **假设 6**：M1-R 后续实施工作由 codex / opencode AI 编码端承接，老杨负责治理审查

---

## 11. v1 → v2 变更对照表（给审阅者快速定位）

| v1 位置 | v2 处理 |
|---|---|
| §1 品牌定位 | 全部保留 |
| §3 信息架构 | 全部保留；仅数据源映射中"Astra 主题模板渲染"改为"Next.js 渲染" |
| §4 R1-R29 | 全部保留 |
| §5 双语策略 | 全部保留 |
| §6 R30-R34 | 全部保留 |
| §9 M1 完成定义 | 扩展为 M1-R Layer A + Layer B 验收（本文 §6） |
| §10 D4 "Astra Child front-page.php 渲染首页" | **取代**为 Headless Hybrid |
| §12 Scope Boundaries "不做视觉精修" | **取消**；视觉完整度纳入 M1-R 验收 |

---

## 12. 下一步

本 PRD 修订版经老卢审核通过后：

1. 进入实施方案：`docs/plans/2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md`
2. 实施方案拆 Unit 0–8，外包团队可直接接手
3. M1-R 验收通过后再起 M2-R（表单 + 订阅 + 生产切换）

---

## 附录 A. 对照资源

- v1 PRD：[docs/brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md](../brainstorms/2026-04-10-mindhikers-homepage-cms-requirements.md)
- v1 实施方案：[docs/plans/2026-04-12-001-feat-m1-cms-content-model-plan.md](2026-04-12-001-feat-m1-cms-content-model-plan.md)
- v2 实施方案：[docs/plans/2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md](2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md)
- 域名边界：[docs/domain-boundary.md](../domain-boundary.md)
- HANDOFF：[docs/dev_logs/HANDOFF.md](../dev_logs/HANDOFF.md)
- M1 专家审查：[docs/dev_logs/M1_REVIEW_FOR_EXPERT.md](../dev_logs/M1_REVIEW_FOR_EXPERT.md)
- 线上视觉基线：[src/components/home-page.tsx](../../src/components/home-page.tsx)
- 线上导航基线：[src/components/navbar.tsx](../../src/components/navbar.tsx)
- 内容字典基线：[src/data/site-content.ts](../../src/data/site-content.ts)
- 现有 CMS 接入：[src/lib/cms/homepage.ts](../../src/lib/cms/homepage.ts)
- 现有 WP mu-plugin：[wordpress/mu-plugins/mindhikers-m1-core.php](../../wordpress/mu-plugins/mindhikers-m1-core.php)

*文档结束。*

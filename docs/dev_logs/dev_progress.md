# MIN-110 开发进度总表

> 仓库：`Mindhikers-Homepage`
> 分支：`codex/cyd-stumpel-home-exploration`
> Linear：`MIN-110`
> Staging：`https://wordpress-l1ta-staging.up.railway.app`
> 最后更新：2026-04-12

---

## 项目概述

将 Mindhikers 官网从 Next.js 自建前台迁移到 WordPress 模版站（Astra - Interior Designer），在 Railway 上搭建 staging 环境，完成品牌对齐、中英文双轨、SEO 收口和模板残留清理。

---

## 阶段一：基础建设（03-29 → 04-04）

### 03-29 ~ 03-30：CMS 方案调研与选型

- 评估多种 CMS 方案（Headless WordPress / Strapi / 自建 API）
- 最终决策：WordPress 模版站 + Elementor 编辑器 + Railway 托管
- 产出方案：
  - `Plan/2026-03-29_Mindhikers_Fullsite_WordPress_CMS_Architecture.md`
  - `Plan/2026-03-29_Mindhikers_Railway_Three_Service_Execution_Plan.md`
  - `Plan/2026-03-30_Mindhikers_WordPress_Template_CMS_Implementation_Plan.md`

### 03-30 ~ 04-02：Railway 部署与调试

- 在 Railway 部署 WordPress 实例（`WordPress-L1ta`）
- 绑定 staging 地址：`wordpress-l1ta-staging.up.railway.app`
- 解决域名重定向、端口治理、Cloudflare Access 等问题
- git 提交：
  - `4d1fe02` refs MIN-23 MIN-24 MIN-29 close homepage hardening and phase cleanup
  - `d9e8e23` refs MIN-110 record homepage domain go-live and production cleanup

### 04-04：模板导入与基线建立

- 导入 `Astra - Interior Designer` 模板
- 清理默认垃圾内容（`Hello world!`、`Sample Page`、默认评论）
- 导航收敛为 `About / Product / Blog / Contact`
- Blog 统一到 WordPress Posts（`page_on_front=1807`、`page_for_posts=1809`）
- 导入首批 3 篇博客文章
- 重置管理员账号 `mindhikers_admin`
- 产出方案：`docs/plans/2026-04-04_MIN-110_WordPress_Template_Rebuild_Execution_Plan.md`

---

## 阶段二：品牌对齐（04-05 → 04-06）

### 04-05：Phase 0 基线盘点

- 建立线上 vs staging 差异基线
- 记录模板默认配色（Poppins / Raleway）、默认 logo（km-logo.svg）、默认文案
- 产出：
  - `docs/plans/2026-04-05_MIN-110_Phase0_Baseline_Inventory.md`
  - `docs/plans/2026-04-05_MIN-110_Staging_Setup_Log.md`

### 04-06 上午：Logo + 配色 + 字体

- **Unit 1 Smoke 基线**：首页 / 博客 / 文章详情均 200 ✅
- **Unit 2 线上基线**：提取线上颜色（`#386652` 主绿）、字体（Cabinet Grotesk / Clash Display）、Hero 文案 ✅
- **Unit 3 Logo 替换**：
  - Header：上传 `MindHikers.png` 到媒体库，设置 `custom_logo` ✅
  - Footer：km-logo.svg 通过 Additional CSS `display: none` 隐藏 + CSS `::before` 插入"心行者 Mindhikers"文本 ✅
- **Unit 4 配色字体注入**：
  - 通过 Additional CSS 注入全局覆盖：`Cabinet Grotesk`（正文）、`Clash Display`（标题）、`#386652`（主色）、`#f9fafb`（背景） ✅
  - 产出：`docs/plans/MIN-110_Additional_CSS_Draft.css`

### 04-06 下午 ~ 深夜：主文案双轨 + SEO

- **Unit 5 主文案中文化**（多会话接力）：
  - Session 2 (Claude)：整理中英文对照表，受限于 headless Elementor 编辑
  - Session 3 (Claude)：REST API 获取 Elementor 数据，整理完整对照表
  - Session 4 (Codex)：通过 Elementor 数据层直接改写首页主文案并发布
    - Hero / Product / Blog 正文全切中文 ✅
    - 主导航改为 `关于 / 产品 / 博客 / 联系` ✅
    - Footer 三列组件全切中文 ✅
    - 模板统计项（400+ Projects Done 等）从数据层清空 ✅
  - Session 5 (Codex)：
    - Header Button `Let's Talk` → `开始联系` ✅
    - Footer 版权行 → `版权所有 © 2026 心行者 Mindhikers Staging` ✅
    - `/en` 独立英文页创建（page 1995）✅
    - 中文首页 Footer 补 `/en/` 链接 ✅
    - 首页 `<title>` 修复为中文 ✅
    - og/twitter 元信息切中文 ✅
  - Session 6 (Claude)：
    - 打通 `wp-login.php` cookie 认证链路
    - 定位 SureRank SEO 字段写入路径

### 04-06 阻塞与止损记录

- Footer Logo 无法通过 REST API 修改 → 止损后用 Additional CSS 方案绕过
- Elementor 编辑器在 headless 环境交互受限 → 改为直接操作数据层
- `agent-browser` 不稳定 → 改用 REST API + curl 方案
- SureRank `GET /post/settings` 触发致命错误 → 记录为已知问题，不纠缠
- 产出：`docs/plans/2026-04-06_MIN-110_Elementor_Homepage_Render_Blocker.md`、`MIN-110_Footer_Logo_Blocker.md`

- git 提交：`42d8926` refs MIN-110 record wordpress template rebuild plan and handoff

---

## 阶段三：SEO 收口与验收（04-12）

### 04-12：Unit 5 收口 + Unit 6 + Unit 7

- **Unit 5.1 首页 meta description 修复** ✅
  - 问题：`meta description` 仍为旧英文
  - 发现正确 API：`POST /surerank/v1/page-seo-checks/fix`（`type: "content-generation"`）
  - 结果：→ `心行者 Mindhikers — 一个关于研究、产品与写作的品牌主页。`

- **Unit 5.2 /en 英文页 HTML 级验收** ✅
  - 200、SEO 全英文、语言切换双向可用

- **Unit 5.3 /en SEO 全字段修复** ✅
  - `<title>` → `Mindhikers | Research, Products, and Writing`
  - `meta description` / `og:*` / `twitter:*` 全切英文

- **Unit 6 模板残留验证** ✅
  - 可见文本层：Kyle Mills / Interior Designer / Projects Done / Powered By / Let's Talk 全部清零
  - km-logo.svg 仍在 HTML 但 CSS 已隐藏

- **Unit 7 Smoke Round 2 全量验证** ✅

  | 端点 | 状态码 | 关键检查 |
  |------|--------|----------|
  | `/` 首页 | 200 | title 中文 / meta 中文 / og 中文 / 导航中文 / Footer 中文 / noindex ✅ |
  | `/blog/` | 200 | 3 篇文章 / 导航中文 ✅ |
  | 文章详情 | 200 | 标题正常 / 正文可读 ✅ |
  | `/en/` | 200 | title 英文 / meta 英文 / og 英文 / 语言切换 ✅ |
  | `wp-admin/` | 200 | 后台可访问 ✅ |

---

## 当前状态

### 已完成

- [x] WordPress 模版站搭建（Astra + Elementor）
- [x] Staging 环境部署（Railway）
- [x] 默认内容清理（垃圾文章、评论、模板页面）
- [x] 导航收敛（4 项）
- [x] Blog 单轨化（WordPress Posts）
- [x] Logo 替换（Header + Footer）
- [x] 配色与字体注入（Additional CSS）
- [x] 中文首页主文案全面中文化
- [x] 主导航 / Footer / Header CTA / 版权行中文化
- [x] `/en` 独立英文页创建
- [x] 语言切换双向入口
- [x] 中英双页 SEO 全字段收口（title / meta description / og / twitter）
- [x] 模板品牌残留可见层清零
- [x] 两轮 Smoke 验收通过
- [x] 日志与 HANDOFF 落盘

### 未完成 / 已知限制

- [ ] `/en` 主题层中文 Header/Footer 在 HTML 中仍可见（CSS/JS 视觉隐藏，未经真实浏览器截图确认）
- [ ] `GET /surerank/v1/post/settings` 仍触发 WordPress 致命错误
- [ ] km-logo.svg 仍在 HTML（CSS 隐藏）
- [ ] staging 保持 `noindex`
- [ ] `homepage-staging.mindhikers.com` 未建立
- [ ] 首页第二轮精修（Hero 人像图、背景轮播、区块重排）
- [ ] git 本地有大量未提交变更（治理骨架文档、日志、方案等）

### 下一步方向

1. 真实浏览器截图验收（`/` 和 `/en`）
2. staging 域名绑定（`homepage-staging.mindhikers.com`）
3. git 提交落盘（将治理骨架和本轮文档提交到分支）
4. 上线准备（noindex 复查、域名切换）
5. 首页第二轮精修

---

## 关键 API 速查

| 目的 | SureRank API | 备注 |
|------|-------------|------|
| 写 `<title>` / `<meta description>` | `POST /surerank/v1/page-seo-checks/fix` | `type:"content-generation"`, `input_key`, `input_value`, `id` |
| 写 og/twitter 字段 | `POST /surerank/v1/post/settings` | `post_id`, `metaData: {og_title, ...}` |
| 全局 SEO 设置 | `GET/POST /surerank/v1/admin/global-settings` | `data: {...}` |
| ⚠️ 崩溃 | `GET /surerank/v1/post/settings?post_id=&post_type=` | 不要用 |

---

## 参与人员

| 角色 | 工具 | 贡献 |
|------|------|------|
| 老卢 | 决策者 | 方向决策、凭据提供、验收确认 |
| Cyd (Codex) | OpenAI Codex | 基础建设、内容清理、导航收敛 |
| Claude (Anthropic) | Claude Code | 基线建立、Logo/配色、SEO 收口、Smoke 验收 |
| Codex (OpenAI) | Codex | 主文案中文化、主题层修复、/en 创建 |
| Claude (本轮) | OpenCode / glm-5 | meta description 修复、/en SEO、Unit 6/7 收口 |

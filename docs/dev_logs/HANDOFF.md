🕐 Last updated: 2026-04-20 12:30
🌿 Branch: `staging`
📌 Base commit: `fe246ec`（staging HEAD，已推送 origin/staging）
🚀 Push status: ✅ `refs MIN-162 docs: staging 全链路验收复盘 + 首批 rules.md`

## 当前状态：staging 全链路完整贯通 🟢

## 交接入口（新会话请从这里开始）

- 工作目录：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- 工作分支：**`staging`**
- **PRD（必读）**：`docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md`
- **实施方案（必读）**：`docs/plans/2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md`
- **本轮战果复盘**：`docs/dev_logs/2026-04-20.md`
- **首批技术规则**：`docs/04_progress/rules.md`
- staging 前端：`https://mindhikers-homepage-staging.up.railway.app`
- staging WP：`https://wordpress-l1ta-staging.up.railway.app`
- Linear 本轮主 issue：[MIN-162](https://linear.app/mindhikers/issue/MIN-162)（MIN-110 子 issue）

## 本轮已完成（2026-04-20）

1. ✅ **头号阻塞破局**：m1-rest 插件通过独立 ZIP 走 WP Admin 上传通道装进 staging WP 容器
2. ✅ **Blog REST 全通**：`/wp-json/mindhikers/v1/blog` 返回文章 + 分类聚合
3. ✅ **前台 Blog 列表/详情**（中英文）
4. ✅ **Contact 区块**（中英文首页 `#contact`）
5. ✅ **手机竖屏**（iPhone 14 Pro 模拟，5 张页面）
6. ✅ **Next.js `/api/revalidate` 端点**（curl POST HTTP 200）
7. ✅ **Revalidate 完整集**：插件 v1.1.0 追加 Carbon Fields Revalidate 字段注册 → WP 后台出现 "Revalidate 配置" 菜单 → 填 URL + Secret → 改 Blog 保存触发 → 前台自动同步
8. ✅ **治理文档 commit 入 origin/staging**：HANDOFF + 2026-04-20.md + rules.md 首批 5 条

## 🔴 下一会话重点任务：运维手册重写

### 背景

现有 `docs/operations-guide.md`（2026-04-16 更新）是**基于旧"WordPress 全栈模板渲染"架构**写的，与当前 **"WordPress Headless + Next.js 前台"** 架构大面积对不上。交接给下一会话重写。

### 现状审校（由本会话已完成）

#### 已过时内容（要删/改）

| 章节 | 问题 |
|---|---|
| §1 环境信息速查 | 缺 Next.js staging 前端 URL（现在是两个服务共同撑起网站，不是单一 WP） |
| §3 首页五大区块内容管理 | 讲"通过 Astra Child Theme PHP 模板渲染"——现在由 Next.js 渲染，WP 只出数据 |
| §9 样式与品牌视觉维护 | 讲"改 style.css 重新部署 child theme"——现在样式在 Next.js Tailwind，WP 主题样式基本不影响前台 |
| §10 部署流程 | 讲 Railway SSH 改 PHP / WP Admin 上传主题 zip——实际主线是 Next.js 仓库 git push 触发 Railway 自动构建 |
| §11.1 / §11.2 / §11.4 / §11.5 | 排错方向都指向 front-page.php、child theme，与 Next.js 前台无关 |
| §12 紧急回滚 | "切回 Astra 父主题"回滚失去意义 |
| §13 红线 #6 "删除 mindhikers-cms-core.php" | 措辞基于旧方案 |
| §14.3 模板文件与数据源映射 | 讲 PHP template-parts——对应新架构是 Next.js React 组件 + REST API |

#### 新架构缺失内容（要补）

1. **Headless 数据链路全貌**：WP Carbon Fields → `/wp-json/mindhikers/v1/*` → Next.js `src/lib/cms/*.ts` → React 组件 → 用户
2. **m1-rest 插件运维**：独立 ZIP 上传通道、函数名唯一前缀策略、容器重建会丢失的预警、当前 v1.1.0 位置 `/tmp/m1-rest.zip`（本地非入仓物，长期要固化）
3. **Revalidate webhook 链路**：WP 后台两个字段 + Railway `REVALIDATE_SECRET` 环境变量 + `src/lib/cms/constants.ts` Cache Tag 白名单（`blog-posts` / `homepage-zh` / `homepage-en` / `product-{slug}`）
4. **两个 Railway 服务的职责分工**：`Mindhikers-Homepage`（Next.js 渲染）vs `WordPress-L1ta`（纯 CMS），各自的 Variables、部署方式、重启影响
5. **变更生效方式对照表**（见下节"模块维护地图"）

### 重写方案 —— 推荐走 B

- **A.** 在旧手册顶部插"⚠️ 架构变更说明 (2026-04-20)"过期告示，标注失效章节 —— 5 分钟，但治标不治本
- **B.** 【推荐】另起新手册 `docs/operations-guide-headless.md`，按新架构重写；旧手册加头部跳转提示，保留作为历史 —— 1~2 小时
- **C.** 直接原地推倒重写 —— 风险较大，历史信息丢失

### 重写手册骨架建议

新手册应该包含的章节（供下一会话参考）：

1. 架构总览（两个 Railway 服务 + 数据流向图）
2. 模块维护地图（见下节"几个模块都在哪儿维护"，直接搬进来）
3. 内容编辑日常流程（WP Admin 各菜单的用途）
4. Revalidate 链路工作原理 + 故障排查
5. m1-rest 插件部署流程（独立 ZIP 通道 + 固化路径）
6. 前台代码开发流程（分支 / 部署 / 环境变量）
7. 变更生效对照表（文案改 vs 代码改 vs 样式改）
8. 常见问题排错（基于新架构）
9. 紧急回滚（基于新架构）
10. 红线与禁忌（基于新架构）
11. 字段速查表（可从旧手册 §14 直接搬运，那部分还有效）

## 📍 几个模块都在哪儿维护（下一会话直接搬进新手册）

| 要改的东西 | 在哪改 | 生效方式 |
|---|---|---|
| 首页文案/图片（Hero/About/Contact） | WP Admin → 对应"管理"菜单 | 保存后自动触发 Revalidate webhook → 前台 5 秒内更新 |
| 博客文章 | WP Admin → 文章 | 同上（tag: `blog-posts`） |
| 产品 | WP Admin → 产品 | 同上（tag: `product-{slug}` + `homepage-zh/en`） |
| 前台外观/布局/交互 | 代码仓库 `src/components/**/*.tsx` + `src/app/**/*.tsx` | git push → Railway 自动构建 Next.js |
| 前台样式/配色/字体 | `src/app/globals.css` + Tailwind 配置 | 同上 |
| WP REST API 逻辑（返回字段、查询条件） | 仓库 `wordpress/mu-plugins/m1-rest/` | ⚠️ 改完必须重新打 ZIP 从 WP Admin 上传；容器封闭，git push 不生效 |
| WP CPT / Carbon Fields 字段定义 | 仓库 `wordpress/mu-plugins/mindhikers-m1-core.php` | ⚠️ 同上，需走独立插件上传路径 |
| Revalidate Secret | **两处同步**：Railway `Mindhikers-Homepage` → Variables `REVALIDATE_SECRET` + WP Admin → Revalidate 配置 → Secret 字段 | 改完 Railway 端触发 Next.js 重部署 |
| 域名 / SSL | Railway Dashboard → 对应服务 → Settings → Domains | 立即生效 |
| 环境变量 | Railway Dashboard → 各服务 → Variables 标签 | 改完触发重部署 |

### 一句话总结（下一会话开头可直接引用）

- **内容**（文字/图片/博客/产品）在 **WP Admin**
- **前台代码**（布局/交互/样式）在 **Next.js 仓库**（即本 repo）
- **WP 插件代码**（REST API、字段定义）在仓库 `wordpress/mu-plugins/`，但**部署通道特殊**——必须打 ZIP 走 WP Admin 上传
- **配置**（URL/Secret/环境变量）在 **Railway Dashboard**

## 其他待办（按优先级排序）

### 1. 运维手册重写（上面已展开）
**建议 issue**：新开 Linear issue 挂 MIN-110 下，由下一会话执行

### 2. 定时炸弹：m1-rest 插件固化到仓库 + Dockerfile
- **风险**：当前 m1-rest 是手工 WP Admin 上传的 v1.1.0（`/tmp/m1-rest.zip`），staging WP 容器若被 Railway 重建，插件会丢
- **长期方案**：按 ce-plan 流程开新 Linear issue，把 m1-rest 作为普通插件纳入镜像构建（Dockerfile COPY 到 `wp-content/plugins/`）
- **临时止血**：已在截图留档位置 `/tmp/m1-rest.zip` 和源码 `/tmp/m1-rest-pkg/m1-rest/`（非入仓）

### 3. 合并 staging → main → production
- 前置：上述运维手册 + 插件固化完成
- 注意：production WP 容器同样缺 m1-rest，合并前要先按同样方式把 ZIP 装进 production WP
- production 端 `REVALIDATE_SECRET` 也要配齐，URL 改为 `https://www.mindhikers.com/api/revalidate`

## 约束与红线（老杨纪律）

1. ❌ 不在 `main` 直接开发
2. ❌ 未经老卢确认不擅改代码
3. ❌ 每次 commit 必须有 Linear issue（`refs MIN-xx`）
4. ❌ commit / push / merge 前必须显式请示
5. ✅ 治理文档与代码变更分开 commit（本轮示范：MIN-162 纯文档 commit）
6. ✅ 敏感凭证落会话后尽快轮换（本轮 `REVALIDATE_SECRET` 老卢决定暂不轮换）

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
| staging | `Mindhikers-Homepage` ✅ | `WordPress-L1ta` ✅ | `mindhikers-homepage-staging.up.railway.app` + `wordpress-l1ta-staging.up.railway.app` |

## 技术栈

- Next.js 16.1.7, React 19, TypeScript, Tailwind 4
- `revalidateTag` 在 Next.js 16 需要 2 个参数：`(tag: string, profile: string \| CacheLifeConfig)` —— 使用 `"default"`
- WP 插件加载顺序：`mu-plugins/*` 早于 `plugins/*`；同名 container/function 会冲突（本轮教训，详见 rules.md WP-001/WP-002）

## 后台账号（staging）

- WP Admin：`https://wordpress-l1ta-staging.up.railway.app/wp-admin/`
- 用户名：`mindhikers_admin`
- 密码：不要入仓；通过安全渠道获取

## 本轮产物清单

### 已入仓（commit `fe246ec`）
- `docs/dev_logs/HANDOFF.md`（本文件）
- `docs/dev_logs/2026-04-20.md`
- `docs/04_progress/rules.md`（新目录）

### 未入仓（本地 /tmp，后续 issue 固化）
- `/tmp/m1-rest-pkg/m1-rest/` —— 插件源码（含新造的 `m1-rest.php` 主入口）
- `/tmp/m1-rest.zip` —— v1.1.0（SHA `0d05227b`），当前 staging WP 运行的是这版

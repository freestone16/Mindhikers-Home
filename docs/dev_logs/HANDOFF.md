🕐 Last updated: 2026-04-19 22:30
🌿 Branch: `staging`
📌 Base commit: `bb8635e`（main HEAD）
🚀 Push status: ✅ 已推送 `origin/staging`

## 当前状态：staging Next.js 部署成功，Blog API 待修复 🟡

## 交接入口（新会话请从这里开始）

- 工作目录：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- 工作分支：**`staging`**
- **PRD（必读）**：`docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md`
- **实施方案（必读）**：`docs/plans/2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md`
- **进度快照**：`docs/dev_logs/PROGRESS_SNAPSHOT_2026-04-19.md`
- staging 前端：`https://mindhikers-homepage-staging.up.railway.app`
- staging WP：`https://wordpress-l1ta-staging.up.railway.app`

## 已完成

1. ✅ staging Root Directory 修复（从 `. ` 改为空）
2. ✅ staging 部署成功（builder: RAILPACK）
3. ✅ production webhook 配置
4. ✅ production railway.json 修复（builder: RAILPACK）
5. ✅ Smoke 验收：首页、产品页、英文页正常
6. ✅ **头号阻塞根因已定位**：远端 `mindhikers-m1-core.php` 是旧版，不含 REST 路由和 `m1-rest` 引用
7. ✅ **远端现场已保护**：旧入口文件已备份为 `mindhikers-m1-core.php.bak.20260419172452`

## 阻塞项（#1 优先级）

**Blog 0 posts — m1-rest 插件未部署到 WP 容器**

- 根因：自定义 REST 端点 `/wp-json/mindhikers/v1/blog` 返回 404
- 原因：m1-rest 插件文件只存在于代码仓库，未同步到 WP 容器
- 文件位置：`wordpress/mu-plugins/m1-rest/`（helpers.php, homepage.php, product.php, blog.php, revalidate.php）
- 目标位置：WP 容器 `/var/www/html/wp-content/mu-plugins/m1-rest/`
- **已尝试的方法**（全部失败）：
  - railway ssh + tee ❌（超时）
  - railway ssh + curl GitHub raw ❌（仓库私有 404）
  - railway ssh + git clone ❌（容器无 git）
  - railway run ❌（新容器非当前运行容器）
  - railway ssh + php -r base64_decode ❌（base64 字符串里的 `(` `)` 被本地 shell 解释，导致 `Syntax error: "(" unexpected`）
  - 交互式 shell stdin ❌（Railway CLI 不支持 TTY）
  - Railway Dashboard Web Shell ❌（当前套餐不提供）
- **本地已生成合并版**：`/tmp/mindhikers-m1-core-merged.php`（1270 行，61KB），但未成功写入远端

## 下一步

### 1. 部署 m1-rest 插件到 WP 容器
需要找到可靠的方式把文件传到 WP 容器：
- 方案 A：通过 WordPress 后台「插件编辑器」手动编辑 `mindhikers-m1-core.php`，把 m1-rest 代码内联进去
- 方案 B：把 `m1-rest/` 打包成独立插件 ZIP，通过 WP Admin 上传安装
- 方案 C：配置 Railway Volume 挂载
- 方案 D：修改 Dockerfile 或构建流程
- 方案 E：使用 Railway 的「Deploy」功能重新部署整个服务

**注意**：此阻塞已超出 AI 端可独立解决范围，需外部专家介入。详见 `docs/dev_logs/PROGRESS_SNAPSHOT_2026-04-19.md`。

### 2. staging 完整验收
- Blog 列表/详情链路
- Contact 区块
- 手机竖屏
- Revalidate webhook

### 3. 合并 staging → main → production

## 约束与红线

1. ❌ 不在 `main` 直接开发
2. ❌ 未经老卢确认不擅改代码
3. ❌ 每次 commit 必须有 Linear issue（`refs MIN-xx`）
4. ❌ commit / push / merge 前必须显式请示

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
| staging | `Mindhikers-Homepage` ✅ | `WordPress-L1ta` ✅ | `mindhikers-homepage-staging.up.railway.app` |

## 技术栈

- Next.js 16.1.7, React 19, TypeScript, Tailwind 4
- `revalidateTag` 在 Next.js 16 需要 2 个参数：`(tag: string, profile: string \| CacheLifeConfig)` — 使用 `"default"`

## 后台账号（staging）

- WP Admin：`https://wordpress-l1ta-staging.up.railway.app/wp-admin/`
- 用户名：`mindhikers_admin`
- 密码：不要入仓；通过安全渠道获取

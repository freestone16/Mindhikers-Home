🕐 Last updated: 2026-04-20 11:20
🌿 Branch: `staging`
📌 Base commit: `c06c062`（staging HEAD）
🚀 Push status: ⏸️ 未提交（本轮只改治理文档）

## 当前状态：staging 核心链路全通 🟢，Revalidate 完整集待装 🟡

## 交接入口（新会话请从这里开始）

- 工作目录：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- 工作分支：**`staging`**
- **PRD（必读）**：`docs/plans/2026-04-18_Mindhikers_Homepage_PRD_Revision_v2.md`
- **实施方案（必读）**：`docs/plans/2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md`
- **本轮战果复盘**：`docs/dev_logs/2026-04-20.md`
- staging 前端：`https://mindhikers-homepage-staging.up.railway.app`
- staging WP：`https://wordpress-l1ta-staging.up.railway.app`

## 本轮已完成（2026-04-20）

1. ✅ **头号阻塞破局**：m1-rest 插件通过独立 ZIP 走 WP Admin 上传通道装进 staging WP 容器
2. ✅ **Blog REST 全通**：`/wp-json/mindhikers/v1/blog` 返回 3 条文章 + 分类聚合
3. ✅ **前台 Blog 列表/详情** 渲染正常（中英文）
4. ✅ **Contact 区块** 验收通过（中英文首页 `#contact`）
5. ✅ **手机竖屏** 验收通过（iPhone 14 Pro 模拟，5 张页面扫过）
6. ✅ **Next.js `/api/revalidate` 端点** 验证通过（`curl POST blog-posts tag → HTTP 200`）

## 待办（优先级排序）

### 1. Revalidate 完整集 — WP 端配置（新 ZIP 已就绪）

- **新 ZIP 位置**：`/tmp/m1-rest.zip`（v1.1.0，SHA `0d05227b`，含 Carbon Fields Revalidate 字段注册）
- **操作步骤**：
  1. WP Admin → 插件 → 停用并删除旧 `Mindhikers M1-R REST API`
  2. 插件 → 上传 → 选 `/tmp/m1-rest.zip` → 安装 → 启用
  3. WP Admin 侧栏应新出现 **"Revalidate 配置"** 菜单（dashicons-update 图标）
  4. 填 `Next.js Revalidate URL` = `https://mindhikers-homepage-staging.up.railway.app/api/revalidate`
  5. 填 `Revalidate Secret` = Railway Next.js staging 的 `REVALIDATE_SECRET`（建议轮换后用新值）
  6. 保存，编辑任一篇 Blog 随便改一下再保存，前台刷新验证内容同步
- **风险**：本次 staging 用的 secret 已在会话里出现过一次，建议轮换后再做完整集测试

### 2. 定时炸弹：插件固化到代码仓 + Dockerfile

- **风险**：m1-rest 插件是手工 WP Admin 上传的，staging WP 容器若被 Railway 重建，插件会丢
- **长期方案**：按 ce-plan 流程开新 Linear issue，把 `m1-rest` 纳入镜像构建
- **临时止血**：每次上传插件后在 WP Admin 截图留档

### 3. 合并 staging → main → production

- 前置：Revalidate 完整集验收通过
- 注意：production 当前 WP 容器也缺 m1-rest，合并前要先按同样方式把 ZIP 装进 production WP

## 约束与红线（老杨纪律）

1. ❌ 不在 `main` 直接开发
2. ❌ 未经老卢确认不擅改代码
3. ❌ 每次 commit 必须有 Linear issue（`refs MIN-xx`）
4. ❌ commit / push / merge 前必须显式请示
5. ✅ 治理文档与代码变更分开 commit
6. ✅ 敏感凭证落会话后尽快轮换

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
- `revalidateTag` 在 Next.js 16 需要 2 个参数：`(tag: string, profile: string \| CacheLifeConfig)` — 使用 `"default"`
- WP 插件加载顺序：`mu-plugins/*` 早于 `plugins/*`；同名 container/function 会冲突

## 后台账号（staging）

- WP Admin：`https://wordpress-l1ta-staging.up.railway.app/wp-admin/`
- 用户名：`mindhikers_admin`
- 密码：不要入仓；通过安全渠道获取

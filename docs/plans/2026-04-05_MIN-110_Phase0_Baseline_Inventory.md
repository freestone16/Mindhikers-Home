# MIN-110 Phase 0 基线清单

日期：2026-04-05  
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`  
分支：`codex/cyd-stumpel-home-exploration`  
关联 Issue：`MIN-110`

## 1. 目的

本文件用于把旧方案冻结成“可回看、可回滚、可迁移”的基线，而不是继续把它当作未来主线。

当前确认的新主线仍然是：

1. 前台直接转向 WordPress 模版站
2. 后台直接使用 WordPress 原生 GUI 管理
3. 首轮首页范围只保留 `Hero / About / Product / Blog / Contact`

## 2. 当前生产拓扑基线

截至 2026-04-05，当前 Railway 生产项目 `Mindhikers-Homepage` 中确认的服务与域名如下：

1. `WordPress-L1ta`
   - 角色：当前 WordPress 管理后台
   - 自定义域名：`homepage-manage.mindhikers.com`
2. `Mindhikers-Homepage`
   - 角色：当前 Next.js 首页前台
   - 自定义域名：`mindhikers.com`
   - 自定义域名：`www.mindhikers.com`
3. `MariaDB-94P8`
   - 角色：WordPress 数据库

当前 volumes：

1. WordPress volume：挂载到 `/var/www/html`
2. MariaDB volume：挂载到 `/var/lib/mysql`

## 3. 当前仓库可复用资产

### 3.1 首页内容迁移底稿

首页静态内容当前仍以仓库内结构化数据为主，主要来源如下：

1. `src/data/site-content.ts`
   - 中文首页基线
   - 英文首页基线
   - 五区块文案、导航、CTA、联系信息
2. `ops/wordpress/homepage-seeds/homepage-zh.json`
   - 中文首页 JSON 迁移种子
3. `ops/wordpress/homepage-seeds/homepage-en.json`
   - 英文首页 JSON 迁移种子

### 3.2 博客迁移底稿

当前仓库内可见的 MDX 文章共有 7 篇：

1. `building-design-systems`
   - 标题：`Building Scalable Design Systems with React and Tailwind`
   - 日期：`2024-12-01`
2. `nextjs-performance-tips`
   - 标题：`10 Next.js Performance Tips for Production Apps`
   - 日期：`2024-12-05`
3. `typescript-best-practices`
   - 标题：`TypeScript Best Practices for Clean, Maintainable Code`
   - 日期：`2024-12-08`
4. `git-workflow-guide`
   - 标题：`Git Workflow Guide: From Chaos to Clarity`
   - 日期：`2024-12-10`
5. `api-design-principles`
   - 标题：`REST API Design Principles That Stand the Test of Time`
   - 日期：`2024-12-12`
6. `testing-react-apps`
   - 标题：`Testing React Applications: A Practical Guide`
   - 日期：`2024-12-14`
7. `remote-work-productivity`
   - 标题：`Mastering Remote Work: Productivity Tips from a Digital Nomad`
   - 日期：`2024-11-25`

这些文章当前仍可作为迁入 WordPress Posts 的首批底稿。

### 3.3 当前实现链路资产

以下代码仍然描述了旧方案的实现方式，因此要视为“参考资产”，不再继续扩建：

1. `src/lib/cms/homepage.ts`
   - 首页通过 WordPress API 拉取数据，不满足校验时回退静态内容
2. `src/lib/cms/index.ts`
   - 博客支持 `mdx / wordpress / hybrid`
3. `src/app/api/revalidate/route.ts`
   - 已具备前台 revalidate 入口
4. `src/app/page.tsx`
5. `src/app/en/page.tsx`
6. `src/app/blog/page.tsx`
7. `src/app/blog/[slug]/page.tsx`

## 4. 当前 SEO 基线

### 4.1 首页基线

当前静态基线中的首页 metadata 如下：

1. 中文首页标题：`心行者 Mindhikers`
2. 中文首页描述：
   - `心行者 Mindhikers 是一个双语品牌主页，用来承载内容、产品实验、博客输出与长期创作协作。`
3. 英文首页标题：`心行者 Mindhikers`
4. 英文首页描述：
   - `心行者 Mindhikers is a bilingual brand home for product experiments, writing, and a quieter long-form creative practice.`

### 4.2 站点根 URL 基线

当前仓库内的站点根 URL 仍写在：

1. `src/data/resume.tsx`
   - `DATA.url = https://www.mindhikers.com`

这意味着后续正式切换时，需同步核对：

1. `www -> apex` 跳转规则
2. canonical 口径
3. OG / 社交分享域名口径

## 5. 当前已知偏差与风险

1. 旧首页 CMS 链路已经接通过，但不应再继续作为长期主线投入
2. 当前博客实现仍支持 `hybrid`，与“统一回 WordPress Posts”目标不一致
3. 当前仓库已有一批未提交的 CMS 接线探索代码，需要明确视作旧路线收尾资产
4. WordPress staging 在 2026-04-05 前尚未形成正式基线记录
5. 测试资产已有 fallback 产物，但此前没有正式 `testing/` 协议目录

## 6. Phase 0 完成定义

当以下事项全部满足时，可认为旧方案已成功冻结为迁移基线：

1. 当前生产拓扑已建账
2. 首页内容迁移底稿已明确
3. 博客迁移底稿已明确
4. SEO 基线已建账
5. 测试协议已补齐最小结构
6. staging 环境已建立并开始承接新主线

## 7. 下一步执行建议

1. 继续完善 staging 环境可访问性与域名口径
2. 在 staging 中导入 `Astra - Interior Designer`
3. 按五区块重建首页
4. 把 Blog 正式运营入口统一到 WordPress Posts
5. 用 `testing/` 协议和 `agent-browser` 接住后续 smoke / 回归

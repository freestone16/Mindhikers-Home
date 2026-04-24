# 2026-04-24 Homepage Production Content Hotfix

## 一句话结论

先用一个独立 hotfix 把生产首页变成“可立即给人看”的状态：主页文案按 `contents/MindHikers_Homepage_Content_Fill_Guide.md` 收口，三篇「碳硅进化论」文章进入本地 MDX fallback，并临时让 homepage 优先读取本地内容，避免被 production WordPress API 的旧文案覆盖。

## 背景

当前 `experiment/wp-traditional-mode` 分支正在做 WP 单栈迁移 Phase 1，不能混入面向生产展示的内容热修。

Linear 归属：`MIN-166` — Homepage 生产内容急救上线：主页可看 + 碳硅进化论三篇文章。

线上核验结果：

1. `https://mindhikers.com/` 仍显示旧文案，如“有呼吸感”“不是履历页面”“博客内容还在整理中”。
2. `https://mindhikers.com/blog` 当前为 `Blog 0 posts`。
3. `https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh|en` 可读，但返回旧 homepage payload。
4. `https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/blog?...` 当前返回 404。
5. `https://homepage-manage.mindhikers.com/wp-admin/` 被 Cloudflare Access 保护，不能在无登录态下直接后台编辑。

## 不做什么

1. 不推 `experiment/wp-traditional-mode`。
2. 不改 production WordPress 插件、主题、Railway 服务结构。
3. 不动 DNS。
4. 不删除或退役旧 production snippet / M1 REST 相关能力。

## P0：主页内容立即可见

做法：

1. 更新 `src/data/site-content.ts` 的 zh/en 本地 homepage 内容。
2. 更新 `ops/wordpress/homepage-seeds/homepage-zh.json` 与 `homepage-en.json`，保证后续 CMS 回填数据与当前前台一致。
3. 在 `src/lib/cms/homepage.ts` 增加 `HOMEPAGE_SOURCE`：
   - 默认：`local`
   - 可选：`wordpress`
4. production 热修期间默认走本地内容，避免被线上旧 WP payload 覆盖。

回滚：

1. 设置环境变量 `HOMEPAGE_SOURCE=wordpress`，恢复 production 从 WordPress homepage API 读取。
2. 或回滚本 hotfix commit。

## P1：三篇文章真实展示

做法：

1. 将 `contents/` 下三篇文章转换为 `content/*.mdx`：
   - `carbon-silicon-01-obsolete-machine.mdx`
   - `carbon-silicon-02-embodied-philosophy.mdx`
   - `carbon-silicon-03-ethics-cleanroom.mdx`
2. 保持现有 `listPosts()` 逻辑：production 如果 WordPress blog API 失败，会 fallback 到 MDX。
3. 更新 `/blog` 页面文案，不再显示通用模板口径。

验收：

1. 首页 Hero 不再出现“有呼吸感”。
2. About 不再出现“不是履历页面”。
3. Blog 区块展示 3 篇真实文章。
4. `/blog` 显示 3 posts。
5. 三篇 `/blog/{slug}` 详情页可打开。
6. `/en` 首页显示英文版新文案。

## 后续正式收口

1. 登录 production WordPress 后台，将同一份 homepage zh/en payload 回写到 CMS。
2. 修复或恢复 `mindhikers/v1/blog` production endpoint。
3. 确认 CMS 内容与前台 hotfix 等价后，将 `HOMEPAGE_SOURCE` 改回 `wordpress`。

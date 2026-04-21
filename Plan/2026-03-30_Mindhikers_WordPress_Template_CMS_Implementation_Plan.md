# MindHikers WordPress 模板 CMS 实施计划

日期：2026-03-30
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
分支：`codex/cyd-stumpel-home-exploration`
目标：以 Railway 模板创建的 `WordPress` 服务作为新的 Homepage CMS 主候选，安全完成从“博客接入”到“全站内容后台”的第一阶段实施。

## 1. 当前结论

### 1.1 模板服务是否可用

已实际验证以下入口：

1. `https://wordpress-production-1486.up.railway.app/`
2. `https://wordpress-production-1486.up.railway.app/wp-login.php`
3. `https://wordpress-production-1486.up.railway.app/wp-admin`

验证结果：

1. 三个入口都能正常进入 `wp-admin/install.php`
2. 页面标题为 `WordPress › Installation`
3. 可见语言选择与 `Continue` 按钮
4. 不再出现旧 `Primary` 的 `403/404`
5. 不再出现 `mindhikers-cms-v2` 的 `502/CRASHED`

结论：

1. Railway 模板创建的 `WordPress` 服务可以作为新的 CMS 主候选继续推进
2. 当前不需要推翻方向
3. 但需要基于这个新主候选重写实施计划，彻底切断旧 `Primary` 与 `mindhikers-cms-v2` 的施工惯性

### 1.2 它能否适配当前 Homepage 内容

可以，但不是“零改造直接接管”。

当前仓库事实：

1. 博客内容已经有 `mdx | wordpress | hybrid` 抽象
2. `src/lib/cms/wordpress.ts` 已能通过 WordPress REST API 拉取博客文章
3. `/` 与 `/en` 主页主体内容仍主要写在 `src/data/site-content.ts`
4. 这些首页内容已经是结构化对象，不是散落模板字符串

这意味着：

1. 当前首页内容天然适合映射到 WordPress 结构化字段
2. WordPress 可以接住现有内容模型
3. 但不能靠“装完 WordPress”自动获得完整后台
4. 仍需要一层 `mindhikers-cms-core` 之类的业务插件来注册字段、输出稳定 API、触发 revalidate

一句话结论：

`WordPress` 可以适配当前主页内容，而且比以前的坏现场更适合；但正确适配方式是“结构化字段 + Next.js 渲染”，不是“把整个主页交给 WordPress 主题或页面搭建器”。

## 2. 相对旧方案的变化与裁剪

## 2.1 保留的能力

本方案仍保留以下核心目标：

1. `WordPress` 负责内容管理后台
2. `Next.js` 继续负责前台渲染、视觉、交互、SEO 与缓存
3. 最终后台域名仍是 `homepage-manage.mindhikers.com`
4. 前台首页仍是 `www.mindhikers.com`
5. 博客文章继续纳入 WordPress 管理
6. Homepage 主体内容逐步进入 CMS

## 2.2 主动裁剪的能力

为了安全、简单、健壮，本轮明确裁掉以下“看起来方便、长期却危险”的能力：

1. 不做 WordPress 主题直出前台
2. 不做重型 page builder
3. 不允许后台自由拼整页 HTML 当作核心交付模式
4. 不继续复用旧 `Primary`
5. 不继续在 `mindhikers-cms-v2` 上叠加补丁
6. 不在 Cloudflare Access 准备好之前，把最终后台长期裸露在公网
7. 不在方案确认前就做旧服务清理

## 2.3 功能上是否比以前“缩水”

从“后台灵活度”看，第一阶段是有意收敛，不是能力退化。

减少的是：

1. 后台对页面布局的任意控制
2. 内容编辑对样式和组件结构的直接控制
3. 各种插件式临时能力堆叠

保住的是：

1. 内容团队能管理首页核心文案、产品区、联系区、博客区
2. 博客文章能正常创建、分类、发布
3. 前台视觉不会被后台误操作破坏
4. API、缓存、回滚、审计边界更清晰
5. 后续还能平滑扩展到产品页、站点设置、多语言和媒体

结论：

这不是“功能缩水”，而是把“任意编辑”换成“可控编辑”。对于 Mindhikers 这种品牌首页，这是正确裁剪。

## 3. 是否符合四个原则

## 3.1 安全

基本符合，但必须补完整这几项才算达标：

1. 后台最终只使用 `homepage-manage.mindhikers.com`
2. 后台前面接 Cloudflare Access
3. 使用结构化字段，不让编辑器直接输出整页自由 HTML
4. 复用 Next.js 已有 revalidate 接口时，必须使用独立 secret
5. 关闭 WordPress 在线文件编辑与不必要入口

## 3.2 简单

比旧方案明显更简单。

原因：

1. 主候选只剩一个：Railway 模板 `WordPress`
2. 前后台分工清楚：`WordPress` 管内容，`Next.js` 管展示
3. 不再维护多个半坏的 CMS 服务
4. 不依赖重型页面搭建器

## 3.3 健壮

当前方向是健壮的，但前提是坚持“分阶段验收”。

关键点：

1. 先拿到稳定可安装的 WordPress
2. 再做业务插件
3. 再接首页 API
4. 再接域名与 Access
5. 最后才谈旧服务清理

只要顺序不乱，健壮性比之前高很多。

## 3.4 可扩展

符合，而且扩展路径自然。

后续可以平滑扩展到：

1. `homepage-zh` / `homepage-en`
2. 产品页内容模型
3. 站点设置单例
4. 首页精选博客控制
5. SEO 字段
6. 媒体资源管理
7. 审核发布工作流

## 4. 推荐最终架构

## 4.1 架构分层

1. `Mindhikers-Homepage`
   - Next.js 前台
   - 负责 `www.mindhikers.com`
   - 负责页面渲染、SEO、缓存、交互
2. `WordPress`
   - 新 CMS 主候选
   - 负责后台内容录入与管理
   - 最终管理域名为 `homepage-manage.mindhikers.com`
3. `MariaDB-e3je`
   - 作为当前模板 WordPress 的数据库
   - 与该 WordPress 成对维护

## 4.2 内容边界

第一阶段纳入 CMS 的内容：

1. 首页元数据
2. 导航文案
3. Hero 文案与 CTA
4. About 区块
5. Product 区块
6. Blog 区块文案
7. Contact 区块
8. 博客文章

第一阶段暂不纳入 CMS 的内容：

1. 视觉样式
2. 页面动效
3. 组件布局实现
4. OG 图片代码
5. 任意自由布局能力

## 4.3 数据形态

推荐采用：

1. `mh_homepage`
   - 两条记录：`zh`、`en`
2. `mh_site_settings`
   - 单例 options
3. 原生 `post`
   - 博客文章
4. 第二阶段再引入 `mh_product_page`
   - 如果首页产品区之外还需要独立产品详情页后台

## 5. 新的实施阶段

## 阶段 A：冻结新主候选基线

目标：

1. 正式确认 `WordPress` 为唯一 CMS 主候选
2. 冻结旧 `Primary` 和 `mindhikers-cms-v2`
3. 记录当前模板服务状态、部署 ID、数据库配对关系

动作：

1. 记录 `WordPress` 当前 Railway 域名和部署信息
2. 记录 `MariaDB-e3je` 与 `wordpress-volume`
3. 明确旧服务只保留，不再补丁

验收：

1. 主候选只有一个
2. 旧现场进入冻结态

## 阶段 B：完成 WordPress 最小安全安装

目标：

1. 把模板服务从“安装页可打开”推进到“后台可登录”

动作：

1. 完成 WordPress 初始化安装
2. 建立管理员账号
3. 设置站点标题与基础语言
4. 关闭文件在线编辑
5. 清点默认插件与主题，移除明显不需要的部分

验收：

1. `/wp-login.php` 可登录
2. `/wp-admin` 可进入后台
3. 后台核心页面无明显报错

## 阶段 C：实现 `mindhikers-cms-core`

目标：

1. 为 Homepage 提供业务专用、结构化、稳定的内容模型

动作：

1. 在仓库中创建 `mindhikers-cms-core`
2. 注册 `mh_homepage`
3. 注册首页所需结构化字段
4. 注册 `mh_site_settings`
5. 提供面向 Next.js 的自定义 REST 输出
6. 在内容变更后回调 Next.js revalidate

验收：

1. 后台可以编辑 `zh` / `en` 首页内容
2. REST 输出字段稳定
3. 不依赖 page builder

## 阶段 D：Next.js 接入 Homepage CMS API

目标：

1. 让首页主体内容从 WordPress 读取，但保留现有组件渲染

动作：

1. 在 `src/lib/cms` 旁新增 homepage 内容获取层
2. 保留 `src/data/site-content.ts` 作为 fallback
3. 让 `/` 与 `/en` 支持 `CMS 优先，静态 fallback`
4. 保持博客 `mdx | wordpress | hybrid` 能力不退化

验收：

1. 即使 WordPress 短暂异常，前台仍可 fallback
2. 首页内容能由后台驱动
3. 现有视觉与布局不被破坏

## 阶段 E：后台域名与访问控制

目标：

1. 把后台切到最终域名，同时补齐访问控制

动作：

1. 为 `WordPress` 绑定 `homepage-manage.mindhikers.com`
2. 校正 WordPress 站点 URL
3. 接入 Cloudflare Access
4. 限制后台只允许授权身份访问

验收：

1. `homepage-manage.mindhikers.com` 可达
2. 管理入口受 Access 保护
3. 无跨项目域名污染

## 阶段 F：内容迁移与业务验收

目标：

1. 把当前首页静态内容迁入后台
2. 完成第一轮业务验收

动作：

1. 将 `src/data/site-content.ts` 的内容映射到 WordPress
2. 校验中英文首页字段完整性
3. 校验博客与首页联动
4. 验证 revalidate 生效

验收：

1. 内容编辑后前台能稳定更新
2. 双语页面都可正常工作
3. 无需改代码即可调整核心首页文案

## 阶段 G：旧现场退役

前置条件：

1. 新后台稳定可登录
2. `homepage-manage.mindhikers.com` 已切到新服务
3. Access 已生效
4. 完成至少一轮重复验证

动作：

1. 再次确认旧 `Primary` 不承担任何流量
2. 再次确认 `mindhikers-cms-v2` 不承担任何流量
3. 决定是否退役旧服务
4. 仅在确认无回滚依赖后再处理冗余数据库

## 6. 关键风险与控制措施

### 风险 1：把后台做成任意拼装器

控制：

1. 明确禁止 page builder 成为核心依赖
2. 只开放结构化字段

### 风险 2：WordPress 挂了导致前台同时挂

控制：

1. 首页保留静态 fallback
2. 使用受控缓存与 revalidate

### 风险 3：过早切域名导致跨项目污染

控制：

1. 先在 Railway 域名上完成后台与 API 验收
2. 再切 `homepage-manage.mindhikers.com`

### 风险 4：旧服务误清理

控制：

1. 旧服务只冻结，不提前删除
2. 必须等新主线稳定后再退役

## 7. 本轮建议执行顺序

如果按最稳妥路径推进，建议下一步严格按这个顺序：

1. 完成 `WordPress` 最小安全安装
2. 在仓库里实现 `mindhikers-cms-core`
3. 接通 Homepage 结构化 API
4. 让 Next.js 首页支持 CMS + fallback
5. 再处理 `homepage-manage.mindhikers.com` 与 Cloudflare Access
6. 最后再谈旧服务清理

## 8. 最终判断

本方案满足：

1. 安全：通过 Access、结构化字段、最小暴露面达成
2. 简单：候选唯一、边界清晰、无旧服务补丁包袱
3. 健壮：前后台解耦，且前台可 fallback
4. 可扩展：后续可扩到产品页、站点设置、精选内容、SEO

因此，推荐继续沿这条线推进，不建议重新推翻，不建议回到旧 `Primary` 或 `mindhikers-cms-v2`。

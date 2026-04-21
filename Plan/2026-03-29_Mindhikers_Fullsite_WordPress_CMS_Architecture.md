# MindHikers 全站 WordPress CMS 架构方案

日期：2026-03-29
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
分支：`codex/cyd-stumpel-home-exploration`
定位：`Mindhikers Homepage` 的长期内容后台与前台解耦方案

## 1. 结论

`Mindhikers` 这次不应把 `WordPress` 只当“博客后台”，而应把它升级为“整站内容后台”。

新的边界定义如下：

1. `Next.js`
   - 负责前台渲染
   - 负责视觉、布局、动效、交互、SEO 输出、缓存策略
2. `WordPress`
   - 负责整个 homepage 的内容管理
   - 负责博客文章管理
   - 负责产品页与未来专题页的内容管理
3. `MySQL`
   - 负责 `WordPress` 的持久化数据

一句话概括：

`WordPress` 管“内容”，`Next.js` 管“页面表现”。

这比“WordPress 只管博客”更符合你现在的目标，也比“让 WordPress 主题直接接管主页”更安全、更稳、更适合长期维护。

## 2. 为什么不能只把 WordPress 当博客后台

如果 CMS 边界只覆盖博客，会出现以下问题：

1. 首页 Hero、About、Product、Contact 仍然写死在代码里
2. 每次改首页文案、联系方式、CTA、产品介绍都要发版
3. 内容团队无法在后台完整管理网站
4. 前端和后台的责任边界不自然

这不符合“我希望 CMS 能管理整个 homepage”的目标。

所以 CMS 边界必须扩展到：

1. 首页内容
2. 产品页内容
3. 博客内容
4. 站点级设置

## 3. 为什么也不能让 WordPress 主题直接接管主页

从“后台可以管理整个 homepage”这句话出发，最容易误入的路线是：

1. 用 WordPress 主题渲染整站
2. 用页面构建器在后台直接拼整页

这条路线短期看方便，长期风险很高。

主要问题：

1. 页面结构容易被后台误操作破坏
2. 可视化页面构建器会引入大量额外插件与前端负担
3. 前台风格和性能会越来越受 WordPress 主题/插件约束
4. 代码审阅、版本管理和回滚都更难
5. 双语、多入口、产品页定制化会越来越乱

因此，本项目不推荐：

1. 把 `www.mindhikers.com` 交给 WordPress 主题渲染
2. 把首页做成一整块自由 HTML
3. 用重型 page builder 作为站点核心

## 4. 推荐架构

推荐采用：

1. `Headless WordPress`
2. `结构化内容模型`
3. `Next.js 前台渲染`
4. `Railway 三服务部署`

### 4.1 核心原则

1. CMS 边界覆盖整个 homepage
2. 但 CMS 只管理“结构化内容”，不直接管理“前台布局实现”
3. 前台所有页面仍由 `Next.js` 组件渲染
4. 所有高价值内容实体都要可版本化、可回滚、可审计

### 4.2 最终形态

`WordPress` 里有完整后台，你可以管理：

1. 中文首页
2. 英文首页
3. 中文产品页
4. 英文产品页
5. 博客文章
6. 站点设置
7. 联系方式
8. 导航文案
9. SEO 文案

而 `Next.js` 会从 `WordPress` 拉取结构化数据，再渲染成现有品牌站的前台。

## 5. CMS 边界定义

### 5.1 必须进入 CMS 的内容

第一阶段建议进入 CMS 的内容如下：

1. 首页元数据
   - title
   - description
2. 导航
   - brand
   - links
   - 语言切换按钮文案
3. Hero
   - eyebrow
   - title
   - description
   - primary action
   - secondary action
   - highlights
   - status label/value
   - availability label/value
   - panel title
4. About
   - title
   - intro
   - paragraphs
   - notes
5. Product section
   - title
   - description
   - headline
   - featured item
   - supporting items
6. Blog section
   - title
   - description
   - headline
   - CTA
   - empty label
   - read article label
7. Contact section
   - title
   - description
   - headline
   - email label
   - email
   - location label
   - location
   - availability label
   - availability
   - links
8. 产品详情页
   - eyebrow
   - title
   - summary
   - bullets
   - stage label/value
   - return home
   - switch language
9. 博客文章
10. 站点级配置
   - 默认品牌名
   - 默认 SEO
   - 联系邮箱
   - 社交链接

### 5.2 暂时不进入 CMS 的内容

第一阶段先不进入 CMS 的内容：

1. 页面动效逻辑
2. CSS 视觉样式
3. 组件布局细节
4. 导航 sticky 逻辑
5. 前台分页逻辑
6. OG 图片生成代码

这些继续由 `Next.js` 管理。

## 6. 内容建模原则

这里是整套方案能否长期稳定的关键。

### 6.1 原则一：不要把首页做成一整块富文本

不推荐：

1. `WordPress` 后台输出一整页 HTML
2. 前端原样渲染

推荐：

1. `WordPress` 提供结构化 JSON
2. 前端用固定组件组合渲染

原因：

1. 更安全
2. 更稳
3. 更容易校验字段
4. 更适合双语
5. 更不容易把布局玩坏

### 6.2 原则二：用“结构化字段”而不是“页面构建器”

不推荐把整个站点的内容体系建立在：

1. Elementor
2. WPBakery
3. 其他重型 page builder

理由：

1. 插件依赖重
2. 迁移成本高
3. 长期可控性差
4. 输出 HTML 容易脏
5. 更难与现有 `Next.js` 品牌页面对齐

### 6.3 原则三：尽量减少第三方核心依赖

为了安全、健壮、长期稳定，内容模型本身不应依赖过多第三方插件。

推荐做法：

1. 使用 `WordPress` 原生 `Posts` 管博客
2. 使用原生 `Pages` 或自定义 Post Type 管首页和产品页
3. 用一个自研轻量插件 `mindhikers-cms-core` 来：
   - 注册内容类型
   - 注册结构化字段
   - 提供面向前端的稳定 REST 输出

这比“把整个后台逻辑交给多个第三方字段插件”更稳。

## 7. 推荐内容模型

### 7.1 总体模型

推荐采用以下内容模型：

1. `mh_site_settings`
   - 单例
   - 管理站点级配置
2. `mh_homepage`
   - 两条内容
   - `zh`
   - `en`
3. `mh_product_page`
   - 产品页集合
   - 第一条为 `golden-crucible`
4. `post`
   - 原生博客文章

### 7.2 为什么不直接把所有内容塞进一个对象

如果只用一个“大型设置页”装所有内容，会出现：

1. 首页、产品页、站点设置耦合在一起
2. 权限难拆
3. 版本记录不清楚
4. 后续扩展专题页困难

所以更推荐分实体管理。

### 7.3 为什么首页按语言拆成两个实体

推荐：

1. `homepage-zh`
2. `homepage-en`

不推荐：

1. 一个首页实体里塞所有 `zh/en` 字段

原因：

1. 后台更清楚
2. 校验更简单
3. 预览更直接
4. 后续扩展其他语言更自然

## 8. 管理后台应该长什么样

如果目标是“我可以在后台管理整个 homepage”，那后台不该只是普通的文章列表。

建议后台菜单结构如下：

1. `Site Settings`
   - 品牌名
   - 默认 SEO
   - 联系邮箱
   - 社交链接
2. `Homepages`
   - `Homepage (ZH)`
   - `Homepage (EN)`
3. `Product Pages`
   - `Golden Crucible (ZH)`
   - `Golden Crucible (EN)`
   - 未来其他产品页
4. `Posts`
   - 博客文章
5. `Media`
   - 媒体上传

后台体验目标：

1. 内容编辑可以不碰代码
2. 结构不会因为自由编辑而失控
3. 每个字段有明确含义
4. 每个实体有 revision

## 9. WordPress 到前台的数据输出方式

### 9.1 不直接使用默认 REST 输出作为最终协议

博客文章可以直接基于 `/wp-json/wp/v2/posts`。

但对于首页和产品页，不建议前端直接依赖复杂、松散的 `post meta` 原始结构。

更推荐：

1. `WordPress` 自研轻量插件提供稳定的自定义 REST 输出

例如：

1. `/wp-json/mindhikers/v1/site-settings`
2. `/wp-json/mindhikers/v1/homepage/zh`
3. `/wp-json/mindhikers/v1/homepage/en`
4. `/wp-json/mindhikers/v1/product/golden-crucible?locale=zh`
5. `/wp-json/mindhikers/v1/product/golden-crucible?locale=en`

### 9.2 为什么要自定义 REST 层

原因：

1. 让前端拿到稳定、扁平、干净的数据结构
2. 屏蔽 WordPress 内部字段实现细节
3. 便于后续调整后台字段而不频繁打断前端
4. 更容易做字段校验

## 10. 推荐实现方式

### 10.1 长期稳定方案

推荐的长期稳定方案是：

1. `WordPress` 官方镜像
2. `MySQL`
3. `wp-content` 持久化卷
4. 自研轻量插件 `mindhikers-cms-core`
5. `Next.js` 前台消费 `mindhikers/v1` 和 `wp/v2/posts`

### 10.2 `mindhikers-cms-core` 这个插件负责什么

它应该负责：

1. 注册 `mh_homepage`
2. 注册 `mh_product_page`
3. 注册 `mh_site_settings`
4. 定义内容字段
5. 控制后台编辑体验
6. 输出自定义 REST
7. 在文章发布、页面更新时调用前台 revalidate webhook

### 10.3 为什么要自己做这个插件

因为你要的是：

1. 安全
2. 健壮
3. 简单明确
4. 长期稳定

这四条加在一起，就意味着：

1. 不适合靠一堆通用 page builder 和字段插件拼凑
2. 更适合一层自己可控的、范围很小的业务插件

这样：

1. 数据结构清楚
2. 升级风险更低
3. 前台协议可控
4. 未来扩展不依赖第三方 UI 生态

## 11. Railway 上的 WordPress 后端服务应该承担什么

在 Railway 上，`WordPress` 后端服务应该是一个真正的“后台服务”，而不只是博客实例。

这个服务要承担：

1. 后台登录
2. 全站内容管理
3. 媒体上传
4. REST API 输出
5. 发布后通知前台刷新缓存

它不承担：

1. 主站前台最终渲染
2. 前台视觉效果
3. 主站路由控制

这正是“让 Railway 上有一个 WordPress 后端服务，可以让我后台管理整个 homepage”的正确实现方式。

## 12. 安全设计

### 12.1 平台层

1. `WordPress` 独立服务
2. `MySQL` 独立服务
3. `wp-content` 单独 Volume
4. 管理后台只走 HTTPS

### 12.2 WordPress 层

建议至少启用：

```php
define('FORCE_SSL_ADMIN', true);
define('DISALLOW_FILE_EDIT', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_POST_REVISIONS', 20);
define('EMPTY_TRASH_DAYS', 15);
```

并执行：

1. 不使用默认 `admin`
2. 管理员人数最少化
3. 管理员与编辑角色分离
4. 强密码
5. 后续接 MFA

### 12.3 前后端边界层

1. 自定义 REST 只暴露前台需要的字段
2. 前台对 WordPress 富文本做净化
3. `REVALIDATE_SECRET` 必须独立保管
4. 任何缓存刷新都必须鉴权

## 13. 可扩展性结论

这套方案保留了你关心的扩展能力：

1. 可以安装插件
2. 可以安装主题
3. 可以做更多产品页
4. 可以做专题页
5. 可以做更多语言
6. 可以做对象存储

但扩展方式必须有纪律：

1. 主题不是主站渲染核心
2. page builder 不是主站内容核心
3. 关键内容模型由 `mindhikers-cms-core` 控制

## 14. 最终建议

最适合 `Mindhikers` 的长期路线不是：

1. “WordPress 只当博客”
2. “WordPress 主题直接接管主页”
3. “后台用 page builder 自由拼站”

而是：

1. `WordPress` 做全站结构化内容后台
2. `Next.js` 做长期稳定前台
3. `Railway` 提供独立 CMS 服务和 DB 服务
4. 用自研轻量插件定义内容模型和 REST 协议

这条路线最符合你的目标：

1. 后台可以管理整个 homepage
2. 安全
3. 健壮
4. 长期稳定
5. 后续还能扩展

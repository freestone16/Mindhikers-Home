# MindHikers WordPress CMS on Railway Integration Plan

日期：2026-03-29
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
分支：`codex/cyd-stumpel-home-exploration`

## 1. 目标

基于当前 `Next.js App Router` 前端站点，引入 `WordPress` 作为内容后台，并部署在 `Railway`，达到以下目标：

1. 保持现有首页、产品页、博客前台样式与路由控制权仍在 `Next.js` 前端手中。
2. 将博客内容的日常维护迁移到 `WordPress` 后台，供运营或内容同学通过 Web 界面管理。
3. 架构上优先满足：
   - 安全
   - 健壮
   - 简单明确
   - 后续可扩展
4. 在不破坏现有站点的前提下，提供一条平滑迁移路径，从当前 `MDX` 博客切换到 `WordPress CMS`。

## 2. 当前前端现状

当前仓库内容结构可分为两部分：

1. 站点结构与品牌文案
   - `src/data/site-content.ts`
   - 中文、英文首页都依赖本地数据文件
2. 博客内容
   - `content/*.mdx`
   - 通过 `content-collections` 编译为 `allPosts`
   - 使用位置：
     - `src/app/page.tsx`
     - `src/app/en/page.tsx`
     - `src/app/blog/page.tsx`
     - `src/app/blog/[slug]/page.tsx`
     - `src/app/blog/[slug]/opengraph-image.tsx`

这意味着最稳妥的第一阶段边界是：

1. 首页、产品页、联系信息继续留在代码仓库管理。
2. 仅将“博客内容源”从本地 `MDX` 替换为 `WordPress`。

这样改动面最小，也最符合“简单明确”的要求。

## 3. 推荐总体方案

推荐采用“`Headless WordPress`”模式：

1. `WordPress` 只做 CMS 后台
2. `Next.js` 继续做前台渲染
3. `Railway` 同时承载：
   - 现有前端服务
   - `WordPress` 服务
   - `MySQL` 或 `MariaDB` 服务

### 3.1 一句话理解

1. 内容团队在 `WordPress` 后台发文章。
2. 前端站点通过 `WordPress REST API` 拉取已发布文章。
3. 用户访问的仍然是 `MindHikers` 自己的前端页面，而不是 `WordPress` 主题页面。

### 3.2 为什么采用这个方案

1. 对现有仓库最友好，不需要推翻现有页面结构。
2. 运营体验成熟，新增文章只需登录 `wp-admin`。
3. `WordPress` 自带 REST API，首版不必额外引入 `GraphQL`。
4. 前台样式、性能、安全头、SEO 输出仍由 `Next.js` 控制。
5. 即使 `WordPress` 服务不能做多副本，前台服务仍可独立扩展。

## 4. Railway 服务拓扑

建议在同一个 Railway Project 中维护以下服务：

1. `mindhikers-web`
   - 当前 `Next.js` 前端服务
   - 对外域名：`www.mindhikers.com`
2. `mindhikers-cms`
   - `WordPress` 服务
   - 管理域名：`homepage-manage.mindhikers.com`
3. `mindhikers-db`
   - `MySQL` 服务
   - 仅供内部连接

建议的域名分工：

1. `mindhikers.com`
   - 301 跳转到 `https://www.mindhikers.com`
2. `www.mindhikers.com`
   - 正式前台
3. `homepage-manage.mindhikers.com`
   - WordPress 后台与 CMS API

## 5. WordPress 服务设计

### 5.1 运行方式

建议使用 `WordPress 官方 Docker 镜像` 在 Railway 上部署，而不是直接把 `WordPress/WordPress` 这个 GitHub 镜像仓库作为业务源码仓库来跑。

原因：

1. 该 GitHub 仓库本质上是核心代码镜像，不适合作为你们内容站定制与协作主仓库。
2. 官方镜像已经约定好环境变量、启动逻辑和初始化方式，更适合 Railway 这种容器平台。

### 5.2 持久化策略

推荐只给 `wp-content` 挂持久化卷，而不是整站根目录全量持久化。

挂载建议：

1. Volume 挂载路径：`/var/www/html/wp-content`

这样做的好处：

1. 上传图片会持久化。
2. 插件与主题会持久化。
3. WordPress 核心文件仍由镜像提供，避免线上核心代码长期漂移。
4. 容器重建时 `wp-config.php` 可通过环境变量重新生成，不依赖手工维护。

### 5.3 数据库

建议使用 Railway 自带 `MySQL` 模板。

理由：

1. WordPress 官方兼容成熟。
2. Railway 会提供标准连接变量：
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`
   - `MYSQLDATABASE`
   - `MYSQL_URL`

### 5.4 关键环境变量

`mindhikers-cms` 至少需要：

1. `WORDPRESS_DB_HOST`
2. `WORDPRESS_DB_USER`
3. `WORDPRESS_DB_PASSWORD`
4. `WORDPRESS_DB_NAME`
5. `WORDPRESS_TABLE_PREFIX`
6. `WORDPRESS_CONFIG_EXTRA`

建议值策略：

1. `WORDPRESS_TABLE_PREFIX`
   - 使用随机且固定前缀，例如 `mhwp_`
2. `WORDPRESS_CONFIG_EXTRA`
   - 放置生产常量与安全常量

建议在 `WORDPRESS_CONFIG_EXTRA` 中写入：

```php
define('FORCE_SSL_ADMIN', true);
define('DISALLOW_FILE_EDIT', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_POST_REVISIONS', 20);
define('EMPTY_TRASH_DAYS', 15);
```

说明：

1. `FORCE_SSL_ADMIN` 强制后台与登录走 HTTPS。
2. `DISALLOW_FILE_EDIT` 禁用后台在线改 PHP 文件，减少误操作与入侵后的代码执行风险。
3. `AUTOMATIC_UPDATER_DISABLED` 关闭自动更新，避免插件或核心在生产环境无预警变更。
4. `WP_POST_REVISIONS` 和回收站天数用于控制内容回滚能力与库膨胀。

## 6. 前后端数据流设计

### 6.1 首版数据边界

第一阶段只迁移博客相关内容：

1. 文章列表
2. 文章详情
3. 最近文章卡片
4. SEO 相关元数据

暂不迁移：

1. 首页 Hero 文案
2. Product 模块文案
3. Contact 区块内容
4. 双语首页结构

这些继续保留在当前代码中，避免把 CMS 边界一次性做得过大。

### 6.2 前端接入方式

建议直接使用 `WordPress REST API`，不在第一阶段引入 `WPGraphQL`。

推荐接口：

1. 文章列表
   - `/wp-json/wp/v2/posts?_embed=wp:featuredmedia`
2. 单篇文章
   - `/wp-json/wp/v2/posts?slug={slug}&_embed=wp:featuredmedia`
3. 分类、标签
   - 按需读取 `/wp-json/wp/v2/categories` 与 `/wp-json/wp/v2/tags`

理由：

1. 原生能力足够覆盖现阶段博客需求。
2. 少一个插件，少一层维护成本。
3. 有利于先把链路跑顺。

### 6.3 Next.js 数据层改造

建议新增统一 CMS 数据层，替换直接依赖 `allPosts` 的方式。

建议新增：

1. `src/lib/cms/types.ts`
2. `src/lib/cms/wordpress.ts`
3. `src/lib/cms/mdx.ts`
4. `src/lib/cms/index.ts`

统一暴露的方法建议：

1. `listPosts()`
2. `getPostBySlug(slug)`
3. `getRecentPosts(limit)`
4. `listPostSlugs()`

数据结构统一为前端友好的内部类型，例如：

```ts
type CmsPost = {
  id: string;
  slug: string;
  title: string;
  summary: string;
  publishedAt: string;
  updatedAt?: string;
  coverImage?: string;
  contentHtml: string;
  locale?: "zh" | "en";
  tags: string[];
  categories: string[];
};
```

### 6.4 迁移期的安全切换方案

为降低切换风险，建议使用内容源开关：

1. `BLOG_SOURCE=mdx`
2. `BLOG_SOURCE=wordpress`
3. `BLOG_SOURCE=hybrid`

其中：

1. `mdx`
   - 全量使用当前仓库中的 `content/*.mdx`
2. `wordpress`
   - 全量使用 WordPress
3. `hybrid`
   - 优先读取 WordPress；若指定 slug 不存在则回退到本地 `MDX`

推荐切换顺序：

1. 开发阶段：`mdx`
2. 联调阶段：`hybrid`
3. 正式切换：`wordpress`

这样即使迁移中有某篇文章漏导，也不会导致线上 404。

## 7. 内容渲染与缓存策略

### 7.1 渲染方式

WordPress 文章正文首版建议直接读取 `content.rendered`，在前端进行受控渲染。

推荐做法：

1. 服务端抓取 HTML
2. 服务端做一次 HTML 净化
3. 再在文章页安全输出

注意：

1. 不建议直接无处理地把 WordPress 返回 HTML 裸插到页面里。
2. 作者权限必须收敛，只给可信内容人员。

### 7.2 缓存方式

建议在 Next.js 里使用服务端 `fetch` + `revalidate`：

1. 列表页：`revalidate: 300`
2. 详情页：`revalidate: 300`
3. 首页最近文章：`revalidate: 300`

即：

1. 即使不做额外动作，发布新文章后最多 5 分钟刷新到前台。
2. 系统简单、稳定，不依赖过多联动。

### 7.3 增强刷新

为了让发布文章后更快生效，建议追加一个受保护的 revalidate 端点：

1. Next.js 新增 `/api/revalidate`
2. 使用 `REVALIDATE_SECRET`
3. WordPress 在文章发布或更新时调用它

实现方式建议：

1. 首选写一个很小的自定义插件或 `mu-plugin`
2. 不建议依赖重型“站点构建触发器”插件

这样发布文章后可以做到近实时刷新，而不需要整个前端重新部署。

## 8. 后台内容管理方式

### 8.1 日常如何发文章

日常运营流程如下：

1. 打开浏览器访问 `https://homepage-manage.mindhikers.com/wp-admin`
2. 登录 WordPress
3. 进入“文章”列表
4. 点击“写文章”
5. 填写：
   - 标题
   - slug
   - 摘要
   - 正文
   - 封面图
   - 分类
   - 标签
6. 点击发布
7. WordPress 调用前端 revalidate 接口
8. `www.mindhikers.com/blog` 与文章详情页更新

所以内容同学不需要本地起 WordPress 服务来发文。日常管理入口就是线上后台。

### 8.2 本地 WordPress 的角色

本地 WordPress 不是日常发文入口，主要用于：

1. 开发调试
2. 测试插件
3. 预演字段结构
4. 做 staging 验证

如果后续需要本地联调，再单独补一份 `docker compose` 本地 CMS 环境即可。

## 9. 内容模型建议

第一阶段只采用 WordPress 原生 `Post`，不急于引入复杂自定义内容模型。

文章需要的字段：

1. 标题
2. slug
3. 摘要 excerpt
4. 正文 content
5. 封面图 featured image
6. 发布时间
7. 更新时间
8. 分类
9. 标签

如需兼容未来中英文内容，建议从一开始就预留 `locale` 字段，方式二选一：

1. 低复杂度方案
   - 用一个约定好的分类或标签表示 `zh` / `en`
2. 更规范方案
   - 后续通过自定义 taxonomy 或 ACF 字段表示语言

建议第一阶段先用“分类或标签标识语言”，不要一开始就引入多语言插件。

## 10. 安全设计

这是本方案最重要的部分之一。

### 10.1 账号与权限

角色建议：

1. `Administrator`
   - 仅技术负责人或站点 owner 使用
   - 不超过 2 人
2. `Editor`
   - 内容团队发布和维护文章
3. `Author`
   - 若有多人撰稿，可只允许写稿，不直接发布

要求：

1. 不共用管理员账号
2. 所有后台账号使用强密码
3. 后续接入 MFA 插件
4. 离职或角色变更时及时回收账号

### 10.2 WordPress 后台安全基线

上线前必须完成：

1. `FORCE_SSL_ADMIN=true`
2. `DISALLOW_FILE_EDIT=true`
3. 关闭默认 `admin` 用户名
4. 限制管理员人数
5. 安装一类成熟的登录保护或 MFA 方案
6. 配置站点邮件，确保密码重置和告警可用

### 10.3 文件修改策略

这里需要在“安全”和“可扩展”之间做平衡。

推荐策略：

1. 永远禁止后台在线编辑插件和主题源码
   - 即 `DISALLOW_FILE_EDIT=true`
2. 允许管理员在后台安装插件或主题
   - 这样保留扩展能力
3. 但要求所有插件或主题变更先在 staging 测试，再进入 production

这是一条比“完全禁止文件修改”更灵活、又比“谁都能在线改代码”更安全的折中方案。

### 10.4 插件和主题的治理原则

允许安装插件和主题，但必须执行以下规则：

1. 只允许 `Administrator` 安装或卸载
2. 维护一份插件白名单
3. 不安装“可执行任意 PHP / JS 注入”的高风险插件
4. 插件变更前先做数据库和卷备份
5. 不启用自动更新
6. 生产环境变更需要维护窗口

## 11. 扩展性说明

### 11.1 能不能安装网页模板和主题

可以，技术上完全支持。

因为：

1. 主题位于 `wp-content/themes`
2. 该目录会被 Volume 持久化
3. 安装后的主题在容器重启后仍然存在

但必须明确：

1. 在本方案里，前台访问的 `www.mindhikers.com` 不是由 WordPress 主题渲染
2. 所以“安装网页模板或主题”不会直接改变现有主页与博客前台
3. 它主要影响：
   - WordPress 自己的前端预览页
   - 后续如果你们想单独开一个 WordPress 渲染的子站或专题页

结论：

1. 具备安装主题和模板的能力
2. 但当前推荐架构不会让主题直接控制主站前台

### 11.2 能不能安装插件

可以，而且这是本方案保留的重点能力之一。

推荐首批只保留少量必要插件：

1. 安全类
2. SEO 或站点信息类
3. 编辑体验类
4. Webhook / revalidate 联动类

后续如果需要：

1. 可增加自定义字段能力
2. 可增加表单能力
3. 可增加重定向能力
4. 可增加站点地图、社交卡片等能力

### 11.3 后续是否能升级为更复杂 CMS

可以，当前方案天然可演进到：

1. `WordPress REST API + 自定义 Post Type`
2. `WordPress + ACF`
3. `WordPress + WPGraphQL`
4. `WordPress + 外部对象存储`
5. `WordPress + 真正的预览链路`

所以第一阶段不需要把复杂度一次性吃满。

## 12. Railway 相关限制与应对

根据 Railway 当前文档，Volume 有几个关键限制：

1. 每个 service 只能有一个 Volume
2. 带 Volume 的 service 不能开 replicas
3. 挂载 Volume 的 service 在重新部署时会有短暂中断

这对本方案的影响如下：

1. `WordPress CMS` 不适合做高并发前台服务
2. 但它只承担后台和内容 API，流量压力相对小，可以接受
3. 真正面向用户流量的是 `Next.js` 前端服务，它不依赖 Volume，可以继续独立扩展

因此，这些 Railway 限制并不会否定本方案，只是进一步说明：

1. `WordPress` 应该做后台
2. `Next.js` 应该做前台

## 13. 媒体资源策略

### 13.1 第一阶段

第一阶段建议直接使用 `WordPress uploads`，即保存在 `wp-content/uploads` 中。

原因：

1. 最简单
2. 上线最快
3. 对内容团队最透明

### 13.2 第二阶段扩展

如果后续图片、音视频、附件变多，再迁移到外部对象存储。

优先建议：

1. `Cloudflare R2`
2. 标准 `S3`

暂不建议把 Railway Bucket 作为公开媒体主方案，原因是 Railway 当前 Bucket 仍以私有桶为主，公开访问需要预签名或代理，复杂度不如直接用更成熟的公开对象存储方案。

## 14. 实施步骤

### 阶段 A：基础设施

1. 在 Railway 新增 `MySQL` 服务
2. 新增 `WordPress` 服务
3. 给 `WordPress` 服务挂载 Volume 到 `/var/www/html/wp-content`
4. 配置 `homepage-manage.mindhikers.com`
5. 录入 `WORDPRESS_DB_*` 与 `WORDPRESS_CONFIG_EXTRA`
6. 完成 WordPress 初始化安装
7. 创建管理员与编辑账号

### 阶段 B：前端接入

1. 新增统一 CMS 数据层
2. 抽离 `MDX Provider` 与 `WordPress Provider`
3. 把以下页面切到 CMS 抽象层：
   - `src/app/page.tsx`
   - `src/app/en/page.tsx`
   - `src/app/blog/page.tsx`
   - `src/app/blog/[slug]/page.tsx`
   - `src/app/blog/[slug]/opengraph-image.tsx`
4. 新增 `BLOG_SOURCE` 环境变量
5. 为 WordPress 图片域名放开 `next/image` 白名单
6. 为文章 HTML 增加服务端净化

### 阶段 C：联动刷新

1. 新增 `/api/revalidate`
2. 配置 `REVALIDATE_SECRET`
3. 在 WordPress 发布文章时触发 webhook
4. 验证：
   - 新增文章
   - 编辑文章
   - 删除或下线文章

### 阶段 D：迁移切换

1. 将现有 `content/*.mdx` 迁移到 WordPress
2. 启用 `BLOG_SOURCE=hybrid`
3. 抽检所有旧文章 slug、摘要、日期、封面、SEO
4. 正式切到 `BLOG_SOURCE=wordpress`
5. 保留 `content/*.mdx` 一段时间作为回滚缓冲

## 15. 回滚策略

如果 WordPress 链路在切换后出现问题，回滚必须足够简单。

回滚方案：

1. 将前端环境变量 `BLOG_SOURCE` 切回 `mdx`
2. 前台立即恢复使用仓库内 `content/*.mdx`
3. WordPress 服务继续保留，不影响后续排查

这就是为什么第一阶段必须保留 `MDX Provider`。

## 16. 测试与验收

上线前至少验证以下内容：

1. `homepage-manage.mindhikers.com/wp-admin` 可正常登录
2. 编辑账号只能发文，不能做高危系统操作
3. 新建文章后能在前台列表出现
4. 文章详情页标题、摘要、正文、封面、OG 信息正常
5. 文章更新时间后前台能自动刷新
6. 图片上传、显示、缓存正常
7. 删除未发布文章不会影响前台
8. 数据库和 Volume 均已配置备份策略
9. WordPress 重新部署后，上传图片与插件不丢失
10. 切回 `BLOG_SOURCE=mdx` 时前台仍可用

## 17. 资源需求清单

开始实施前，请集中准备以下资源：

1. Railway
   - 一个可用的 Railway Project
   - 至少 3 个服务位：
     - `mindhikers-web`
     - `mindhikers-cms`
     - `mindhikers-db`
   - 给 CMS 服务准备一个 Volume
2. 域名与 DNS
   - `www.mindhikers.com`
   - `homepage-manage.mindhikers.com`
   - 根域跳转策略
3. 后台账号
   - 至少 1 个管理员账号
   - 至少 1 个编辑账号
4. 邮件能力
   - 用于重置密码和后台通知的 SMTP 服务
5. 前端环境变量
   - `BLOG_SOURCE`
   - `WORDPRESS_API_URL`
   - `REVALIDATE_SECRET`
6. WordPress 环境变量
   - `WORDPRESS_DB_HOST`
   - `WORDPRESS_DB_USER`
   - `WORDPRESS_DB_PASSWORD`
   - `WORDPRESS_DB_NAME`
   - `WORDPRESS_TABLE_PREFIX`
   - `WORDPRESS_CONFIG_EXTRA`
7. 迁移资料
   - 现有 `MDX` 文章清单
   - 历史 slug 对照表
   - 需要保留的封面图资源
8. 可选增强项
   - Cloudflare 或其他 DNS/WAF 能力
   - 未来对象存储账号（R2 或 S3）

## 18. 最终建议

如果以“安全、健壮、简单明确、具备可扩展性”为优先级排序，本项目最推荐的落地方式是：

1. 保持 `Next.js` 继续做前台
2. 让 `WordPress` 只做博客 CMS 后台
3. 第一阶段只迁移博客，不迁移首页与产品文案
4. 使用 `WordPress REST API`，不急着引入 `GraphQL`
5. 使用 `BLOG_SOURCE` 做平滑切换与回滚
6. 允许插件和主题安装，但严格收口到管理员，并要求 staging 先验证

这是当前最适合 `MindHikers Homepage` 的方案：

1. 不会推翻现有站点
2. 内容团队可以立刻获得成熟后台
3. 技术上有清晰回滚路径
4. 后续若要继续扩展到多语言博客、专题内容、对象存储或更复杂内容模型，也都留好了口子

# MindHikers Railway 三服务实施计划

日期：2026-03-29
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
分支：`codex/cyd-stumpel-home-exploration`
适用项目：Railway Project `Mindhikers-Homepage`

## 1. 目的

在现有 `Mindhikers-Homepage` Railway 项目内，建设一个完整、清晰、可运维的三服务架构：

1. `Mindhikers-Homepage`
   - 现有 `Next.js` 前台服务
2. `mindhikers-cms`
   - 新增 `WordPress` CMS 服务
3. `mindhikers-db`
   - 新增 `MySQL` 数据库服务

目标是：

1. 保留现有前台站点与品牌页面不变。
2. 让 `WordPress` 只负责后台内容管理与内容 API。
3. 让数据库、CMS、前台职责清晰分离。
4. 在保证安全和可回滚的前提下，支持后续扩展主题、插件、对象存储和更多内容模型。

## 2. 当前现状

截至当前会话，Railway 侧已确认：

1. 当前目录已绑定到 Railway Project：`Mindhikers-Homepage`
2. 当前环境：`production`
3. 当前仅有 1 个服务：
   - `Mindhikers-Homepage`
4. `WordPress` 服务尚未创建
5. `MySQL` 服务尚未创建

代码侧现状：

1. 前端已完成 CMS 抽象层接入
2. 已支持：
   - `BLOG_SOURCE=mdx`
   - `BLOG_SOURCE=wordpress`
   - `BLOG_SOURCE=hybrid`
3. 已提供 `/api/revalidate`
4. 已具备接入 `WordPress REST API` 的能力

这意味着：

1. 平台资源还没建
2. 代码接入基础已经完成
3. 下一步重点是“把三服务真正建起来，并把变量与关系打通”

## 3. 目标拓扑

### 3.1 服务清单

目标服务结构如下：

1. `Mindhikers-Homepage`
   - 类型：应用服务
   - 技术：Next.js
   - 职责：对外主站、博客前台、SEO、缓存刷新入口
2. `mindhikers-cms`
   - 类型：应用服务
   - 技术：WordPress
   - 职责：后台管理、文章编辑、媒体上传、内容 API
3. `mindhikers-db`
   - 类型：数据库服务
   - 技术：MySQL
   - 职责：为 WordPress 提供持久化数据存储

### 3.2 服务关系

三者关系必须保持如下方向：

1. `Mindhikers-Homepage` 读取 `mindhikers-cms`
   - 通过 `WORDPRESS_API_URL`
2. `mindhikers-cms` 连接 `mindhikers-db`
   - 通过 `WORDPRESS_DB_*`
3. `mindhikers-cms` 回调 `Mindhikers-Homepage`
   - 通过 `/api/revalidate`

也就是说：

1. 用户访问主站时，不直接访问数据库
2. 用户访问主站时，也不直接使用 WordPress 主题页面
3. WordPress 只是内容后台和 API 提供者

## 4. 域名规划

建议域名分工如下：

1. `mindhikers.com`
   - 作用：根域名
   - 策略：301 到 `https://www.mindhikers.com`
2. `www.mindhikers.com`
   - 绑定服务：`Mindhikers-Homepage`
   - 作用：正式前台站点
3. `homepage-manage.mindhikers.com`
   - 绑定服务：`mindhikers-cms`
   - 作用：WordPress 后台和内容 API

注意：

1. `WordPress` 的 `Site URL` 与 `Home URL` 都应配置为 `https://homepage-manage.mindhikers.com`
2. 前端只消费 `homepage-manage.mindhikers.com/wp-json/...`

## 5. 服务设计明细

### 5.1 服务一：`Mindhikers-Homepage`

当前状态：

1. 已存在
2. 已部署
3. 已绑定 Railway 项目

后续需要补充的环境变量：

1. `BLOG_SOURCE`
   - 初始值：`hybrid`
   - 作用：迁移期平滑切换
2. `WORDPRESS_API_URL`
   - 值：`https://homepage-manage.mindhikers.com`
   - 作用：前台读取 WordPress REST API
3. `REVALIDATE_SECRET`
   - 值：长随机字符串
   - 作用：仅允许受信请求触发缓存刷新

职责边界：

1. 对外展示博客页面
2. 不负责后台文章编辑
3. 不存储文章内容主数据
4. 对 WordPress 故障具备回退到 `MDX` 的能力

### 5.2 服务二：`mindhikers-cms`

部署形式：

1. 使用标准 `WordPress` 模板或标准 WordPress 镜像
2. 服务名固定建议为：`mindhikers-cms`

必须配置的持久化卷：

1. Volume 挂载路径：`/var/www/html/wp-content`

为什么只挂 `wp-content`：

1. 插件会被持久化
2. 主题会被持久化
3. 上传媒体会被持久化
4. 核心代码仍由镜像提供，减少漂移与升级混乱

必须配置的环境变量：

1. `WORDPRESS_DB_HOST`
2. `WORDPRESS_DB_USER`
3. `WORDPRESS_DB_PASSWORD`
4. `WORDPRESS_DB_NAME`
5. `WORDPRESS_TABLE_PREFIX`
6. `WORDPRESS_CONFIG_EXTRA`

推荐值：

1. `WORDPRESS_TABLE_PREFIX=mhwp_`
2. `WORDPRESS_CONFIG_EXTRA` 建议为：

```php
define('FORCE_SSL_ADMIN', true);
define('DISALLOW_FILE_EDIT', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_POST_REVISIONS', 20);
define('EMPTY_TRASH_DAYS', 15);
```

后续 WordPress 站点初始化后，还需要在后台补：

1. 管理员账号
2. 编辑账号
3. SMTP 插件或邮件发送能力
4. 安全插件或登录保护
5. 发布后 webhook 触发逻辑

职责边界：

1. 管理文章
2. 管理媒体
3. 管理分类与标签
4. 提供 `REST API`
5. 不承担对外主站渲染

### 5.3 服务三：`mindhikers-db`

部署形式：

1. 使用标准 `MySQL` 模板
2. 服务名固定建议为：`mindhikers-db`

数据库职责：

1. 存储 WordPress 所有结构化数据
2. 仅供 WordPress 内部连接
3. 不对互联网公开使用

数据库输出变量通常包括：

1. `MYSQLHOST`
2. `MYSQLPORT`
3. `MYSQLUSER`
4. `MYSQLPASSWORD`
5. `MYSQLDATABASE`
6. `MYSQL_URL`

这些变量需要映射到 `mindhikers-cms`：

1. `WORDPRESS_DB_HOST=${{mindhikers-db.MYSQLHOST}}:${{mindhikers-db.MYSQLPORT}}`
2. `WORDPRESS_DB_USER=${{mindhikers-db.MYSQLUSER}}`
3. `WORDPRESS_DB_PASSWORD=${{mindhikers-db.MYSQLPASSWORD}}`
4. `WORDPRESS_DB_NAME=${{mindhikers-db.MYSQLDATABASE}}`

## 6. 三服务之间的变量关系

### 6.1 数据库 -> CMS

这是第一条必须打通的依赖：

1. `mindhikers-db` 生成数据库连接信息
2. `mindhikers-cms` 消费这些信息
3. WordPress 启动并完成安装

推荐映射表：

1. `WORDPRESS_DB_HOST`
   - 来源：`MYSQLHOST` + `MYSQLPORT`
2. `WORDPRESS_DB_USER`
   - 来源：`MYSQLUSER`
3. `WORDPRESS_DB_PASSWORD`
   - 来源：`MYSQLPASSWORD`
4. `WORDPRESS_DB_NAME`
   - 来源：`MYSQLDATABASE`

### 6.2 CMS -> 前台

这是第二条必须打通的依赖：

1. `Mindhikers-Homepage` 读取 `mindhikers-cms`
2. 接口入口为 `https://homepage-manage.mindhikers.com/wp-json/wp/v2/...`

推荐变量：

1. `WORDPRESS_API_URL=https://homepage-manage.mindhikers.com`

### 6.3 CMS -> 前台缓存刷新

这是第三条必须打通的依赖：

1. WordPress 发布文章后
2. 调用 `Mindhikers-Homepage` 的 `/api/revalidate`
3. 刷新首页、博客列表、博客详情缓存

推荐变量：

1. 前台：
   - `REVALIDATE_SECRET=<long-random-secret>`
2. WordPress 侧 webhook：
   - URL：`https://www.mindhikers.com/api/revalidate`
   - Header 或 query 带上 `REVALIDATE_SECRET`

## 7. 执行顺序

为了减少返工，执行顺序必须固定：

### 阶段 A：先建数据库

1. 在现有 Railway 项目中创建 `mindhikers-db`
2. 确认数据库服务正常启动
3. 记录数据库变量名

为什么必须先做：

1. WordPress 初始化依赖数据库
2. 如果先建 WordPress，后面还要回头补连接，容易产生不必要重启

### 阶段 B：再建 CMS

1. 在同一项目中创建 `mindhikers-cms`
2. 挂载 `wp-content` Volume
3. 将数据库变量映射到 `WORDPRESS_DB_*`
4. 配置 `WORDPRESS_CONFIG_EXTRA`
5. 绑定 Railway 生成域名
6. 后续再切自定义域 `homepage-manage.mindhikers.com`

验收点：

1. CMS 服务启动成功
2. WordPress 安装页可访问
3. 安装后 `/wp-admin` 可登录
4. `/wp-json/wp/v2/posts` 可返回数据

### 阶段 C：接前台变量

1. 给 `Mindhikers-Homepage` 写入：
   - `BLOG_SOURCE=hybrid`
   - `WORDPRESS_API_URL=https://homepage-manage.mindhikers.com`
   - `REVALIDATE_SECRET=<secret>`
2. 前端 redeploy
3. 验证前台能正常读取 CMS 内容

为什么先用 `hybrid`：

1. 可以保留旧 MDX 文章兜底
2. 即使 WordPress 内容还没迁移完，线上也不会空

### 阶段 D：做后台初始化

1. 创建管理员
2. 创建编辑账号
3. 关闭默认高风险入口
4. 安装基础安全插件或登录保护
5. 配置 SMTP
6. 配置发布后 webhook

### 阶段 E：迁移内容

1. 将现有 `content/*.mdx` 迁移到 WordPress
2. 核对：
   - 标题
   - slug
   - 摘要
   - 发布时间
   - 封面图
3. 用 `BLOG_SOURCE=hybrid` 做联调
4. 确认无误后切到 `BLOG_SOURCE=wordpress`

## 8. 明确暂停点

本计划要求在以下节点暂停并人工确认，不自动跨过：

1. `mindhikers-db` 创建完成后
   - 确认服务名、变量名和可用性
2. `mindhikers-cms` 创建并挂卷后
   - 确认服务形态、存储路径和初始化页面
3. 绑定 `homepage-manage.mindhikers.com` 前
   - 确认 DNS 准备好
4. 前台切换到 `BLOG_SOURCE=hybrid` 前
   - 确认 WordPress API 已可读
5. 前台切换到 `BLOG_SOURCE=wordpress` 前
   - 确认内容迁移完成

## 9. 安全要求

### 9.1 CMS 安全要求

必须满足：

1. `FORCE_SSL_ADMIN=true`
2. `DISALLOW_FILE_EDIT=true`
3. 不使用默认 `admin` 账号
4. 管理员人数最少化
5. 管理员与编辑权限分离
6. 强密码
7. 后续补 MFA

### 9.2 前台安全要求

必须满足：

1. `REVALIDATE_SECRET` 为高强度随机值
2. 前台仅信任 `WORDPRESS_API_URL`
3. WordPress HTML 必须净化后再输出
4. 不因为 CMS 失败而让前台整体不可用

### 9.3 插件与主题管理原则

允许，但要有纪律：

1. 仅管理员可安装
2. 变更先 staging，后 production
3. 安装前确认用途
4. 安装后记录版本
5. 禁止随意堆高风险插件

## 10. 扩展性结论

这套三服务结构具备以下扩展能力：

1. 可以安装 WordPress 插件
2. 可以安装 WordPress 主题
3. 可以继续引入对象存储
4. 可以继续引入 `WPGraphQL`
5. 可以继续增加自定义内容模型
6. 可以继续加 staging 环境

但必须清楚：

1. 当前主站前台仍由 `Next.js` 控制
2. 安装 WordPress 主题不会直接改写 `www.mindhikers.com`
3. 主题能力主要服务于 WordPress 自己的页面或未来扩展子站

## 11. 回滚策略

如果 CMS 接入阶段出问题，回滚路径必须非常短：

1. 前台把 `BLOG_SOURCE` 切回 `mdx`
2. 重新部署前台
3. 线上立即恢复为旧博客内容源
4. WordPress 和数据库保留，不急于删除

这样可以做到：

1. 新服务出问题时，前台不跟着一起挂
2. 平台资源和迁移数据仍保留，便于排障

## 12. 验收标准

只有同时满足以下条件，才算三服务方案真正建成：

1. Railway 项目中有且仅有以下核心服务：
   - `Mindhikers-Homepage`
   - `mindhikers-cms`
   - `mindhikers-db`
2. `mindhikers-cms` 可访问 `/wp-admin`
3. `mindhikers-cms` 可访问 `/wp-json/wp/v2/posts`
4. `Mindhikers-Homepage` 已配置 `WORDPRESS_API_URL`
5. `Mindhikers-Homepage` 已配置 `REVALIDATE_SECRET`
6. 发布一篇 WordPress 文章后，前台能出现
7. `BLOG_SOURCE=hybrid` 正常
8. `BLOG_SOURCE=wordpress` 正常
9. 切回 `BLOG_SOURCE=mdx` 仍正常

## 13. 下一步执行建议

在你确认这份计划后，下一轮执行应严格按以下顺序推进：

1. 创建 `mindhikers-db`
2. 创建 `mindhikers-cms`
3. 挂载 `wp-content` Volume
4. 映射数据库变量到 `WORDPRESS_DB_*`
5. 生成 Railway 临时域名验证 CMS
6. 绑定 `homepage-manage.mindhikers.com`
7. 给前台写入 `WORDPRESS_API_URL` 与 `REVALIDATE_SECRET`
8. 先切 `BLOG_SOURCE=hybrid`
9. 迁移内容
10. 最后切到 `BLOG_SOURCE=wordpress`

这份文档就是后续使用 Railway MCP 实际施工的唯一执行蓝图。

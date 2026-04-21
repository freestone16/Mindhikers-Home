# MindHikers 全站 WordPress CMS 三服务执行计划

日期：2026-03-29
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
分支：`codex/cyd-stumpel-home-exploration`
适用对象：Railway Project `Mindhikers-Homepage`

## 1. 本计划要解决什么

这份计划解决的是：

1. 如何在现有 Railway 项目中建设三服务
2. 如何让 WordPress 成为整个 homepage 的后台服务
3. 如何保证这套系统安全、健壮、长期稳定

三服务目标如下：

1. `Mindhikers-Homepage`
   - Next.js 前台服务
2. `mindhikers-cms`
   - WordPress 后台服务
3. `mindhikers-db`
   - MySQL 数据库服务

## 2. 当前项目现状

当前 Railway 项目状态：

1. 项目名：`Mindhikers-Homepage`
2. 环境：`production`
3. 已存在服务：
   - `Mindhikers-Homepage`
4. 尚不存在：
   - `mindhikers-cms`
   - `mindhikers-db`

当前代码状态：

1. 前端已经完成博客层 CMS 抽象
2. 但整站首页和产品页仍未切到 CMS
3. 下一步应改成“全站内容由 WordPress 管理”的落地路径

## 3. 最终服务拓扑

### 3.1 服务一：`Mindhikers-Homepage`

职责：

1. 对外主站
2. 渲染中文首页和英文首页
3. 渲染产品页
4. 渲染博客列表和详情
5. 暴露 `/api/revalidate`

对外域名：

1. `www.mindhikers.com`

### 3.2 服务二：`mindhikers-cms`

职责：

1. 登录后台
2. 管理整站内容
3. 管理博客文章
4. 管理产品页
5. 管理站点设置
6. 对外提供 REST API
7. 发布内容后回调前台刷新缓存

对外域名：

1. `homepage-manage.mindhikers.com`

### 3.3 服务三：`mindhikers-db`

职责：

1. 存储 WordPress 数据
2. 仅供 CMS 服务使用

## 4. 服务间关系

### 4.1 `mindhikers-db -> mindhikers-cms`

数据库变量流向 CMS：

1. `MYSQLHOST` -> `WORDPRESS_DB_HOST`
2. `MYSQLPORT` -> `WORDPRESS_DB_HOST` 补端口
3. `MYSQLUSER` -> `WORDPRESS_DB_USER`
4. `MYSQLPASSWORD` -> `WORDPRESS_DB_PASSWORD`
5. `MYSQLDATABASE` -> `WORDPRESS_DB_NAME`

### 4.2 `mindhikers-cms -> Mindhikers-Homepage`

CMS 向前台提供：

1. `site settings`
2. `homepage zh`
3. `homepage en`
4. `product page zh`
5. `product page en`
6. `posts`

前台通过：

1. `WORDPRESS_API_URL`

访问：

1. `/wp-json/mindhikers/v1/...`
2. `/wp-json/wp/v2/posts`

### 4.3 `mindhikers-cms -> Mindhikers-Homepage /api/revalidate`

CMS 在内容更新后调用前台：

1. `https://www.mindhikers.com/api/revalidate`

需要：

1. `REVALIDATE_SECRET`

## 5. WordPress 内容模型实施方案

### 5.1 不采用 page builder 作为核心

本计划明确不采用：

1. Elementor 作为站点核心
2. 后台自由拼页作为站点核心

因为这不符合长期稳定目标。

### 5.2 采用自研轻量插件 `mindhikers-cms-core`

该插件是整个 CMS 落地的核心。

职责：

1. 注册内容类型
2. 提供后台字段
3. 提供 REST 输出
4. 提供更新回调

### 5.3 内容类型设计

建议如下：

1. `mh_homepage`
   - `Homepage (ZH)`
   - `Homepage (EN)`
2. `mh_product_page`
   - `Golden Crucible (ZH)`
   - `Golden Crucible (EN)`
3. `mh_site_settings`
   - 单例
4. `post`
   - 原生文章

## 6. 前台需要的 API 协议

推荐由 `mindhikers-cms-core` 输出以下接口：

1. `/wp-json/mindhikers/v1/site-settings`
2. `/wp-json/mindhikers/v1/homepage/zh`
3. `/wp-json/mindhikers/v1/homepage/en`
4. `/wp-json/mindhikers/v1/product/golden-crucible?locale=zh`
5. `/wp-json/mindhikers/v1/product/golden-crucible?locale=en`
6. `/wp-json/wp/v2/posts`

目标：

1. 前端拿到稳定 JSON
2. 不直接消费后台零散 meta 结构

## 7. Railway 侧执行顺序

### 阶段 A：创建数据库服务

创建：

1. `mindhikers-db`

完成后检查：

1. 服务启动正常
2. MySQL 变量存在

### 阶段 B：创建 CMS 服务

创建：

1. `mindhikers-cms`

必须同时完成：

1. 绑定 Volume 到 `/var/www/html/wp-content`
2. 写入 `WORDPRESS_DB_*`
3. 写入 `WORDPRESS_TABLE_PREFIX`
4. 写入 `WORDPRESS_CONFIG_EXTRA`

### 阶段 C：初始化 WordPress

完成：

1. 绑定 Railway 临时域名
2. 首次安装
3. 创建管理员
4. 创建编辑账号

### 阶段 D：部署 `mindhikers-cms-core`

必须做：

1. 将 `mindhikers-cms-core` 放入 `mu-plugins` 或受控插件目录
2. 确保其注册内容类型和 REST 路由

推荐：

1. 放入 `wp-content/mu-plugins`

原因：

1. 关键内容模型不依赖手工启用
2. 更稳定

### 阶段 E：接前台变量

给 `Mindhikers-Homepage` 写入：

1. `WORDPRESS_API_URL=https://homepage-manage.mindhikers.com`
2. `REVALIDATE_SECRET=<secret>`
3. 后续新增全站内容源模式开关

### 阶段 F：前端从博客 CMS 升级到全站 CMS

前端后续要做的改造：

1. `src/data/site-content.ts` 逐步迁出
2. 中文首页改为读取 `homepage/zh`
3. 英文首页改为读取 `homepage/en`
4. 产品页改为读取 `product/...`
5. 博客继续读取 `posts`

### 阶段 G：内容录入与迁移

后台录入：

1. 中文首页
2. 英文首页
3. 中文产品页
4. 英文产品页
5. 博客文章

## 8. 环境变量表

### 8.1 `mindhikers-cms`

必须具备：

1. `WORDPRESS_DB_HOST`
2. `WORDPRESS_DB_USER`
3. `WORDPRESS_DB_PASSWORD`
4. `WORDPRESS_DB_NAME`
5. `WORDPRESS_TABLE_PREFIX=mhwp_`
6. `WORDPRESS_CONFIG_EXTRA`

推荐 `WORDPRESS_CONFIG_EXTRA`：

```php
define('FORCE_SSL_ADMIN', true);
define('DISALLOW_FILE_EDIT', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_POST_REVISIONS', 20);
define('EMPTY_TRASH_DAYS', 15);
```

### 8.2 `Mindhikers-Homepage`

必须具备：

1. `WORDPRESS_API_URL=https://homepage-manage.mindhikers.com`
2. `REVALIDATE_SECRET=<long-random-secret>`

## 9. 安全策略

### 9.1 WordPress 后台安全

必须执行：

1. 禁止后台在线编辑文件
2. 管理员最少化
3. 强密码
4. 编辑角色和管理员角色分开
5. 后续启用 MFA

### 9.2 插件策略

允许插件，但要区分层级：

1. 核心业务插件
   - `mindhikers-cms-core`
2. 运维插件
   - SMTP
   - 安全
3. 非核心增强插件
   - 必须谨慎

不允许：

1. 把 page builder 当主系统依赖
2. 把整站结构交给不可控第三方插件

### 9.3 主题策略

可以装主题，但说明如下：

1. 主题不会接管 `www.mindhikers.com`
2. 主题只影响 WordPress 自己的页面和未来可能的子站
3. 主站前台仍由 `Next.js` 控制

## 10. 健壮性设计

### 10.1 服务隔离

1. 前台、CMS、数据库分离
2. CMS 故障不应直接拖垮前台

### 10.2 数据边界

1. 站点结构由前端代码控制
2. 内容由 WordPress 管理

### 10.3 回滚

如果 CMS 整站内容接入未完成：

1. 前端继续保留当前本地内容兜底
2. 分模块迁移，不一次性切全站

## 11. 施工暂停点

以下节点必须停下来确认：

1. `mindhikers-db` 创建完成后
2. `mindhikers-cms` 创建并挂卷后
3. WordPress 首次初始化完成后
4. `mindhikers-cms-core` 设计确认后
5. 前端从博客 CMS 升级到全站 CMS 开始前

## 12. 本计划确认后下一步

如果你确认这份计划，下一轮执行顺序应为：

1. 在 Railway 创建 `mindhikers-db`
2. 在 Railway 创建 `mindhikers-cms`
3. 配置 Volume 与 `WORDPRESS_DB_*`
4. 初始化 WordPress
5. 设计并落地 `mindhikers-cms-core`
6. 再开始前端从“博客 CMS”升级到“全站 CMS”

这一步很关键：

当前不应该直接继续“盲目创建服务并往前冲”，而应该先按这份全站版计划，把 WordPress 的角色彻底定义清楚。

# MindHikers 安全访问架构重设计

日期：2026-03-29
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
分支：`codex/cyd-stumpel-home-exploration`
目标：以安全、强壮、简单、长期稳定维护为原则，重新定义 Homepage 与 CMS 的访问边界

## 1. 结论

如果按你的真实目标重新收口，`Mindhikers` 最合适的生产架构不是：

1. 公开旧 `cms.mindhikers.com`
2. 公开一个读内容的 `content.mindhikers.com`
3. 让 WordPress 同时承担“对内后台”和“对外内容接口”

更合适的方案是：

1. `www.mindhikers.com`
   - 唯一公开主站
2. `homepage-manage.mindhikers.com`
   - 只给你和极少数管理员
   - 全站放在 `Cloudflare Access` 后
3. `WordPress` 服务
   - 作为 CMS origin
   - 对前台优先走 Railway 私网访问
4. `DB`
   - 内部支撑，仅 CMS 使用
5. 媒体资源
   - 不长期依赖 CMS origin 暴露
   - 最终迁到 `R2/S3 + assets.mindhikers.com`

一句话概括：

`Homepage` 是唯一公开网站，`manage` 是受零信任保护的后台入口，CMS 服务本身尽量不承担公开内容出口。

## 2. 重新定义你真正关心的两个“面”

你刚才提得非常准确：

1. `Homepage` 对外
2. `CMS` 对内，对你

其他都只是支撑。

所以系统设计不应该从“我有几个模板服务”出发，而应该从“哪两个界面是人真的会用到的”出发。

### 2.1 对外面

只有一个：

1. `www.mindhikers.com`

它承担：

1. 品牌首页
2. 产品页
3. 博客页
4. SEO
5. 访客访问

### 2.2 对内面

只有一个：

1. `homepage-manage.mindhikers.com`

它承担：

1. 登录后台
2. 编辑 homepage 内容
3. 编辑产品页
4. 编辑博客
5. 管理站点配置

### 2.3 支撑层

包括：

1. `WordPress` 服务 origin
2. `MariaDB`
3. 对象存储
4. 缓存刷新 webhook

这些都不应该成为你日常感知的入口。

## 3. 推荐的最终拓扑

### 3.1 生产拓扑

1. `Homepage`
   - 服务：`Mindhikers-Homepage`
   - 域名：`www.mindhikers.com`
   - 公开

2. `CMS`
   - 服务：当前 `Primary`
   - 管理域名：`homepage-manage.mindhikers.com`
   - 不作为公开内容域名使用
   - 通过 Cloudflare Access 保护

3. `DB`
   - 服务：推荐保留 `MariaDB`
   - 仅内部连接

4. `Assets`
   - 建议新增 `assets.mindhikers.com`
   - 指向 R2 或 S3
   - 供首页图片、博客封面、上传媒体公开访问

### 3.2 为什么不再推荐 `content.mindhikers.com`

因为从“安全、简单、长期稳定”角度看，`content` 域名会把问题重新复杂化：

1. 又多一个公网入口
2. 你还得额外处理 WordPress canonical URL、API、媒体地址和权限边界
3. 后台和只读内容出口耦合在同一个服务上，长期容易变脏

与其公开 `content` 域名，不如：

1. 前台通过 Railway 私网读取 CMS 结构化内容
2. 媒体走对象存储公开 CDN

这样更干净。

## 4. 为什么这是更安全的设计

### 4.1 后台不再裸露在公网

`homepage-manage.mindhikers.com` 虽然有公网 DNS，但它不是“裸露后台”。

它应该被：

1. Cloudflare Access 全站保护
2. 只允许你的邮箱或极少数管理员账号通过
3. 强制 MFA

Cloudflare 官方支持对自托管应用做 Access 认证层，也支持按域名和路径设置策略。[Create an Access application](https://developers.cloudflare.com/learning-paths/clientless-access/access-application/create-access-app/) [Application paths](https://developers.cloudflare.com/cloudflare-one/access-controls/policies/app-paths/)

### 4.2 WordPress 后台路径可以再加一层规则

即使已经有 Access，仍建议对：

1. `/wp-admin/*`
2. `/wp-login.php`

继续加 Cloudflare WAF / Access path 规则。

Cloudflare 文档明确支持按 admin 路径做限制，甚至可以叠加来源 IP 规则。[Require known IP addresses in site admin area](https://developers.cloudflare.com/waf/custom-rules/use-cases/site-admin-only-known-ips/)

### 4.3 前台不依赖公开 CMS 域名

前台 `Homepage` 服务应该优先通过 Railway 私网去请求 CMS。

也就是：

1. `Homepage` -> `Primary.railway.internal`

这样：

1. 不需要把 CMS 当成公共 API 服务暴露
2. 前台和 CMS 之间的内容获取不走公网
3. 只剩一个受保护的管理入口

## 5. 为什么这是更强壮的设计

### 5.1 前台和后台职责彻底分离

1. Homepage 只负责展示
2. CMS 只负责管理
3. DB 只负责存储

当任何一层出问题时，更容易定位和回滚。

### 5.2 CMS 故障不等于首页彻底瘫痪

如果前台继续保留本地兜底或缓存策略：

1. CMS 一时不可达
2. 前台仍可以用缓存或兜底内容继续服务

### 5.3 媒体外置后，CMS 压力更小

一旦媒体迁到对象存储：

1. WordPress 服务不必承担公开图片流量
2. 前台加载性能更稳
3. 迁移和缓存策略更容易做

## 6. 为什么这是更简单的设计

简化后的系统，用户真正感知到的只有：

1. `www.mindhikers.com`
2. `homepage-manage.mindhikers.com`

而不是：

1. `www`
2. `cms`
3. `content`
4. `db`
5. 一堆模板默认域名

你以后自己维护的时候，思路会很清楚：

1. 访客看 Homepage
2. 你进 manage 后台
3. 其他都只是基础设施

## 7. 为什么这是更利于长期维护的设计

### 7.1 认知简单

系统边界固定为：

1. 公开前台
2. 私有后台
3. 内部数据库

### 7.2 技术债更少

避免了这些长期麻烦：

1. WordPress 多公网域名的 canonical 问题
2. 后台和公共 API 混用的权限问题
3. 媒体 URL 后续迁移困难

### 7.3 更适合权限治理

未来要新增协作者时，只需要：

1. 给 Cloudflare Access 增人
2. 给 WordPress 后台角色授权

不需要再额外暴露新入口。

## 8. 对当前 Railway 半施工状态的建议收口

当前项目里已经变成：

1. `Mindhikers-Homepage`
2. `Primary`
3. `MariaDB`
4. `MySQL`

按新的安全架构，我建议：

### 8.1 保留

1. `Mindhikers-Homepage`
2. `Primary`
3. `MariaDB`

### 8.2 清理

1. `MySQL`

原因：

1. `Primary` 当前已经和 `MariaDB` 对上了
2. 继续保留 `MariaDB` 改动更小
3. `MySQL` 是额外加出来的支线，不值得继续背着

### 8.3 域名重命名建议

当前已经添加了 `cms.mindhikers.com` 到 `Primary`。

建议后续改为：

1. 放弃 `cms.mindhikers.com`
2. 改绑 `homepage-manage.mindhikers.com`

这样语义更准确：

1. 它不是对外内容域名
2. 它就是后台管理入口

## 9. WordPress 服务的新角色

在这套重设计里，WordPress 的角色是：

1. 后台 CMS origin
2. 供 `Homepage` 私网拉取结构化内容
3. 供你通过 `homepage-manage.mindhikers.com` 进入后台

它不是：

1. 公共站点
2. 面向公众的内容入口
3. 主题驱动的网站前台

这和前面的全站 CMS 方案是一致的，只是访问边界更安全。

## 10. 媒体策略

这是这版方案里非常关键的一点。

如果 CMS 只保留受保护后台入口，而前台又不能依赖公开 CMS 域名，那么媒体必须从 CMS 分离。

### 10.1 推荐目标

1. 媒体上传最终写入对象存储
2. 公开访问走 `assets.mindhikers.com`

推荐：

1. Cloudflare R2
2. S3 兼容存储

### 10.2 第一阶段折中

如果第一阶段还没接对象存储：

1. Homepage 继续优先使用仓库内固定品牌图片
2. 博客封面先谨慎使用
3. 尽快把 CMS 上传媒体迁出

也就是说：

1. 文本内容可以先走 CMS
2. 公开媒体不建议长期绑定到受保护的后台域名

## 11. 推荐的最终访问策略

### 11.1 公开入口

1. `www.mindhikers.com`
   - 公开

2. `assets.mindhikers.com`
   - 公开静态资源

### 11.2 管理入口

1. `homepage-manage.mindhikers.com`
   - 不裸露
   - Cloudflare Access
   - 只允许指定邮箱
   - 强制 MFA

### 11.3 内部入口

1. `Primary.railway.internal`
   - Homepage -> CMS 私网访问
2. `mariadb.railway.internal`
   - CMS -> DB 私网访问

## 12. 现在应该怎么重新规划施工

如果按这版架构重新施工，顺序应改为：

1. 收口服务
   - 保留 `Primary + MariaDB`
   - 删除多余 `MySQL`
2. 收口域名
   - 将管理域名从 `cms` 迁移为 `manage`
3. 加 Cloudflare Access
   - 整个 `homepage-manage.mindhikers.com`
4. 保留 `Homepage` 为唯一公开站点
5. 让 `Homepage` 走私网读取 CMS 内容
6. 将媒体策略规划为 `assets.mindhikers.com`
7. 再开始 `mindhikers-cms-core` 实现与前端全站接入

## 13. 我对这版方案的建议

如果你问我哪版最符合：

1. 安全
2. 强壮
3. 简单
4. 长期稳定维护

我的答案就是这版：

1. `www` 对外
2. `manage` 对你
3. CMS 私网供前台读内容
4. DB 只内部
5. 媒体独立出去

这版比“公开 cms 域名”更安全，也比“再搞一个公开 content 域名”更干净。

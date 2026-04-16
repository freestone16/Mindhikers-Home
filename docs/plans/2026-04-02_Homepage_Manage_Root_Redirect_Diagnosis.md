# Homepage Manage Root Redirect 诊断说明

日期：2026-04-02  
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`  
分支：`codex/cyd-stumpel-home-exploration`

## 1. 这份文档是给谁看的

这份文档是给外部专家或新的接手者看的。

目的不是介绍整个项目，而是把当前问题的前因后果、已验证事实、已排除路径、当前停点与建议下一步一次性说清楚，避免重复试错。

## 2. 当前真正剩下的问题是什么

当前剩下的唯一未收口问题是：

1. `https://homepage-manage.mindhikers.com/`
2. 访问根路径 `/`
3. 目前仍返回 `200` 并显示默认 WordPress 前台页
4. 预期应收口为跳转到：
   - `https://homepage-manage.mindhikers.com/wp-admin/`

注意：

1. 这已经不是 Cloudflare Access 未生效的问题
2. 也不是 CMS 内容或 REST 未打通的问题
3. 更不是 `homepage-manage` 域名本身未接通的问题

## 3. 当前已经完成并确认正确的部分

### 3.1 域名与服务主线

当前新 CMS 主线服务是：

1. Railway 服务：`WordPress-L1ta`
2. 数据库服务：`MariaDB-94P8`

当前正式管理域名是：

1. `homepage-manage.mindhikers.com`

这个域名当前已经挂到 CMS 服务上，并且 Cloudflare DNS 已接入。

### 3.2 Cloudflare Access 已经真正生效

当前已通过 Cloudflare MCP 完成：

1. Zero Trust organization 创建
2. Access application 创建
3. allow policy 创建
4. One-time PIN 身份提供者创建
5. `homepage-manage.mindhikers.com` CNAME 切为 `proxied=true`

当前外部 HTTP 复验结论：

1. `https://homepage-manage.mindhikers.com/wp-login.php`
   - 返回 `302`
   - 跳转到：
     - `https://mindhikers-homepage.cloudflareaccess.com/cdn-cgi/access/login/...`
2. `https://homepage-manage.mindhikers.com/wp-admin/`
   - 也返回同样的 Cloudflare Access 登录跳转

结论：

1. 后台路径保护已经完成
2. Cloudflare Access 不是当前问题

### 3.3 公开接口没有被误伤

当前外部 HTTP 复验结论：

1. `https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh`
   - 返回 `200`
2. `https://homepage-manage.mindhikers.com/`
   - 返回 `200`

结论：

1. 这说明当前 Access 规则只命中后台路径
2. 没有误锁 `wp-json/*`
3. 没有误锁整个 host

## 4. 这次我们确认的真正根因

在继续处理根路径跳转前，先做了一轮“是不是站点真身没收口”的核对。

结果发现：

1. `WordPress-L1ta` 的 Railway 变量里，原先的 `WORDPRESS_CONFIG_EXTRA` 仍然写着 Railway 临时域名：
   - `DOMAIN_CURRENT_SITE='wordpress-l1ta-production.up.railway.app'`
   - `WP_HOME='https://wordpress-l1ta-production.up.railway.app'`
   - `WP_SITEURL='https://wordpress-l1ta-production.up.railway.app'`

这会导致：

1. WordPress 自己生成的后台跳转、REST `_links`、站点 canonical 等仍认临时域名
2. 外层虽然已经套了 `homepage-manage.mindhikers.com`
3. 但站点内部认知仍不一致

### 4.1 已执行的根因修复

已把 `WordPress-L1ta` 的 `WORDPRESS_CONFIG_EXTRA` 改为：

1. `DOMAIN_CURRENT_SITE='homepage-manage.mindhikers.com'`
2. `WP_HOME='https://homepage-manage.mindhikers.com'`
3. `WP_SITEURL='https://homepage-manage.mindhikers.com'`

并保持原安全常量不变：

1. `FORCE_SSL_ADMIN`
2. `DISALLOW_FILE_EDIT`
3. `AUTOMATIC_UPDATER_DISABLED`
4. `WP_POST_REVISIONS`
5. `EMPTY_TRASH_DAYS`

Railway 已触发重部署，并成功完成。

### 4.2 根因修复后的验证结果

修复后再次复验：

1. `https://homepage-manage.mindhikers.com/wp-json/wp/v2/types`
2. 返回内容中的 `_links`
3. 已经全部从 Railway 临时域名切到：
   - `https://homepage-manage.mindhikers.com/...`

结论：

1. 站点“真身 URL”问题已经修正成功
2. WordPress 现在已经真正把自己认作 `homepage-manage.mindhikers.com`

## 5. 为什么这次仍然没有解决根路径跳转

虽然 `WP_HOME / WP_SITEURL / DOMAIN_CURRENT_SITE` 已经修正，但根路径 `/` 目前仍然返回 `200`。

这说明：

1. 修正站点正式 URL
2. 不等于
3. “访问根路径时自动跳后台”

换句话说：

1. 我们已经解决了“站点认知错乱”的根因
2. 但“根路径是否应该跳 `/wp-admin/`”仍然是一个独立的入口策略问题

## 6. 为什么没有继续硬做下去

之所以在这里停手，是因为后面的路已经不再是简单排障，而是“选实现控制面”。

当前可选控制面有三个：

### 6.1 Cloudflare 边缘层

想法：

1. 在 Cloudflare 上给 `https://homepage-manage.mindhikers.com/`
2. 配一条 302
3. 跳到 `/wp-admin/`

为什么没继续：

1. 当前 Cloudflare MCP 凭证可以成功改：
   - Access
   - DNS
2. 但无法改：
   - Page Rules
   - Rulesets
3. 实测 API 返回：
   - `9109 Unauthorized to access requested resource`

结论：

1. 当前不是规则写法问题
2. 是当前账号没有这类重定向规则权限

### 6.2 WordPress / MU Plugin 层

想法：

1. 在 `mindhikers-cms-core` 里加一个很窄的 host/path 判断
2. 只在命中：
   - host = `homepage-manage.mindhikers.com`
   - path = `/`
   - method = `GET/HEAD`
3. 时做 `302 -> /wp-admin/`

为什么没继续：

1. 这已经不是“排错”，而是“产品入口策略实现”
2. 用户明确要求：如果这一步还不行，就先停、完整落盘、交给外部专家诊断
3. 因此没有继续推进代码补丁与线上部署

### 6.3 Web Server / 站点前台模板层

理论上也可以：

1. 在主题、首页模板、服务器 rewrite 层做根路径跳转

为什么不推荐优先：

1. 当前站点没有走这条治理路径
2. 可审计性与复用性不如边缘层或 MU Plugin 层

## 7. 当前最准确的问题定义

请不要再把当前问题描述成：

1. “Cloudflare Access 还没加好”
2. “CMS 域名还没接通”
3. “WordPress 还在认 Railway 临时域名”

这些都已经不是当前问题。

当前问题的最准确表述应为：

1. `homepage-manage.mindhikers.com` 作为 CMS 管理域名
2. 已经完成 DNS、Access、站点正式 URL 收口
3. 但其根路径 `/` 的入口策略尚未实现
4. 当前仍显示默认 WordPress 前台页
5. 需要选择一个合适控制面实现：
   - Cloudflare Redirect
   - MU Plugin redirect
   - 或其他更合适的入口治理方案

## 8. 当前系统状态快照

### 8.1 当前访问行为

1. `https://homepage-manage.mindhikers.com/`
   - 当前结果：`200`
   - 当前行为：默认 WordPress 前台页
2. `https://homepage-manage.mindhikers.com/wp-admin/`
   - 当前结果：`302`
   - 当前行为：进入 Cloudflare Access 登录页
3. `https://homepage-manage.mindhikers.com/wp-login.php`
   - 当前结果：`302`
   - 当前行为：进入 Cloudflare Access 登录页
4. `https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh`
   - 当前结果：`200`
   - 当前行为：正常返回 JSON
5. `https://homepage-manage.mindhikers.com/wp-json/wp/v2/types`
   - 当前结果：`200`
   - 当前行为：返回的 `_links` 已经使用正式域名

### 8.2 当前已生效的关键配置

#### Cloudflare

1. `homepage-manage.mindhikers.com` 已是 `proxied=true`
2. Access application 已创建
3. Access policy 已创建
4. One-time PIN identity provider 已创建

#### Railway / WordPress-L1ta

1. `RAILWAY_PUBLIC_DOMAIN=homepage-manage.mindhikers.com`
2. `WORDPRESS_CONFIG_EXTRA` 已改为正式域名版本

## 9. 对外部专家最值得优先回答的问题

建议外部专家重点回答这三个问题：

1. 在当前架构下，`homepage-manage.mindhikers.com/` 根路径跳 `/wp-admin/`，最稳的控制面应该放在哪一层？
2. 如果当前 Cloudflare 账号没有 Page Rules / Rulesets 权限，是否值得继续争取边缘层实现，还是应直接落到 MU Plugin？
3. 如果走 WordPress 层，最佳实现应该是：
   - MU Plugin 精确重定向
   - 主题模板首页重定向
   - 还是其他 WordPress 官方推荐的站点入口治理方式？

## 10. 我对下一步的建议

如果外部专家只想给一个最实用的决策，我建议他在这两条里二选一：

### 方案 A：继续走 Cloudflare

前提：

1. 能获得 Page Rules / Redirect Rules / Rulesets 的写权限

做法：

1. 仅对：
   - `https://homepage-manage.mindhikers.com/`
2. 做一条 302 到：
   - `https://homepage-manage.mindhikers.com/wp-admin/`

优点：

1. 不改 WordPress 代码
2. 入口治理留在边缘层

### 方案 B：改走 MU Plugin

做法：

1. 在 `mindhikers-cms-core` 中增加一个非常窄的重定向逻辑
2. 只命中根路径 `/`
3. 只命中管理域名 host
4. 只处理 `GET/HEAD`
5. 显式跳过：
   - `wp-json`
   - `wp-admin`
   - `wp-login.php`
   - cron
   - ajax
   - CLI

优点：

1. 不依赖当前 Cloudflare 权限模型
2. 跟随代码版本治理
3. 可审计、可回滚

## 11. 这次停在这里的原因

这次不是没有进展，而是已经把问题从“混乱现场”收敛成了一个非常明确的最后入口策略问题。

已经真正解决的部分：

1. Cloudflare Access
2. DNS 代理
3. WordPress 真身 URL 统一
4. REST `_links` 临时域名残留

剩下的唯一问题：

1. 根路径 `/` 的入口策略到底放在哪一层实现

因此现在停下来交给外部专家诊断，是合理的，不是半途而废。

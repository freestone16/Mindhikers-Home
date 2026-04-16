# Mindhikers Homepage CMS Cloudflare Access 执行操作单

日期：2026-04-01  
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`  
分支：`codex/cyd-stumpel-home-exploration`

## 1. 本次目标

只为 `homepage-manage.mindhikers.com` 的 WordPress 后台入口加最小必要的 Cloudflare Access 保护。

本次只做两件事：

1. 保护 `https://homepage-manage.mindhikers.com/wp-admin/*`
2. 保护 `https://homepage-manage.mindhikers.com/wp-login.php`

## 2. 安全施工边界

### 2.1 本次明确不做

1. 不修改 `www.mindhikers.com`
2. 不开启组织级 `deny_unmatched_requests`
3. 不保护整个 `homepage-manage.mindhikers.com/*`
4. 不拦截 `wp-json/*`
5. 不修改 WordPress 站内配置
6. 不修改 Railway 服务或 DNS 记录

### 2.2 本次默认安全策略

1. 只做路径级最小保护
2. `app_launcher_visible=false`
3. 不启用会影响全站的全局拒绝策略
4. 仅允许一个明确管理员邮箱先通过
5. 先保证 `wp-admin` 与 `wp-login.php` 可控，再处理根路径 `/ -> /wp-admin/`

## 3. 当前已知现场

1. Cloudflare Zone：`mindhikers.com`
2. Zone ID：`43c448703755bcff84a62750efaed3af`
3. Cloudflare Account ID：`8f55c4513df3dc5c90898cba2644bde9`
4. 当前 `homepage-manage.mindhikers.com` 已通到 Railway CMS
5. 当前 Access API 返回：`access.api.error.not_enabled`
6. 2026-04-01 23:22 追加核验：
   - `homepage-manage.mindhikers.com` 当前 DNS 记录为 `proxied=false`
   - 这意味着请求仍直达 Railway，Access 不会真正接管流量

结论：

1. 当前不是“策略还没建”，而是这个账号下的 Zero Trust Access 还未启用
2. 必须先启用 Zero Trust organization，再创建 Access application 和 allow policy
3. 若要让 Access 生效，`homepage-manage.mindhikers.com` 必须切到 Cloudflare 代理

## 4. 执行顺序

### 阶段 A：启用 Zero Trust Access

目标：

1. 只启用组织能力
2. 不打开全局拦截

动作：

1. 创建 Zero Trust organization
2. 设置独立 `auth_domain`
3. 保持 `deny_unmatched_requests=false`

建议值：

1. `name`: `Mindhikers Zero Trust`
2. `auth_domain`: `mindhikers.cloudflareaccess.com`
3. `session_duration`: `24h`
4. `allow_authenticate_via_warp`: `false`
5. `auto_redirect_to_identity`: `false`
6. `deny_unmatched_requests`: `false`

验收：

1. 组织创建成功
2. 后续 `GET /access/organizations` 不再报 `not_enabled`

### 阶段 B：将管理域名切到 Cloudflare 代理

目标：

1. 只让 `homepage-manage.mindhikers.com` 进入 Cloudflare 代理链路
2. 不改其他子域名

动作：

1. 将 `homepage-manage.mindhikers.com` 的 CNAME 从 `proxied=false` 改为 `proxied=true`

验收：

1. 该记录仍指向原 Railway 域名
2. 只有代理状态变化
3. 后续请求开始经过 Cloudflare

### 阶段 C：创建后台 Access 应用

目标：

1. 只锁后台登录入口
2. 不影响 CMS 公开读接口

动作：

1. 创建一条自托管 Access application
2. 主域名展示使用 `homepage-manage.mindhikers.com/wp-admin/*`
3. 应用目的地只包含：
   - `homepage-manage.mindhikers.com/wp-admin/*`
   - `homepage-manage.mindhikers.com/wp-login.php`

建议值：

1. `name`: `Mindhikers Homepage CMS Admin`
2. `type`: `self_hosted`
3. `app_launcher_visible`: `false`
4. `allow_authenticate_via_warp`: `false`
5. `http_only_cookie_attribute`: `true`
6. `same_site_cookie_attribute`: `strict`
7. `enable_binding_cookie`: `true`
8. `path_cookie_attribute`: `true`

验收：

1. 应用创建成功
2. 应用目的地仅覆盖后台路径
3. 根路径 `/` 与 `wp-json/*` 不在保护范围内

### 阶段 D：创建最小 allow 策略

目标：

1. 先只放一个明确管理员入口
2. 不使用宽松规则

动作：

1. 创建 `allow` policy
2. 只允许管理员邮箱
3. 默认不加 `everyone`、不加整域名放行

当前默认假设：

1. 首个允许邮箱使用 Cloudflare 账户对应邮箱：
   - `Contact.mindhiker@gmail.com`

验收：

1. 策略创建成功
2. 只有该邮箱可收到 Access 验证流程

### 阶段 E：最小验收

验收路径：

1. `https://homepage-manage.mindhikers.com/wp-admin/`
2. `https://homepage-manage.mindhikers.com/wp-login.php`
3. `https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh`

预期：

1. 前两者进入 Cloudflare Access 认证
2. 第三个不受本轮 Access 影响

## 5. 回滚策略

如果出现错误锁定或匹配范围过大，回滚顺序如下：

1. 删除新建的 Access policy
2. 如仍异常，删除新建的 Access application
3. Zero Trust organization 保留，不做破坏性删除

原因：

1. 组织级能力启用本身不是风险点
2. 真正影响流量的是 application 与 policy
3. 保留 organization 可避免后续重复初始化

## 6. 本次执行后不自动继续的事项

本次完成后，先停在这里，不顺手扩大施工面：

1. 不自动处理 `/ -> /wp-admin/`
2. 不自动切前台 `WORDPRESS_API_URL`
3. 不自动轮换 WordPress 管理员密码

## 7. 成功定义

当以下条件同时成立时，本次任务算完成：

1. Zero Trust Access 已启用
2. `wp-admin/*` 与 `wp-login.php` 已受保护
3. `wp-json/mindhikers/v1/*` 仍可按当前口径访问
4. 没有影响 `www.mindhikers.com`

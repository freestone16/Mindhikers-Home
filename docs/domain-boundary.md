# MindHikers Domain Boundary

更新时间：2026-03-30

## 目标

把 `mindhikers.com` 作为主根域来治理，并明确首页、CMS、各产品的子域边界，避免跨项目混用和跳转污染。

## 分层原则

1. `mindhikers.com` 只承载品牌主根域语义，不直接承担多个产品的混合入口职责。
2. 首页、后台、产品必须使用不同子域名。
3. 一个子域名只归属一个项目。
4. 强语义子域名只能在全局确认归属后启用。

## 推荐边界

### 首页

1. `www.mindhikers.com`
   - 归属：`Mindhikers-Homepage`
   - 用途：品牌首页 / 官方站
   - 对外公开

### CMS

1. `homepage-manage.mindhikers.com`
   - 归属：`Mindhikers CMS`
   - 用途：Homepage 的内容管理后台
   - 不对公众开放
   - 后续应放在 Cloudflare Access 后

### 产品

1. 各产品必须使用独立子域名
   - 示例：`gc.mindhikers.com`
   - 示例：`saas.mindhikers.com`
   - 示例：`product-name.mindhikers.com`
2. 产品不得复用首页或 CMS 域名
3. 产品自己的管理后台，也不应复用 `homepage-manage.mindhikers.com`

### 暂不启用

1. `cms.mindhikers.com`
   - 当前只作为过渡历史残留
   - 不建议再作为最终方案继续使用

## 当前建议映射

1. `www.mindhikers.com`
   - Mindhikers Homepage
2. `homepage-manage.mindhikers.com`
   - Mindhikers Homepage CMS
3. `gc.mindhikers.com` 或 `saas.mindhikers.com`
   - Golden Crucible 产品入口
4. `gc-admin.mindhikers.com` 或更明确的产品后台域名
   - Golden Crucible 自己的管理域名

## 不建议的做法

1. 把多个项目都挂到同一个 Homepage CMS 管理域名
2. 把产品域名跳转到首页 CMS 域名
3. 在未统一治理前复用 `admin.*`、`manage.*`、`api.*`
4. 首页、CMS、产品共用同一语义子域名

## 给隔壁项目的同步说明

可直接同步下面这段：

> 从 Mindhikers 这一侧确认，Homepage CMS 的最终目标域名已经收口为 `homepage-manage.mindhikers.com`。我们没有发现本项目代码或当前服务存在“主动把其他站点重定向到 Homepage CMS 域名”的逻辑，而且当前 CMS 服务本身也尚未进入可正常登录的可用状态。  
> 因此，如果你们的 SAAS 域名访问时落到了 Mindhikers 的 CMS 管理域名，更可能是你们项目内部存在历史环境变量、反向代理、canonical/base URL、登录回调、域名转发或平台配置指向了这个地址。建议优先排查你们自己的域名绑定、应用层重定向、网关/Nginx/Vercel/Railway 自定义域名和环境变量，而不是把它当成 Mindhikers 这边主动发起的跳转。

## 给隔壁项目的排查清单

1. 检查产品域名是否在平台层做了 301/302/308 转发。
2. 检查 `BASE_URL`、`SITE_URL`、`APP_URL`、`NEXT_PUBLIC_SITE_URL`、登录回调地址等环境变量。
3. 检查反向代理或 CDN 规则里是否存在对 Mindhikers CMS 管理域名的 rewrite/redirect。
4. 检查代码中的 canonical、middleware、登录后跳转、组织后台入口配置。
5. 检查该项目自己的自定义域名绑定是否指向错误服务。

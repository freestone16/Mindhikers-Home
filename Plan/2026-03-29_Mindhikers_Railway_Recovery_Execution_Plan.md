# MindHikers Railway 收口施工计划

日期：2026-03-29
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
分支：`codex/cyd-stumpel-home-exploration`
用途：为下一轮真实施工提供一份低风险、按顺序推进的收口计划

## 1. 这份计划解决什么

当前 Railway 项目已经进入半施工状态，但拓扑不干净。

当前真实状态：

1. `Mindhikers-Homepage`
2. `Primary`
3. `MariaDB`
4. `MySQL`

目标状态：

1. `Mindhikers-Homepage`
   - 对外主站
2. `Primary`
   - 作为最终 CMS 服务
   - 通过 `homepage-manage.mindhikers.com` 管理
3. `MariaDB`
   - 作为最终 DB 服务

因此，这份计划的目标不是“继续堆功能”，而是：

1. 把 4 服务收口成 3 服务
2. 把后台访问模型从 `cms` 收口到 `manage`
3. 把后续全站 CMS 施工建立在一个稳定底座上

## 2. 收口原则

下一轮真实施工必须遵循这 4 条原则：

1. 不再新增多余基础设施
2. 优先做收口，不做扩散
3. 先稳定平台，再做全站 CMS 开发
4. 每一步都要有清晰回滚点

## 3. 当前已确认事实

截至本轮结束，已确认：

1. `Primary` 当前就是 WordPress 服务
2. `Primary` 当前使用：
   - public domain: `primary-production-bf013.up.railway.app`
   - 历史 custom domain: `cms.mindhikers.com`
3. `Primary` 当前连接的是：
   - `MariaDB`
4. `Primary` 当前 volume：
   - 名称：`primary-volume`
   - 挂载路径：`/var/www/html/wp-content`
5. `MySQL` 是额外创建出来的重复数据库
6. 旧规划中的 `manage.mindhikers.com` 已成功添加到 `Primary`
7. `Primary` 当前保留的域名集合：
   - `primary-production-bf013.up.railway.app`
   - `cms.mindhikers.com`
   - `manage.mindhikers.com`
8. `Primary` 当前 `WORDPRESS_CONFIG_EXTRA` 仍指向 `cms.mindhikers.com`
   - 这意味着在 `manage` 的 DNS 和证书完成前，不应贸然切换 WordPress 站点主域
9. 当前 CLI 未发现稳定的单服务删除入口
   - `MySQL` 的删除更适合在 Railway 控制台中完成

由此可得：

1. `Primary + MariaDB` 是天然成对的
2. `MySQL` 是多余支线
3. 当前主线已不再采用泛 `manage`，而是收口到 `homepage-manage.mindhikers.com`
4. 当前最优策略不是继续改接线，而是收口

## 4. 下一轮施工目标

下一轮施工只做 5 类事情：

1. 明确保留/删除哪些服务
2. 收口后台域名
3. 落 Cloudflare Access
4. 确认 CMS volume 策略
5. 固化前台和 CMS 的访问边界

不做：

1. 全站 CMS 插件实现
2. 前端全站数据层接入
3. 内容迁移
4. 媒体迁移

这些都放到收口完成之后。

## 5. 服务收口计划

### 阶段 A：冻结现状并做最终确认

下一轮开工第一步应确认：

1. `Primary` 当前是否仍健康
2. `MariaDB` 当前是否健康
3. `MySQL` 当前是否未被任何服务使用
4. `cms.mindhikers.com` 当前是否只是绑定，还是已进入 DNS 生效

验收点：

1. `Primary` 可访问
2. `Primary` 变量仍指向 `mariadb.railway.internal`
3. `MySQL` 未被引用

### 阶段 B：正式决定保留集

保留：

1. `Mindhikers-Homepage`
2. `Primary`
3. `MariaDB`

待删除：

1. `MySQL`

原因：

1. 这条链路最少变更
2. 风险最低
3. 最符合“简单、稳定”

### 阶段 C：清理多余数据库

在确认 `Primary` 没有任何变量指向 `MySQL` 后，删除：

1. `MySQL`

执行条件：

1. `Primary` -> `MariaDB` 已确认
2. 无任何计划要把 `Primary` 改连 `MySQL`

回滚策略：

1. 删除前先完整记录 `MySQL` 变量
2. 删除动作完成后立刻重新核对服务清单

## 6. 域名收口计划

### 阶段 D：收口后台域名语义

当前已有：

1. `cms.mindhikers.com`

目标改为：

1. `homepage-manage.mindhikers.com`

建议动作：

1. 后续应为当前有效 CMS 服务新增 `homepage-manage.mindhikers.com`
2. 补齐 DNS 验证
3. 后台入口后续统一使用 `homepage-manage.mindhikers.com`
4. `cms.mindhikers.com` 在过渡期保留
5. 确认 `manage` 正常后，再移除 `cms`

当前待补的 DNS 信息：

1. `CNAME`
   - `homepage-manage.mindhikers.com -> <待最终 CMS 服务确认后的 Railway 域名>`
2. `TXT`
   - `_railway-verify.manage = railway-verify=3ff3fd464b0da158cfd900a3414c8dd9a1b47fc8957ec5b6a7d38c5f7b4e5e8f`

为什么不立即删除 `cms`：

1. 避免管理入口瞬断
2. 允许一轮观察期

### 阶段 E：Cloudflare Access

这是收口里最关键的安全动作。

必须做：

1. 把 `homepage-manage.mindhikers.com` 放到 Cloudflare Access 后
2. 只允许你的邮箱和明确授权邮箱
3. 强制 MFA

建议策略：

1. 整个 `homepage-manage.mindhikers.com/*`
2. 额外强化：
   - `/wp-admin/*`
   - `/wp-login.php`

验收点：

1. 未授权浏览器无法进入
2. 授权账号可登录
3. 后台可正常使用

## 7. Volume 收口计划

### 阶段 F：决定是否改挂载路径

当前：

1. `primary-volume -> /var/www/html/wp-content`

目标偏好：

1. `/var/www/html/wp-content`

本轮已经完成迁移，因此下一轮不再把 volume 调整当成主任务。

下一轮应重点确认：

1. `Primary` 在新挂载路径下运行正常
2. `wp-content` 中后续上传、插件、主题可持续持久化
3. 不再把 volume 作为阻塞项

## 8. 前台与 CMS 边界固化

### 阶段 G：前台访问策略

下一轮不做全站接入，但要把边界先定死：

1. `Homepage` 对外只认 `www.mindhikers.com`
2. `Homepage` 后续读取 CMS 内容优先走 Railway 私网
3. `Primary` 不再作为公开内容域名长期使用

后续代码层要对应：

1. `WORDPRESS_API_URL` 不再从“公开内容域名”思路设计
2. 优先按“内部 CMS origin”思路设计

## 9. 下一轮施工清单

下一轮建议严格按下面顺序执行：

1. 复核 `Primary`、`MariaDB`、`MySQL` 当前关系
2. 通过 Railway 控制台删除 `MySQL`
3. 完成 `manage` 的 DNS
4. 给 `manage` 加 Cloudflare Access
5. 在 DNS 生效后，把 `WORDPRESS_CONFIG_EXTRA` 从 `cms` 切到 `manage`
6. 确认 `cms.mindhikers.com` 进入退役过渡状态
7. 确认最终服务清单只剩：
   - `Mindhikers-Homepage`
   - `Primary`
   - `MariaDB`

## 10. 下一轮完成后的交付标准

如果下一轮收口施工做完，应该满足：

1. Railway 项目中只剩 3 个核心服务
2. `Primary` 是明确的 CMS 服务
3. `MariaDB` 是明确的 DB 服务
4. `homepage-manage.mindhikers.com` 可作为后台入口
5. `manage` 已有 Access 保护
6. `cms.mindhikers.com` 已进入退役或过渡状态
7. 后续实现 `mindhikers-cms-core` 时不会再被平台结构拖住

## 11. 风险提示

下一轮最容易踩坑的不是代码，而是“把多个动作绑在一起”。

特别要避免：

1. 一边删服务，一边改 volume
2. 一边改域名，一边上 Access，一边做内容初始化
3. 一边收口平台，一边开始写全站 CMS 插件

正确做法是：

1. 平台收口单独一轮
2. CMS 插件实现单独一轮
3. 前端全站接入再单独一轮

## 12. 给下一轮的明确建议

下一轮施工时，最重要的一句话是：

不要再扩建，只做收口。

也就是说：

1. 删除多余 `MySQL`
2. 收口到 `manage`
3. 上 Access
4. 固定 `Primary + MariaDB`

只要这四步完成，下面一轮真正实现全站 CMS 就会轻松很多。

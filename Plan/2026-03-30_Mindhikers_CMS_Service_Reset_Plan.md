# MindHikers CMS 服务重置规划

日期：2026-03-30
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
分支：`codex/cyd-stumpel-home-exploration`
用途：在发现当前 Railway WordPress 服务无法稳定初始化后，给出一份更安全、更稳妥的重置方案

## 1. 结论

当前 `Primary` 这台 WordPress CMS 服务，不适合继续在原地修补。

推荐策略：

1. 停止继续对当前 `Primary` 做增量修补
2. 保留现场记录
3. 采用“干净重置 CMS 服务”的方式重新建立后台
4. 只在新 CMS 服务成功打开后台后，再迁移 `homepage-manage.mindhikers.com`

这是当前最符合“安全、稳定、健壮”的路径。

## 2. 已确认的根因

`Primary` 当前确实是官方 `wordpress` 镜像，但初始化逻辑与我们之前的 volume 调整策略发生了冲突。

已确认事实：

1. `Primary` 的镜像源是 `wordpress`
2. `Primary` 的启动命令会调用 `docker-entrypoint.sh`
3. 官方 WordPress 镜像只有在 `/var/www/html` 足够“空”时，才会把核心文件复制进去
4. 我们把 volume 在 `/var/www/html` 与 `/var/www/html/wp-content` 之间调整过
5. 当前 `primary-volume` 中已经形成一份“不完整但非空”的目录状态
6. 结果是：
   - Apache 能启动
   - 但 `/var/www/html/index.php` 不存在
   - `wp-login.php` 不存在
   - `/wp-admin` 返回 `404`
   - 根路径返回 `403`

也就是说：

1. 域名不是主要问题
2. 证书不是主要问题
3. 数据库不是第一阻塞点
4. 真正的阻塞点是：CMS 服务文件系统初始化状态已经“脏掉”

## 3. 为什么不建议继续原地修补

继续修补当前 `Primary` 的问题在于：

1. 需要继续对已有 volume 做高风险处理
2. 每次修补都可能让当前状态更不可预测
3. 当前服务尚未进入“可用后台”阶段，继续叠补丁收益很低
4. 即使勉强拉起，也会留下一个很难向后维护的隐患底座

这不符合：

1. 安全
2. 稳定
3. 健壮
4. 长期可维护

## 4. 推荐的新策略

### 方案 A：干净重建 CMS 服务

推荐指数：最高

做法：

1. 保留 `Mindhikers-Homepage`
2. 保留 `MariaDB`
3. 放弃当前 `Primary` 作为最终 CMS 候选
4. 新建一个全新的 WordPress 服务
5. 从一开始就使用模板默认、经过验证的挂载方式
6. 在新服务可用前，不切 `manage`
7. 新服务验证成功后，再把 `homepage-manage.mindhikers.com` 指向新 CMS

优点：

1. 最干净
2. 最容易验证
3. 最适合长期维护

缺点：

1. 需要一次服务重建
2. 当前 `Primary` 会变成待退役服务

### 方案 B：清空/重建当前 `primary-volume`

推荐指数：中等偏低

做法：

1. 尝试删除或重建 `primary-volume`
2. 保留 `Primary`
3. 再次触发 WordPress 初始化

问题：

1. 需要可靠的 volume 删除/重建动作
2. 当前 CLI 能力是否足够，需要再次确认
3. 仍然是在一台已经异常过的服务上修复

因此不作为首选。

## 5. 推荐执行顺序

下一轮应按这个顺序施工：

1. 冻结当前 `Primary`
2. 明确 `MariaDB` 是否继续复用
3. 新建干净 CMS 服务
4. 验证新 CMS 是否能打开：
   - `/`
   - `/wp-login.php`
   - `/wp-admin`
5. 只有在后台可用后，才把 `manage` 切到新服务
6. 再做管理员账号初始化
7. 再做 Cloudflare Access
8. 最后退役旧 `Primary`

## 6. 当前不该继续做的事

在新规划确认前，不建议继续：

1. 修改 `WORDPRESS_CONFIG_EXTRA`
2. 删除 `Primary`
3. 删除 `MariaDB`
4. 把 `www` 切到 Railway
5. 开始 `mindhikers-cms-core` 实现

## 7. 用户侧影响

当前对外没有新增风险：

1. `www.mindhikers.com` 仍保持你现在的 YouTube 去向
2. Homepage CMS 的最终目标域名为 `homepage-manage.mindhikers.com`，后台尚未真正上线
3. 现在没有生产内容损失，因为 WordPress 后台尚未投入使用

## 8. 建议决策

建议直接采用：

1. 干净重建 CMS 服务
2. 不再继续原地修补当前 `Primary`

这是现在最稳的选择。

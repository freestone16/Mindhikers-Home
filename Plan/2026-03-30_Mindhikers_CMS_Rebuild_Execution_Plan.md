# MindHikers CMS 重建执行计划

日期：2026-03-30
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
分支：`codex/cyd-stumpel-home-exploration`
目标：在不影响当前 `www.mindhikers.com` 的前提下，重建一个干净、可登录、可长期维护的 WordPress CMS 服务，并在验收通过后安全清理旧现场。

## 1. 目标状态

本轮完成后，目标状态应为：

1. `Mindhikers-Homepage`
   - 继续保留
   - 仅用于前台 Homepage
2. `mindhikers-cms-v2`
   - 新建的干净 WordPress CMS 服务
   - 能正常打开：
     - `/`
     - `/wp-login.php`
     - `/wp-admin`
3. `MariaDB`
   - 继续复用
   - 作为 CMS 数据库
4. `homepage-manage.mindhikers.com`
   - 指向新的 CMS 服务
5. 旧 `Primary`
   - 暂不删
   - 在新 CMS 验收通过后再退役
6. 旧 `MySQL`
   - 暂不删
   - 在最终复核后再清理

## 2. 为什么要重建

当前 `Primary` 已经不适合继续原地修补，原因如下：

1. 当前服务已经进入“不完整但非空”的 WordPress 目录状态
2. Apache 可启动，但 WordPress 核心文件缺失
3. `/wp-login.php` 与 `/wp-admin` 不可用
4. 继续在原地修补会让底座越来越不可预测

因此，本轮原则是：

1. 新建
2. 验收
3. 切换
4. 清理

而不是继续在旧现场反复补丁。

## 3. 执行原则

1. 先搭新现场，后切入口
2. 先验收通过，后清理旧现场
3. 每一步都必须可回滚
4. 所有清理动作都在重复检查后执行

## 4. 服务命名策略

为避免再次混淆，本轮不用模糊名称。

新服务建议命名：

1. `mindhikers-cms-v2`

保留旧服务名称：

1. `Primary`
   - 仅作为待退役旧现场

## 5. 详细执行步骤

### 阶段 A：冻结旧现场

本阶段不删除任何旧资源，只做确认：

1. 记录当前 `Primary` 变量
2. 记录当前 `Primary` 域名绑定
3. 记录当前 `MariaDB` 变量
4. 记录当前 `MySQL` 变量

验收点：

1. 旧现场信息完整可追溯
2. 未发生破坏性动作

### 阶段 B：创建新 CMS 服务

动作：

1. 新建服务：`mindhikers-cms-v2`
2. 使用官方 `wordpress` 镜像
3. 为该服务创建新的独立 volume
4. 挂载到 `/var/www/html`

注意：

1. 本轮不再尝试把 volume 直接挂到 `/var/www/html/wp-content`
2. 先追求“后台可用”，再做后续精细化

验收点：

1. 服务创建成功
2. 新 volume 创建成功
3. volume 正确挂在 `/var/www/html`

### 阶段 C：注入数据库与站点变量

动作：

1. 把新 CMS 服务连接到现有 `MariaDB`
2. 设置：
   - `WORDPRESS_DB_HOST`
   - `WORDPRESS_DB_NAME`
   - `WORDPRESS_DB_USER`
   - `WORDPRESS_DB_PASSWORD`
3. 初始站点主域优先使用 Railway 自带域名验证
4. 在 `homepage-manage` 真正确认可用前，不急于写死到 `homepage-manage.mindhikers.com`

说明：

1. 这一阶段优先拿到一个真正可初始化的 WordPress
2. 域名切换放到后面

验收点：

1. 新服务变量完整
2. 数据库连通

### 阶段 D：初始化验收

必须实际验证：

1. Railway 自带域名上的 `/`
2. Railway 自带域名上的 `/wp-login.php`
3. Railway 自带域名上的 `/wp-admin`

验收标准：

1. 不能是 `403`
2. 不能是 `404`
3. 必须能看到 WordPress 初始化页或登录页

如果这一阶段不通过：

1. 立即停下
2. 不切换 `manage`
3. 不清理旧现场

### 阶段 E：切换管理域名

只有在阶段 D 通过后才执行。

动作：

1. 把 `homepage-manage.mindhikers.com` 绑定到新 CMS 服务
2. 绑定到 `mindhikers-cms-v2`
3. 更新 WordPress 站点域名到 `homepage-manage.mindhikers.com`
4. 再次验证：
   - `https://homepage-manage.mindhikers.com/`
   - `https://homepage-manage.mindhikers.com/wp-login.php`
   - `https://homepage-manage.mindhikers.com/wp-admin`

验收标准：

1. 后台可访问
2. 登录页正常
3. 无错误跳转

### 阶段 F：安全加固

动作：

1. 准备 Cloudflare Access 接入后台
2. 限制后台仅授权邮箱访问
3. 强制 MFA
4. 后续再关闭不必要的 WordPress 能力：
   - 文件在线编辑
   - 不必要的 XML-RPC

这一阶段可在后台可用后继续完善，但不应阻塞 CMS 可用性验收。

### 阶段 G：旧现场清理

只有在下列条件全部满足时，才开始清理：

1. 新 CMS 后台已可用
2. `manage` 已切到新 CMS
3. 实测登录链路正常
4. 至少完成一轮重复检查

清理顺序：

1. 先确认旧 `Primary` 已不再承接 `manage`
2. 再确认旧 `Primary` 无需保留做回滚
3. 再决定是否退役 `Primary`
4. 最后清理多余 `MySQL`

## 6. 回滚策略

### 若新 CMS 创建失败

1. 保留旧现场不动
2. 不切域名
3. 不删任何服务

### 若新 CMS 可创建但后台不可用

1. 保留新服务做诊断
2. 不切 `manage`
3. 旧现场继续保留

### 若 `manage` 切换后异常

1. 立即把 `manage` 指回旧服务
2. 保留新 CMS 继续诊断

## 7. 验收清单

平台验收：

1. 新 CMS 服务存在
2. 新 volume 存在且独立
3. `MariaDB` 复用成功

功能验收：

1. `/` 可访问
2. `/wp-login.php` 可访问
3. `/wp-admin` 可访问
4. 可看到 WordPress 初始化页或后台登录页

安全验收：

1. `www.mindhikers.com` 未被误切
2. 旧现场未提前删除
3. 清理动作发生前已完成重复检查

## 8. 本轮不做的事

1. 不切 `www.mindhikers.com`
2. 不开始全站 CMS 插件开发
3. 不开始首页内容迁移
4. 不开始媒体迁移

## 9. 最终建议

本轮施工必须把重心放在一件事上：

1. 拿到一个干净、可登录、可长期维护的 CMS 后台

只有这件事完成后，后面的全站 CMS 接入才值得继续。

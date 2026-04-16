# MindHikers Homepage CMS 候选重建实施方案

日期：2026-03-30  
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`  
分支：`codex/cyd-stumpel-home-exploration`

## 1. 方案概览

本方案不推翻当前架构，也不继续在当前 `WordPress` 实例上做高风险接管补丁。

最终选择：

1. 保留当前仓库内已经完成的 CMS 代码与方案资产
2. 冻结旧 `Primary`、`mindhikers-cms-v2`，也冻结当前这台已被临时改密逻辑污染的 `WordPress`
3. 并行新建一套干净的 `WordPress + MariaDB` 候选实例
4. 用人工可验证的方式完成首次安装、管理员创建、登录验收
5. 验收通过后，再部署 `mindhikers-cms-core` 并联调前台
6. 全链路验证通过后，才切换为新的 CMS 主线

一句话：

不是“全删重来”，而是“局部重建 CMS 候选实例，保留正确架构与已有代码成果”。

## 2. 为什么选这条路

### 2.1 不继续修当前 `WordPress`

原因：

1. 当前 `WordPress` 服务虽然能打开登录页，但环境变量中仍残留一次性改密逻辑
2. 这说明它已经不是一个干净、可长期托管的后台基线
3. 如果继续在这台实例上补丁式接管，后续很难证明它的安全性与可维护性

结论：

1. 它可以作为“现场参考”
2. 但不应再作为“最终 CMS 主线基座”

### 2.2 不全量删除重建

原因：

1. 当前真正有价值的资产主要在仓库，不在现有 WordPress 实例
2. 仓库中已经有：
   - `mindhikers-cms-core` MU Plugin 骨架
   - Homepage CMS 读取层
   - 博客 WordPress 抽象
   - revalidate 接口
   - 多份架构与实施方案文档
3. 如果把整套项目思路和服务一起推倒，会扩大施工面，增加新的不确定性

结论：

1. 只需要重建“CMS 候选实例”
2. 不需要重建“整体方案”与“前台代码”

## 3. 目标与边界

### 3.1 本轮目标

1. 拿到一套干净、可登录、可控的 WordPress CMS 候选实例
2. 让该实例可以承载 `mindhikers-cms-core`
3. 打通前台对 Homepage 结构化 API 的读取

### 3.2 本轮不做

1. 不切换 `www.mindhikers.com`
2. 不直接启用 `homepage-manage.mindhikers.com`
3. 不在本轮接入 Cloudflare Access
4. 不清理旧数据库与旧服务
5. 不继续尝试救活 `Primary` 或 `mindhikers-cms-v2`

## 4. 实施原则

1. 简单优先：优先使用 Railway 已验证可运行的模板，不再自建复杂镜像方案
2. 健壮优先：每个阶段都必须有独立验收，不允许边猜边推进
3. 安全优先：不再依赖一次性改密钩子、隐式密码或邮件找回
4. 可回退优先：新候选实例通过前，不触碰现有前台域名和对外入口

## 5. 实施阶段

### 阶段 A：冻结旧现场与当前脏实例

目标：

1. 统一主线认知
2. 阻止继续在坏现场和脏实例上浪费时间

动作：

1. 明确 `Primary`、`mindhikers-cms-v2` 为历史冻结态
2. 明确当前 `WordPress` 为“参考实例”，不再继续注入改密补丁
3. 记录当前服务名、数据库配对、卷信息、临时域名

验收：

1. 施工对象只剩“新 CMS 候选实例”
2. 后续命令与人工操作都不再落到旧现场

### 阶段 B：并行新建干净 CMS 候选实例

目标：

1. 新建一套干净的 `WordPress + MariaDB` 模板实例
2. 不污染当前可运行但不可托管的实例

动作：

1. 通过 Railway 模板新建新的 WordPress 服务和其配套数据库
2. 为新实例记录：
   - 服务名
   - 数据库服务名
   - volume 名称
   - 临时公网域名
3. 确认模板启动成功，且站点仍处于未安装或初始安装阶段

验收：

1. `/`
2. `/wp-login.php`
3. `/wp-admin`

以上三个入口都应正常进入安装流程或受控的初始状态，不得出现 `403`、`404`、`502`

### 阶段 C：人工完成首次安装与后台接管

目标：

1. 用确定性的方式创建管理员账号
2. 当场验证后台可登录

动作：

1. 使用浏览器手工完成首次安装
2. 明确创建管理员用户名、密码、邮箱
3. 安装完成后立即访问：
   - `/wp-login.php`
   - `/wp-admin`
4. 当场验证：
   - 用户名正确
   - 密码正确
   - 后台首页可进入
5. 将管理员信息存放到受控的后续运维记录中，不写入仓库

验收：

1. 登录成功不依赖密码猜测
2. 登录成功不依赖数据库直改
3. 登录成功不依赖邮件找回

### 阶段 D：固化最小安全基线

目标：

1. 让新实例具备可持续施工的最小安全条件

动作：

1. 写入基础安全常量：
   - `FORCE_SSL_ADMIN`
   - `DISALLOW_FILE_EDIT`
   - `AUTOMATIC_UPDATER_DISABLED`
   - `WP_POST_REVISIONS`
   - `EMPTY_TRASH_DAYS`
2. 清理任何一次性改密逻辑或临时恢复代码
3. 记录当前 `WP_HOME`、`WP_SITEURL` 与临时域名关系

验收：

1. 不再存在一次性改密入口
2. 登录页与后台仍可正常访问
3. 站点没有因安全常量注入而进入 500 状态

### 阶段 E：部署 `mindhikers-cms-core`

目标：

1. 将仓库里的业务内容模型正式装进 WordPress

动作：

1. 将以下文件部署到 WordPress `mu-plugins`：
   - `wordpress/mu-plugins/mindhikers-cms-core.php`
   - `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php`
2. 在后台确认：
   - `mh_homepage` 可见
   - 设置页可见
3. 建立两条 Homepage 内容记录：
   - `zh`
   - `en`

验收：

1. 后台可编辑 Homepage 结构化 JSON
2. 插件启用后后台无明显 PHP 报错
3. REST 路由可访问

### 阶段 F：联调 Homepage API 与前台

目标：

1. 让 Next.js 前台真正从新 CMS 候选实例读取数据

动作：

1. 将前台环境中的 `WORDPRESS_API_URL` 指向新实例
2. 验证以下路由返回结构化 JSON：
   - `/wp-json/mindhikers/v1/homepage/zh`
   - `/wp-json/mindhikers/v1/homepage/en`
3. 验证首页：
   - `/`
   - `/en`
4. 验证内容读取顺序：
   - CMS 成功时读 CMS
   - CMS 失败时回退静态内容

验收：

1. 中文首页成功读取 `zh` 内容
2. 英文首页成功读取 `en` 内容
3. 内容修改后可触发前台刷新或在缓存窗口后生效

### 阶段 G：主线收口与后续入口准备

目标：

1. 为后续 `homepage-manage.mindhikers.com` 和 Cloudflare Access 做准备
2. 但不在本轮提前切域名

动作：

1. 输出当前新实例的最终验收结果
2. 明确后续下一阶段任务：
   - 接 `homepage-manage.mindhikers.com`
   - 接 Cloudflare Access
   - 再评估旧实例清理

验收：

1. CMS 主线已被新候选实例接管
2. 域名与 Access 进入下一阶段，而不是与本轮混做

## 6. 风险与对应策略

### 风险 1：新模板实例再次出现初始化异常

策略：

1. 不手改模板基础镜像
2. 先验证三个入口的真实 HTTP 行为
3. 未通过前不进入安装和插件部署

### 风险 2：安装完成后又出现账号不可控

策略：

1. 必须人工录入管理员账号
2. 必须安装完成后立即实测登录
3. 未实测成功，不算完成阶段 C

### 风险 3：插件部署后后台报错

策略：

1. 部署前先以最小文件集进入
2. 部署后立即验证后台菜单、编辑页、REST 路由
3. 出现报错时优先回滚插件文件，不回滚整站

### 风险 4：前台接 CMS 后影响现网体验

策略：

1. 保持现有静态 fallback 逻辑
2. 先在候选实例临时域名上联调
3. 不在本轮切 `www.mindhikers.com`

## 7. 决策门槛

只有同时满足以下条件，才允许宣布本轮实施成功：

1. 新 WordPress 候选实例可稳定登录后台
2. 不依赖任何一次性改密逻辑
3. `mindhikers-cms-core` 成功部署
4. `/wp-json/mindhikers/v1/homepage/zh|en` 可用
5. 前台 `/` 与 `/en` 已能读取 CMS 内容

若以上任一条件未满足，本轮都只能算“候选验证中”，不能宣布 CMS 已完成。

## 8. 下一轮实施顺序

下一轮严格按下面顺序执行，不跳步：

1. 冻结旧现场与当前脏实例
2. 新建干净 CMS 候选实例
3. 浏览器手工完成安装并验证登录
4. 固化最小安全基线
5. 部署 `mindhikers-cms-core`
6. 联调 Homepage API 与前台
7. 记录验收结果，准备下一阶段域名与 Access

## 9. 本轮输出物

本轮只输出方案，不进入实现。

下一轮开始时，以上文档即为唯一施工口径。

# MIN-110 Staging 搭建记录

日期：2026-04-05  
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`  
分支：`codex/cyd-stumpel-home-exploration`  
关联 Issue：`MIN-110`

## 1. 本次动作概览

本次已开始实际实施新主线，而不再停留在路线讨论阶段。

已完成动作：

1. 基于 `production` 复制创建 Railway 环境：`staging`
2. 当前工作目录已切换到 `staging`
3. 已确认 `staging` 中存在 `WordPress-L1ta` 的成功部署记录
4. 已确认 `staging` 中可见 Railway 公网域名变量
5. 已按 `A+` 路线把 staging 作为全新 WordPress 实例初始化
6. 已成功登录后台并激活 `Astra` 主题
7. 已成功打开 `Starter Templates` 的 Elementor 模板库入口
8. 已完成 `Astra - Interior Designer` 模板导入
9. 已确认 staging 公开首页已切换到模板前台

## 2. 当前确认到的 staging 信息

### 2.1 环境

1. Railway 项目：`Mindhikers-Homepage`
2. 当前环境：`staging`

### 2.2 服务

当前项目仍包含以下服务：

1. `WordPress-L1ta`
2. `Mindhikers-Homepage`
3. `MariaDB-94P8`

### 2.3 staging 中已确认的 WordPress 公网入口

从 staging 环境变量中已确认：

1. `RAILWAY_PUBLIC_DOMAIN = wordpress-l1ta-staging.up.railway.app`
2. `RAILWAY_STATIC_URL = wordpress-l1ta-staging.up.railway.app`

这说明 staging 已经具备一个可用于初步验收的独立 Railway 域名入口。

## 3. 当前未完成项

1. `WORDPRESS_CONFIG_EXTRA` 仍保持生产后台域名口径：
   - 已修正为 staging Railway 域名口径
   - 但尚未切到最终自定义 staging 域名
2. 尚未把 WordPress staging 的 `WP_HOME / WP_SITEURL / DOMAIN_CURRENT_SITE` 改成 staging 域名
   - 已改为 `wordpress-l1ta-staging.up.railway.app`
3. 尚未生成或绑定自定义 staging 域名：
   - 目标建议：`homepage-staging.mindhikers.com`
4. 首次浏览器验收已确认 staging 最初是安装页
5. 现已完成全新初始化，因此不再走“恢复整站数据”路线
6. `Astra` 已安装并激活
7. `Starter Templates` 模板库已可进入
8. 尚未完成默认示例内容清理与 Mindhikers 五区块收敛

## 3.1 首次浏览器验收结果

使用 `agent-browser` 打开：

1. `https://wordpress-l1ta-staging.up.railway.app`

实际结果：

1. 页面跳到 `https://wordpress-l1ta-staging.up.railway.app/wp-admin/install.php`
2. 页面显示 `WordPress › Installation`
3. 当前截图证据：
   - `docs/testing_artifacts/2026-04-05-wordpress-staging-installation.png`

这说明 staging 环境虽已建立并可访问，但当前更接近“全新 WordPress 实例”，而不是“复制出一套已可登录的现成后台”。

## 3.2 A+ 路线实施结果

基于“当前没有实质内容包袱”的判断，本次已采用：

1. 全新初始化 WordPress staging
2. 定向迁移真正需要的内容与资产
3. 不做整站数据恢复

已完成结果：

1. 站点标题已初始化为：
   - `心行者 Mindhikers Staging`
2. 后台管理员用户名已建立：
   - `mindhikers_admin`
3. 已开启：
   - `Discourage search engines from indexing this site`
4. 已成功登录后台 Dashboard
5. 已安装并激活 `Astra`
6. 已进入 `Starter Templates` 的 Elementor 模板选择入口

当前证据：

1. 安装页截图：
   - `docs/testing_artifacts/2026-04-05-wordpress-staging-installation.png`
2. 后台 Dashboard 截图：
   - `docs/testing_artifacts/2026-04-05-wordpress-staging-dashboard.png`
3. Elementor 模板库截图：
   - `docs/testing_artifacts/2026-04-05-starter-templates-elementor.png`
4. `Interior Designer` 模板详情页截图：
   - `docs/testing_artifacts/2026-04-05-interior-designer-detail-state-forced.png`
5. 模板导入完成页截图：
   - `docs/testing_artifacts/2026-04-05-interior-designer-import-complete.png`
6. 模板导入后首页截图：
   - `docs/testing_artifacts/2026-04-05-interior-designer-homepage-live.png`

## 3.3 模板导入结果

本次已实际完成：

1. 为 `Interior Designer` 安装并激活依赖插件：
   - `Elementor`
   - `WPForms Lite`
   - `SureForms`
2. 已成功进入 `Interior Designer` 模板预览与定制界面
3. 已启动导入流程，并在导入页看到：
   - `Congratulations`
   - `100%`
4. 已验证 staging 公开首页切换到模板前台

当前首页表现为模板默认内容，说明“模版导入”这一步已经走通，后续工作从“环境搭建”正式切换到“模板内容收敛与替换”。

## 4. 下一步执行顺序

1. 先把 WordPress staging 配置口径从生产后台域名切开
2. 用浏览器检查 `wordpress-l1ta-staging.up.railway.app` 是否可正常打开
3. 删除无关默认内容：
   - `Hello world!`
   - Sample Page
4. 按五区块重建首页骨架：
   - `Hero`
   - `About`
   - `Product`
   - `Blog`
   - `Contact`
5. 再按清单迁移首页文案与博客资产

## 5. 风险提醒

1. 在 staging 未彻底切开前，不应把它当作生产后台替身使用
2. `WORDPRESS_CONFIG_EXTRA` 已切到 staging Railway 域名，但后续绑定自定义 staging 域名时仍需再核对一次
3. 当前模板已导入，但模板默认的占位人物、服务、项目、联系信息仍需替换
4. 在 staging 验收前，不应变更生产 `homepage-manage.mindhikers.com`

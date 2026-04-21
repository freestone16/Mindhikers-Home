# 开发进度存档 - 2026-04-19

## 当前状态

### 已完成 ✅
1. staging Root Directory 修复（从 `. ` 改为空）
2. staging 部署成功（builder: RAILPACK）
3. production webhook 配置
4. production railway.json 修复（builder: RAILPACK）
5. Smoke 验收：首页、产品页、英文页正常
6. **头号阻塞根因已定位**：远端 `mindhikers-m1-core.php` 是旧版，不含 REST 路由和 `m1-rest` 引用
7. **远端现场已保护**：旧入口文件已备份为 `mindhikers-m1-core.php.bak.20260419172452`

### 进行中 🟡
- Blog 0 posts 问题 — m1-rest 插件未部署到 WP 容器
- 本地已生成合并版 `mindhikers-m1-core.php`（1270 行，61KB base64），但未成功写入远端

### 待处理 🔴
1. **把合并后的 m1-rest 代码部署到 staging WordPress 容器**
   - 文件位置：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage/wordpress/mu-plugins/m1-rest/`
   - 目标位置：WP 容器 `/var/www/html/wp-content/mu-plugins/m1-rest/`
   - 文件清单：helpers.php, homepage.php, product.php, blog.php, revalidate.php
   - 或者：把 5 个文件内联进现有的 `mindhikers-m1-core.php`
   - **卡点**：`railway ssh` 传文件时 base64 字符串里的 `(` `)` 被本地 shell 解释，导致 `Syntax error: "(" unexpected`

2. **staging 完整验收**
   - Blog 列表/详情链路
   - Contact 区块
   - 手机竖屏
   - Revalidate webhook

3. **合并 staging → main → production**

## 关键决策
- 等 staging 验收通过后再合并到 production
- production 当前是旧代码（3月26日），暂不处理
- **已决定**：当前阻塞超出 AI 端可独立解决范围，需外部专家介入

## 环境信息
- staging URL: https://mindhikers-homepage-staging.up.railway.app
- WP staging: https://wordpress-l1ta-staging.up.railway.app
- 分支: staging (领先 main 多个 commit)
- 当前 HEAD: bb8635e

## 阻塞项（给外部专家的交接面）

### #1 优先级：m1-rest 插件部署

**现象**：
- `/wp-json/mindhikers/v1/blog` 返回 404
- 前台 Blog 显示 0 posts

**根因**：
- 仓库里有 `wordpress/mu-plugins/m1-rest/`（5 个 PHP 文件）
- 但运行中的 WordPress 容器里没有这套文件
- 远端现有的 `mindhikers-m1-core.php` 是旧版（7151 字节），不含 REST 路由注册和 `m1-rest` 引用

**已尝试的方法**（全部失败）：
1. `railway ssh + tee` ❌（超时）
2. `railway ssh + curl GitHub raw` ❌（仓库私有 404）
3. `railway ssh + git clone` ❌（容器无 git）
4. `railway run` ❌（新容器非当前运行容器）
5. `railway ssh + php -r base64_decode` ❌（base64 字符串里的 `(` `)` 被本地 shell 解释为命令替换，导致 `Syntax error: "(" unexpected`）
6. 交互式 shell stdin ❌（Railway CLI 不支持 TTY）
7. Railway Dashboard Web Shell ❌（当前套餐不提供）

**可用的部署路径**（需外部专家评估）：
- **路径 A**：通过 WordPress 后台「插件编辑器」手动编辑 `mindhikers-m1-core.php`，把 m1-rest 代码内联进去（已生成完整 1270 行合并版文件）
- **路径 B**：把 `m1-rest/` 打包成独立插件 ZIP，通过 WP Admin 上传安装（需处理 mu-plugin 加载顺序）
- **路径 C**：配置 Railway Volume 挂载，让仓库文件自动同步到容器
- **路径 D**：修改 Dockerfile 或构建流程，在镜像构建时把 mu-plugins 复制进去
- **路径 E**：使用 Railway 的「Deploy」功能重新部署整个服务，让新镜像包含最新文件

**现场保护**：
- 远端旧版 `mindhikers-m1-core.php` 已备份为 `mindhikers-m1-core.php.bak.20260419172452`
- 如果新文件导致问题，可随时恢复

## 技术栈
- Next.js 16.1.7, React 19, TypeScript, Tailwind 4
- `revalidateTag` 在 Next.js 16 需要 2 个参数：`(tag: string, profile: string \| CacheLifeConfig)` — 使用 `"default"`

## 后台账号（staging）
- WP Admin：`https://wordpress-l1ta-staging.up.railway.app/wp-admin/`
- 用户名：`mindhikers_admin`
- 密码：不要入仓；通过安全渠道获取

## 下一步（外部专家接手后）
1. 选择并执行上述部署路径之一
2. 验证 `/wp-json/mindhikers/v1/blog` 返回 200 且有数据
3. 验证 Blog 列表/详情链路
4. 验证 Revalidate webhook
5. 完成 staging 验收
6. 合并 staging → main → production

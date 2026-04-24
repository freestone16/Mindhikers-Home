---
title: WP 单栈多模板改造 · 外包可执行实施手册
type: refactor
status: active
date: 2026-04-23
origin: docs/plans/2026-04-23-002-wp-single-stack-migration-plan.md
audience: codex 外包团队 LLM（零上下文假设）
---

# WP 单栈多模板改造 · 外包可执行实施手册

## TL;DR

本文档是**给 codex 外包团队 LLM 直接逐步执行**的迁移手册，将 Mindhikers 从当前 "Next.js 前台 + WordPress headless" 的双栈结构，彻底改造为 "WordPress 单栈 + 可切换多主题"。改造完成后：

- `www.mindhikers.com` 由 WordPress 主题直接渲染（不再由 Next.js 渲染）
- 仓库 `wordpress/` 目录成为 WP 代码唯一真源，`git push` 自动部署
- 所有 Code Snippets 补丁（`mhs`/`mhs02`/`mhs03`）退役
- 半成品插件 `M1 REST API` 卸载，逻辑全部回迁 mu-plugin
- Next.js 代码归档到 `legacy/nextjs-frontend/`
- WP 主题重命名为 `mindhikers-main`
- 支持通过 WP Admin → 外观 → 主题 切换不同主题模板

**工期**：8-10 工作日（约 1.5-2 周）。
**当前分支**：`experiment/wp-traditional-mode`（改造完成后合并 main）。
**主 Linear issue**：`MIN-30`（父级/归属：`MIN-7 网站开发`）。

---

## 决策锁定表（老卢 2026-04-23 已拍板，外包团队不得变更）

| # | 决策项 | 锁定值 | 对执行的影响 |
|---|---|---|---|
| 1 | Linear 主 issue | `MIN-30`（父级/归属：`MIN-7 网站开发`） | 所有 commit message 必须 `refs MIN-30` |
| 2 | 主题目录命名 | `mindhikers-main`（不是 `astra-child`、不是 `mindhikers-child`） | Phase 1 含重命名 + 全量引用替换 |
| 3 | 插件策略 | 混合模式（见下表） | 必须在 Dockerfile 里显式 COPY bundled 插件 |
| 4 | Next.js `src/` 处置 | 改造完 `git mv src legacy/nextjs-frontend/src`（归档，不删除） | Phase 7 执行 |
| 5 | 备份执行者 | 老卢（Railway dashboard 手动快照 + `mysqldump`） | P0 全部为 🛑 卡点，等老卢回执 |
| 6 | BlurFade 等动画 | 接近即可（CSS transition + IntersectionObserver），不追求像素级一致 | Phase 3.3 降低复杂度 |
| 7 | DNS 切换 | LLM 指导老卢在 Cloudflare 面板操作 | Phase 6 仅给指引，不代劳 |

### 插件策略（决策 3 详细展开）

| 插件 | 处置 | 放置位置 |
|---|---|---|
| Carbon Fields | **打进 image** | `wordpress/plugins-bundled/carbon-fields/` |
| Polylang | **打进 image** | `wordpress/plugins-bundled/polylang/` |
| Akismet | 留 Volume（WP 后台可升级） | 不入仓库 |
| Elementor | 留 Volume | 不入仓库 |
| SureForms | 留 Volume | 不入仓库 |
| Starter Templates | 留 Volume | 不入仓库 |
| SureRank | 留 Volume | 不入仓库 |
| WPForms | 留 Volume | 不入仓库 |
| **M1 REST API**（半成品） | **直接卸载 + 删除** | 逻辑回迁到 `wordpress/mu-plugins/mindhikers-cms-core/src/formatters/` |

---

## 前置说明（外包团队必读）

### 1. 仓库约定

- 所有文件路径默认是 repo-relative（基于 `Mindhikers-Homepage/` 根目录）。
- 绝对路径基准：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage/`（老卢本机路径；外包执行时以 `git clone` 后的实际位置为准）。
- Git 分支：在 `experiment/wp-traditional-mode` 上直接改，**不要**新开子分支。所有 commit 必须 `refs MIN-30`。

### 2. 提交纪律（不可违反）

- **治理文档与代码变更必须分开 commit**（老杨红线）：
  - 本 playbook、`rules.md`、`HANDOFF.md` 的变更 → 单独的 "docs:" commit
  - Dockerfile、主题代码、mu-plugin 代码、脚本 → 独立的 "feat:"/"refactor:"/"fix:" commit
- **同一治理修复单元**（代码 + 对应文档）必须**同一个 commit**。
- **绝对不推送**：`git push`、`git merge` 到公共分支前必须显式向老卢请示（外包团队所有 push 都需老卢二次确认）。
- commit message 必须 `refs MIN-30 <动作>`（仅老卢明确收口时才允许 `fixes`/`closes`）。

### 3. 🛑 卡点机制

手册中出现 🛑 的地方，表示必须**停止执行、等待老卢回执后才能继续**。外包团队不得自行判断或跳过。

### 4. 跨 Phase 之间的依赖

- Phase 0 全部完成 → 才能开始 Phase 1
- Phase 1 staging 验证通过 → 才能开始 Phase 2
- Phase 1-4 在 staging 全部通过 → 才能开始 Phase 5
- Phase 5 全站验证通过 → 才能开始 Phase 6
- Phase 6 DNS 切换稳定 72 小时 → 才能开始 Phase 7

---

## 关键环境信息

### Railway 服务拓扑

| 环境 | 服务名 | 域名 | 角色 |
|---|---|---|---|
| production | `Mindhikers-Homepage`（Next.js） | `www.mindhikers.com` | 当前主前台（改造完停服） |
| production | `WordPress-L1ta` | `homepage-manage.mindhikers.com` | 当前后台（改造完兼任前台） |
| production | `MariaDB-xxxx` | 内部 | WP 数据库（不变） |
| staging | `Mindhikers-Homepage`（Next.js） | `mindhikers-homepage-staging.up.railway.app` | 当前 staging 前台 |
| staging | `WordPress-L1ta` | `wordpress-l1ta-staging.up.railway.app` | staging 后台 |
| staging | `MariaDB-94P8` | 内部 | staging WP 数据库 |

### Volumes

| Volume | 挂载路径 | 内容 |
|---|---|---|
| `wordpress-volume-vRzA`（staging） | `/var/www/html/wp-content/`（整个） | 当前全部 WP 代码 + uploads + plugins |
| production 对应 volume | 同上 | production 全部 WP 代码 + uploads + plugins |
| `mariadb-volume-x1on`（staging） | MariaDB 数据目录 | staging 数据库 |
| production MariaDB volume | 同上 | production 数据库 |

改造后 Volume 只保留 `uploads/`、`wp-config.php`、非 bundled 的 `plugins/` 目录。

### 关键环境变量（Railway）

**production 现有**（改造后保留）：
- `REVALIDATE_SECRET`
- `WORDPRESS_API_URL`（Next.js 退役后可删）
- `BLOG_SOURCE=wordpress`（Next.js 退役后可删）

**WP 容器新增**（Phase 1 配置）：
- `MINDHIKERS_REVALIDATE_ENDPOINT`（Next.js 退役后可删；过渡期保留）
- `MINDHIKERS_REVALIDATE_SECRET`（同上）

---

# Phase 0 · 备份与基线锁定（0.5 工作日）

**目标**：所有后续改造都必须有完整、可还原的回退基线。

## P0.1 🛑 老卢手工备份 production Volume（`wp-content`）

### WHY

production Volume 内含**仓库之外**的真实代码（当前 mu-plugins、plugins、uploads），Dockerfile COPY 改造后部分路径会被镜像层覆盖，必须先备份。

### HOW（老卢执行，外包团队不得代劳）

1. 登录 Railway Dashboard：`https://railway.app/` → 选择 `Mindhikers-Homepage` 项目 → 切到 `production` 环境
2. 点击 `WordPress-L1ta` 服务 → 顶部 Tab `Volumes`
3. 找到 `wordpress-volume-xxx` → 右侧菜单 `...` → `Download snapshot`（若 Railway 未提供直接下载，走方式 B）
4. 方式 B（CLI 方式）：
   ```bash
   # 老卢本机执行
   cd /Users/luzhoua/Mindhikers/Mindhikers-Homepage
   mkdir -p backups/2026-04-23-production
   railway login
   railway link  # 选择 Mindhikers-Homepage production
   railway run --service WordPress-L1ta bash -c "cd /var/www/html && tar czf /tmp/wp-content-backup.tgz wp-content"
   railway run --service WordPress-L1ta cat /tmp/wp-content-backup.tgz > backups/2026-04-23-production/wp-content.tgz
   ```
5. 本地验证：
   ```bash
   tar tzf backups/2026-04-23-production/wp-content.tgz | head -20
   # 应能看到 wp-content/uploads/ wp-content/plugins/ wp-content/mu-plugins/ 等目录
   ```

### 验证

- `backups/2026-04-23-production/wp-content.tgz` 存在且大小 > 1MB
- `tar tzf` 能列出目录结构
- `uploads/` 目录在备份内可见

### 回退

备份本身不需回退；如果备份失败，不得进入后续步骤。

---

## P0.2 🛑 老卢手工备份 production MariaDB

### WHY

Phase 1 Dockerfile 改造不改数据库，但 Phase 3.4（Polylang 接入）可能触发 locale 字段新增；Phase 4（博客迁移）会写入 wp_posts 表。任何一步出错都需要回滚数据库。

### HOW（老卢执行）

1. 登录 Railway Dashboard → `production` 环境 → `MariaDB-xxxx` 服务
2. 顶部 Tab `Variables` → 记录以下变量值（备用）：
   - `MYSQL_DATABASE`
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`
   - `MYSQL_ROOT_PASSWORD`
   - `MYSQLHOST` / `MYSQLPORT`
3. 使用 Railway CLI 导出：
   ```bash
   cd /Users/luzhoua/Mindhikers/Mindhikers-Homepage/backups/2026-04-23-production
   railway run --service MariaDB-xxxx bash -c "mysqldump \
     --single-transaction --routines --triggers --events \
     -u root -p\"\$MYSQL_ROOT_PASSWORD\" \
     \"\$MYSQL_DATABASE\"" > mariadb-production.sql
   ```
4. 验证导出大小 > 10KB 且包含 `wp_posts`、`wp_options`、`wp_postmeta` 表。

### 验证

```bash
ls -lh backups/2026-04-23-production/mariadb-production.sql
grep "CREATE TABLE \`wp_posts\`" backups/2026-04-23-production/mariadb-production.sql
grep "CREATE TABLE \`wp_options\`" backups/2026-04-23-production/mariadb-production.sql
grep "CREATE TABLE \`wp_postmeta\`" backups/2026-04-23-production/mariadb-production.sql
```

三个 `grep` 都应匹配到。

### 回退

不适用（备份动作本身）。

---

## P0.3 🛑 老卢手工备份 staging Volume + MariaDB

### WHY

staging 虽无业务数据，但作为结构对照和 Phase 1 首次验证的环境，也需要备份。

### HOW

重复 P0.1 + P0.2，将 `--service` 参数切换到 staging，备份到 `backups/2026-04-23-staging/`。

### 验证

`backups/2026-04-23-staging/wp-content.tgz` 和 `mariadb-staging.sql` 都存在。

### 回退

不适用。

---

## P0.4 导出 production 插件清单与激活状态

### WHY

Phase 1 决定哪些插件打进 image、哪些留 Volume 时，需要完整清单。

### HOW（外包团队可执行）

1. 登录 `https://homepage-manage.mindhikers.com/wp-admin/` →（🛑 卡点：需要老卢提供 admin 账户）
2. 菜单：**插件** → **已安装插件**
3. 截图整个列表（包含激活/停用状态、版本号、作者）
4. 保存截图到：`docs/archive/2026-04-23_migration/production-plugins-screenshot.png`
5. 同步记录到 `docs/archive/2026-04-23_migration/production-plugins.md`：
   ```markdown
   # Production WordPress 插件清单（备份时间：2026-04-23）

   | 名称 | 版本 | 状态 | 作者 | 改造后处置 |
   |---|---|---|---|---|
   | Akismet 反垃圾评论 | 5.6 | 未启用 | Automattic | 留 Volume |
   | Carbon Fields | 3.6.9 | 启用 | htmlburger | 打进 image |
   | Elementor | 4.0.1 | 启用 | Elementor.com | 留 Volume |
   | M1 REST API | 1.4.0 | 启用 | Mindhikers | **卸载 + 删除** |
   | Polylang | 3.8.2 | 启用 | WP SYNTEX | 打进 image |
   | Starter Templates | 4.5.0 | 启用 | Brainstorm Force | 留 Volume |
   | SureForms | <待填> | <待填> | <待填> | 留 Volume |
   | SureRank | <待填> | <待填> | <待填> | 留 Volume |
   | WPForms | <待填> | <待填> | <待填> | 留 Volume |
   | （第 10 个） | <待填> | <待填> | <待填> | <待填> |
   ```

### 验证

`docs/archive/2026-04-23_migration/production-plugins.md` 有 10 行插件记录（对应 staging 截图里的"全部(10)"）。

### 回退

不适用。

---

## P0.5 导出 production 所有 Code Snippets 代码

### WHY

`mhs02` 是产线依赖，Phase 2 退役前必须有归档；`mhs`、`mhs03` 虽然是 Run Once 状态，也要归档防意外。

### HOW（🛑 卡点：需要老卢或授权外包登录 production admin）

1. WP Admin → **Snippets**（左侧菜单）→ 列表页
2. 对每个 snippet（`mhs`、`mhs02`、`mhs03`）：
   - 点击名称进入编辑页
   - 复制整个代码块（PHP 部分）
   - 保存为：`docs/archive/2026-04-23_wp_snippets/<name>.php`
3. 同步保存元数据到 `docs/archive/2026-04-23_wp_snippets/README.md`：
   ```markdown
   # Production Code Snippets 归档（2026-04-23）

   改造前的三个补丁，改造后应全部删除。本目录仅作紧急回滚参考。

   | 名称 | 状态 | 触发时机 | 用途 | 是否可删 |
   |---|---|---|---|---|
   | mhs | Run Once（已执行） | Everywhere | 一次性清理旧 m1-rest 目录 | ✅ 可删 |
   | mhs02 | **Active** | rest_api_init | **产线依赖**：override REST 路由返回新 schema | ❌ 删前须等 Phase 2 Dockerfile 上线后验证 |
   | mhs03 | Run Once（已执行） | Everywhere | 一次性写入 revalidate option | ✅ 可删 |
   ```

### 验证

```bash
ls docs/archive/2026-04-23_wp_snippets/
# 应看到 mhs.php / mhs02.php / mhs03.php / README.md
```

### 回退

不适用。

---

## P0.6 🛑 老卢确认 P0 全部完成 → 开启 Phase 1

外包团队在 P0.1~P0.5 全部产出落盘后，**明示告知老卢**：
> "老卢，Phase 0 全部完成：wp-content 备份、MariaDB dump、插件清单、Snippets 归档，全部就绪。请确认后进入 Phase 1。"

得到老卢"可以进入 Phase 1"的回复后才继续。

---

# Phase 1 · Dockerfile 改造 + 插件清理（1 工作日）

**目标**：git push 成为 WP 代码唯一部署通道；bundled 插件就位；M1 REST API 逻辑回迁 mu-plugin。

## P1.1 下载 Carbon Fields 与 Polylang 源码打包入仓库

### WHY

决策 3 锁定：Carbon Fields + Polylang 打进 image。这两个插件是 Mindhikers 业务核心（CMS 字段框架 + 双语），必须跟 git 一起版本管理。

### HOW

1. 创建目录：
   ```bash
   cd /Users/luzhoua/Mindhikers/Mindhikers-Homepage
   mkdir -p wordpress/plugins-bundled
   ```

2. 下载 Carbon Fields 3.6.9（与 production 版本一致）：
   ```bash
   cd wordpress/plugins-bundled
   curl -L -o carbon-fields.zip "https://downloads.wordpress.org/plugin/carbon-fields.3.6.9.zip"
   unzip -q carbon-fields.zip
   rm carbon-fields.zip
   # 产出：wordpress/plugins-bundled/carbon-fields/
   ```

   如果 wordpress.org 没有 3.6.9（碳字段通常从 composer 安装），改用：
   ```bash
   cd wordpress/plugins-bundled
   mkdir carbon-fields && cd carbon-fields
   composer require htmlburger/carbon-fields:3.6.9 --no-dev --prefer-dist
   # 产出：wordpress/plugins-bundled/carbon-fields/vendor/htmlburger/carbon-fields/
   ```

   🛑 **卡点**：若 composer 方式产出不是标准 plugin 结构，改为从 GitHub release 获取：
   ```bash
   curl -L -o cf.zip "https://github.com/htmlburger/carbon-fields/archive/refs/tags/3.6.9.zip"
   unzip -q cf.zip && mv carbon-fields-3.6.9 carbon-fields && rm cf.zip
   ```

3. 下载 Polylang 3.8.2：
   ```bash
   cd /Users/luzhoua/Mindhikers/Mindhikers-Homepage/wordpress/plugins-bundled
   curl -L -o polylang.zip "https://downloads.wordpress.org/plugin/polylang.3.8.2.zip"
   unzip -q polylang.zip
   rm polylang.zip
   # 产出：wordpress/plugins-bundled/polylang/
   ```

4. 校验入口文件存在：
   ```bash
   test -f wordpress/plugins-bundled/carbon-fields/carbon-fields.php && echo "CF OK"
   test -f wordpress/plugins-bundled/polylang/polylang.php && echo "PL OK"
   ```

### 验证

- `wordpress/plugins-bundled/carbon-fields/carbon-fields.php` 存在
- `wordpress/plugins-bundled/polylang/polylang.php` 存在
- 两个目录的总大小合理（Carbon Fields ~5MB，Polylang ~10MB）

### 回退

若 Phase 1 最终失败要回退：`rm -rf wordpress/plugins-bundled/`。

### 提交

```
refs MIN-30 feat(wp): bundle carbon-fields 3.6.9 and polylang 3.8.2 into repo

- Add wordpress/plugins-bundled/carbon-fields/ (from wordpress.org 3.6.9)
- Add wordpress/plugins-bundled/polylang/ (from wordpress.org 3.8.2)
- These plugins are version-locked and will be COPY'd into the Docker image (P1.2)
- Other plugins remain in Volume for admin-panel upgrade flexibility
```

---

## P1.2 改写 Dockerfile：COPY wordpress/ 进 image

### WHY

这是整个改造的**原罪修复点**。当前 Dockerfile 只做 Apache 配置，不 COPY 任何仓库代码，导致 mu-plugin 改动全靠手动塞 volume。

### HOW

**BEFORE**（当前 `ops/mindhikers-cms-runtime/Dockerfile` 全文）：

```dockerfile
FROM wordpress:php8.3-apache

# Railway 上直接跑官方 wordpress 镜像时，Apache 可能出现多 MPM 同时加载。
# 这里不依赖镜像默认 CMD，而是显式复用已在旧 Primary 上验证过的启动链路。
ENTRYPOINT ["/bin/bash", "-lc", "echo 'ServerName 0.0.0.0' >> /etc/apache2/apache2.conf && echo 'DirectoryIndex index.php index.html' >> /etc/apache2/apache2.conf && echo 'upload_max_filesize = 50M' >> /usr/local/etc/php/php.ini && echo 'post_max_size = 50M' >> /usr/local/etc/php/php.ini && docker-entrypoint.sh a2dismod mpm_event && a2dismod mpm_worker && a2enmod mpm_prefork && exec apache2-foreground"]
```

**AFTER**（完整替换为）：

```dockerfile
FROM wordpress:php8.3-apache

# === Build context 说明 ===
# 本 Dockerfile 预期在仓库根目录 (Mindhikers-Homepage/) 执行 build，
# 以便相对路径 wordpress/** 可见。Railway 的 build context 需要配置为仓库根。

# === 复制 mu-plugins（生效优先于 plugins，永不被 wp-admin 禁用） ===
COPY wordpress/mu-plugins/ /var/www/html/wp-content/mu-plugins/

# === 复制主题目录 ===
COPY wordpress/themes/ /var/www/html/wp-content/themes/

# === 复制 bundled plugins（Carbon Fields + Polylang，版本锁定） ===
COPY wordpress/plugins-bundled/carbon-fields/ /var/www/html/wp-content/plugins/carbon-fields/
COPY wordpress/plugins-bundled/polylang/ /var/www/html/wp-content/plugins/polylang/

# === 权限修正（WordPress 容器约定 www-data:www-data） ===
RUN chown -R www-data:www-data /var/www/html/wp-content/mu-plugins \
                               /var/www/html/wp-content/themes \
                               /var/www/html/wp-content/plugins/carbon-fields \
                               /var/www/html/wp-content/plugins/polylang

# === 声明持久化卷：uploads 必须持久化，其他 plugins（非 bundled）也需持久化 ===
# 注意：Volume 声明仅对 docker run 直接启动生效，Railway 通过 service-level volume mount 覆盖。
VOLUME ["/var/www/html/wp-content/uploads"]

# === Apache MPM 修正 + 启动（保留原有逻辑） ===
ENTRYPOINT ["/bin/bash", "-lc", "echo 'ServerName 0.0.0.0' >> /etc/apache2/apache2.conf && echo 'DirectoryIndex index.php index.html' >> /etc/apache2/apache2.conf && echo 'upload_max_filesize = 50M' >> /usr/local/etc/php/php.ini && echo 'post_max_size = 50M' >> /usr/local/etc/php/php.ini && docker-entrypoint.sh a2dismod mpm_event && a2dismod mpm_worker && a2enmod mpm_prefork && exec apache2-foreground"]
```

### 需要同步的 Railway 配置

🛑 **卡点**：需要老卢在 Railway Dashboard 做一次性配置变更。

1. Railway Dashboard → `Mindhikers-Homepage` → **staging** 环境 → `WordPress-L1ta` 服务 → **Settings**
2. **Build** 栏目：
   - `Root Directory`：设为仓库根（`/` 或空）
   - `Dockerfile Path`：设为 `ops/mindhikers-cms-runtime/Dockerfile`
   - `Build Context`：仓库根（让 `COPY wordpress/...` 可解析）
3. **Deploy** 栏目：
   - `Volumes`：确保 `/var/www/html/wp-content` 的 volume mount 仍然存在（但理想情况是在 Phase 1.4 之后改为只挂 `/var/www/html/wp-content/uploads` + `/var/www/html/wp-content/plugins`）

**Volume mount 策略**：

现状（改造前）：
- Volume `wordpress-volume-vRzA` 挂载 `/var/www/html/wp-content`（整个目录）

改造后（推荐）：
- Volume `wordpress-volume-vRzA` 挂载 `/var/www/html/wp-content/uploads`（只挂 uploads）
- Volume 新挂载点 `wordpress-plugins-volume` 挂载 `/var/www/html/wp-content/plugins`
- mu-plugins、themes、bundled plugins 通过 image COPY 提供

**但**：Railway 的 Volume 迁移较复杂，更稳妥的**过渡策略**是：
- Volume 仍挂 `/var/www/html/wp-content`（整个）
- image 里 COPY 的内容会被 Volume 覆盖（Volume 优先）
- 因此需要 **首次部署后，手动把 image 里的 mu-plugins/themes 同步到 Volume**

详见 P1.4 首次部署验证步骤。

### 验证

Dockerfile 本地语法检查：
```bash
docker build --no-cache -t mh-wp-test -f ops/mindhikers-cms-runtime/Dockerfile .
# 应看到 COPY 步骤成功执行，最终 image 产出
docker run --rm -d --name mh-wp-test -p 8080:80 mh-wp-test
sleep 5
curl -I http://localhost:8080/  # 应返回 302 或 200（WP 安装向导）
docker stop mh-wp-test
docker rm mh-wp-test
docker rmi mh-wp-test
```

### 回退

`git revert` 对应 commit；Railway 手动 rollback 到上一个 deployment。

### 提交

```
refs MIN-30 feat(ops): Dockerfile COPY wordpress/ code into WP image

- COPY wp-content/mu-plugins, themes, bundled plugins (Carbon Fields + Polylang)
- Fix www-data ownership
- Declare VOLUME for uploads persistence
- Preserve existing Apache MPM fix

This makes git the single source of truth for WP code. After this lands:
- Volume mount strategy needs narrowing (uploads + non-bundled plugins only)
- See playbook Phase 1.4 for first-deployment sync steps
```

---

## P1.3 M1 REST API 逻辑回迁到 mu-plugin

### WHY

决策 3 锁定：M1 REST API 插件直接删除。但其内部的 `m1_build_hero` 等数据格式化函数可能被 `mhs02` snippet 引用，回迁到 mu-plugin 才能安全删除插件。

### HOW

1. 在仓库根目录获取 M1 REST API 1.4.0 源码：
   ```bash
   cd /Users/luzhoua/Mindhikers/Mindhikers-Homepage
   mkdir -p /tmp/m1-rest-inspect
   cd /tmp/m1-rest-inspect
   unzip /Users/luzhoua/Mindhikers/Mindhikers-Homepage/m1-rest-v1.4.0.zip
   ls  # 应看到 m1-rest/ 目录
   ```

2. 定位有用的格式化函数：
   ```bash
   grep -rn "function m1_build_" m1-rest/
   # 应找到 m1_build_hero、m1_build_homepage 等
   ```

3. 创建 mu-plugin formatters 目录：
   ```bash
   mkdir -p /Users/luzhoua/Mindhikers/Mindhikers-Homepage/wordpress/mu-plugins/mindhikers-cms-core/src/formatters
   ```

4. 新建文件 `wordpress/mu-plugins/mindhikers-cms-core/src/formatters/homepage-formatter.php`：

   ```php
   <?php
   /**
    * Homepage payload formatter
    *
    * 迁移自 M1 REST API 插件 1.4.0 的 homepage.php。
    * 原插件已退役（Phase 1.5 卸载），格式化逻辑统一由 mu-plugin 维护。
    */

   declare(strict_types=1);

   if (!defined('ABSPATH')) {
       exit;
   }

   /**
    * Build hero block from raw payload.
    *
    * @param array $raw Raw hero data from mh_homepage post meta
    * @param string $locale 'zh' | 'en'
    * @return array Normalized hero structure
    */
   if (!function_exists('mindhikers_build_hero')) {
       function mindhikers_build_hero(array $raw, string $locale = 'zh'): array
       {
           return [
               'eyebrow' => isset($raw['eyebrow']) ? (string) $raw['eyebrow'] : '',
               'title' => isset($raw['title']) ? (string) $raw['title'] : '',
               'description' => isset($raw['description']) ? (string) $raw['description'] : '',
               'primaryAction' => mindhikers_build_link($raw['primaryAction'] ?? []),
               'secondaryAction' => mindhikers_build_link($raw['secondaryAction'] ?? []),
               'highlights' => mindhikers_build_string_list($raw['highlights'] ?? []),
               'quickLinks' => mindhikers_build_quick_links($raw['quickLinks'] ?? []),
               'panelTitle' => isset($raw['panelTitle']) ? (string) $raw['panelTitle'] : '',
           ];
       }
   }

   if (!function_exists('mindhikers_build_link')) {
       function mindhikers_build_link(array $raw): array
       {
           return [
               'href' => isset($raw['href']) ? esc_url_raw((string) $raw['href']) : '',
               'label' => isset($raw['label']) ? (string) $raw['label'] : '',
           ];
       }
   }

   if (!function_exists('mindhikers_build_string_list')) {
       function mindhikers_build_string_list(array $raw): array
       {
           $out = [];
           foreach ($raw as $item) {
               if (is_scalar($item)) {
                   $out[] = trim((string) $item);
               }
           }
           return array_values(array_filter($out));
       }
   }

   if (!function_exists('mindhikers_build_quick_links')) {
       function mindhikers_build_quick_links(array $raw): array
       {
           $out = [];
           foreach ($raw as $item) {
               if (!is_array($item)) continue;
               $out[] = [
                   'label' => isset($item['label']) ? (string) $item['label'] : '',
                   'href' => isset($item['href']) ? esc_url_raw((string) $item['href']) : '',
                   'description' => isset($item['description']) ? (string) $item['description'] : '',
               ];
           }
           return $out;
       }
   }
   ```

   🛑 **卡点**：`m1_build_hero` 的**完整字段集**需要外包团队对照 `/tmp/m1-rest-inspect/m1-rest/homepage.php` 原文补全。上面是骨架，外包必须读完原文后补全所有字段。

5. 在 `bootstrap.php` 头部引入 formatter：

   **BEFORE**（`wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php` 开头）：
   ```php
   <?php

   declare(strict_types=1);

   defined('ABSPATH') || exit;

   if (class_exists('Mindhikers_Cms_Core')) {
       return;
   }
   ```

   **AFTER**：
   ```php
   <?php

   declare(strict_types=1);

   defined('ABSPATH') || exit;

   if (class_exists('Mindhikers_Cms_Core')) {
       return;
   }

   // === Load formatters (migrated from m1-rest plugin v1.4.0) ===
   require_once __DIR__ . '/src/formatters/homepage-formatter.php';
   ```

6. 验证 `normalizeHomepagePayload()` 方法已覆盖所有 m1_build_* 相关字段。对照 `bootstrap.php:722-792`（当前已有 normalize 方法）和迁移过来的 formatter 函数，确保**没有遗漏字段**。

### 验证

```bash
php -l wordpress/mu-plugins/mindhikers-cms-core/src/formatters/homepage-formatter.php
php -l wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php
# 两个都应输出 "No syntax errors detected"
```

### 回退

`git revert` 对应 commit。

### 提交

```
refs MIN-30 refactor(cms-core): inline m1-rest formatters into mu-plugin

- Create src/formatters/homepage-formatter.php with m1_build_* functions
- Require it from bootstrap.php top
- Prepares m1-rest plugin uninstall (Phase 1.5)

Source: m1-rest-v1.4.0.zip (archived in repo root, to be removed in P6)
```

---

## P1.4 首次 staging 部署验证

### WHY

Dockerfile 改了，需要在 staging 先验证 image 能起、Volume 与 image 兼容、现有功能不炸。

### HOW

1. 提交 Phase 1.1~1.3 的所有 commit 到 `experiment/wp-traditional-mode` 分支
   🛑 **卡点**：push 前显式请示老卢"可以推送到 experiment/wp-traditional-mode 吗？"

2. 得到确认后：
   ```bash
   git push origin experiment/wp-traditional-mode
   ```

3. 🛑 **卡点**：需要老卢在 Railway 面板把 staging 的 `WordPress-L1ta` 服务的"关联分支"切到 `experiment/wp-traditional-mode`（默认可能是 `main` 或 `staging`）。
   - Railway Dashboard → staging 环境 → `WordPress-L1ta` → Settings → Source → Branch → 选 `experiment/wp-traditional-mode`
   - 保存后 Railway 自动触发 build & deploy

4. 等待 build 完成（约 3-5 分钟），观察 Deploy Logs：
   - 应看到 `COPY wordpress/mu-plugins/` 步骤成功
   - 应看到 `COPY wordpress/plugins-bundled/...` 步骤成功
   - 应看到 Apache 启动、WordPress 监听 80

5. **关键：Volume vs Image 冲突排查**

   因为 Volume 仍挂 `/var/www/html/wp-content`（整个），Volume 会**覆盖** image 里的 COPY。所以首次部署后，WP 容器里实际生效的是 Volume 里的**旧代码**，image 的新代码被遮蔽。

   解决方案（首次一次性同步）：
   ```bash
   # 本地执行，通过 railway CLI
   railway link  # 选 staging WordPress-L1ta

   # 步骤 A：把 image 里的 mu-plugins/themes/bundled-plugins 拷贝到 Volume 里
   railway run --service WordPress-L1ta bash -c '
     # 清空 Volume 里的旧 mu-plugins（确保用 image 版本）
     rm -rf /var/www/html/wp-content/mu-plugins/mindhikers-cms-core
     # mu-plugins 从 image 覆盖不了，需要另起一个临时 image 路径做源
     # Railway 的 build context 里 image 层的代码在容器内通常路径是 /
     # 实际操作：需要在 Dockerfile 里加一步把源码复制到 /opt/wp-bundle/ 作为备份
     exit 0
   '
   ```

   🛑 **卡点**：这是最微妙的一步。推荐方案是在 Dockerfile 再加一层：

   **修正后的 Dockerfile**（P1.2 的 AFTER 再补一段）：
   ```dockerfile
   # === 备份到 /opt/wp-bundle/ 供 entrypoint 运行时同步到 Volume ===
   RUN mkdir -p /opt/wp-bundle && \
       cp -r /var/www/html/wp-content/mu-plugins /opt/wp-bundle/ && \
       cp -r /var/www/html/wp-content/themes /opt/wp-bundle/ && \
       mkdir -p /opt/wp-bundle/plugins && \
       cp -r /var/www/html/wp-content/plugins/carbon-fields /opt/wp-bundle/plugins/ && \
       cp -r /var/www/html/wp-content/plugins/polylang /opt/wp-bundle/plugins/

   # === 启动时同步 /opt/wp-bundle → Volume ===
   COPY ops/mindhikers-cms-runtime/sync-bundle.sh /usr/local/bin/mh-sync-bundle.sh
   RUN chmod +x /usr/local/bin/mh-sync-bundle.sh
   ```

   并创建 `ops/mindhikers-cms-runtime/sync-bundle.sh`：
   ```bash
   #!/bin/bash
   # Sync bundled WP code from image's /opt/wp-bundle into Volume-mounted wp-content.
   # Runs on every container start; idempotent.
   set -e

   BUNDLE=/opt/wp-bundle
   TARGET=/var/www/html/wp-content

   echo "[mh-sync-bundle] syncing mu-plugins..."
   mkdir -p "$TARGET/mu-plugins"
   cp -rf "$BUNDLE/mu-plugins/." "$TARGET/mu-plugins/"

   echo "[mh-sync-bundle] syncing themes..."
   mkdir -p "$TARGET/themes"
   cp -rf "$BUNDLE/themes/." "$TARGET/themes/"

   echo "[mh-sync-bundle] syncing bundled plugins..."
   mkdir -p "$TARGET/plugins"
   for p in "$BUNDLE/plugins/"*; do
     name=$(basename "$p")
     echo "  - $name"
     rm -rf "$TARGET/plugins/$name"
     cp -rf "$p" "$TARGET/plugins/$name"
   done

   echo "[mh-sync-bundle] setting ownership..."
   chown -R www-data:www-data "$TARGET/mu-plugins" "$TARGET/themes" \
     "$TARGET/plugins/carbon-fields" "$TARGET/plugins/polylang"

   echo "[mh-sync-bundle] done."
   ```

   修改 ENTRYPOINT 以在启动前运行同步脚本：
   ```dockerfile
   ENTRYPOINT ["/bin/bash", "-lc", "/usr/local/bin/mh-sync-bundle.sh && echo 'ServerName 0.0.0.0' >> /etc/apache2/apache2.conf && echo 'DirectoryIndex index.php index.html' >> /etc/apache2/apache2.conf && echo 'upload_max_filesize = 50M' >> /usr/local/etc/php/php.ini && echo 'post_max_size = 50M' >> /usr/local/etc/php/php.ini && docker-entrypoint.sh a2dismod mpm_event && a2dismod mpm_worker && a2enmod mpm_prefork && exec apache2-foreground"]
   ```

6. 重新 push，等 Railway 部署完。

### 验证（staging 上）

- [ ] 访问 `https://wordpress-l1ta-staging.up.railway.app/wp-admin/` → 能登录
- [ ] 菜单 **插件** → 应看到 Carbon Fields + Polylang 仍在列表中
- [ ] **外观** → **主题** → 应看到 Mindhikers Astra Child（名字还没改，P3.1 才改）
- [ ] 访问 `https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/zh` → 返回 JSON 且含 `hero`、`about`、`product`、`blog`、`contact` 等字段
- [ ] 访问 `/wp-content/uploads/` 下任意一张已有图片 URL → 正常返回
- [ ] Railway Deploy Logs 看到 `[mh-sync-bundle] done.`

### 回退

如任一验证失败：
1. Railway Dashboard → WordPress-L1ta → Deployments → 上一个成功部署 → "Rollback to this deployment"
2. 若回滚后仍不正常：P0.3 备份的 staging wp-content.tgz 还原到 Volume（需要老卢操作）。

### 提交（合并到前面的）

```
refs MIN-30 feat(ops): image-to-volume bundle sync on container start

- Add /opt/wp-bundle/ staging area in Dockerfile
- Add ops/mindhikers-cms-runtime/sync-bundle.sh for entrypoint-time sync
- Ensures Volume-mounted wp-content receives latest mu-plugin/themes/bundled-plugins from image

This pattern is necessary because Railway mounts Volume on top of image layers;
image's COPY'd files under /var/www/html/wp-content would otherwise be shadowed.
```

---

## P1.5 卸载 M1 REST API 插件（staging）

### WHY

逻辑已回迁到 mu-plugin（P1.3），此时插件可以卸载而不会破坏功能。

### HOW

🛑 **卡点**：需要老卢或授权外包登录 staging admin 执行。

1. WP Admin（staging）→ **插件** → **已安装插件**
2. 找到 **M1 REST API 1.3.0** → 点 **停用**
3. 停用后链接变为 **删除** → 点击 → 确认删除
4. 验证：`curl https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/zh` 仍返回 200 + 完整 JSON

### 验证

- WP Admin 插件列表不再含 M1 REST API
- REST API 仍正常工作（mu-plugin 接管）

### 回退

若 REST 响应异常：WP Admin → 上传插件 → 重传 `m1-rest-v1.3.0.zip`（从仓库根目录）。

### 提交

无（这是 WP Admin 操作，不产生 git commit）。

---

## P1.6 🛑 老卢确认 Phase 1 通过 → 开启 Phase 2

外包告知老卢：
> "老卢，Phase 1 staging 全部通过：Dockerfile 上线、bundle sync 生效、Carbon Fields/Polylang/mu-plugin 都在 image 里、M1 REST API 已卸载、REST API 正常。请确认进入 Phase 2。"

---

# Phase 2 · Code Snippets 补丁退役（0.5 工作日）

**目标**：production 也跑上新 Dockerfile，然后把 `mhs`/`mhs02`/`mhs03` 三个补丁全部删除。

## P2.1 production 部署新 Dockerfile

### WHY

Phase 1 只在 staging 验证了。现在要让 production 也用 image 里的 mu-plugin，为 Phase 2.2 删 `mhs02` 做准备。

### HOW

🛑 **卡点**：需要老卢授权并执行。

1. 🛑 **卡点**：老卢先确认 Phase 0 的 production 备份**真的完整**（重要：这是改 production 前的最后安全锁）。
2. Railway Dashboard → production 环境 → `WordPress-L1ta` → Settings → Source → Branch
   - 当前可能是 `main` 或某个分支
   - **暂时不要切到 `experiment/wp-traditional-mode`**，先 merge 到 main 再切

3. 🛑 **卡点**：老卢决定合并时机。建议路径：
   - 选项 A：把 `experiment/wp-traditional-mode` 的 Phase 1 commits cherry-pick 到 `staging` 分支 → staging 验证 → 再 merge staging 到 main → production 自动部署
   - 选项 B：直接 merge `experiment/wp-traditional-mode` 到 `main`（激进）
   - **推荐 A**，但需要老卢拍板。

4. production 部署后，Deploy Logs 看到同样的 `[mh-sync-bundle] done.`。

### 验证

- [ ] `curl https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh` 返回含 `quickLinks` 的新 schema（与 mhs02 激活时一致）
- [ ] WP Admin 插件列表完整，Carbon Fields / Polylang 正常
- [ ] uploads 目录图片仍可访问

### 回退

Railway 手动 rollback 到上一个 deployment。

---

## P2.2 对比 mhs02 snippet 与 mu-plugin 的等价性

### WHY

删 mhs02 前必须**明确证明**：mu-plugin 返回的 schema 与 mhs02 override 返回的完全一致（含 quickLinks 字段）。

### HOW

1. 记录 **mhs02 active 时** 的 API 响应（P0.5 归档之后、Phase 2.1 部署前的状态）：
   ```bash
   # 如果 production 已经上了 Phase 1 新镜像，这一步应该已不可得。
   # 但 mhs02 在 Code Snippets 里仍是 active 状态（没删），所以响应仍由 snippet 主导。
   curl -s https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh \
     | jq . > /tmp/mhs02-active.json
   ```

2. 临时停用 mhs02（不删）：
   🛑 **卡点**：需要老卢或授权外包。
   - WP Admin（production）→ **Snippets** → 找到 `mhs02` → 右侧 **停用**（不要点删除）

3. 记录 mu-plugin 单独响应的 schema：
   ```bash
   curl -s https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh \
     | jq . > /tmp/mu-plugin-only.json
   ```

4. 对比：
   ```bash
   diff <(jq -S . /tmp/mhs02-active.json) <(jq -S . /tmp/mu-plugin-only.json)
   ```
   - 无 diff 或仅差动态字段（如时间戳） → 等价
   - 有结构差异 → **立即重启 mhs02**，分析 mu-plugin 缺什么字段 → 回 P1.3 补 formatter

### 验证

- diff 输出为空或仅包含动态字段
- 前台（www.mindhikers.com 仍然由 Next.js 渲染，会消费 API）访问 homepage → quickLinks 区块仍正常显示

### 回退

`Snippets → mhs02 → 启用` 立即恢复。

---

## P2.3 删除 mhs / mhs02 / mhs03

### WHY

等价性确认后，补丁可以永久清除。

### HOW

🛑 **卡点**：需要老卢授权。

1. WP Admin（production）→ **Snippets**
2. 对每个 snippet：
   - `mhs`（Run Once 已执行）→ 删除
   - `mhs03`（Run Once 已执行）→ 删除
   - `mhs02`（P2.2 已停用）→ 删除
3. staging 环境同样操作（如果 staging 也有这些 snippet，参考 P0.5 的清单）

### 验证

- WP Admin → Snippets 列表 → 为空或仅剩无关 snippet
- 前台 API 仍正常（连续 30 分钟监控）

### 回退

P0.5 归档的 PHP 代码重新粘回创建 snippet。

---

## P2.4 卸载 Code Snippets 插件本身（可选）

### WHY

如果后续不再依赖 snippet 机制（推荐如此，因为所有逻辑应在 mu-plugin / 主题），可以把 Code Snippets 插件也卸载。

### HOW

🛑 **卡点**：老卢决策。

如果决定卸载：
- WP Admin → **插件** → **Code Snippets** → 停用 → 删除
- staging 同样操作

### 验证

- 插件列表不再含 Code Snippets
- 前台和 API 均正常

### 回退

重新安装插件（WP Admin → 添加新插件 → 搜索 "Code Snippets"）。

---

## P2.5 🛑 老卢确认 Phase 2 通过 → 开启 Phase 3

---

# Phase 3 · 主题重命名 + 补齐所有前端模板（3-5 工作日）

**目标**：把 `astra-child` 改名为 `mindhikers-main`，并补齐 Next.js 现有全部路由对应的 WP 模板，使其成为完整前台。

## P3.1 主题重命名 `astra-child` → `mindhikers-main`

### WHY

决策 2 锁定。名字承载语义："Mindhikers 品牌主主题"，可独立于 Astra 父主题演化。

### HOW

1. **文件系统重命名**：
   ```bash
   cd /Users/luzhoua/Mindhikers/Mindhikers-Homepage/wordpress/themes
   git mv astra-child mindhikers-main
   ```

2. **改 `style.css` 主题头**：

   **BEFORE**（`wordpress/themes/mindhikers-main/style.css` 开头）：
   ```css
   /*
   Theme Name: Mindhikers Astra Child
   Theme URI: https://mindhikers.com
   Description: Mindhikers 品牌子主题，继承 Astra 并覆盖品牌视觉风格
   Author: Mindhikers
   Author URI: https://mindhikers.com
   Template: astra
   Version: 1.0.0
   License: GPL-2.0+
   License URI: http://www.gnu.org/licenses/gpl-2.0.html
   Text Domain: mindhikers-astra-child
   */
   ```

   **AFTER**：
   ```css
   /*
   Theme Name: Mindhikers Main
   Theme URI: https://mindhikers.com
   Description: Mindhikers 官方主题（前身为 astra-child，2026-04-23 独立命名）。可通过 WP 外观面板切换到其他 Mindhikers 主题变体。
   Author: Mindhikers
   Author URI: https://mindhikers.com
   Template: astra
   Version: 2.0.0
   License: GPL-2.0+
   License URI: http://www.gnu.org/licenses/gpl-2.0.html
   Text Domain: mindhikers-main
   */
   ```

3. **全量替换 `mindhikers-astra-child` → `mindhikers-main`**（Text Domain 引用）：
   ```bash
   cd /Users/luzhoua/Mindhikers/Mindhikers-Homepage
   grep -rn "mindhikers-astra-child" wordpress/themes/mindhikers-main/
   # 逐个文件改为 mindhikers-main
   ```

   涉及文件（基于现有扫描）：
   - `wordpress/themes/mindhikers-main/functions.php:15` 的 `load_theme_textdomain('mindhikers-astra-child', ...)` → `'mindhikers-main'`

4. **提示：Astra 父主题依赖**

   `style.css` 里 `Template: astra` 意味着此主题仍是 Astra 子主题。决策阶段保留此父子关系（避免推翻 Astra 提供的响应式框架、导航、widget 结构）。

   如果老卢未来希望完全独立（不依赖 Astra），需要另开 Phase（不在本次 1.5-2 周工期内）。

5. **WP 数据库层面**：主题切换时 WP 用目录名作为主题 slug（`wordpress/themes/<slug>/`）。重命名目录后，WP Admin → 外观 → 主题 会看到 `Mindhikers Main`，但**当前激活的主题在数据库里仍指向旧 slug `astra-child`**。首次部署后需要一次性切换（见 P3.1.6）。

6. 🛑 **卡点**：这一步涉及 staging 的 WP 数据库 `wp_options` 里的 `template` / `stylesheet` option。外包团队不应直接改数据库；改为在 staging 通过 WP Admin UI 激活新主题：
   - WP Admin → 外观 → 主题 → **Mindhikers Main** → 激活
   - 前台刷新确认视觉一致

### 验证

- [ ] `ls wordpress/themes/` 只有 `mindhikers-main`（不再有 `astra-child`）
- [ ] `grep -r "astra-child" wordpress/themes/mindhikers-main/` 无结果（或仅注释里的历史说明）
- [ ] Dockerfile 重新 build & push → Railway staging → WP Admin 外观面板可见新主题并激活成功

### 回退

`git mv mindhikers-main astra-child` 反向重命名；`git revert` 对应 commit。

### 提交

```
refs MIN-30 refactor(theme): rename astra-child -> mindhikers-main

- git mv wordpress/themes/astra-child wordpress/themes/mindhikers-main
- Update Theme Name, Description, Version in style.css
- Update Text Domain mindhikers-astra-child -> mindhikers-main
- Parent theme (Template: astra) relationship preserved for now

Activation: manual via WP Admin -> Appearance -> Themes after deploy.
```

---

## P3.2 主题结构扩展：补齐 WP 模板层级

### WHY

Next.js 现有路由：
- `/` `/en` → 首页
- `/blog` → 博客列表
- `/blog/[slug]` → 博客详情
- `/product/[slug]` `/en/product/[slug]` → 产品详情
- `/golden-crucible` `/en/golden-crucible` → 专题页
- `/health` → 健康检查

当前 `mindhikers-main` 主题只有 `front-page.php`（首页），其他模板全缺。

### HOW

本 Phase 创建以下文件（详细内容见 P3.3 ~ P3.9）：

```
wordpress/themes/mindhikers-main/
├── style.css                          (已有，P3.1 已改)
├── functions.php                      (已有，P3.3 扩展)
├── header.php                         🆕 (P3.3)
├── footer.php                         🆕 (P3.3)
├── front-page.php                     (已有，P3.4 复核)
├── home.php                           🆕 博客列表 (P3.5)
├── single.php                         🆕 博客详情 (P3.6)
├── single-product.php                 🆕 产品详情 (P3.7)
├── page-golden-crucible.php           🆕 专题页 (P3.8)
├── assets/
│   ├── js/
│   │   ├── theme-toggle.js            🆕 深浅色切换 (P3.3)
│   │   ├── blur-fade.js               🆕 进入动画 (P3.3)
│   │   └── language-switcher.js       🆕 语言切换 (P3.9)
│   └── css/
│       └── theme-animations.css       🆕 (P3.3)
├── template-parts/                    (已有 5 个首页区块)
│   ├── hero.php                       (已有)
│   ├── about.php                      (已有)
│   ├── product.php                    (已有)
│   ├── blog.php                       (已有)
│   ├── contact.php                    (已有)
│   ├── navbar.php                     🆕 (P3.3)
│   ├── footer-site.php                🆕 (P3.3)
│   └── blog-card.php                  🆕 博客卡片复用 (P3.5)
└── inc/
    ├── enqueue.php                    🆕 资源加载 (P3.3)
    ├── polylang.php                   🆕 双语辅助 (P3.9)
    └── seo.php                        🆕 SEO meta (P3.10)
```

### 提交策略

Phase 3 的每个子阶段（P3.3~P3.10）对应独立 commit，便于 review 和 revert。

---

## P3.3 Header / Footer / Navbar + 主题交互 JS（1 天）

### WHY

每个页面模板都要有一致的 header（含 navbar、语言切换、深浅色切换）和 footer。

### HOW

#### P3.3.1 创建 `wordpress/themes/mindhikers-main/header.php`

```php
<?php
/**
 * Theme Header
 *
 * @package Mindhikers_Main
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh';
$locale = $lang === 'en' ? 'en' : 'zh';
$payload = function_exists('mindhikers_get_homepage_data') ? mindhikers_get_homepage_data($locale) : [];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> data-theme="light">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="mh-site-wrapper">
    <?php get_template_part('template-parts/navbar', null, ['payload' => $payload, 'locale' => $locale]); ?>
    <main class="mh-main" role="main">
```

#### P3.3.2 创建 `wordpress/themes/mindhikers-main/footer.php`

```php
<?php
/**
 * Theme Footer
 *
 * @package Mindhikers_Main
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh';
$locale = $lang === 'en' ? 'en' : 'zh';
$payload = function_exists('mindhikers_get_homepage_data') ? mindhikers_get_homepage_data($locale) : [];
?>
    </main>
    <?php get_template_part('template-parts/footer-site', null, ['payload' => $payload, 'locale' => $locale]); ?>
</div>

<button
    id="mh-theme-toggle"
    class="mh-theme-toggle"
    aria-label="<?php echo $locale === 'en' ? 'Toggle theme' : '切换主题'; ?>"
    type="button"
>
    <svg class="mh-icon-sun" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
    <svg class="mh-icon-moon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
```

#### P3.3.3 创建 `wordpress/themes/mindhikers-main/template-parts/navbar.php`

```php
<?php
/**
 * Navbar template part.
 *
 * @var array $args {
 *   @type array  $payload
 *   @type string $locale
 * }
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$payload = $args['payload'] ?? [];
$locale = $args['locale'] ?? 'zh';
$navigation = $payload['navigation'] ?? [];
$brand = $navigation['brand'] ?? '心行者 Mindhikers';
$links = is_array($navigation['links'] ?? null) ? $navigation['links'] : [];
$switchLanguage = $navigation['switchLanguage'] ?? ['href' => '', 'label' => ''];
?>
<header class="mh-navbar" role="banner">
    <div class="mh-navbar-inner">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="mh-navbar-brand">
            <span class="mh-navbar-logo" aria-hidden="true">
                <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/mindhikers-logo.png'); ?>" alt="">
            </span>
            <span class="mh-navbar-brand-text"><?php echo esc_html($brand); ?></span>
        </a>

        <nav class="mh-navbar-nav" aria-label="<?php echo $locale === 'en' ? 'Main navigation' : '主导航'; ?>">
            <?php foreach ($links as $link) :
                if (!is_array($link)) continue;
                $href = (string) ($link['href'] ?? '');
                $label = (string) ($link['label'] ?? '');
                if ($href === '' || $label === '') continue;
                $active = untrailingslashit($_SERVER['REQUEST_URI'] ?? '') === untrailingslashit($href);
                ?>
                <a
                    href="<?php echo esc_url($href); ?>"
                    class="mh-navbar-link <?php echo $active ? 'is-active' : ''; ?>"
                ><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if (!empty($switchLanguage['href']) && !empty($switchLanguage['label'])) : ?>
            <a
                href="<?php echo esc_url($switchLanguage['href']); ?>"
                class="mh-lang-switcher"
                data-mh-lang-target="<?php echo $locale === 'en' ? 'zh' : 'en'; ?>"
            ><?php echo esc_html($switchLanguage['label']); ?></a>
        <?php endif; ?>
    </div>
</header>
```

#### P3.3.4 创建 `wordpress/themes/mindhikers-main/template-parts/footer-site.php`

参考 mindhikers.com 线上 footer（截图里看到的"联系方式 / 开始联系 / 所在 / 版权"结构）。代码结构类似 navbar，读取 `$payload['contact']`、`$payload['navigation']` 等字段。

🛑 **卡点**：footer 的详细结构老卢可以给一张线上 footer 截图，或外包对照 `src/components/home-page.tsx` footer 部分摹写。

#### P3.3.5 创建 `wordpress/themes/mindhikers-main/assets/js/theme-toggle.js`

```javascript
(function () {
  'use strict';

  const STORAGE_KEY = 'mh-theme';
  const root = document.documentElement;

  function getInitialTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === 'dark' || stored === 'light') return stored;
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark';
    return 'light';
  }

  function applyTheme(theme) {
    root.setAttribute('data-theme', theme);
    localStorage.setItem(STORAGE_KEY, theme);
  }

  // Apply ASAP to avoid flash
  applyTheme(getInitialTheme());

  document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('mh-theme-toggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
      const current = root.getAttribute('data-theme') || 'light';
      applyTheme(current === 'dark' ? 'light' : 'dark');
    });
  });
})();
```

#### P3.3.6 创建 `wordpress/themes/mindhikers-main/assets/js/blur-fade.js`

```javascript
(function () {
  'use strict';

  if (!('IntersectionObserver' in window)) {
    // Fallback: show everything immediately
    document.querySelectorAll('[data-mh-blur-fade]').forEach(el => {
      el.style.opacity = '1';
      el.style.filter = 'none';
      el.style.transform = 'none';
    });
    return;
  }

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      const delay = Number(el.dataset.mhDelay || 0);
      setTimeout(function () {
        el.classList.add('mh-visible');
      }, delay);
      observer.unobserve(el);
    });
  }, { rootMargin: '-50px' });

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-mh-blur-fade]').forEach(el => observer.observe(el));
  });
})();
```

对应 CSS（追加到 `style.css` 末尾，或新建 `assets/css/theme-animations.css` 由 enqueue.php 加载）：

```css
/* === BlurFade 进入动画 === */
[data-mh-blur-fade] {
  opacity: 0;
  filter: blur(6px);
  transform: translateY(-6px);
  transition: opacity 0.4s ease-out, filter 0.4s ease-out, transform 0.4s ease-out;
  will-change: opacity, filter, transform;
}

[data-mh-blur-fade].mh-visible {
  opacity: 1;
  filter: blur(0);
  transform: translateY(0);
}

/* === 深浅色主题变量 === */
:root,
[data-theme="light"] {
  --mh-background: #f9fafb;
  --mh-foreground: #272a2f;
  --mh-primary: #386652;
  --mh-primary-foreground: #f9fafb;
  --mh-border: #e2e3e5;
  --mh-muted: #f4f5f7;
  --mh-muted-foreground: #6b7280;
}

[data-theme="dark"] {
  --mh-background: #0e1512;
  --mh-foreground: #e6e8ea;
  --mh-primary: #5e8473;
  --mh-primary-foreground: #f9fafb;
  --mh-border: #2a332f;
  --mh-muted: #1a211e;
  --mh-muted-foreground: #9ca3af;
}

/* === 主题切换按钮 === */
.mh-theme-toggle {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 50;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--mh-border);
  background: rgba(var(--mh-background-rgb, 249,250,251), 0.88);
  backdrop-filter: blur(10px);
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: transform 0.15s ease;
  color: var(--mh-foreground);
}

.mh-theme-toggle:hover {
  transform: scale(1.05);
}

.mh-theme-toggle .mh-icon-sun,
.mh-theme-toggle .mh-icon-moon {
  display: none;
}

[data-theme="light"] .mh-theme-toggle .mh-icon-moon {
  display: block;
}

[data-theme="dark"] .mh-theme-toggle .mh-icon-sun {
  display: block;
}

/* === Navbar === */
.mh-navbar {
  position: fixed;
  top: 3px;
  left: 0;
  right: 0;
  z-index: 40;
  padding: 0 16px;
  pointer-events: none;
}

.mh-navbar-inner {
  pointer-events: auto;
  max-width: 72rem;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 12px;
  border: 1px solid color-mix(in srgb, var(--mh-border) 70%, transparent);
  background: color-mix(in srgb, var(--mh-background) 88%, transparent);
  border-radius: 16px;
  box-shadow: 0 12px 40px rgba(20,24,22,0.08);
  backdrop-filter: blur(12px);
}

.mh-navbar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: inherit;
}

.mh-navbar-logo {
  width: 36px;
  height: 36px;
  border-radius: 6px;
  border: 1px solid var(--mh-border);
  overflow: hidden;
  display: inline-block;
}

.mh-navbar-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.mh-navbar-brand-text {
  font-family: var(--mh-font-display);
  font-weight: 600;
  font-size: 1.125rem;
  color: var(--mh-foreground);
}

.mh-navbar-nav {
  display: none;
  gap: 4px;
}

@media (min-width: 768px) {
  .mh-navbar-nav {
    display: flex;
  }
}

.mh-navbar-link {
  padding: 4px 12px;
  border-radius: 999px;
  color: var(--mh-muted-foreground);
  text-decoration: none;
  font-size: 14px;
  transition: background 0.15s, color 0.15s;
}

.mh-navbar-link:hover,
.mh-navbar-link.is-active {
  background: color-mix(in srgb, var(--mh-muted) 100%, transparent);
  color: var(--mh-foreground);
}

.mh-lang-switcher {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  border: 1px solid var(--mh-border);
  border-radius: 999px;
  color: var(--mh-foreground);
  text-decoration: none;
  font-size: 13px;
  background: color-mix(in srgb, var(--mh-background) 85%, transparent);
}

.mh-lang-switcher:hover {
  background: var(--mh-muted);
}

/* === 给主体留顶部空间（navbar 是 fixed） === */
.mh-main {
  padding-top: 80px;
  min-height: 60vh;
}
```

#### P3.3.7 创建 `wordpress/themes/mindhikers-main/inc/enqueue.php`

```php
<?php
/**
 * Asset enqueue logic.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', static function (): void {
    $theme_uri = get_stylesheet_directory_uri();
    $theme_dir = get_stylesheet_directory();

    // Parent (Astra)
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');

    // Child (us)
    wp_enqueue_style(
        'mindhikers-main-style',
        get_stylesheet_uri(),
        ['astra-parent-style'],
        (string) filemtime($theme_dir . '/style.css')
    );

    // Animations (BlurFade + theme vars override)
    if (file_exists($theme_dir . '/assets/css/theme-animations.css')) {
        wp_enqueue_style(
            'mindhikers-animations',
            $theme_uri . '/assets/css/theme-animations.css',
            ['mindhikers-main-style'],
            (string) filemtime($theme_dir . '/assets/css/theme-animations.css')
        );
    }

    // Theme toggle (must run before DOMContentLoaded to avoid flash)
    wp_enqueue_script(
        'mindhikers-theme-toggle',
        $theme_uri . '/assets/js/theme-toggle.js',
        [],
        (string) filemtime($theme_dir . '/assets/js/theme-toggle.js'),
        false  // load in <head>, not footer
    );

    // BlurFade
    wp_enqueue_script(
        'mindhikers-blur-fade',
        $theme_uri . '/assets/js/blur-fade.js',
        [],
        (string) filemtime($theme_dir . '/assets/js/blur-fade.js'),
        true  // footer
    );
});
```

#### P3.3.8 修改 `functions.php` 挂接 enqueue.php

**BEFORE**（当前 `wordpress/themes/mindhikers-main/functions.php`）：
```php
<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_style('astra-parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('astra-child-style', get_stylesheet_uri(), ['astra-parent-style']);
});

add_action('after_setup_theme', static function (): void {
    load_theme_textdomain('mindhikers-astra-child', get_stylesheet_directory() . '/languages');
});

// Carbon Fields dependency removed — theme now reads from CMS Core JSON via mindhikers_get_homepage_data()
// require_once __DIR__ . '/lib/carbon-fields.php';

/**
 * English page text translations are now handled via CMS Core JSON payload.
 * ...deprecated block...
 */
```

**AFTER**：
```php
<?php
/**
 * Mindhikers Main Theme — entry file.
 *
 * @package Mindhikers_Main
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// === Load subsystems ===
require_once __DIR__ . '/inc/enqueue.php';
require_once __DIR__ . '/inc/polylang.php';
require_once __DIR__ . '/inc/seo.php';

// === Theme setup ===
add_action('after_setup_theme', static function (): void {
    load_theme_textdomain('mindhikers-main', get_stylesheet_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
    add_theme_support('automatic-feed-links');
    add_theme_support('custom-logo');
});

// === Register menus ===
add_action('after_setup_theme', static function (): void {
    register_nav_menus([
        'primary' => __('Primary Navigation', 'mindhikers-main'),
        'footer' => __('Footer Navigation', 'mindhikers-main'),
    ]);
});
```

### 验证（Phase 3.3 总体）

staging 部署后：
- [ ] `view-source:` 任意页面 → `<head>` 内有 `theme-toggle.js`、`style.css`、`theme-animations.css`
- [ ] 深浅色切换按钮右下角出现，点击后 `<html data-theme>` 属性变化
- [ ] navbar 固定顶部、圆角毛玻璃
- [ ] 首页区块进入视口时淡入（BlurFade 生效）

### 回退

`git revert` Phase 3.3 对应 commit。

### 提交（多个 commit）

- `refs MIN-30 feat(theme): add header/footer/navbar/footer-site template parts`
- `refs MIN-30 feat(theme): add theme-toggle and blur-fade assets`
- `refs MIN-30 refactor(theme): centralize enqueue via inc/enqueue.php`

---

## P3.4 首页模板复核（0.5 天）

### WHY

`front-page.php` 已经在实验分支写过，需要复核是否与新 navbar/footer 兼容，并为 `data-mh-blur-fade` 属性补齐。

### HOW

**BEFORE**（当前 `wordpress/themes/mindhikers-main/front-page.php`）：
```php
<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh';
$locale = $lang === 'en' ? 'en' : 'zh';
$payload = mindhikers_get_homepage_data($locale);
if (!is_array($payload)) { $payload = []; }

get_header();

get_template_part('template-parts/hero', null, ['payload' => $payload]);
get_template_part('template-parts/about', null, ['payload' => $payload]);
get_template_part('template-parts/product', null, ['payload' => $payload]);
get_template_part('template-parts/blog', null, ['payload' => $payload]);
get_template_part('template-parts/contact', null, ['payload' => $payload]);

get_footer();
```

**AFTER**（不变；但需要给每个 template-part 里的大块容器加 `data-mh-blur-fade` 属性）：

保持 `front-page.php` 不变，改 `template-parts/hero.php`（示例）：

**BEFORE**（`template-parts/hero.php` 当前）：
```php
<section class="mh-hero-section" id="hero">
    <div class="mh-container">
        <div class="mh-hero-content">
            <?php if (!empty($hero['eyebrow'])) : ?>
                <span class="mh-hero-eyebrow"><?php echo esc_html($hero['eyebrow']); ?></span>
            ...
```

**AFTER**：
```php
<section class="mh-hero-section" id="hero">
    <div class="mh-container">
        <div class="mh-hero-content">
            <?php if (!empty($hero['eyebrow'])) : ?>
                <span class="mh-hero-eyebrow" data-mh-blur-fade><?php echo esc_html($hero['eyebrow']); ?></span>
            <?php endif; ?>
            <?php if (!empty($hero['title'])) : ?>
                <h1 class="mh-hero-title" data-mh-blur-fade data-mh-delay="80"><?php echo esc_html($hero['title']); ?></h1>
            <?php endif; ?>
            ...
```

为所有 template-parts（hero/about/product/blog/contact）里关键段落加上 `data-mh-blur-fade`，可选补 `data-mh-delay` 制造错落感（参考原 Next.js `BLUR_FADE_DELAY = 0.06` * N）。

### 验证

- staging 首页访问 → 区块依次淡入
- 视觉上与 Next.js 线上对比差别可接受（非像素级）

### 回退

`git revert`。

### 提交

```
refs MIN-30 feat(theme): wire BlurFade into homepage template parts
```

---

## P3.5 博客列表 `home.php`（0.5 天）

### WHY

Next.js `/blog` 路由对应的 WP 模板。WP 默认会把 `is_home()` 的主查询结果渲染到 `home.php`（如果存在），否则退化到 `index.php`。

### HOW

创建 `wordpress/themes/mindhikers-main/home.php`：

```php
<?php
/**
 * Blog Index (home.php)
 *
 * @package Mindhikers_Main
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh';
$locale = $lang === 'en' ? 'en' : 'zh';

get_header();

$total = (int) wp_count_posts('post')->publish;
$title_text = $locale === 'en' ? 'Blog' : '博客';
$posts_label = $locale === 'en' ? 'posts' : '篇文章';
$empty_label = $locale === 'en' ? 'No blog posts yet. Check back soon!' : '暂无文章，请稍后再来。';
$intro = $locale === 'en'
    ? 'My thoughts on software development, life, and more.'
    : '关于软件开发、生活与思考的一些记录。';
?>

<div class="mh-blog-index mh-container">
    <header class="mh-blog-index-header" data-mh-blur-fade>
        <h1 class="mh-blog-index-title">
            <?php echo esc_html($title_text); ?>
            <span class="mh-blog-index-count"><?php echo esc_html($total . ' ' . $posts_label); ?></span>
        </h1>
        <p class="mh-blog-index-intro"><?php echo esc_html($intro); ?></p>
    </header>

    <?php if (have_posts()) : ?>
        <ol class="mh-blog-index-list" data-mh-blur-fade data-mh-delay="80">
            <?php $index = 1; ?>
            <?php while (have_posts()) : the_post(); ?>
                <li class="mh-blog-index-item" data-mh-blur-fade data-mh-delay="<?php echo 120 + $index * 40; ?>">
                    <a href="<?php the_permalink(); ?>" class="mh-blog-index-link">
                        <span class="mh-blog-index-idx"><?php echo str_pad((string) $index, 2, '0', STR_PAD_LEFT); ?>.</span>
                        <div class="mh-blog-index-body">
                            <h2 class="mh-blog-index-post-title"><?php the_title(); ?></h2>
                            <time class="mh-blog-index-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                <?php echo esc_html(get_the_date()); ?>
                            </time>
                        </div>
                    </a>
                </li>
                <?php $index++; ?>
            <?php endwhile; ?>
        </ol>

        <nav class="mh-blog-index-pagination" aria-label="Pagination">
            <?php the_posts_pagination([
                'mid_size' => 2,
                'prev_text' => $locale === 'en' ? 'Previous' : '上一页',
                'next_text' => $locale === 'en' ? 'Next' : '下一页',
            ]); ?>
        </nav>
    <?php else : ?>
        <div class="mh-blog-index-empty" data-mh-blur-fade>
            <p><?php echo esc_html($empty_label); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer();
```

补充 CSS（追加到 `style.css` 或 `theme-animations.css`）：

```css
.mh-blog-index {
  padding: 48px 24px;
}

.mh-blog-index-header {
  margin-bottom: 48px;
}

.mh-blog-index-title {
  font-family: var(--mh-font-display);
  font-size: 1.75rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

.mh-blog-index-count {
  padding: 4px 8px;
  border: 1px solid var(--mh-border);
  border-radius: 6px;
  background: var(--mh-muted);
  font-size: 0.875rem;
  color: var(--mh-muted-foreground);
  font-family: var(--mh-font-sans);
  font-weight: 400;
}

.mh-blog-index-intro {
  margin-top: 8px;
  color: var(--mh-muted-foreground);
  font-size: 0.95rem;
}

.mh-blog-index-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.mh-blog-index-link {
  display: flex;
  gap: 12px;
  text-decoration: none;
  color: inherit;
  transition: transform 0.15s;
}

.mh-blog-index-link:hover {
  transform: translateX(3px);
}

.mh-blog-index-idx {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, monospace;
  font-size: 0.75rem;
  font-weight: 500;
  font-variant-numeric: tabular-nums;
  margin-top: 6px;
}

.mh-blog-index-body {
  flex: 1;
}

.mh-blog-index-post-title {
  font-size: 1.125rem;
  font-weight: 500;
  color: var(--mh-foreground);
  margin: 0 0 6px;
}

.mh-blog-index-date {
  font-size: 0.75rem;
  color: var(--mh-muted-foreground);
}

.mh-blog-index-pagination {
  margin-top: 40px;
}

.mh-blog-index-empty {
  padding: 48px;
  border: 1px solid var(--mh-border);
  border-radius: 12px;
  text-align: center;
  color: var(--mh-muted-foreground);
}
```

### 验证

- [ ] staging `/blog` 访问 → 看到博客列表（若数据库里有文章，Phase 4 迁移前可能为空）
- [ ] 分页按钮正常
- [ ] `?page=2` URL 切换分页

### 回退

`git revert`。

### 提交

```
refs MIN-30 feat(theme): add home.php for blog listing page
```

---

## P3.6 博客详情 `single.php`（1-1.5 天，含 MDX 组件等价样式）

### WHY

Next.js `/blog/[slug]` 路由对应。还要把 MDX 里的 `<MediaContainer>`、`<CodeBlock>`、`<mark>` 等在 WP 端等价样式化。

### HOW

创建 `wordpress/themes/mindhikers-main/single.php`：

```php
<?php
/**
 * Single Blog Post
 *
 * @package Mindhikers_Main
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh';
$locale = $lang === 'en' ? 'en' : 'zh';

get_header();
?>

<article class="mh-single mh-container" data-mh-blur-fade>
    <?php while (have_posts()) : the_post(); ?>
        <header class="mh-single-header">
            <h1 class="mh-single-title"><?php the_title(); ?></h1>
            <div class="mh-single-meta">
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                    <?php echo esc_html(get_the_date()); ?>
                </time>
                <?php if (get_the_modified_date('c') !== get_the_date('c')) : ?>
                    · <span><?php echo esc_html($locale === 'en' ? 'updated' : '更新于'); ?> <?php echo esc_html(get_the_modified_date()); ?></span>
                <?php endif; ?>
            </div>
        </header>

        <?php if (has_post_thumbnail()) : ?>
            <figure class="mh-single-cover" data-mh-blur-fade data-mh-delay="100">
                <?php the_post_thumbnail('large'); ?>
            </figure>
        <?php endif; ?>

        <div class="mh-single-content">
            <?php the_content(); ?>
        </div>

        <nav class="mh-single-nav" aria-label="Post navigation">
            <?php
            $prev = get_previous_post();
            $next = get_next_post();
            ?>
            <div class="mh-single-nav-prev">
                <?php if ($prev) : ?>
                    <a href="<?php echo esc_url(get_permalink($prev)); ?>">
                        ← <?php echo esc_html($prev->post_title); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="mh-single-nav-next">
                <?php if ($next) : ?>
                    <a href="<?php echo esc_url(get_permalink($next)); ?>">
                        <?php echo esc_html($next->post_title); ?> →
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    <?php endwhile; ?>
</article>

<?php get_footer();
```

补充 CSS：

```css
.mh-single {
  padding: 48px 24px;
  max-width: 720px;
}

.mh-single-header {
  margin-bottom: 32px;
}

.mh-single-title {
  font-family: var(--mh-font-display);
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  line-height: 1.2;
  margin: 0 0 12px;
}

.mh-single-meta {
  color: var(--mh-muted-foreground);
  font-size: 0.875rem;
}

.mh-single-cover {
  margin: 0 -24px 32px;
  border-radius: 12px;
  overflow: hidden;
}

.mh-single-cover img {
  width: 100%;
  height: auto;
  display: block;
}

.mh-single-content {
  font-family: var(--mh-font-sans);
  font-size: 1.0625rem;
  line-height: 1.8;
  color: var(--mh-foreground);
}

.mh-single-content h2 {
  font-family: var(--mh-font-display);
  font-size: 1.5rem;
  margin: 2em 0 0.75em;
}

.mh-single-content h3 {
  font-size: 1.25rem;
  margin: 1.5em 0 0.5em;
}

.mh-single-content p {
  margin: 0 0 1.25em;
}

.mh-single-content ul,
.mh-single-content ol {
  margin: 0 0 1.25em;
  padding-left: 1.5em;
}

.mh-single-content li {
  margin-bottom: 0.5em;
}

.mh-single-content mark {
  background: linear-gradient(transparent 55%, color-mix(in srgb, var(--mh-primary) 35%, transparent) 55%);
  padding: 0 2px;
}

.mh-single-content del,
.mh-single-content s {
  color: var(--mh-muted-foreground);
}

.mh-single-content code {
  background: var(--mh-muted);
  padding: 2px 6px;
  border-radius: 4px;
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
  font-size: 0.875em;
}

.mh-single-content pre {
  background: #1a211e;
  color: #e6e8ea;
  padding: 20px;
  border-radius: 10px;
  overflow-x: auto;
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
  font-size: 0.875rem;
  line-height: 1.6;
  margin: 0 0 1.5em;
}

.mh-single-content pre code {
  background: transparent;
  padding: 0;
  color: inherit;
}

.mh-single-content figure {
  margin: 24px 0;
}

.mh-single-content figure img {
  border-radius: 10px;
}

.mh-single-nav {
  margin-top: 64px;
  padding-top: 24px;
  border-top: 1px solid var(--mh-border);
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

.mh-single-nav a {
  color: var(--mh-primary);
  text-decoration: none;
  font-size: 0.875rem;
}

.mh-single-nav a:hover {
  text-decoration: underline;
}
```

### 验证

- [ ] staging 任意博客详情页 → 正常渲染（如果 Phase 4 迁移还没做，需要 WP 后台手动建一篇测试文章）
- [ ] `<mark>`、`<del>`、`<pre><code>` 渲染样式符合预期
- [ ] 上/下篇导航可点

### 回退

`git revert`。

### 提交

```
refs MIN-30 feat(theme): add single.php for blog post detail
```

---

## P3.7 产品详情 `single-product.php`（0.5 天）

### WHY

Next.js `/product/[slug]` 路由。Mindhikers 产品存为 `product` post type（假设已有；若无，mu-plugin 需注册，见 🛑）。

### HOW

🛑 **卡点**：确认 `product` post type 注册位置：
```bash
grep -rn "register_post_type.*product" wordpress/mu-plugins/
grep -rn "register_post_type.*product" wordpress/themes/
```

如果 mu-plugin 没注册但线上 API 返回产品数据，说明产品注册在数据库层（某个插件）。需要老卢确认：
- 产品是独立 CPT（`product`）还是用 `mh_homepage` payload 里的 `product.items`？

根据 `bootstrap.php:754` 的 `normalizeHomepagePayload()` 观察，`product` 在 homepage payload 里只是**首页区块用**的数据，真正的 `/product/[slug]` 详情页可能**另有数据源**。外包团队必须**先核实这一点**：
```bash
grep -rn "getProductBySlug\|listProducts" src/lib/cms/
```

读 `src/lib/cms/products.ts`（已列出存在）找到真实数据模型。

**假设 1**：产品是 WP 独立 CPT `product`（与 WP 常规 post 并列），则：

```php
<?php
/**
 * Single Product
 *
 * @package Mindhikers_Main
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh';
$locale = $lang === 'en' ? 'en' : 'zh';

get_header();
?>

<article class="mh-product-detail mh-container" data-mh-blur-fade>
    <?php while (have_posts()) : the_post();
        $eyebrow = get_post_meta(get_the_ID(), '_mh_product_eyebrow', true);
        $summary = get_post_meta(get_the_ID(), '_mh_product_summary', true);
        $bullets = get_post_meta(get_the_ID(), '_mh_product_bullets', true);
        $stage_label = get_post_meta(get_the_ID(), '_mh_product_stage_label', true);
        $stage_value = get_post_meta(get_the_ID(), '_mh_product_stage_value', true);
    ?>
        <header class="mh-product-detail-header">
            <?php if ($eyebrow) : ?>
                <span class="mh-product-detail-eyebrow"><?php echo esc_html($eyebrow); ?></span>
            <?php endif; ?>
            <h1 class="mh-product-detail-title"><?php the_title(); ?></h1>
            <?php if ($summary) : ?>
                <p class="mh-product-detail-summary"><?php echo esc_html($summary); ?></p>
            <?php endif; ?>
            <?php if ($stage_label && $stage_value) : ?>
                <div class="mh-product-detail-stage">
                    <span class="mh-product-detail-stage-label"><?php echo esc_html($stage_label); ?></span>
                    <span class="mh-product-detail-stage-value"><?php echo esc_html($stage_value); ?></span>
                </div>
            <?php endif; ?>
        </header>

        <?php if (is_array($bullets) && count($bullets) > 0) : ?>
            <ul class="mh-product-detail-bullets" data-mh-blur-fade data-mh-delay="100">
                <?php foreach ($bullets as $bullet) : ?>
                    <li><?php echo esc_html($bullet); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="mh-product-detail-content">
            <?php the_content(); ?>
        </div>

        <a href="<?php echo esc_url(home_url('/')); ?>" class="mh-product-detail-home">
            ← <?php echo esc_html($locale === 'en' ? 'Back to home' : '返回首页'); ?>
        </a>
    <?php endwhile; ?>
</article>

<?php get_footer();
```

**假设 2**：产品数据在 homepage payload 的 `product.items[]` 或 `productDetail` 中，则不需要独立 CPT，只要在 `page-product.php` 或自定义 rewrite rule 中消费。

🛑 **卡点**：外包团队读完 `src/lib/cms/products.ts` 后决定走哪个方案，并让老卢拍板。

### 验证

- [ ] staging 访问某个产品 URL → 正常渲染
- [ ] 返回首页链接正常

### 回退

`git revert`。

### 提交

```
refs MIN-30 feat(theme): add single-product.php for product detail
```

---

## P3.8 专题页 `page-golden-crucible.php`（0.5 天）

### WHY

Next.js `/golden-crucible` 是一个独立专题页，不在博客或产品流中。

### HOW

🛑 **卡点**：查看 `src/app/golden-crucible/page.tsx` 全文，提取全部内容（文字、图片、结构）。因为这是专题页，内容相对静态。

WP 端做法：
1. WP Admin → 页面 → 新建页面，标题 "Golden Crucible"，slug `golden-crucible`，发布
2. 主题创建 `page-golden-crucible.php` 按 slug 自动匹配此页面
3. 英文版通过 Polylang 创建 `/en/golden-crucible` 的翻译页面

模板骨架 `wordpress/themes/mindhikers-main/page-golden-crucible.php`：

```php
<?php
/**
 * Template Name: Golden Crucible
 * Template Post Type: page
 *
 * @package Mindhikers_Main
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'zh';
$locale = $lang === 'en' ? 'en' : 'zh';

get_header();
?>

<article class="mh-golden-crucible mh-container" data-mh-blur-fade>
    <?php while (have_posts()) : the_post(); ?>
        <header class="mh-page-header">
            <h1 class="mh-page-title"><?php the_title(); ?></h1>
        </header>
        <div class="mh-page-content">
            <?php the_content(); ?>
        </div>
    <?php endwhile; ?>
</article>

<?php get_footer();
```

然后在 WP Admin 页面编辑器里用 Gutenberg 块重建 `src/app/golden-crucible/page.tsx` 的视觉结构（标题、段落、图片、CTA）。

### 验证

- [ ] staging `/golden-crucible` 渲染正常
- [ ] `/en/golden-crucible` 渲染英文版

### 回退

`git revert` + WP Admin 删除页面。

### 提交

```
refs MIN-30 feat(theme): add page-golden-crucible.php template
```

---

## P3.9 Polylang 双语接入（1 天）

### WHY

决策锁定 `/` = zh、`/en/` = en，与 Next.js 现状保持一致。

### HOW

#### P3.9.1 创建 `wordpress/themes/mindhikers-main/inc/polylang.php`

```php
<?php
/**
 * Polylang integration.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register mh_homepage post type for Polylang translations.
 */
add_filter('pll_get_post_types', static function (array $post_types): array {
    $post_types['mh_homepage'] = 'mh_homepage';
    if (post_type_exists('product')) {
        $post_types['product'] = 'product';
    }
    return $post_types;
});

/**
 * Helper: get current locale (zh|en) from Polylang or fallback.
 */
if (!function_exists('mindhikers_current_locale')) {
    function mindhikers_current_locale(): string
    {
        if (function_exists('pll_current_language')) {
            $lang = (string) pll_current_language('slug');
            return $lang === 'en' ? 'en' : 'zh';
        }
        return 'zh';
    }
}

/**
 * Helper: get URL of the counterpart language version of current page.
 */
if (!function_exists('mindhikers_other_language_url')) {
    function mindhikers_other_language_url(): string
    {
        if (!function_exists('pll_get_post') || !function_exists('pll_current_language')) {
            return '';
        }
        $current_lang = pll_current_language('slug');
        $other_lang = $current_lang === 'en' ? 'zh' : 'en';

        // Try to find translated post
        if (is_singular()) {
            $translated_id = pll_get_post(get_the_ID(), $other_lang);
            if ($translated_id) {
                return get_permalink($translated_id);
            }
        }

        // Fallback to home of other language
        if (function_exists('pll_home_url')) {
            return pll_home_url($other_lang);
        }
        return $other_lang === 'en' ? home_url('/en/') : home_url('/');
    }
}
```

#### P3.9.2 在 WP Admin 配置 Polylang

🛑 **卡点**：需要老卢或授权外包登录 staging WP Admin 操作。

1. WP Admin → **语言** → **语言**
2. 确认已有 `Chinese (Simplified) zh_CN`（slug: `zh`）和 `English en_US`（slug: `en`）
3. **Polylang 设置**：
   - URL modifications：`/en/` 前缀 for English
   - 默认语言：`Chinese (Simplified)`
   - URL 方式：目录（非子域）
4. **字符串翻译**：把主题里需要翻译的字符串（如 "Back to home"）加入翻译列表

#### P3.9.3 数据层：`mh_homepage` 注册 Polylang 关联

`bootstrap.php` 里 `mh_homepage` 已是标准 CPT，通过 `pll_get_post_types` filter 即可让 Polylang 识别。但 `locale` meta 的逻辑与 Polylang 的语言机制需要协调：

方案 1：继续用 `locale` meta（`zh`/`en`），Polylang 只负责 URL 路由。
方案 2：完全用 Polylang 的语言关联代替 `locale` meta。

**推荐方案 1**（现状延续，不改数据层），Polylang 负责前端的 URL 前缀和翻译关联，`getHomepageDataForTheme` 仍按 `locale` meta 查询。

需要：每个 `mh_homepage` post 除了设 `mindhikers_locale` meta，还要通过 Polylang 后台"语言"下拉设置为对应 zh/en。

### 验证

- [ ] staging `/` 显示中文首页
- [ ] staging `/en/` 显示英文首页
- [ ] navbar 语言切换按钮能在 zh/en 间互相跳转
- [ ] 博客、产品页都有双语 URL

### 回退

`git revert` + WP Admin Polylang 禁用某语言。

### 提交

```
refs MIN-30 feat(theme): integrate Polylang bilingual routing
```

---

## P3.10 SEO Meta 迁移（0.25 天）

### WHY

Next.js 原本用 `generateMetadata` 输出 title / description / OG，WP 端要用主题 + SEO 插件。

### HOW

创建 `wordpress/themes/mindhikers-main/inc/seo.php`：

```php
<?php
/**
 * SEO meta integration.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Inject Open Graph meta on frontend.
 */
add_action('wp_head', static function (): void {
    if (!is_front_page() && !is_singular()) return;

    $locale = function_exists('mindhikers_current_locale') ? mindhikers_current_locale() : 'zh';
    $site_settings = get_option('mindhikers_site_settings_payload', '');
    $settings = is_string($site_settings) ? json_decode($site_settings, true) : null;
    if (!is_array($settings)) $settings = [];

    $og_image = (string) ($settings['defaultOgImage'] ?? '');
    $og_locale = $locale === 'en' ? 'en_US' : 'zh_CN';

    if (is_front_page()) {
        $payload = function_exists('mindhikers_get_homepage_data') ? mindhikers_get_homepage_data($locale) : [];
        $title = $payload['metadata']['title'] ?? ($settings['defaultSeoTitle'] ?? '心行者 Mindhikers');
        $description = $payload['metadata']['description'] ?? ($settings['defaultSeoDescription'] ?? '');
    } else {
        $title = (string) get_the_title();
        $description = (string) get_the_excerpt();
    }

    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:locale" content="' . esc_attr($og_locale) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular('post') ? 'article' : 'website') . '">' . "\n";
    if ($og_image) {
        echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
    }
    echo '<meta property="og:url" content="' . esc_url(home_url(add_query_arg(null, null))) . '">' . "\n";

    // Twitter Card
    echo '<meta name="twitter:card" content="' . ($og_image ? 'summary_large_image' : 'summary') . '">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
}, 5);

/**
 * Customize document title for homepage.
 */
add_filter('pre_get_document_title', static function (string $title): string {
    if (!is_front_page()) return $title;

    $locale = function_exists('mindhikers_current_locale') ? mindhikers_current_locale() : 'zh';
    $payload = function_exists('mindhikers_get_homepage_data') ? mindhikers_get_homepage_data($locale) : [];
    $override = $payload['metadata']['title'] ?? '';

    return $override !== '' ? (string) $override : $title;
});
```

### 验证

- [ ] `view-source:https://wordpress-l1ta-staging.up.railway.app/` → `<head>` 有 `og:title` `og:description` `og:locale` 等
- [ ] 博客详情页 OG 标签反映文章标题/摘要

### 回退

`git revert`。

### 提交

```
refs MIN-30 feat(theme): add SEO meta (OG/Twitter) via inc/seo.php
```

---

# Phase 4 · 博客数据迁移（1 工作日）

**目标**：仓库 `content/*.mdx` 的 7 篇文章导入 WP 数据库。

⚠️ **重要发现**：`content/*.mdx` 文件头部已标注 `ARCHIVED: 2026-04-19 — Migrated to WordPress Posts`。说明在本次改造前，博客已经迁移过一轮。外包团队必须先确认：

🛑 **卡点**：
1. production WP 数据库里是否已经有这 7 篇博客？
   ```bash
   curl -s "https://homepage-manage.mindhikers.com/wp-json/wp/v2/posts?per_page=20" | jq '. | length'
   ```
2. 如果已经有，Phase 4 大部分工作跳过，只需要：
   - 验证每篇文章内容、封面图、代码块都正确
   - 补上可能缺失的 meta（tags、summary、MDX 特殊组件）
3. 如果数据库里没有或不完整，按 P4.1 执行完整迁移。

## P4.1 迁移脚本 `scripts/migrate-mdx-to-wp.ts`

### WHY

自动化把 MDX → WP Gutenberg block，减少人工粘贴错误。

### HOW

创建 `scripts/migrate-mdx-to-wp.ts`：

```typescript
/**
 * One-time MDX -> WP post migration script.
 *
 * Usage:
 *   WP_URL=https://wordpress-l1ta-staging.up.railway.app \
 *   WP_USER=mindhikers_admin \
 *   WP_APP_PASSWORD=xxxx \
 *   npx tsx scripts/migrate-mdx-to-wp.ts --dry-run
 *
 * Real run: omit --dry-run.
 */

import fs from 'node:fs';
import path from 'node:path';
import matter from 'gray-matter';
import { marked } from 'marked';

const CONTENT_DIR = path.resolve(process.cwd(), 'content');
const DRY_RUN = process.argv.includes('--dry-run');

const WP_URL = process.env.WP_URL || '';
const WP_USER = process.env.WP_USER || '';
const WP_APP_PASSWORD = process.env.WP_APP_PASSWORD || '';

if (!DRY_RUN && (!WP_URL || !WP_USER || !WP_APP_PASSWORD)) {
  console.error('Missing WP_URL / WP_USER / WP_APP_PASSWORD env vars.');
  process.exit(1);
}

interface MdxFrontmatter {
  title: string;
  publishedAt: string;
  updatedAt?: string;
  summary?: string;
  image?: string;
  author?: string;
}

function transformMdxBody(raw: string): string {
  // 1) <MediaContainer src="..." alt="..." /> -> <figure><img src alt></figure>
  let body = raw.replace(
    /<MediaContainer\s+src="([^"]+)"\s+alt="([^"]*)"\s*\/>/g,
    (_, src, alt) =>
      `<figure class="mh-media"><img src="${src}" alt="${alt}" loading="lazy"></figure>`,
  );

  // 2) <CodeBlock language="xxx"> ... </CodeBlock> -> fenced code
  body = body.replace(
    /<CodeBlock\s+language="([^"]+)">([\s\S]*?)<\/CodeBlock>/g,
    (_, lang, code) => '\n```' + lang + '\n' + code.trim() + '\n```\n',
  );

  // 3) Convert markdown to HTML (Gutenberg accepts raw HTML in "wp-block-html")
  return marked.parse(body, { gfm: true, breaks: false }) as string;
}

async function postToWp(post: {
  title: string;
  slug: string;
  content: string;
  date: string;
  excerpt: string;
  featured_media_url?: string;
}): Promise<void> {
  if (DRY_RUN) {
    console.log(`[DRY] would POST "${post.title}" (slug=${post.slug})`);
    return;
  }

  const auth = 'Basic ' + Buffer.from(`${WP_USER}:${WP_APP_PASSWORD}`).toString('base64');

  // Upload featured media first if present
  let featuredMediaId: number | undefined;
  if (post.featured_media_url) {
    // TODO: download image, upload via WP REST /media endpoint, get ID
    console.warn(`[TODO] upload featured_media for ${post.slug}: ${post.featured_media_url}`);
  }

  const res = await fetch(`${WP_URL}/wp-json/wp/v2/posts`, {
    method: 'POST',
    headers: {
      'Authorization': auth,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      title: post.title,
      slug: post.slug,
      content: post.content,
      date: post.date,
      excerpt: post.excerpt,
      status: 'publish',
      featured_media: featuredMediaId,
    }),
  });

  if (!res.ok) {
    const text = await res.text();
    throw new Error(`POST failed ${res.status}: ${text}`);
  }

  const created = await res.json();
  console.log(`Created post #${created.id} (${created.slug})`);
}

async function main() {
  const files = fs.readdirSync(CONTENT_DIR).filter(f => f.endsWith('.mdx'));
  console.log(`Found ${files.length} MDX files.`);

  for (const file of files) {
    const fullPath = path.join(CONTENT_DIR, file);
    const raw = fs.readFileSync(fullPath, 'utf8');
    const { data, content } = matter(raw);
    const fm = data as MdxFrontmatter;
    const slug = file.replace(/\.mdx$/, '');

    const html = transformMdxBody(content);

    await postToWp({
      title: fm.title,
      slug,
      content: html,
      date: new Date(fm.publishedAt).toISOString(),
      excerpt: fm.summary || '',
      featured_media_url: fm.image,
    });
  }
}

main().catch(err => {
  console.error(err);
  process.exit(1);
});
```

package.json 需要依赖：
```bash
npm install --save-dev gray-matter marked tsx
```

### 使用流程

🛑 **卡点**：需要老卢在 WP Admin → Users → mindhikers_admin → Application Passwords 创建一个 app password（不要用主密码）。

```bash
# 1. Dry run（看日志，不写数据库）
WP_URL=https://wordpress-l1ta-staging.up.railway.app \
WP_USER=mindhikers_admin \
WP_APP_PASSWORD=<app_password> \
npx tsx scripts/migrate-mdx-to-wp.ts --dry-run

# 2. 真跑（先在 staging 验证）
WP_URL=https://wordpress-l1ta-staging.up.railway.app \
WP_USER=mindhikers_admin \
WP_APP_PASSWORD=<app_password> \
npx tsx scripts/migrate-mdx-to-wp.ts

# 3. staging 验证通过后，production 同样跑一次（如果 production 没有这些文章）
```

### 验证（每篇逐一人工核对）

- [ ] `/blog` 列表有 7 篇
- [ ] 每篇详情页：标题、日期、摘要正确
- [ ] `<MediaContainer>` 转成的 `<figure>` 图片正常加载
- [ ] 代码块（```ts ... ```）渲染成 `<pre><code>`
- [ ] `<mark>` / `<del>` 等特殊标签有样式

### 回退

- 数据库层面：登录 WP Admin → 文章 → 批量删除迁移进去的文章
- 代码层面：`git revert` 脚本 commit

### 提交

```
refs MIN-30 feat(scripts): add one-time MDX -> WP migration script
```

---

# Phase 5 · Staging 全站验证（1 工作日）

**目标**：staging 等价于未来的 production，全量走查。

## P5.1 验证清单（全部打勾才算通过）

### 功能验证

- [ ] 首页中文 `/` 正常渲染，5 区块完整
- [ ] 首页英文 `/en/` 正常渲染
- [ ] 博客列表 `/blog` 显示 7 篇（按发布时间倒序）
- [ ] 博客详情 `/blog/<slug>` 每篇都渲染正确（抽查 3 篇）
- [ ] 产品详情 `/product/<slug>` 渲染正确（抽查 2 个产品）
- [ ] Golden Crucible `/golden-crucible` 和 `/en/golden-crucible` 都正常
- [ ] Navbar 固定顶部、毛玻璃效果
- [ ] 语言切换按钮能在 zh/en 互跳
- [ ] 深浅色切换按钮工作，localStorage 记忆
- [ ] BlurFade 进入动画生效
- [ ] 移动端（< 768px）响应式布局正常

### 数据流验证

- [ ] WP Admin → Homepage post → 改 Hero 标题 → 保存 → 5 秒内 staging 前台更新
- [ ] WP Admin → Snippets 为空（P2 已删）
- [ ] WP Admin → 插件 → M1 REST API 不存在（P1 已删）
- [ ] `/wp-json/mindhikers/v1/homepage/zh` 返回 200 + 完整 JSON
- [ ] `/wp-json/mindhikers/v1/site-settings` 返回 200 + 完整 JSON

### SEO 验证

- [ ] `view-source:` 首页 → 含 `<meta property="og:title">` 等
- [ ] robots.txt 可访问
- [ ] sitemap.xml 可访问（由 Astra 或 SureRank 生成）
- [ ] 博客详情页 OG 图使用文章特色图

### 性能基线（可选）

- [ ] Lighthouse 首页 Performance > 70（如低于 60 需优化）
- [ ] Largest Contentful Paint < 3s
- [ ] 和 Next.js 线上基线相比，可接受范围 -10% 以内

### 回归

- [ ] 访问一个不存在的 slug `/blog/nonexistent` → 404 模板
- [ ] 浏览器刷新几次，无白屏、无 500

## P5.2 视觉对比

把 staging 首页与 www.mindhikers.com（Next.js）各截一张全屏图，并排对比：
- 字体、颜色、间距大致一致
- 动画效果接近（不追求 1:1）

## P5.3 🛑 老卢验收

外包团队整理一份报告：
- staging URL
- P5.1 全部验证截图
- P5.2 视觉对比截图
- 已知差异清单（如动画速度略不同）

老卢验收确认："可以进入 Phase 6 production 切换"。

---

# Phase 6 · Production 切换（0.5 工作日）

**目标**：production 部署新 Dockerfile + 主题 + 博客 + DNS 切换。

## P6.1 production 部署新主题

### HOW

🛑 **卡点**：老卢决定合并时机：
- 路径 A：`experiment/wp-traditional-mode` 合并到 `staging` → staging 自动部署（已是 P1 Dockerfile）→ staging 绿灯 → 合并到 `main` → production 自动部署
- 路径 B：`experiment/wp-traditional-mode` 直接合并到 `main` → production 自动部署

**推荐路径 A**。

合并命令（待老卢授权执行）：
```bash
git checkout staging
git merge --no-ff experiment/wp-traditional-mode
git push origin staging
# 观察 staging 自动部署...

# staging 绿灯后
git checkout main
git merge --no-ff staging
git push origin main
# production 自动部署
```

🛑 **push 前必须再次向老卢请示确认**。

### 验证

production 重新部署后，按 Phase 5 清单再扫一遍 production 版本（尚未切 DNS，此时 production WP 前台仍然只能通过 `homepage-manage.mindhikers.com` 访问）。

---

## P6.2 production 执行博客迁移

### HOW

如果 production 数据库里已经有博客（前文发现 MDX 已归档），此步骤可能跳过。否则：

```bash
WP_URL=https://homepage-manage.mindhikers.com \
WP_USER=mindhikers_admin \
WP_APP_PASSWORD=<prod_app_password> \
npx tsx scripts/migrate-mdx-to-wp.ts
```

🛑 **卡点**：这一步直接影响生产数据，老卢必须在场。

### 验证

- production `/wp-json/wp/v2/posts?per_page=20` 返回 7+ 篇
- WP Admin 文章列表完整

---

## P6.3 production 抽查

- [ ] 访问 `https://homepage-manage.mindhikers.com/` → 新主题首页（此时 DNS 还没切）
- [ ] 博客列表、详情页渲染
- [ ] REST API 正常

---

## P6.4 DNS 切换（LLM 指导老卢执行）

### WHY

`www.mindhikers.com` 原本指向 Railway 的 Next.js 服务，要改指向 WordPress 服务。

### 前置：获取 WordPress 服务公共域名

1. Railway Dashboard → production → `WordPress-L1ta` → **Settings** → **Networking** → **Public Networking**
2. 记录 Railway 分配的公共域名，形如：`wordpress-l1ta-production.up.railway.app`

### 前置：给 WordPress 服务添加自定义域名

🛑 **卡点**：老卢执行。

1. Railway Dashboard → `WordPress-L1ta`（production）→ Settings → Networking → **+ Custom Domain**
2. 输入 `www.mindhikers.com`，点 **Add Domain**
3. Railway 会显示：
   - 一个 CNAME 目标值（形如 `xxxx.up.railway.app`）
   - 或一个 A 记录 IP
4. **不要点"检查 DNS"**，下一步在 Cloudflare 改完再回来验证

### 步骤 1：降低 Cloudflare TTL（提前 24 小时执行）

🛑 **卡点**：强烈建议**提前 24 小时**执行此步骤，减少切换期间的 DNS 缓存延迟。

1. 登录 Cloudflare：`https://dash.cloudflare.com/`
2. 选中域 `mindhikers.com`
3. 左侧菜单 → **DNS** → **Records**
4. 找到 `www` 这条 CNAME 或 A 记录
5. 点击右侧 **Edit**
6. 把 **TTL** 从 `Auto` 改为 `1 Minute`（或最低可选值）
7. **Proxy status**（橙云）：记录当前状态（默认 Proxied 橙云），切换时保持不变
8. 点 **Save**

等 24 小时让全网 DNS 缓存过期。

### 步骤 2：切换 CNAME（正式切换时刻）

🛑 **卡点**：正式切换由老卢执行，LLM 提供逐步指令。

1. Cloudflare → DNS → Records → `www` 条目 → **Edit**
2. **Type**：保持 `CNAME`（如果原来是 A，改为 CNAME）
3. **Target**：改为 Railway WordPress 服务的公共域名（前置步骤记录的 `wordpress-l1ta-production.up.railway.app`）
4. **Proxy status**：橙云（保持 Proxied）
5. **TTL**：保持 `1 Minute`
6. 点 **Save**

### 步骤 3：回 Railway 验证域名绑定

1. Railway → WordPress-L1ta → Settings → Networking → Custom Domains → `www.mindhikers.com` → **Check DNS**
2. 应显示 "✓ DNS is configured correctly"
3. Railway 自动申请 Let's Encrypt 证书（约 1-3 分钟）

### 步骤 4：验证切换生效

```bash
# 查 DNS 解析
dig www.mindhikers.com CNAME
# 应返回指向 Railway 的 CNAME

# 查 HTTP 响应
curl -I https://www.mindhikers.com/
# 应返回 WordPress 的响应头（含 X-Powered-By: PHP）
# 如果还是 Next.js 的响应（含 x-vercel-... 或类似），DNS 还没完全生效，等几分钟
```

### 步骤 5：多地点验证（可选）

使用 `https://www.whatsmydns.net/` 查 `www.mindhikers.com` 的 CNAME 在全球多个节点都已切换。

### 回退（72 小时内任何时刻）

🛑 **关键回退指令**：

1. Cloudflare DNS → `www` → Edit → Target 改回原 Next.js 地址（Railway Next.js 服务的公共域名或 IP）
2. Save
3. 5 分钟后验证 `curl -I https://www.mindhikers.com/` 回到 Next.js

**前置条件**：Next.js 服务在 Railway 保持运行 72 小时（参见 P6.5）。

---

## P6.5 保留 Next.js 服务 72 小时待命

### HOW

**不要停 Next.js 服务**。让它继续运行 72 小时，作为紧急回退托底。

观察指标：
- WP 前台访问正常
- 没有用户报错
- API 和博客都稳

72 小时期满且一切正常，进入 Phase 7 收尾。

---

# Phase 7 · 收尾（0.5 工作日）

## P7.1 Next.js 服务停机

🛑 **卡点**：老卢确认 72 小时稳定后执行。

1. Railway Dashboard → `Mindhikers-Homepage` 服务 → Settings → **Danger Zone** → **Pause Service** (不要直接 Delete，先暂停)
2. 观察 7 天：无人反馈任何问题
3. 7 天后再 Delete

## P7.2 Next.js 代码归档

### HOW

```bash
cd /Users/luzhoua/Mindhikers/Mindhikers-Homepage
mkdir -p legacy/nextjs-frontend
git mv src legacy/nextjs-frontend/src
git mv content legacy/nextjs-frontend/content
git mv next.config.ts legacy/nextjs-frontend/next.config.ts
# 可选：package.json 根目录保留，但清理 Next.js 相关依赖；或整个 package.json 也挪过去
```

### 验证

- `ls legacy/nextjs-frontend/src` 可见
- 根目录不再有 `src/`

### 提交

```
refs MIN-30 refactor: archive Next.js frontend to legacy/nextjs-frontend/

- git mv src legacy/nextjs-frontend/src
- git mv content legacy/nextjs-frontend/content
- Next.js service permanently retired on Railway production

Reason: WP single-stack migration completed. Next.js retained as git history
for reference but no longer deployed.
```

## P7.3 清理仓库 M1 REST ZIP

```bash
rm /Users/luzhoua/Mindhikers/Mindhikers-Homepage/m1-rest-v*.zip
# 加入 .gitignore
echo 'm1-rest-v*.zip' >> /Users/luzhoua/Mindhikers/Mindhikers-Homepage/.gitignore
```

### 提交

```
refs MIN-30 chore: remove legacy m1-rest zip files from repo root
```

## P7.4 更新治理文档

文档更新清单：

- [ ] `docs/04_progress/rules.md` — 删除 "push 即部署 WP" 错误描述；新增 "多模板切换通过 WP 外观面板"；删除 mhs02 相关记录
- [ ] `docs/dev_logs/HANDOFF.md` — 覆盖写，记录迁移完成时间、分支名、回退期已过
- [ ] `docs/04_progress/dev_progress.md` — 添加里程碑 "WP 单栈改造完成"
- [ ] `AGENTS.md` / `CLAUDE.md` — 项目架构描述从 "双栈" 改为 "WP 单栈"
- [ ] `docs/operations-guide-headless.md` → 重命名为 `docs/operations-guide.md`，内容全面更新

### 提交

```
refs MIN-30 docs: update governance docs for WP single-stack architecture
```

## P7.5 Linear 关闭

🛑 **卡点**：老卢操作。

- 关闭主 issue `MIN-30`（fixes）
- 关闭相关子 issue
- 归档本 playbook 到 `docs/plans/` 末尾加 `status: completed`

---

# 附录 A · 全量验证清单（Phase 结束前打勾）

## Phase 0
- [ ] P0.1 production wp-content.tgz 备份落盘
- [ ] P0.2 production mariadb-production.sql 备份落盘
- [ ] P0.3 staging 两项备份落盘
- [ ] P0.4 production 插件清单 md 落盘
- [ ] P0.5 三个 snippets PHP 落盘
- [ ] 🛑 老卢确认 Phase 0 完成

## Phase 1
- [ ] P1.1 Carbon Fields 和 Polylang 打包入仓库
- [ ] P1.2 Dockerfile 改写 + sync-bundle.sh 落地
- [ ] P1.3 m1-rest 格式化函数迁移到 mu-plugin
- [ ] P1.4 staging 首次部署验证通过
- [ ] P1.5 staging 卸载 M1 REST API
- [ ] 🛑 老卢确认 Phase 1 完成

## Phase 2
- [ ] P2.1 production 部署新 Dockerfile
- [ ] P2.2 mhs02 停用 + 等价性对比通过
- [ ] P2.3 production 三个 snippet 删除
- [ ] P2.4 Code Snippets 插件卸载（可选）
- [ ] 🛑 老卢确认 Phase 2 完成

## Phase 3
- [ ] P3.1 astra-child → mindhikers-main 重命名生效
- [ ] P3.3 header/footer/navbar + JS + CSS 就位
- [ ] P3.4 首页 BlurFade 生效
- [ ] P3.5 博客列表 home.php 就绪
- [ ] P3.6 博客详情 single.php 就绪
- [ ] P3.7 产品详情 single-product.php 就绪
- [ ] P3.8 专题页 page-golden-crucible.php 就绪
- [ ] P3.9 Polylang 双语路由就绪
- [ ] P3.10 SEO meta 注入就绪

## Phase 4
- [ ] P4.1 迁移脚本落地
- [ ] staging 迁移执行成功
- [ ] 7 篇博客人工审核通过

## Phase 5
- [ ] 功能验证 10 项全打勾
- [ ] 数据流验证 5 项全打勾
- [ ] SEO 验证 4 项全打勾
- [ ] 🛑 老卢验收通过

## Phase 6
- [ ] P6.1 production 合并 + 部署成功
- [ ] P6.2 production 博客迁移（或确认已存在）
- [ ] P6.3 production 抽查通过
- [ ] P6.4 DNS 切换 + 域名证书就绪
- [ ] P6.5 72 小时无事故

## Phase 7
- [ ] P7.1 Next.js 服务暂停
- [ ] P7.2 代码归档到 legacy
- [ ] P7.3 ZIP 清理 + .gitignore
- [ ] P7.4 治理文档更新
- [ ] P7.5 Linear 关闭

---

# 附录 B · 回滚预案（按 Phase 从后往前回滚）

| 出问题的 Phase | 回滚动作 | 回滚到 |
|---|---|---|
| P7 | `git revert` + 把 `legacy/` 挪回根 | Phase 6 完成状态 |
| P6 DNS 切换 | Cloudflare DNS 改回 Next.js CNAME（5 分钟） | Next.js 重新接管 |
| P6 production 部署 | Railway rollback 到上一个 deployment | Phase 5 完成状态 |
| P4 博客迁移 | WP Admin 批量删除迁移的文章 | Phase 3 完成状态 |
| P3 主题补齐 | `git revert` 对应 commit | Phase 2 完成状态 |
| P2 Snippets 退役 | P0.5 归档的 PHP 重新创建 snippet | Phase 1 完成状态 |
| P1 Dockerfile | Railway rollback + Volume 还原 P0.1 备份 | Phase 0 完成状态 |
| 灾难级 | P0.1 + P0.2 全量还原 production Volume 和 MariaDB | 改造前状态 |

---

# 附录 C · 环境变量总账

| 变量 | 服务 | Phase 1 后 | Phase 7 后 |
|---|---|---|---|
| `REVALIDATE_SECRET` | Next.js production | 保留 | 删除 |
| `WORDPRESS_API_URL` | Next.js production | 保留 | 删除 |
| `BLOG_SOURCE` | Next.js production | 保留 | 删除 |
| `MINDHIKERS_REVALIDATE_ENDPOINT` | WP production | 保留（过渡） | 删除 |
| `MINDHIKERS_REVALIDATE_SECRET` | WP production | 保留（过渡） | 删除 |

---

# 附录 D · 老卢决策备忘（本 playbook 锁定值）

- **Linear 主 issue**：`MIN-30`（父级/归属：`MIN-7 网站开发`）
- **主题命名**：`mindhikers-main`（不接受其他名）
- **插件策略**：Carbon Fields + Polylang 打进 image；其他 6 个留 Volume；M1 REST API 卸载
- **Next.js 代码**：改造完 `git mv src legacy/nextjs-frontend/src`（归档，不 `git rm`）
- **备份**：老卢手工做，外包不代劳
- **动画精度**：接近即可，不追求像素级
- **DNS 切换**：老卢手动，LLM 给逐步指令

---

# 附录 E · 提交顺序参考

以下是建议的 commit 顺序（每个 commit message 以 `refs MIN-30` 开头）：

1. `docs: add execution playbook 2026-04-23-003 (this file)`
2. `feat(wp): bundle carbon-fields 3.6.9 and polylang 3.8.2 into repo`
3. `feat(ops): Dockerfile COPY wordpress/ code into WP image`
4. `feat(ops): image-to-volume bundle sync on container start`
5. `refactor(cms-core): inline m1-rest formatters into mu-plugin`
6. `refactor(theme): rename astra-child -> mindhikers-main`
7. `feat(theme): add header/footer/navbar/footer-site template parts`
8. `feat(theme): add theme-toggle and blur-fade assets`
9. `refactor(theme): centralize enqueue via inc/enqueue.php`
10. `feat(theme): wire BlurFade into homepage template parts`
11. `feat(theme): add home.php for blog listing page`
12. `feat(theme): add single.php for blog post detail`
13. `feat(theme): add single-product.php for product detail`
14. `feat(theme): add page-golden-crucible.php template`
15. `feat(theme): integrate Polylang bilingual routing`
16. `feat(theme): add SEO meta (OG/Twitter) via inc/seo.php`
17. `feat(scripts): add one-time MDX -> WP migration script`
18. (Phase 6-7 合并和归档相关 commit)

每次 push 前**显式请示老卢**。

---

# 结束语

本 playbook 面向零上下文外包团队 LLM，逐步执行可完成迁移。遇到任何 🛑 必须停下等老卢回执。遇到技术判断不明，优先选择"对数据最安全"的路径，不要贪快。

任何一步失败，回滚优先于前进。

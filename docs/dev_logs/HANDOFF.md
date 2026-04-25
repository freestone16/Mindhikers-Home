🕐 Last updated: 2026-04-25 16:00 CST
🌿 Branch: `experiment/wp-traditional-mode`
📌 Latest commit: `20327c2` refs MIN-30 fix(wp): correct m1-rest require path from mu-plugins to plugins dir
🚀 Push status: ✅ 已 push，staging 网站已恢复

---

## 当前状态：staging WordPress 已恢复访问

一句话：经过三轮修复，staging WordPress 已恢复。修复内容：(1) 将 Astra 父主题加入仓库 (2) 将 `m1-seed.php` 移出 mu-plugins（它是 CLI 脚本，不应自动加载）(3) 修复 `mindhikers-m1-core.php` 中 `m1-rest` 的 require 路径。当前网站可访问、wp-login 200、REST API 200，但返回空数据（尚未执行 seed）。

---

## 修复记录

| 问题 | 原因 | 修复 |
|---|---|---|
| 500 白屏 | `rm -rf` 删除了 Astra 父主题 | 从 wordpress.org 下载 Astra 加入 `wordpress/themes/astra` |
| 500 致命错误 | `m1-seed.php` 作为 mu-plugin 在 WP 启动时 boot Carbon Fields，但 autoload 尚未加载 | 将 `m1-seed.php` 移出 mu-plugins 到 `ops/wordpress/`，sync-bundle.sh 清理残留 |
| 500 致命错误 | `mindhikers-m1-core.php` 中 `__DIR__ . '/m1-rest/'` 指向 mu-plugins，但 `m1-rest` 在 plugins 目录 | 改为 `WP_PLUGIN_DIR . '/m1-rest/'` |

一句话：WP 单栈迁移 Phase 1 Dockerfile 部署已成功，但在清理 Volume 旧文件时引入了 `rm -rf`，删除了 Astra 父主题导致 WordPress 致命错误/白屏。已回退 `sync-bundle.sh` 移除 `rm-rf`，当前等待 `d8fc902` 部署恢复网站访问。

给新窗口的第一句话：当前 staging WordPress 可能仍白屏，请等待 Railway 部署 `d8fc902` 完成（约 1-2 分钟），然后检查网站是否恢复，再按下面的排错步骤继续。

---

## 本窗口完成内容

### 已提交 commit（已 push）

1. `5546d7c refs MIN-30 docs: add WP single-stack migration playbook`
2. `8d30f42 refs MIN-30 feat(wp): bundle carbon-fields and polylang`
3. `f9f0349 refs MIN-30 feat(ops): copy and sync WP bundle into image`
4. `09930cc refs MIN-30 refactor(cms-core): preserve homepage quick links schema`
5. `7ddd43b refs MIN-30 docs: save Phase 1 handoff`
6. `e28460d refs MIN-30 docs: update handoff after push`
7. `94c9127 refs MIN-30 fix(ops): use DOCKERFILE builder with correct dockerfilePath`
8. `1be92c3 refs MIN-30 fix(ops): remove unsupported VOLUME directive`
9. `bab5e84 refs MIN-30 docs: update handoff with Dockerfile VOLUME fix`
10. `efe8a30 refs MIN-30 fix(wp): remove fix-blog-posts.php`
    - 该文件在 mu-plugins 中，每次请求都输出文字污染 REST API
11. `4ad2a84 refs MIN-30 fix(ops): clear mu-plugins/themes before sync`
    - ❌ **导致白屏！** 在 `sync-bundle.sh` 中加 `rm -rf` 清空 mu-plugins/themes
    - 删除了 Volume 中的 Astra 父主题（不在仓库里）→ WordPress fatal error
12. `d8fc902 refs MIN-30 fix(ops): remove rm -rf from mu-plugins/themes sync`
    - 回退 `4ad2a84`，移除 `rm -rf`，恢复为只复制不删除

### 本次会话关键操作时间线

- ✅ Dashboard 切换 Source：Docker Image → Git Repo (`experiment/wp-traditional-mode`)
- ✅ 修复 `railway.json`：builder → DOCKERFILE
- ✅ 修复 Dockerfile：删除不支持的 `VOLUME` 指令
- ✅ 首次部署成功（`bab5e84`），服务 Online
- ⚠️ 发现 REST API 被 `fix-blog-posts.php` 污染
- ✅ 删除 `fix-blog-posts.php` 并 push（`efe8a30`）
- ❌ 为清理旧文件，在 `sync-bundle.sh` 加 `rm -rf`（`4ad2a84`）
- ❌ **白屏**：`rm -rf` 删除了 Astra 父主题 → WordPress fatal error
- ✅ 回退 `rm -rf`（`d8fc902`），当前等待部署恢复

---

## 白屏原因（供新窗口参考）

**`rm -rf "$TARGET/themes/"*`** 删除了 Astra 父主题。

- 仓库 `wordpress/themes/` 只有 `astra-child`
- Astra 父主题通过 WP Admin 安装，**不在仓库里**
- `astra-child` 依赖父主题，删除后 WordPress 找不到 → fatal error

**教训**：`rm -rf` 在 Volume sync 场景下是危险操作，必须知道 Volume 里有什么才能删。

---

## 当前未提交文件

1. `src/app/globals.css` / `src/app/layout.tsx` / `src/components/theme-toggle.tsx` — dark mode 实验
2. `.playwright-mcp/*` — 浏览器验证产物
3. `m1-rest-v*.zip` — 历史部署包
4. `staging-homepage-full.png` — staging 截图
5. `contents/` — 博客草稿

---

## 下一步：新窗口排错清单

### P0：确认网站恢复

1. 等 Railway 部署 `d8fc902` 完成（Dashboard 看状态）
2. `curl -I https://wordpress-l1ta-staging.up.railway.app/` → 确认返回 200
3. 浏览器打开 `https://wordpress-l1ta-staging.up.railway.app/wp-admin` → 确认可登录

### P1：清理 Volume 中的 `fix-blog-posts.php`

**问题**：`efe8a30` 删除了仓库里的 `fix-blog-posts.php`，但由于 `sync-bundle.sh` 只复制不删除，**Volume 中可能还残留这个文件**。

**方案 A（推荐）**：
1. 确认当前部署后 `fix-blog-posts.php` 是否还在污染输出
2. 如果在，手动通过 WP Admin 文件管理器或 SSH 删除 `/var/www/html/wp-content/mu-plugins/fix-blog-posts.php`

**方案 B**：
1. 临时修改 `sync-bundle.sh` 在 mu-plugins sync 时显式删除 `fix-blog-posts.php`（最小改动）

### P2：验证 REST API

```bash
curl "https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/zh"
```

- 应返回 200 + 干净的 JSON
- JSON 内含 `hero.quickLinks`

### P3：Must-Use 插件检查

WP Admin → 插件 → Must-Use：
- 确认有 `Mindhikers CMS Core`

### P4：完整 P1 验证清单

1. WP Admin 可登录
2. 插件列表有 Carbon Fields + Polylang
3. 主题列表有 Astra Child
4. REST API `/wp-json/mindhikers/v1/homepage/zh` 返回 200
5. JSON 含 `hero.quickLinks`
6. uploads 图片正常
7. 前台 Next.js 消费 API 正常

### P5：如果仍有问题

- 检查 Deploy Logs 是否有 `[mh-sync-bundle] done.`
- 检查 WordPress 错误日志（WP Admin → 工具 → 站点健康，或 volume 中的 `wp-content/debug.log`）
- 确认 `mindhikers-cms-core.php` 是否成功加载

---

## 离生产还有多远

1. ✅ push 实验分支
2. ✅ Railway staging Source 切换到 Git Repo
3. ✅ Dockerfile build 成功
4. ✅ 服务 Online
5. ⏳ 清理 fix-blog-posts.php 污染
6. ⏳ REST API 验证通过
7. ⏳ 完整 P1 验证
8. ⏳ production Volume + MariaDB 备份
9. ⏳ production 部署
10. ⏳ 证明 mu-plugin schema 与 `mhs02` 等价
11. ⏳ 退役 snippets / Code Snippets
12. ⏳ 全站验收 + DNS 切换

---

## 红线提醒

1. 不在 `main` 直接开发。
2. 所有后续 commit 继续使用 `refs MIN-30`。
3. push / merge / production 操作前必须再次问老卢。
4. production `mhs02` snippet 仍是红线，Phase 2 完成等价接管前绝不能删。
5. **Volume sync 场景中，不确认 Volume 内容前绝不使用 `rm -rf`。**

---

## 给新窗口的上下文

- 当前分支：`experiment/wp-traditional-mode`
- 当前 commit：`d8fc902`
- 当前问题：staging WordPress 可能白屏，等 `d8fc902` 部署恢复
- 核心待解决：`fix-blog-posts.php` 残留污染 REST API
- 验证目标：P1 清单全部通过
- 工具限制：Railway CLI 连不上，主要用 Dashboard + curl
- 注意：Astra 父主题不在仓库里，任何删除 themes/ 目录的操作都危险

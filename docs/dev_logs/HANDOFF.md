🕐 Last updated: 2026-04-25 12:30 CST
🌿 Branch: `experiment/wp-traditional-mode`
📌 Base commit: `3370c37`
🚀 Push status: ✅ 已 push（含 Dockerfile VOLUME 修复）

---

## 当前状态：003 Phase 1 代码已 push，Railway staging 首次部署失败已修复，等待重新部署

一句话：WP 单栈迁移 Phase 1 代码已推送到 `experiment/wp-traditional-mode`。老卢已在 Dashboard 把 `WordPress-L1ta` 的 Source 从 Docker Image 切换到 Git Repo + `experiment/wp-traditional-mode` 分支。首次部署失败原因是 Dockerfile 包含 Railway 不支持的 `VOLUME` 指令，已删除并重新 push。现在等 Railway 自动检测文件变更并触发新部署。

给新窗口的第一句话：先不要碰 production；当前等 Railway staging 新部署完成，观察 Deploy Logs 是否出现 `[mh-sync-bundle] done.`。

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
   - 修复 `railway.json`：builder 从 `RAILPACK` 改为 `DOCKERFILE`
   - 指定 `dockerfilePath: ops/mindhikers-cms-runtime/Dockerfile`
8. `1be92c3 refs MIN-30 fix(ops): remove unsupported VOLUME directive for Railway Dockerfile builder`
   - 删除 Dockerfile 第 30 行 `VOLUME ["/var/www/html/wp-content/uploads"]`
   - Railway Dockerfile 构建器不支持 `VOLUME` 指令，导致构建失败
   - Volume 挂载已通过 Dashboard 的 `wordpress-volume-vRzA` 配置，无需 Dockerfile 声明

### 本次会话操作记录

- ✅ `git push origin experiment/wp-traditional-mode`（首次 push 5 个 Phase 1 commit）
- ✅ 协助老卢在 Railway Dashboard 切换 `WordPress-L1ta` Source
  - 从 `wordpress:latest` Docker Image → Git Repo `freestone16/Mindhikers-Home`
  - Branch: `experiment/wp-traditional-mode`
  - Root Directory: `/`
- ✅ 发现 Dashboard Build 标签页显示 Builder 被锁定为 `Railpack`（由 `railway.json` 控制）
- ✅ 修复 `railway.json`：builder → DOCKERFILE，增加 dockerfilePath
- ✅ 重新 push `railway.json` 修复
- ❌ 首次部署失败（commit `94c9127`，34 分钟前）
  - 错误：`dockerfile invalid: docker VOLUME at Line 30 is not supported, use Railway Volumes`
  - 原因：Railway Dockerfile 构建器不支持 `VOLUME` 指令
- ✅ 修复 Dockerfile：删除第 30 行 `VOLUME ["/var/www/html/wp-content/uploads"]`
- ✅ 重新 push Dockerfile 修复（commit `1be92c3`）

---

## 已验证

本地 Docker 已启动并验证通过（见上次 handoff）。

---

## 当前未提交文件

这些文件刻意没有混入 commit：

1. `src/app/globals.css` / `src/app/layout.tsx` / `src/components/theme-toggle.tsx` — dark mode 实验，与 WP 迁移无关
2. `.playwright-mcp/*` — 浏览器验证产物
3. `m1-rest-v*.zip` — 历史部署包
4. `staging-homepage-full.png` — staging 截图
5. `contents/` — 博客草稿，等老卢确认

---

## 下一步：观察 Railway staging 部署

### 当前预期

Railway 应该已检测到 `1be92c3` 变更，自动触发新部署。Builder 类型为 `Dockerfile`，且不再包含 `VOLUME` 指令。

### 需要观察的日志关键字

Deploy Logs 里找：
- `Step 1/xx : FROM ...` — Docker build 开始
- `composer install` — Carbon Fields 依赖安装
- `[mh-sync-bundle] installing WordPress core`
- `[mh-sync-bundle] syncing mu-plugins`
- `[mh-sync-bundle] syncing themes`
- `[mh-sync-bundle] syncing bundled plugins`
- **`[mh-sync-bundle] done.`** ← 关键成功标志

### 如果部署失败

可能原因：
1. `dockerfilePath` 路径错误 → 检查 `ops/mindhikers-cms-runtime/Dockerfile` 是否存在于仓库
2. Build context 问题 → 检查 Root Directory 是否为 `/`
3. Volume 冲突 → 检查 `/var/www/html` 的 volume mount 是否与新镜像冲突

### P1：staging 验证清单（部署成功后）

1. WP Admin 可登录 — `/wp-admin`
2. 插件列表看到 Carbon Fields + Polylang
3. 主题列表看到当前 Astra Child
4. `/wp-json/mindhikers/v1/homepage/zh` 返回 200
5. JSON 内含 `hero.quickLinks`
6. uploads 下历史图片 URL 正常
7. 前台 Next.js 仍能消费 API，不丢 Quick Links

### 离生产还有多远

1. ✅ push 实验分支
2. ✅ Railway staging Source 切换到 Git Repo
3. ✅ `railway.json` Builder 修复为 DOCKERFILE
4. ⏳ 等待 Railway 自动部署
5. ⏳ staging 验证通过
6. ⏳ production Volume + MariaDB 备份
7. ⏳ production 部署
8. ⏳ 证明 mu-plugin schema 与 `mhs02` 等价
9. ⏳ 退役 snippets / Code Snippets
10. ⏳ 全站验收 + DNS 切换

粗略判断：离“可谨慎推 production 部署”还差 1-2 个认真验证窗口；离“最终 DNS 切换并稳定收口”还差 2-4 个窗口。

---

## 红线提醒

1. 不在 `main` 直接开发。
2. 所有后续 commit 继续使用 `refs MIN-30`。
3. push / merge / production 操作前必须再次问老卢。
4. production `mhs02` snippet 仍是红线，Phase 2 完成等价接管前绝不能删。
5. Next.js dark mode 改动不是 003 主线，不能混入 WP 单栈迁移 commit。

---

## 给新窗口的上下文

当前分支：`experiment/wp-traditional-mode`
当前目标：等待 Railway staging 自动部署，验证 `[mh-sync-bundle] done.`
已知问题：Railway CLI 暂时连不上（Connection reset by peer），Dashboard 操作是主要手段
下一个阻塞：部署成功后执行 P1 验证清单

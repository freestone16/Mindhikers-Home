🕐 Last updated: 2026-04-25 10:45 CST
🌿 Branch: `experiment/wp-traditional-mode`
📌 Base commit: `3370c37`
🚀 Push status: ✅ 已 push 到远端；等待老卢配置 Railway staging

---

## 当前状态：003 Phase 1 本地代码已 push，等待 Railway staging 部署

一句话：WP 单栈迁移 Phase 1 代码已推送到 `experiment/wp-traditional-mode`。下一步由老卢在 Railway staging 控制台把 `WordPress-L1ta` 服务接到这个分支，观察 build 和 deploy 是否通过。

给新窗口的第一句话：先不要碰 production；当前等 Railway staging 部署结果，如果老卢还没配 staging，提醒老卢按下面步骤配置。

---

## 本窗口完成内容

### 已提交 commit（已 push）

1. `5546d7c refs MIN-30 docs: add WP single-stack migration playbook`
   - 落盘 002/003 迁移方案与 handoff
   - 确认 `MIN-30`，父级/归属：`MIN-7 网站开发`

2. `8d30f42 refs MIN-30 feat(wp): bundle carbon-fields and polylang`
   - 新增 `wordpress/plugins-bundled/carbon-fields/`
   - 新增 `wordpress/plugins-bundled/polylang/`
   - Carbon Fields 使用 `carbon-fields-plugin` v3.6.9 plugin loader，`composer.lock` 锁定 `htmlburger/carbon-fields` v3.6.9
   - Polylang 使用 wordpress.org 3.8.2 包

3. `f9f0349 refs MIN-30 feat(ops): copy and sync WP bundle into image`
   - 新增 `.dockerignore`
   - 改写 `ops/mindhikers-cms-runtime/Dockerfile`
   - 新增 `ops/mindhikers-cms-runtime/sync-bundle.sh`
   - Docker build 阶段把 WP mu-plugins/themes/bundled plugins COPY 进 image
   - 启动时先安装 WordPress core，再把 `/opt/wp-bundle` 同步到 Volume 挂载的 `wp-content`
   - 修正 003 playbook 中 Carbon Fields 获取方式

4. `09930cc refs MIN-30 refactor(cms-core): preserve homepage quick links schema`
   - `bootstrap.php` 输出 `hero.quickLinks`
   - contact links 保留 `qrImage`
   - 新增 `src/compat/m1-rest-functions.php`
   - M1 REST 兼容函数延迟到 `plugins_loaded` 注册，避免 M1 REST 插件仍激活时函数重名 fatal

5. `7ddd43b refs MIN-30 docs: save Phase 1 handoff`
   - 保存 Phase 1 本地验证结果
   - 标记剩余未提交文件与下一步 staging 入口

### 本次会话新增操作

- ✅ `git push origin experiment/wp-traditional-mode` — 分支已推送到 GitHub
- PR 链接：https://github.com/freestone16/Mindhikers-Home/pull/new/experiment/wp-traditional-mode

---

## 已验证

本地 Docker 已启动并验证通过：

1. `docker build --no-cache -t mh-wp-test -f ops/mindhikers-cms-runtime/Dockerfile .`
   - ✅ build 通过
   - ✅ Carbon Fields Composer 依赖按 lock 安装
   - ✅ Polylang bundled 文件进入 image

2. PHP lint：
   - ✅ `mindhikers-cms-core.php`
   - ✅ `bootstrap.php`
   - ✅ `src/compat/m1-rest-functions.php`

3. Runtime smoke：
   - ✅ `mh-sync-bundle` 日志出现：
     - `installing WordPress core`
     - `syncing mu-plugins`
     - `syncing themes`
     - `syncing bundled plugins`
     - `done`
   - ✅ `curl -I http://localhost:8080/` 返回 `302` 到 `/wp-admin/setup-config.php`
   - ✅ runtime 内确认存在：
     - `/var/www/html/index.php`
     - Carbon Fields `vendor/autoload.php`
     - Polylang `polylang.php`
   - ✅ 测试容器已停止

4. `git diff --check`
   - ✅ 当前自有代码 diff 通过
   - 注意：第三方 plugin 包内存在上游行尾空格，未改动第三方发布物

---

## 当前未提交文件

这些文件刻意没有混入 003 commit：

1. `src/app/globals.css`
2. `src/app/layout.tsx`
3. `src/components/theme-toggle.tsx`
   - Next.js dark mode/theme toggle 实验
   - 与 WP 单栈迁移主线无关，后续单独决定保留/丢弃/另开 commit

4. `.playwright-mcp/*`
   - 浏览器验证产物
   - 默认不提交；如有证据价值，提炼结论进文档即可

5. `m1-rest-v1.2.0.zip` / `m1-rest-v1.3.0.zip` / `m1-rest-v1.3.1.zip` / `m1-rest-v1.4.0.zip`
   - 历史部署包/对照源
   - `v1.4.0` 本窗口已用于兼容层对照
   - 后续 P7 清理或迁到归档区，不应长期留仓库根目录

6. `staging-homepage-full.png`
   - staging 视觉截图
   - 如果作为验收 baseline，应迁到 `docs/evidence/`；否则不提交

7. `contents/`
   - 当前含 `黄金精神 Human Golden Spirit.md` 与 `blog-01.md`
   - 像是内容灌入素材/博客草稿
   - 新窗口先不要默认提交或删除；等老卢确认它们是否属于 Phase 4 Blog 内容迁移素材

---

## 下一步建议

### P0：Railway staging 配置（等老卢操作）

老卢需要在 Railway staging 控制台：

1. 进入 `WordPress-L1ta` 服务
2. Settings → Source → Branch 切到 `experiment/wp-traditional-mode`
3. Build Context 设为**仓库根目录**（`/`）
4. Dockerfile Path 设为 `ops/mindhikers-cms-runtime/Dockerfile`
5. 保存，触发 Deploy
6. 观察 Deploy Logs 是否出现 `[mh-sync-bundle] done.`
7. `curl -I <staging-url>` 确认返回 200/302

### P1：staging 验证清单

部署成功后验证：

1. WP Admin 可登录 — `/wp-admin`
2. 插件列表看到 Carbon Fields + Polylang
3. 主题列表看到当前 Astra Child
4. `/wp-json/mindhikers/v1/homepage/zh` 返回 200
5. JSON 内含 `hero.quickLinks`
6. uploads 下历史图片 URL 正常
7. 前台 Next.js 仍能消费 API，不丢 Quick Links

### 离生产还有多远

当前本地 Phase 1 已 push，离最终生产还差：

1. ✅ push 实验分支（本次完成）
2. ⏳ Railway staging 接分支并部署
3. ⏳ staging 验证 WP Admin / plugins / REST / uploads / Next.js API 消费
4. ⏳ production Volume + MariaDB 备份
5. ⏳ production 部署
6. ⏳ 证明 mu-plugin schema 与 `mhs02` 等价
7. ⏳ 再退役 snippets / Code Snippets
8. ⏳ 全站验收后才进入 DNS 切换

粗略判断：离“可谨慎推 production 部署”还差 1-2 个认真验证窗口；离“最终 DNS 切换并稳定收口”还差 2-4 个窗口。

### P2：暂不做的事

1. 不动 production
2. 不删 production `mhs02`
3. 不卸载 Code Snippets
4. 不做 DNS 切换
5. 不归档 Next.js

这些都必须等 Phase 1 staging 通过后再进入 Phase 2/后续阶段。

---

## 红线提醒

1. 不在 `main` 直接开发。
2. 所有后续 commit 继续使用 `refs MIN-30`。
3. push / merge / production 操作前必须再次问老卢。
4. production `mhs02` snippet 仍是红线，Phase 2 完成等价接管前绝不能删。
5. Next.js dark mode 改动不是 003 主线，不能混入 WP 单栈迁移 commit。

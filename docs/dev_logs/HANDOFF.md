🕐 Last updated: 2026-04-27 11:55 CST
🌿 Branch: `staging`
📌 Latest commit: `da5a642` docs(agents): add branch discipline rules for staging/main workflow
🚀 Push status: ✅ 已 push，staging 自动部署中

---

## 当前状态：方案A数据层统一完成，分支整理完成，前台服务502待排查

**一句话**：EN API 空值已修复，分支已合并到正式 `staging`，AGENTS.md 已更新。但前台 Next.js 服务返回 502，需要排查。

---

## 本窗口完成内容

### ✅ 已完成

1. **诊断并修复 EN API 空值** — 核心突破
   - 根因：`wp_json_encode` 将英文双引号 `"` 转义为 `\\\"`，WordPress `sanitize_callback` 执行前 `wp_unslash` 去掉了反斜杠，破坏 JSON 结构
   - 修复：移除 `register_post_meta` 的 `sanitize_callback`，seed 脚本改用 `$wpdb` 直写数据库

2. **分支整理**
   - `experiment/wp-traditional-mode` → 合并到 `staging` → 删除旧分支
   - 当前分支：`staging`
   - `staging` 已 push 到 origin

3. **更新 AGENTS.md**
   - 新增第 3 节 "Branch Discipline"
   - 明确 `main`（生产）、`staging`（预发）、`experiment/*`（临时开发）三级分支规范

4. **验证后台 API**
   - EN API: `hero.title='A brand home for research...'` ✅
   - ZH API: `hero.title='心行者 MindHikers'` ✅

### ⚠️ 待排查

**前台 Next.js 服务 502**

现象：
- `https://mindhikers-homepage-staging.up.railway.app/` → 502 Application failed to respond
- `https://mindhikers-homepage-staging.up.railway.app/health` → 502
- 后台 `https://wordpress-l1ta-staging.up.railway.app` → 正常 200

排查方向：
1. Railway CLI `railway service status` 只显示 `WordPress-L1ta` 一个服务
2. 前台 Next.js 服务可能在另一个 Railway 项目，或已停止/被删除
3. 需要用户在 Railway Dashboard 确认前台服务状态

### ⏳ 待用户完成

1. **确认 Railway Dashboard 前台服务状态**
   - 打开 Railway Dashboard → Mindhikers-Homepage 项目
   - 确认有几个服务（应该有两个：后台 WordPress + 前台 Next.js）
   - 如果前台服务存在，检查最近部署状态（SUCCESS / FAILED / CRASHED）
   - 如果前台服务不存在，可能需要重新创建或从其他项目找回

2. **Staging 验收**（前台恢复后）
   - 后台 CMS 编辑流程
   - 前台首页渲染
   - Blog 链路、Contact 区块、手机竖屏

---

## 根因总结（EN API 空值）

1. `m1-seed.php` 构建 EN payload 时，`blog.description` 包含英文双引号 `"Carbon-Silicon Evolution"`
2. `wp_json_encode` 将其转义为 `\\\"Carbon-Silicon Evolution\\\"`
3. `update_post_meta` 调用 `sanitize_meta`，触发 `sanitizeJsonPayload`
4. WordPress 在传递参数前对字符串应用了 `wp_unslash`，`\\\"` 变成 `"`，破坏 JSON 结构
5. `sanitizeJsonPayload` 中 `json_decode` 失败，回退到 `[]`
6. API 返回空值

**修复**：移除 `sanitize_callback`，seed 改用 `$wpdb` 直写。

---

## 关键代码变更文件

| 文件 | 变更 | 状态 |
|---|---|---|
| `ops/wordpress/m1-seed.php` | 重写为创建 post + `$wpdb` 直写 meta | ✅ |
| `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php` | 删除 CF fallback + `suppress_filters` + 移除 `sanitize_callback` | ✅ |
| `wordpress/mu-plugins/mindhikers-m1-core.php` | 删除 Hero/About/Contact/Product/Blog CF 容器 | ✅ |
| `ops/mindhikers-cms-runtime/sync-bundle.sh` | 失败时不写 hash + 成功时写 hash | ✅ |
| `AGENTS.md` | 新增 Branch Discipline 章节 | ✅ |

---

## 验收域名

| 环境 | 域名 | 状态 |
|---|---|---|
| 后台 CMS | `https://wordpress-l1ta-staging.up.railway.app/wp-admin` | ✅ 正常 |
| 前台 Next.js | `https://mindhikers-homepage-staging.up.railway.app` | ❌ 502 |
| ZH API | `https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/zh` | ✅ 正常 |
| EN API | `https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/en` | ✅ 正常 |

---

## 下一步（等待用户指令）

1. **用户确认 Railway Dashboard 前台服务状态**
   - 截图或描述 Dashboard 上的服务列表
   - 前台服务是否存在？最近部署状态？
2. **修复前台 502**
3. **完成 staging 验收**
4. **生产环境部署**

---

## 给新窗口的上下文

- 当前分支：`staging`
- 当前 commit：`da5a642`
- 后台状态：API 正常，WP Admin 可访问
- 前台状态：502，待排查
- Railway CLI 只检测到 `WordPress-L1ta` 一个服务，前台服务可能未部署或在其他项目
- 验证命令：
  - 后台：`curl -sL "https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/en"`
  - 前台：`curl -sL "https://mindhikers-homepage-staging.up.railway.app"`

---

(End of file)

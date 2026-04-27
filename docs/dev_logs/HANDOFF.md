🕐 Last updated: 2026-04-27 09:51 CST
🌿 Branch: `experiment/wp-traditional-mode`
📌 Latest commit: `7ea589f` refs MIN-30 fix(seed): clear transient cache after seed to avoid stale API responses
🚀 Push status: ✅ 已 push，staging 已自动部署

---

## 当前状态：方案A数据层统一实施中，EN API 仍返回空值

**一句话**：已完成 m1-seed.php 重写、CF fallback 清理、sync-bundle.sh 修复、管理员用户绕过 auth_callback、suppress_filters 和缓存清除，但 EN API 仍返回空值。ZH API 正常。

---

## 本窗口完成内容（方案A步骤1-5基本完成）

### ✅ 已完成

1. **重写 m1-seed.php** — 从写 Carbon Fields theme options 改为创建 `mh_homepage` post + `update_post_meta`
2. **清理 bootstrap.php** — 删除 `buildHomepagePayloadFromCarbonFields()` 方法 (~130行)
3. **清理 mindhikers-m1-core.php** — 删除 Hero/About/Contact/Product/Blog 的 CF theme options 容器 (~80行)
4. **修复 bootstrap.php 语法错误** — 删除残留代码导致 500 错误
5. **更新 sync-bundle.sh** — 失败时不写 hash，成功时写 hash，避免失败后被跳过
6. **seed 脚本设置管理员用户** — `wp_set_current_user()` 绕过 `register_post_meta` 的 `auth_callback`
7. **findHomepagePostByLocale 添加 suppress_filters** — 排除 Polylang 等插件的查询干扰
8. **seed 后清除 transient 缓存** — `delete_transient("mindhikers_homepage_data_{$locale}")`

### ⏳ 待解决（核心阻塞）

**EN API `/wp-json/mindhikers/v1/homepage/en` 返回所有字段为空**

关键证据：
- deploy logs 显示 `Sanitize en: input_len=1957, output_len=3197` → `sanitizeJsonPayload` 本身没问题
- deploy logs 显示 `Updated mh_homepage post for en: 2019` → `update_post_meta` 返回 true
- 但 `curl EN API` 返回 `hero.title=''`, `product.title=''`, `blog.title=''`, `contact.email=''` 等全部空值
- ZH API 正常

已排除的原因：
1. ❌ `auth_callback` 阻止更新 → 已修复（设置管理员用户）
2. ❌ `sanitizeJsonPayload` 截断 JSON → 已排除（直接测试 output_len=3197）
3. ❌ transient 缓存 → 已排除（seed 脚本已清除缓存）
4. ❌ Polylang 过滤 → 已排除（已添加 suppress_filters）
5. ❌ bootstrap.php 语法错误 → 已修复

仍可能的原因：
- **数据库中存在重复的 `mindhikers_homepage_payload` meta 记录** — 之前 `bootstrap.php` 有语法错误时 `register_post_meta` 未执行，`update_post_meta` 可能创建了重复记录；现在 `get_post_meta` 读取到的是旧的空记录
- **`findHomepagePostByLocale('en')` 仍找不到 post** — 即使 `suppress_filters` 已添加，也可能有其他原因
- **其他 hook 覆盖 EN payload** — 某些插件或自定义代码在 EN post 保存后覆盖了 payload

---

## 下一步行动计划

### 方案A步骤6：验证运营编辑流程（被 EN API 空值阻塞）

**优先级1：诊断 EN API 空值根因**

建议操作：
1. 在 seed 脚本中 `update_post_meta` 之前先 `delete_post_meta($postId, 'mindhikers_homepage_payload')`，确保删除所有旧记录
2. 在 seed 脚本中 `update_post_meta` 之后立即用 `get_post_meta` 读取并输出长度，确认写入成功
3. 如果写入成功但 API 仍为空，检查 `findHomepagePostByLocale('en')` 是否返回了正确的 post（在 `getHomepageByLocale` 中输出日志）
4. 如果 `findHomepagePostByLocale` 找到了 post 但 `get_post_meta` 返回空值，说明数据库中有重复记录或 `register_post_meta` 的 `sanitize_callback` 在读取时也被调用

**优先级2：验证运营编辑流程**

1. 登录 WP Admin → Mindhikers Homepages
2. 编辑 zh 或 en 的 JSON payload
3. 保存后 curl 验证 API 返回新值
4. 确认没有 Carbon Fields 的 homepage 表单干扰（已删除）

**优先级3：P3 验收环境准备**

1. 完整 P3 验证清单（首页五区块、Blog 链路、Contact 区块、手机竖屏）
2. 前台 Next.js 消费 API 验证
3. 准备验收报告

---

## 关键代码变更文件

| 文件 | 变更 | 状态 |
|---|---|---|
| `ops/wordpress/m1-seed.php` | 重写为创建 post + 管理员用户绕过 + 缓存清除 | ✅ 仍有 debug 注释待清理 |
| `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php` | 删除 CF fallback + 添加 suppress_filters | ✅ |
| `wordpress/mu-plugins/mindhikers-m1-core.php` | 删除 Hero/About/Contact/Product/Blog CF 容器 | ✅ |
| `ops/mindhikers-cms-runtime/sync-bundle.sh` | 失败时不写 hash + 成功时写 hash | ✅ |

---

## 红线提醒

1. 不在 `main` 直接开发
2. 所有 commit 使用 `refs MIN-30`
3. push / merge / production 操作前必须问老卢
4. `m1-seed.php` 中仍有 debug 注释（`debug: testing sanitizeJsonPayload behavior`），清理前不要合并到 main

---

## 给新窗口的上下文

- 当前分支：`experiment/wp-traditional-mode`
- 当前 commit：`7ea589f`
- 核心阻塞：EN API 空值，seed 写入成功但 API 不返回
- 建议首先尝试：`delete_post_meta` + `update_post_meta` 组合，确保删除旧记录
- 验证命令：`curl -sL "https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/en" | python3 -c "import sys,json; d=json.load(sys.stdin); print('hero.title:', repr(d['hero']['title'])); print('blog.title:', repr(d['blog']['title'])); print('contact.email:', repr(d['contact']['email'])); print('quickLinks:', len(d['hero']['quickLinks']));"`
- 部署状态：staging 自动部署已启用
- Railway CLI 偶尔连接失败，多试几次

---

(End of file)

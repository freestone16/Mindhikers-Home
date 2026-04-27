🕐 Last updated: 2026-04-27 10:50 CST
🌿 Branch: `experiment/wp-traditional-mode`
📌 Latest commit: `572cd8b` refs MIN-30 cleanup: remove debug logs, restore normal get_post_meta in API
🚀 Push status: ✅ 已 push，staging 已自动部署

---

## 当前状态：方案A数据层统一完成，ZH/EN API 均正常，等待 staging 验收

**一句话**：EN API 空值问题已修复，数据层统一完成。staging 环境就绪，等待用户验收。

---

## 本窗口完成内容

### ✅ 已完成

1. **诊断并修复 EN API 空值** — 核心突破
   - 根因：`wp_json_encode` 将英文双引号 `"` 转义为 `\"`，WordPress `sanitize_callback` 执行前 `wp_unslash` 去掉了反斜杠，破坏 JSON 结构
   - ZH 不受影响是因为中文 payload 使用 `「」` 而非 `"`
   - 修复：移除 `register_post_meta` 的 `sanitize_callback`，seed 脚本改用 `$wpdb` 直写数据库绕过 `update_post_meta` 的隐藏处理

2. **清理所有 debug 代码**
   - 移除 `bootstrap.php` 中的 `error_log` 调试
   - 移除 `m1-seed.php` 中的 `force-reseed` 注释和测试日志

3. **验证 ZH/EN API**
   - EN API: `hero.title='A brand home for research...'`, `blog.title='Carbon-Silicon Evolution'`, `contact.email='hello@mindhikers.com'` ✅
   - ZH API: `hero.title='心行者 MindHikers'`, `blog.title='碳硅进化论'`, `contact.email='hello@mindhikers.com'` ✅

4. **准备 staging 验收清单**
   - 后台 CMS 验收项
   - 前台 API 验收项
   - 前台 Next.js 验收项

### ⏳ 待用户完成

1. **Staging 验收**
   - 登录 WP Admin → Mindhikers Homepages
   - 编辑 EN/ZH 的 JSON payload，保存后 curl 验证 API 返回新值
   - 确认没有 Carbon Fields 的 homepage 表单干扰
   - 前台 Next.js 渲染验证（首页、Blog、Contact、手机竖屏）

---

## Staging 验收清单（P3）

### 后台 CMS
- [ ] 登录 WP Admin `https://wordpress-l1ta-staging.up.railway.app/wp-admin`
- [ ] 确认 Mindhikers Homepages 菜单可见
- [ ] 编辑 ZH post，修改 `hero.title`，保存
- [ ] curl 验证 ZH API 返回新值
- [ ] 编辑 EN post，修改 `hero.title`，保存
- [ ] curl 验证 EN API 返回新值
- [ ] 确认没有 Carbon Fields 的 homepage 表单干扰（已删除）

### 前台 API
- [ ] ZH API `/wp-json/mindhikers/v1/homepage/zh` 返回完整数据
- [ ] EN API `/wp-json/mindhikers/v1/homepage/en` 返回完整数据
- [ ] 首页五区块数据完整（hero, about, product, blog, contact）

### 前台 Next.js（如果已部署）
- [ ] 首页 `/` 渲染正常
- [ ] 英文首页 `/en` 渲染正常
- [ ] Blog 列表与详情链路正常
- [ ] Contact 区块可达
- [ ] 手机竖屏可读性与主要 CTA 正常

---

## 根因总结（供复盘）

**EN API 空值根因链**：
1. `m1-seed.php` 构建 EN payload 时，`blog.description` 包含英文双引号 `"Carbon-Silicon Evolution"`
2. `wp_json_encode` 将其转义为 `\"Carbon-Silicon Evolution\"`
3. `update_post_meta` 调用 `sanitize_meta`，触发 `sanitizeJsonPayload`
4. WordPress 在传递参数前对字符串应用了 `wp_unslash`，`\"` 变成 `"`，破坏 JSON 结构
5. `sanitizeJsonPayload` 中 `json_decode` 失败，回退到 `[]`
6. API 返回 `normalizeHomepagePayload([], 'en')`，所有字段为空字符串

**修复措施**：
- 移除 `mindhikers_homepage_payload` 的 `sanitize_callback`（JSON 验证已移至 `saveHomepageMeta`）
- seed 脚本改用 `$wpdb` 直写 `wp_postmeta` 表，绕过 `update_post_meta`
- 添加 `clean_post_cache()` + `wp_cache_flush()` 确保缓存一致

---

## 关键代码变更文件

| 文件 | 变更 | 状态 |
|---|---|---|
| `ops/wordpress/m1-seed.php` | 重写为创建 post + `$wpdb` 直写 meta + 管理员用户绕过 + 缓存清除 | ✅ |
| `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php` | 删除 CF fallback + 添加 `suppress_filters` + 移除 `sanitize_callback` | ✅ |
| `wordpress/mu-plugins/mindhikers-m1-core.php` | 删除 Hero/About/Contact/Product/Blog CF 容器 | ✅ |
| `ops/mindhikers-cms-runtime/sync-bundle.sh` | 失败时不写 hash + 成功时写 hash | ✅ |

---

## 下一步（等待用户指令）

1. **用户完成 staging 验收**（按上方清单）
2. **确认 WP Admin 编辑流程正常**
3. **生产环境备份 + 部署**（需要用户提供生产环境 Railway 项目名或确认部署目标）

---

## 给新窗口的上下文

- 当前分支：`experiment/wp-traditional-mode`
- 当前 commit：`572cd8b`
- staging 状态：API 正常，待用户验收 WP Admin 编辑流程
- 核心变更：`bootstrap.php` 移除 `sanitize_callback`，`m1-seed.php` 改用 `$wpdb` 直写
- 验证命令：`curl -sL "https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/en" | python3 -c "import sys,json; d=json.load(sys.stdin); print('hero.title:', repr(d['hero']['title']));"`
- Railway CLI 偶尔连接失败，多试几次

---

(End of file)

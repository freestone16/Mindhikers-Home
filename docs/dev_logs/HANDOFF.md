🕐 Last updated: 2026-04-25 17:30 CST
🌿 Branch: `experiment/wp-traditional-mode`
📌 Latest commit: `ff0cdfb` refs MIN-30 debug(ops): read m1-rest helpers.php
🚀 Push status: ✅ 已 push

---

## 当前状态：Seed 已执行，数据已写入，但 REST API 仍返回空值

一句话：从生产环境 mindhikers.com 爬取的内容已通过 m1-seed.php 写入 staging WordPress，产品 CPT（黄金坩埚）也成功创建。但 REST API `/wp-json/mindhikers/v1/homepage/zh` 返回空字符串，排查中。

---

## 本窗口完成内容

### 已完成步骤

1. ✅ **修复 Astra 白屏** — 下载 Astra 父主题加入仓库 `wordpress/themes/astra`
2. ✅ **修复 m1-seed.php fatal error** — 将 CLI 脚本移出 mu-plugins，避免自动加载时 Carbon Fields 未就绪
3. ✅ **修复 m1-rest require 路径** — `mindhikers-m1-core.php` 中改为 `WP_PLUGIN_DIR . '/m1-rest/'`
4. ✅ **修复 Dockerfile seed 复制** — 用 COPY 指令将 `ops/wordpress/m1-seed.php` 复制到镜像 `/opt/wp-bundle/seed/`
5. ✅ **修复 PHP 语法** — m1-seed.php 中误加了反斜杠转义（`\$zh_product_id` → `$zh_product_id`）
6. ✅ **执行 Seed** — 通过 `run-seed.php` 成功执行，Theme Options 和 Product CPT 已创建
7. ✅ **验证数据写入** — 直接读取 `carbon_get_theme_option('hero_title_zh')` 返回 "心行者 MindHikers"
8. ✅ **读取 m1-rest 源码** — 已确认 `helpers.php` 中的 `m1_get_theme_option()` 函数会检查 `function_exists('carbon_get_theme_option')`

### 当前阻塞

| 项目 | 状态 | 说明 |
|---|---|---|
| 首页访问 | ✅ 200 | `https://wordpress-l1ta-staging.up.railway.app/` |
| wp-login | ✅ 200 | `https://wordpress-l1ta-staging.up.railway.app/wp-login.php` |
| REST API | ⚠️ 200 但空值 | `hero.title = ''`, `about.title = ''` |
| 数据写入 | ✅ 成功 | 直接读 `carbon_get_theme_option` 能读到 |
| Seed 文件 | ✅ 已执行 | 创建了 ZH Product (ID 2014) 和 EN Product (ID 2015) |

### 排查记录

**已确认的事实：**
1. `carbon_get_theme_option('hero_title_zh')` 在诊断代码中返回正确值
2. REST API 的 `m1_get_theme_option()` 函数有 `function_exists('carbon_get_theme_option')` 保护
3. 如果 CF 未加载，函数返回空字符串 '' — **这与当前现象一致**
4. 但 Seed 脚本能成功 boot Carbon Fields 并写入数据，说明 CF 本身没问题

**当前假设：**
- REST API 执行时，Carbon Fields 的插件可能尚未完全加载，导致 `function_exists('carbon_get_theme_option')` 返回 false
- 或者 CF 的 boot 时机在 REST API 请求处理之后

**已读取的关键源码：**
- `m1-rest/helpers.php` 第 20-30 行：`m1_get_theme_option()` 函数
- `m1-rest/homepage.php` 第 67-97 行：`m1_build_hero()` 函数调用 `m1_get_theme_option()`
- `wordpress/mu-plugins/mindhikers-m1-core.php` 第 186-189 行：CF 存在性检查 admin notice

---

## 下一步行动计划（给新窗口）

### P0：排查 REST API 空值根因

1. **在 staging 上执行诊断代码**：创建一个临时 PHP 文件，在 REST API 执行上下文中检查：
   - `function_exists('carbon_get_theme_option')` 是否 true
   - `class_exists('Carbon_Fields\Carbon_Fields')` 是否 true
   - 如果 false，检查 CF 插件是否已激活（`is_plugin_active('carbon-fields/carbon-fields-plugin.php')`）

2. **根据诊断结果修复**：
   - 如果 CF 在 API 时未加载 → 在 `mindhikers-m1-core.php` 或 `m1-rest/helpers.php` 中提前 boot CF
   - 如果 CF 已加载但函数不存在 → 检查 autoload 路径
   - 如果 CF 完全正常 → 检查字段 key 名是否匹配（如 `hero_title_zh` vs `hero_title`）

### P1：验证 REST API 返回非空数据

```bash
curl -sL "https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/zh" | python3 -m json.tool | grep -E "hero|about|contact"
```

应看到：
- `hero.title` = "心行者 MindHikers"
- `hero.eyebrow` = "MindHikers"
- `hero.quickLinks` = 包含黄金坩埚和碳硅进化论
- `about.title` = "About"
- `contact.description` = "有合作想法，或者单纯想聊聊？"

### P2：清理临时诊断代码

1. 从 `sync-bundle.sh` 中移除所有临时诊断代码（`check-cf.php`、`check-rest.php`、`run-seed.php` 创建逻辑）
2. 如果服务器上还残留 `debug-probe.php`，确认并清理
3. Commit 清理改动

### P3：完整 P1 验证清单

1. WP Admin 可登录
2. 插件列表有 Carbon Fields + Polylang
3. 主题列表有 Astra Parent + Astra Child
4. REST API `/wp-json/mindhikers/v1/homepage/zh` 返回 200 + 非空 JSON
5. JSON 含 `hero.quickLinks`
6. 前台 Next.js 消费 API 正常（如果可能）

---

## 离生产还有多远

1. ✅ push 实验分支
2. ✅ Railway staging Source 切换到 Git Repo
3. ✅ Dockerfile build 成功
4. ✅ 服务 Online（200）
5. ✅ 致命错误已修复（Astra + m1-seed + m1-rest 路径）
6. ✅ Seed 已执行，数据已写入
7. ⏳ REST API 返回空值 — **当前阻塞**
8. ⏳ 清理临时诊断代码
9. ⏳ 完整 P1 验证
10. ⏳ production Volume + MariaDB 备份
11. ⏳ production 部署
12. ⏳ 证明 mu-plugin schema 与 `mhs02` 等价
13. ⏳ 退役 snippets / Code Snippets
14. ⏳ 全站验收 + DNS 切换

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
- 当前 commit：`ff0cdfb`
- 当前问题：REST API 200 但返回空值
- 已知：数据已写入（直接读 CF 能读到），但 API 层读不到
- 核心排查方向：REST API 执行时 Carbon Fields 是否已加载
- 验证目标：P0 根因找到并修复 → P1 验证通过
- 工具：curl + Railway CLI 可用（`railway list-deployments` 等）

---

## 关键文件索引

| 文件 | 作用 | 当前状态 |
|---|---|---|
| `ops/wordpress/m1-seed.php` | Seed 脚本（已执行） | ✅ 数据已写入 |
| `ops/mindhikers-cms-runtime/Dockerfile` | Docker 镜像构建 | ✅ 已修复 seed COPY |
| `ops/mindhikers-cms-runtime/sync-bundle.sh` | Volume sync + 临时诊断 | ⚠️ 含临时代码待清理 |
| `wordpress/mu-plugins/mindhikers-m1-core.php` | M1 Core mu-plugin | ✅ 已修复 m1-rest 路径 |
| `wordpress/themes/astra/` | Astra 父主题 | ✅ 已加入仓库 |

(End of file)

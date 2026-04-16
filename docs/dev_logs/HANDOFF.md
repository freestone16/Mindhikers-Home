🕐 Last updated: 2026-04-16 02:00
🌿 Branch: codex/cyd-stumpel-home-exploration
📌 Latest commit: `42d8926`

## 交接入口

- 工作目录：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- Linear 主线：`MIN-8`（网站上线）→ `MIN-110`（CMS 内容模型）
- 执行方案：`docs/plans/2026-04-12-001-feat-m1-cms-content-model-plan.md`
- staging 地址：`https://wordpress-l1ta-staging.up.railway.app`

## 当前状态：M1 Unit 1-5 代码+部署完成 ✅ · Unit 6-7 待收尾

### 已完成

| 阶段 | 状态 | 说明 |
|------|------|------|
| 视觉打底层 (Unit 1-7) | ✅ | Logo / 配色 / 字体 / 主文案 / SEO / 模板残留清理 / Smoke R2 |
| M1 Unit 0: Polylang Gate | ✅ | Polylang 3.8.2 安装 + ZH/EN 语言配置 + 兼容性验证全通过 |
| M1 Unit 1: Child Theme + Carbon Fields | ✅ | Astra Child Theme 已创建并部署；Carbon Fields v3.6.9 已安装并激活；Additional CSS 已迁移 |
| M1 Unit 2: Product CPT | ✅ | `mh_product` CPT 已注册；黄金坩埚 ZH (ID:1998) + EN (ID:1999) 已创建并 Polylang 关联；状态已纠正为"构思中" |
| M1 Unit 3: Blog 分类 | ✅ | 3 主分类 × 4 次级分类已创建；现有 3 篇文章已分配主分类+次级分类 |
| M1 Unit 4: Hero/About/Contact 字段 | ✅ | Carbon Fields Theme Options 已创建并填充第一版内容；邮箱已更新为 `ops@mindhikers.com` |
| M1 Unit 5: 首页模板 | ✅ | `front-page.php` 五区块渲染已部署；中文首页 `/` 已正常显示 CMS 数据 |

### M1 待执行 Unit

| Unit | 名称 | 状态 | 阻塞/备注 |
|------|------|------|-----------|
| 6 | 双语渲染验证 + EN 页面收口 | ⏳ | `/en/` 仍路由到 Blog（需在 WP Admin → Polylang → 设置首页翻译） |
| 7 | M1 端到端验收 | ⏳ | 待 Unit 6 完成后执行老卢操作流程验证 |

### 当前 Staging 首页 (`/`) 验证结果

| 检查项 | 结果 |
|--------|------|
| 首页 `/` 200 | ✅ |
| Hero 显示 CMS 内容 | ✅ |
| About 显示品牌定位原文 | ✅ |
| Product 只显示当前语言产品 | ✅ |
| 黄金坩埚状态 = "构思中" | ✅ |
| Contact 显示 `ops@mindhikers.com` | ✅ |
| 社交矩阵显示 Twitter/Bilibili/微信 | ✅ |
| 无 PHP Fatal / 500 | ✅ |
| Blog 区显示文章 | ❌ 显示"暂无文章"（需排查 `WP_Query`） |
| `/en/` 显示英文首页 | ❌ 仍路由到 Blog（Unit 6 待配置） |

### 新部署文件

| 文件 | 位置 | 说明 |
|------|------|------|
| Child Theme | `wordpress/themes/astra-child/` | 已打包上传 staging |
| MU Plugin | `wordpress/mu-plugins/mindhikers-m1-core.php` | CPT + Carbon Fields 字段定义 |
| Seed 脚本 | `wordpress/mu-plugins/m1-seed.php` | 内容批量填充脚本（已执行） |
| Carbon Fields | `wp-content/plugins/carbon-fields/` | v3.6.9（Composer 安装） |

### Polylang 配置详情

- **版本**: Polylang 3.8.2 (Free)
- **语言列表**: 中文 (zh_CN, slug: zh, 默认) + English (en_US, slug: en)
- **URL 模式**: 目录模式，`hide_default: true`（中文隐藏前缀，英文 `/en/`）
- **当前 `/en/` 行为**: 路由到 Blog 页面（尚无 English 首页翻译，需 Unit 6 在 WP Admin 配置）

### 关键 API 发现

| 目的 | API | 方法 |
|------|-----|------|
| 写 `<title>` / `<meta description>` | `/surerank/v1/page-seo-checks/fix` | POST `type:"content-generation"`, `input_key`, `input_value`, `id` |
| 写 og/twitter 字段 | `/surerank/v1/post/settings` | POST `post_id`, `metaData: {og_title, og_description, ...}` |
| 读写全局 SEO 设置 | `/surerank/v1/admin/global-settings` | GET/POST `data: {...}` |
| ⚠️ 会崩溃 | `GET /surerank/v1/post/settings?post_id=&post_type=` | 不要用 |

### 已知限制（非阻塞）

1. `/en` 当前路由到 Blog（Polylang 尚未配置首页翻译，Unit 6 处理）
2. `GET /surerank/v1/post/settings` 会触发 WordPress 致命错误
3. km-logo.svg 仍在 HTML 中但已被 CSS `display: none` 隐藏
4. staging 保持 `noindex`
5. `homepage-staging.mindhikers.com` 未建立
6. Blog 首页区块 `WP_Query` 返回 0 篇文章，显示"暂无文章"（分类和归类已做，需排查 Polylang/查询参数）
7. Footer 仍显示旧邮箱 `contactmindhiker@gmail.com`（Astra Footer 未覆盖）

### 下一窗口建议

#### 优先级 P0：完成 M1 收尾
1. **Unit 6**: 在 WP Admin → Polylang → 语言 → 设置中，将英文首页（需新建或关联现有英文首页页面）设为 `/en/` 的静态首页
2. 排查 Blog `WP_Query` 为什么返回 0 篇文章（可能 Polylang 默认过滤导致，或文章语言标记与查询不匹配）
3. 修复 Product 详情页入口链接未显示问题（`carbon_set_post_meta` 在 CLI 下可能未生效）

#### 优先级 P1：M1 验收
4. **Unit 7**: 执行老卢端到端操作流程验证（修改 Hero → 新增测试产品 → 改博客分类）
5. 更新 `docs/dev_logs/HANDOFF.md` 为 M1 完成状态

#### 优先级 P2：视觉与域名
6. staging 域名：建立 `homepage-staging.mindhikers.com`
7. 首页内容精修：Hero 人像图、背景轮播、区块重排等第二轮精修

### 关键认证信息

- 登录方式：`POST wp-login.php` cookie 登录（密码 `+` 需 URL encode 为 `%2B`）
- REST nonce：从 `wp-admin/` HTML 中提取 `wpApiSettings.nonce`（会过期）

### 当前可用凭据

- 用户名：`mindhikers_admin`
- 密码：`IW0pGAFhiydfFg3GC5xxgl+L`

### 当前不要做的事

1. 不要回到旧的 Next.js 前台路线
2. 不要在生产环境直接试错
3. 不要提前取消 staging 的 `noindex`
4. 不要把 `/` 和 `/en` 的语言职责重新混在一起
5. 不要盲改 SureRank 字段名——必须用 `page-seo-checks/fix` 路径
6. 不要把 `dev_progress.md` 迁到 `docs/04_progress/`——本项目用 `docs/dev_logs/` 即可
7. 不要卸载 Elementor——其他页面可能仍需要它
8. 不要删除 `wordpress/mu-plugins/mindhikers-cms-core.php`（旧 headless 插件），只保留不启用即可

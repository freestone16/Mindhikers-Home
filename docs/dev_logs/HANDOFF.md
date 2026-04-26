🕐 Last updated: 2026-04-26 16:30 CST
🌿 Branch: `experiment/wp-traditional-mode`
📌 Latest commit: `d8d2233` refs MIN-30 docs: update handoff after P2 baseline cleanup complete
🚀 Push status: ✅ 已 push，staging 已自动部署

---

## 当前状态：第二段基线清理全部完成，REST API 全字段验证通过

一句话：product / blog 字段空值已修复，sync-bundle.sh 临时诊断代码已清理，容器首次启动自动执行 seed 机制已建立。API ZH/EN 全字段返回正常。

---

## 本窗口完成内容

### 第一段回顾（P0-P1）

1. ✅ **根因定位** — `m1-seed.php` 写入 Carbon Fields theme options，但 REST API 读的是 `mh_homepage` post meta JSON，两者数据层不互通
2. ✅ **修复方案实施** — 在 `bootstrap.php` 中添加 `buildHomepagePayloadFromCarbonFields()` fallback 方法
3. ✅ **P1 API 验证通过** — hero / about / contact 字段返回正常

### 第二段完成（P2）

4. ✅ **清理 sync-bundle.sh 临时诊断代码** — 移除 `check-rest.php` 创建逻辑
5. ✅ **修复 product / blog 字段映射** — 在 `mindhikers-m1-core.php` 中补充缺失的 Carbon Fields 字段定义（`product_title_zh/en`、`product_desc_zh/en`、`blog_title_zh/en`、`blog_desc_zh/en`）
6. ✅ **建立自动 seed 机制** — 修改 `sync-bundle.sh`，容器首次启动时自动执行 `m1-seed.php`，并创建 `.m1-seed-executed` 标志防止重复执行
7. ✅ **重新部署并验证** — staging 自动 rebuild，seed 成功执行，API 全字段验证通过

### 最终 API 验证结果

**ZH:**
- `hero.title` = "心行者 MindHikers"
- `hero.description` = "研究复杂问题 · 制作清晰表达 · 实验产品化路径"
- `hero.quickLinks` = 2（黄金坩埚、碳硅进化论）
- `product.title` = "Product"
- `product.description` = "一个围绕研究、写作、表达与创作者工作流展开的产品实验。"
- `blog.title` = "碳硅进化论"
- `blog.description` = "三篇「碳硅进化论」文章已经上线，讨论 AI 时代的教育、肉身经验与伦理成长。"
- `contact.title` = "Contact"
- `contact.description` = "有合作想法，或者单纯想聊聊？"
- `contact.email` = "hello@mindhikers.com"

**EN:**
- `hero.title` = "A brand home for research, products, and writing that still feels alive."
- `hero.quickLinks` = 2（Golden Crucible、Carbon-Silicon Evolution）
- `product.title` = "Product"
- `blog.title` = "Carbon-Silicon Evolution"
- `contact.title` = "Contact"

---

## 根因详细说明

**不是** Carbon Fields 加载时序问题。

真正原因（两段修复）：
1. **第一段**：`m1-seed.php` 把数据写进了 **Carbon Fields theme options**，但 REST API 读的是 **`mh_homepage` post meta JSON**，两者数据层不互通
   - 修复：在 `bootstrap.php` 的 fallback 路径中增加从 Carbon Fields 读取数据的逻辑
2. **第二段**：`mindhikers-m1-core.php` 中**缺少 Product 和 Blog 区块的 theme options 字段定义**
   - seed 脚本写入了 `product_title_zh`、`blog_desc_zh` 等字段，但 Carbon Fields 不认识它们，导致数据无法正确存储和读取
   - 修复：在 `mindhikers-m1-core.php` 中补充定义了所有缺失的字段

---

## 关键代码变更

### 1. `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php`
- 新增 `buildHomepagePayloadFromCarbonFields()` 方法
- 从 CF theme options 读取 hero / about / product / blog / contact / quickLinks / socialMatrix 等全部字段
- 当 `mh_homepage` post 缺失时自动 fallback

### 2. `wordpress/mu-plugins/mindhikers-m1-core.php`
- 新增 "Product 区块" theme options 容器（`product_title_zh/en`、`product_desc_zh/en`）
- 新增 "Blog 区块" theme options 容器（`blog_title_zh/en`、`blog_desc_zh/en`）

### 3. `ops/mindhikers-cms-runtime/sync-bundle.sh`
- 移除临时诊断代码（`check-rest.php` 创建逻辑）
- 新增首次启动自动执行 seed 逻辑（创建 `.m1-seed-executed` 标志防止重复执行）

### 4. `ops/mindhikers-cms-runtime/Dockerfile`
- 新增 `COPY ops/wordpress/run-seed-web.php /var/www/html/run-seed-web.php`（备用方案，未实际使用）

---

## 下一步行动计划

### P3：完整验证清单（建议执行）

1. WP Admin 可登录
2. 插件列表有 Carbon Fields + Polylang
3. 主题列表有 Astra Parent + Astra Child
4. REST API `/wp-json/mindhikers/v1/homepage/zh` 返回 200 + 非空 JSON
5. JSON 含 `hero.quickLinks`
6. 前台 Next.js 消费 API 正常（如果可能）

### P4：推进 04-23 Playbook（待你决策）

当前两段阻塞已全部解除，可以继续按 04-23 Playbook 推进：
- Phase 0：production 备份（需你执行）
- Phase 1：Dockerfile 改造 + 插件清理
- Phase 2：Code Snippets 退役
- Phase 3：主题重命名 + 补齐模板

### P5：架构决策 — 统一数据层（方案A，强烈推荐）

当前系统存在**双层数据存储**的根本缺陷：
- **抽屉A**：Carbon Fields theme options（由 `m1-seed.php` 自动写入）
- **抽屉B**：`mh_homepage` post meta JSON（由 WP Admin 编辑写入）
- **问题**：两者永不同步，API 返回什么取决于 `mh_homepage` post 是否存在 —— 运营人员不可控

**第一性原理**：一个内容块，一个存储位置，一个编辑入口。

**方案A目标**：统一用 `mh_homepage` post meta JSON 作为唯一数据源，彻底消除双层歧义。

#### 方案A实施步骤

**步骤1：修改 `m1-seed.php` — 创建 `mh_homepage` post 而不是写 CF**

当前 `m1-seed.php` 使用 `carbon_set_theme_option()` 把数据写入 CF。改为：
1. 加载 `wp-load.php` 和 `bootstrap.php`（或直接使用 `wp_insert_post` + `update_post_meta`）
2. 读取 `ops/wordpress/homepage-seeds/homepage-zh.json` 和 `homepage-en.json`
3. 检查是否已存在 `locale=zh` 和 `locale=en` 的 `mh_homepage` post
4. 如果不存在，创建两条 post：
   ```php
   $post_id = wp_insert_post([
       'post_type'   => 'mh_homepage',
       'post_title'  => 'Homepage ZH',
       'post_status' => 'publish',
   ]);
   update_post_meta($post_id, 'mindhikers_locale', 'zh');
   update_post_meta($post_id, 'mindhikers_homepage_payload', $json_string);
   ```
5. 对 EN 重复同样操作

**步骤2：验证 API 返回完整 JSON**

```bash
curl -sL "https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/zh" | jq '{hero: .hero.title, product: .product.title, blog: .blog.title, contact: .contact.title}'
```
应看到所有字段非空。

**步骤3：清理 `bootstrap.php` 中的 CF fallback 代码**

删除或注释掉：
- `buildHomepagePayloadFromCarbonFields()` 方法（~130行）
- `getDefaultHomepagePayload()` 中对 `buildHomepagePayloadFromCarbonFields()` 的调用
- 恢复为直接返回 `normalizeHomepagePayload([], $locale)`

**步骤4：清理 `mindhikers-m1-core.php` 中的 homepage 相关 CF 字段**

删除以下容器（保留 Product CPT 和 Revalidate 配置）：
- "Hero 管理" theme options 容器
- "About 管理" theme options 容器
- "Contact 管理" theme options 容器
- "Product 区块" theme options 容器（步骤2中新增的）
- "Blog 区块" theme options 容器（步骤2中新增的）

**保留**：
- Product CPT 的 post meta 字段（`product_subtitle`、`product_status` 等）—— 这些是产品详情页用的，不是首页区块
- Revalidate 配置

**步骤5：更新 `sync-bundle.sh` 自动 seed 逻辑**

当前 `sync-bundle.sh` 在首次启动时执行 `m1-seed.php`。由于 seed 脚本已改为创建 post 而不是写 CF，需要确认：
- `.m1-seed-executed` 标志是否需要清除以触发重新 seed
- 或者手动执行 seed（通过 WP CLI 或 web runner）

**步骤6：验证运营编辑流程**

1. 登录 WP Admin → Mindhikers Homepages
2. 编辑 zh 或 en 的 JSON payload
3. 保存后 curl 验证 API 返回新值
4. 确认没有 Carbon Fields 的 homepage 表单干扰（已删除）

#### 方案A影响范围

| 文件 | 变更 | 备注 |
|---|---|---|
| `ops/wordpress/m1-seed.php` | 重写 | 从写 CF 改为创建 `mh_homepage` post |
| `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php` | 删除 ~130 行 | 移除 `buildHomepagePayloadFromCarbonFields()` |
| `wordpress/mu-plugins/mindhikers-m1-core.php` | 删除 ~80 行 | 移除 Hero/About/Contact/Product/Blog 的 CF 容器 |
| `ops/mindhikers-cms-runtime/sync-bundle.sh` | 可能需要调整 | 确认 seed 执行时机和标志逻辑 |

#### 方案A验收标准

- [ ] `m1-seed.php` 执行后创建两条 `mh_homepage` post（zh + en）
- [ ] API `/homepage/zh` 和 `/homepage/en` 返回 200 + 完整 JSON（不依赖 CF）
- [ ] 删除 `buildHomepagePayloadFromCarbonFields()` 后 API 仍然正常
- [ ] WP Admin 只剩一个 homepage 编辑入口（`mh_homepage` post 的 JSON 编辑框）
- [ ] 运营人员修改 JSON 后，API 即时生效

#### 方案A风险提示

1. **删除 CF 字段后无法回滚**：如果删除 CF 字段定义，已存储的 CF 数据不会丢失（仍在数据库 `wp_options` 中），但 WP Admin 界面不再显示这些字段。如需回滚，只需恢复字段定义代码。
2. **现有 staging/production 数据**：staging 上已有 seed 写入的 CF 数据，执行新 seed 后会创建 post，API 立即切换数据源。需提前验证新 seed 创建的 post 数据完整性。
3. **Next.js 前台缓存**：如果前台使用 ISR 缓存，修改 post meta 后需要等待缓存失效或手动触发 revalidate。当前 CMS Core 的 `saveHomepageMeta()` 已包含 revalidate webhook 触发逻辑，应自动生效。

#### 如果不执行方案A的风险

- 运营人员永远面对两个不可同步的数据源
- API 行为不可预测（取决于 post 是否存在）
- 任何 future bug 都可能引发"改了数据但前台不更新"的困惑
- 技术债累积，04-23 Playbook 推进时需要额外处理数据层兼容

---

**决策点**：是否同意执行方案A？如果同意，可以从步骤1开始实施。如果希望先保持现状（双层并存），需要明确这是一个**临时补丁状态**，并记录后续清理计划。

---

## 离生产还有多远

1. ✅ push 实验分支
2. ✅ Railway staging Source 切换到 Git Repo
3. ✅ Dockerfile build 成功
4. ✅ 服务 Online（200）
5. ✅ 致命错误已修复（Astra + m1-seed + m1-rest 路径）
6. ✅ Seed 已执行，数据已写入
7. ✅ **REST API 空值阻塞已解除**
8. ✅ **product / blog 字段已修复**
9. ✅ **临时诊断代码已清理**
10. ✅ **自动 seed 机制已建立**
11. ⏳ 完整 P3 验证（含前台 Next.js）
12. ⏳ production Volume + MariaDB 备份
13. ⏳ production 部署
14. ⏳ 证明 mu-plugin schema 与 `mhs02` 等价
15. ⏳ 退役 snippets / Code Snippets
16. ⏳ 全站验收 + DNS 切换

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
- 当前 commit：`ccd8610`
- 当前问题：REST API 全字段正常，两段阻塞已全部解除
- 核心修复文件：
  - `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php`（CF fallback）
  - `wordpress/mu-plugins/mindhikers-m1-core.php`（新增 Product/Blog 字段定义）
  - `ops/mindhikers-cms-runtime/sync-bundle.sh`（自动 seed + 清理临时代码）
- 验证目标：P3 完整验证 → P4 Playbook 推进
- 工具：curl + Railway CLI 可用

---

## 关键文件索引

| 文件 | 作用 | 当前状态 |
|---|---|---|
| `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php` | CMS Core 主逻辑（含 CF fallback） | ✅ 已修改，API 正常 |
| `wordpress/mu-plugins/mindhikers-m1-core.php` | M1 Core（含 CF 字段定义） | ✅ 已添加 Product/Blog 字段 |
| `ops/wordpress/m1-seed.php` | Seed 脚本 | ✅ 自动执行成功 |
| `ops/wordpress/run-seed-web.php` | Web 触发 seed（备用） | ✅ 已添加 |
| `ops/mindhikers-cms-runtime/Dockerfile` | Docker 镜像构建 | ✅ 已更新 |
| `ops/mindhikers-cms-runtime/sync-bundle.sh` | Volume sync + 自动 seed | ✅ 已清理临时代码 |
| `wordpress/themes/astra/` | Astra 父主题 | ✅ 已加入仓库 |

(End of file)

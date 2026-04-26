🕐 Last updated: 2026-04-26 14:45 CST
🌿 Branch: `experiment/wp-traditional-mode`
📌 Latest commit: `0d65d6d` refs MIN-30 fix(cms-core): fallback to Carbon Fields when mh_homepage post is missing
🚀 Push status: ✅ 已 push，staging 已自动部署

---

## 当前状态：REST API 空值阻塞已解除

一句话：通过在 `bootstrap.php` 中添加 `buildHomepagePayloadFromCarbonFields()` fallback 方法，REST API 现已返回从 Carbon Fields theme options 读取的完整数据。P0 诊断完成，P1 验证通过。

---

## 本窗口完成内容

### 已完成步骤

1. ✅ **根因定位** — `m1-seed.php` 写入 Carbon Fields theme options，但 REST API 读的是 `mh_homepage` post meta JSON，两者数据层不互通
2. ✅ **修复方案实施** — 在 `bootstrap.php` 中添加 `buildHomepagePayloadFromCarbonFields()` 方法，当 `mh_homepage` post 缺失时自动从 CF 读取已 seed 的数据
3. ✅ **映射全部关键字段** — hero / about / product / blog / contact / quickLinks / socialMatrix
4. ✅ **Commit & Push** — `refs MIN-30 fix(cms-core): fallback to Carbon Fields...`
5. ✅ **Staging 自动部署** — Railway 检测到 push 后自动 rebuild
6. ✅ **P1 API 验证通过** —
   - ZH: `hero.title` = "心行者 MindHikers", `hero.description` = "研究复杂问题 · 制作清晰表达 · 实验产品化路径", `hero.quickLinks` 含黄金坩埚和碳硅进化论
   - EN: `hero.title` = "A brand home for research, products, and writing that still feels alive.", `hero.quickLinks` 含 Golden Crucible 和 Carbon-Silicon Evolution
   - `contact.title` = "Contact", `contact.description` = "有合作想法，或者单纯想聊聊？"

### 仍需关注

| 项目 | 状态 | 说明 |
|---|---|---|
| product 区块 | ⚠️ 部分空值 | `product.title` / `product.description` 返回空，seed 中虽有 `product_title_zh` 但可能未正确写入或字段名不匹配 |
| blog 区块 | ⚠️ 部分空值 | `blog.title` / `blog.description` 返回空，同理需核对 CF 字段名 |
| 前台 Next.js | ⏳ 待验证 | 当前仅验证了 WP REST API，未验证 Next.js 消费端是否正常渲染 |

---

## 根因详细说明

**不是** Carbon Fields 加载时序问题。

真正原因：
1. `m1-seed.php` 把数据写进了 **Carbon Fields theme options**（如 `hero_title_zh`）
2. REST API `getHomepageByLocale()` 读的是 **`mh_homepage` post meta JSON**（`mindhikers_homepage_payload`）
3. **Seed 脚本没有创建 `mh_homepage` 文章**
4. 因此 API 读到空 post，返回默认空值（`normalizeHomepagePayload([], 'zh')`）

两个数据层完全不通。修复方法是在 fallback 路径（`getDefaultHomepagePayload`）中增加从 Carbon Fields 读取数据的逻辑，这样无论哪个 REST route 胜出（旧的 `m1_rest_homepage` 还是新的 `getHomepageByLocale`），都能拿到有效 payload。

---

## 下一步行动计划

### P2：基线清理（建议立即执行）

1. **清理临时诊断代码**
   - 从 `sync-bundle.sh` 中移除所有临时诊断代码（`check-cf.php`、`check-rest.php`、`run-seed.php` 创建逻辑）
   - 如果服务器上还残留 `debug-probe.php`，确认并清理

2. **验证 product / blog 字段映射**
   - 检查 `carbon_get_theme_option('product_title_zh')` 和 `carbon_get_theme_option('blog_title_zh')` 在 staging 上是否实际有值
   - 如果 seed 写入的字段名与读取的字段名不匹配，修正映射

3. **Commit 清理改动**

### P3：完整验证清单

1. WP Admin 可登录
2. 插件列表有 Carbon Fields + Polylang
3. 主题列表有 Astra Parent + Astra Child
4. REST API `/wp-json/mindhikers/v1/homepage/zh` 返回 200 + 非空 JSON
5. JSON 含 `hero.quickLinks`
6. 前台 Next.js 消费 API 正常（如果可能）

### P4：推进 04-23 Playbook（待你决策）

当前 P0 阻塞已解除，可以继续按 04-23 Playbook 推进：
- Phase 0：production 备份（需你执行）
- Phase 1：Dockerfile 改造 + 插件清理
- Phase 2：Code Snippets 退役
- Phase 3：主题重命名 + 补齐模板

---

## 离生产还有多远

1. ✅ push 实验分支
2. ✅ Railway staging Source 切换到 Git Repo
3. ✅ Dockerfile build 成功
4. ✅ 服务 Online（200）
5. ✅ 致命错误已修复（Astra + m1-seed + m1-rest 路径）
6. ✅ Seed 已执行，数据已写入
7. ✅ **REST API 空值阻塞已解除**
8. ⏳ 清理临时诊断代码
9. ⏳ 验证 product / blog 字段完整性
10. ⏳ 完整 P1 验证（含前台 Next.js）
11. ⏳ production Volume + MariaDB 备份
12. ⏳ production 部署
13. ⏳ 证明 mu-plugin schema 与 `mhs02` 等价
14. ⏳ 退役 snippets / Code Snippets
15. ⏳ 全站验收 + DNS 切换

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
- 当前 commit：`0d65d6d`
- 当前问题：REST API 空值已修复，但 product / blog 字段仍为空，需进一步核对
- 核心修复文件：`wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php`
- 验证目标：P2 清理 → P3 完整验证 → P4 Playbook 推进
- 工具：curl + Railway CLI 可用

---

## 关键文件索引

| 文件 | 作用 | 当前状态 |
|---|---|---|
| `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php` | CMS Core 主逻辑（含新 fallback） | ✅ 已修改，API 正常 |
| `ops/wordpress/m1-seed.php` | Seed 脚本（已执行） | ✅ 数据已写入 CF |
| `ops/mindhikers-cms-runtime/Dockerfile` | Docker 镜像构建 | ✅ 已修复 seed COPY |
| `ops/mindhikers-cms-runtime/sync-bundle.sh` | Volume sync + 临时诊断 | ⚠️ 含临时代码待清理 |
| `wordpress/mu-plugins/mindhikers-m1-core.php` | M1 Core mu-plugin | ✅ 已修复 m1-rest 路径 |
| `wordpress/themes/astra/` | Astra 父主题 | ✅ 已加入仓库 |

(End of file)

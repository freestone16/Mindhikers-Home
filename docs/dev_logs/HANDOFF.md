🕐 Last updated: 2026-04-23 (本窗口结束时)
🌿 Branch: `experiment/wp-traditional-mode`
📌 Base commit: `c67c2d7`
🚀 Push status: ❌ 不推远端（本地实验分支）

---

## 当前状态：WP 轻量定制实验 — Units 1-9 代码实施完成 🟡（待 WP 容器验证）

**一句话**：在 `experiment/wp-traditional-mode` 分支上完成了 WP 轻量定制模式的代码改造，让 WordPress 主题能直接读取 CMS Core 的 JSON 数据并渲染首页。代码已就绪，待本地 WP 容器验证。

### 本窗口核心成就

1. ✅ **方案制定**：用 ce:plan 制定了 `docs/plans/2026-04-23-001-feat-wp-lightweight-customization-plan.md`
2. ✅ **Unit 1**：CMS Core 新增数据桥接层 `getHomepageDataForTheme()` + WP Transients 缓存（6 小时 TTL）
3. ✅ **Unit 2**：`mh_homepage` post type 改为 `public => true`（可查询但不暴露 URL）
4. ✅ **Unit 3**：重写 `front-page.php` 通过 `mindhikers_get_homepage_data()` 获取数据
5. ✅ **Units 4-8**：重写 5 个 template-parts（hero/about/product/blog/contact）改用 JSON 数据
6. ✅ **Unit 9**：清理 Carbon Fields 依赖（注释掉 `require_once`，保留历史文件）
7. ⏳ **Unit 10**：缓存失效 + 双语验证（需 WP 容器运行）

### 关键改动文件

| 文件 | 改动 |
|---|---|
| `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php` | +数据桥接层 `getHomepageDataForTheme()`、`clearHomepageTransient()`、全局函数 `mindhikers_get_homepage_data()`；`public => true`；缓存失效钩子 |
| `wordpress/themes/astra-child/front-page.php` | 改用 `mindhikers_get_homepage_data()` 传递 payload |
| `wordpress/themes/astra-child/template-parts/hero.php` | 从 Carbon Fields 改为 `$payload['hero']` |
| `wordpress/themes/astra-child/template-parts/about.php` | 从 Carbon Fields 改为 `$payload['about']` |
| `wordpress/themes/astra-child/template-parts/product.php` | 从 Carbon Fields 改为 `$payload['product']` + 标准 post meta |
| `wordpress/themes/astra-child/template-parts/blog.php` | 从 Carbon Fields 改为 `$payload['blog']` |
| `wordpress/themes/astra-child/template-parts/contact.php` | 从 Carbon Fields 改为 `$payload['contact']` |
| `wordpress/themes/astra-child/functions.php` | 注释掉 Carbon Fields require |

### 技术决策

- **数据零迁移**：保留 `mh_homepage` JSON meta，主题直接读取
- **缓存策略**：WP Transients，按 locale 分离，6 小时 TTL，save_post 时失效
- **主题切换安全**：任何主题调用 `mindhikers_get_homepage_data($locale)` 即可获取数据
- **双语支持**：通过 `pll_current_language()` 或默认 'zh'，与 CMS Core `locale` meta 对应
- **REST API 兼容**：现有 `mindhikers/v1/homepage/{locale}` 完全不变，Next.js 可并行运行

---

## 📋 下一窗口开工 checklist

### 必做（验证）

1. [ ] 启动本地 WP 容器（`docker-compose up` 或本地 WP 环境）
2. [ ] 确保 `mh_homepage` post 存在（zh 和 en 各一个）
3. [ ] 访问 WP 前台首页 → 确认 5 个区块渲染正常
4. [ ] 编辑 CMS 内容 → 保存 → 确认前台 5 秒内更新（缓存失效）
5. [ ] 切换语言（Polylang）→ 确认双语内容互不干扰

### 应做（完善）

6. [ ] 测试主题切换：临时激活其他 WP 主题，调用 `mindhikers_get_homepage_data()` 验证数据可访问
7. [ ] 检查 REST API `mindhikers/v1/homepage/zh` 响应是否不变
8. [ ] 评估是否合并到 staging（老卢决策）

---

## ⚠️ 关键认知纠正（本窗口踩过的大坑，请务必记住）

### 1. REST 路由真正的注册点**不在 m1-rest 插件**

- 之前 HANDOFF 把 m1-rest 写成"REST API 注册核心"——**错**
- 真正注册 `/wp-json/mindhikers/v1/homepage/{locale}` 的是 `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php:190`
- m1-rest v1.4.0 包里**根本没有 `register_rest_route` 调用**，它只是个半成品数据格式化工具（有 `m1_build_hero` 但没挂上路由）
- 这就是为什么装了 v1.4.0 但 API 响应没变

### 2. Railway WP 容器**不通过 git push 部署代码**

- `ops/mindhikers-cms-runtime/Dockerfile` 只有 5 行，**没有 COPY wordpress/mu-plugins/**
- mu-plugins 被冻结在 Railway 持久 Volume 里，仓库改动对容器零影响
- 之前以为"push 就更新 WP 代码"——**错**，这是 P1 架构债

### 3. 本仓库 `wordpress/` 目录的真实作用**模糊**

- 之前以为它是 WP 容器的源，实则 Dockerfile 不引用它
- 只作为参考/存档存在；m1-rest 子目录已在本窗口删除（c67c2d7）
- 需要下个窗口明确定位：要么接入 Dockerfile，要么加 README 说明用途

### 4. m1-rest v1.4.0 本身是半成品

- 有：插件入口 `m1-rest.php`（版本号 1.4.0）、`homepage.php` 含 quickLinks 逻辑、`revalidate.php`
- 缺：**register_rest_route**（导致装了也没用）+ **Revalidate 配置后台菜单**（导致 URL/Secret 无法在 WP Admin 填写）
- 即：它只是一堆辅助函数，没有"挂钩"到 WP

---

## 🧯 当前 Production 依赖的 Code Snippets（重要！切勿删除）

在 Production WP Admin → Snippets 下，当前有 3 个补丁：

| 名称 | 用途 | 状态 | 是否可删 |
|---|---|---|---|
| `mhs` | 一次性尝试清理旧 m1-rest 目录 | Run Once 已执行 | ✅ 可删除 |
| **`mhs02`** | **重注册 `/homepage/(?P<locale>zh\|en)` 路由，override=true，调 `m1_build_hero` 返回新 schema** | **🔴 Active，产线依赖中** | ❌ **绝不能删**（删了 API 立刻退回旧 schema） |
| `mhs03` | 一次性写入 `mh_nextjs_revalidate_url` 与 `mh_revalidate_secret` option | Run Once 已执行 | ✅ 可删除 |

**`mhs02` 替代方案**：待中长期架构修复后，把等效逻辑回迁到 `mindhikers-cms-core/bootstrap.php` 或在 m1-rest 里补齐 `register_rest_route`，才能下线此 snippet。

---

## 🔄 本窗口实验分支状态

**分支**: `experiment/wp-traditional-mode`
**目标**: 验证 WP 轻量定制模式（主题直接读取 CMS Core JSON）
**代码完成度**: 9/10 Units（Units 1-9 完成，Unit 10 待验证）
**是否可合并**: 需先通过 Unit 10 验证（WP 容器运行）

---

---

## 🏗️ 架构债清单（中长期任务，按优先级）

### P1 · WP 容器无代码部署通道 🔴

- **问题**：`ops/mindhikers-cms-runtime/Dockerfile` 不 COPY mu-plugins，仓库是展示橱窗而非源
- **影响**：所有 WP 代码变更都要走手动 ZIP 上传 / Code Snippets 补丁，不可追溯、不可 revert
- **方案方向**：改 Dockerfile 把 `wordpress/mu-plugins/mindhikers-cms-core` 直接 COPY 进镜像；或改走 composer 拉 git repo
- **阻塞**：需评估 Railway 持久 Volume 与镜像 COPY 的优先级关系（mu-plugins 是否在 Volume 内）

### P2 · m1-rest 插件半成品 🟠

- **问题**：v1.4.0 缺 `register_rest_route` + 缺后台菜单
- **两个选择**：
  - **选 A**：把 m1-rest 补完整（补 route 注册 + Carbon Fields 后台菜单），走正式插件渠道
  - **选 B**：放弃 m1-rest，所有逻辑回迁到 `mindhikers-cms-core`，一个 mu-plugin 搞定
- **推荐 B**：避免两套插件互相抢函数名（本轮就差点翻车）

### P3 · 运维手册与实际架构脱节 🟠

- 本轮发现的认知误区未沉淀：`wordpress/` 目录用途、Dockerfile 不 COPY、route 真正注册点
- 需在 `docs/operations-guide-headless.md` 加"容器部署通道现状"一节

### P4 · `/api/revalidate` 路由 tag 分支有问题 🟡

- 现象：POST 传 `{"tag":"homepage-zh"}`，Next.js 响应 `tag: "blog-posts"`（走了 legacy 路径分支）
- 位置：`src/app/api/revalidate/route.ts:50-60`
- 可能原因：Railway/CDN 剥离 body，或 `isValidCacheTag` 校验误拒
- 影响：轻微——legacy 分支照样调 revalidatePath("/" + "/en")，页面能刷新；但 tag 精准失效没实现

### P5 · `wordpress/` 目录定位不清 🟡

- 仓库里有 `wordpress/mu-plugins/mindhikers-cms-core/` 但容器不用
- 需要：加 README 说明"此为参考，容器实际运行在 Railway Volume 内"，或直接接入 Dockerfile

### P6 · 仓库根目录堆了 4 个 m1-rest zip 🟢

```
m1-rest-v1.2.0.zip
m1-rest-v1.3.0.zip
m1-rest-v1.3.1.zip
m1-rest-v1.4.0.zip
```
- 应加 `.gitignore` 过滤 `m1-rest-v*.zip`

### P7 · `rules.md` / `operations-guide` 关于 "push 即部署 WP" 的错误描述 🟢

- 需修正为"WP 容器当前不走 git 部署，需手动 ZIP 或 Dockerfile 改造"

---

## 📋 下一窗口开工 checklist

### 必做（短）

1. [ ] 验证 Railway production Next.js 已部署完新 main 分支（`95008e6`）
2. [ ] 访问 https://www.mindhikers.com（无痕窗口）核对 Quick Links 模块显示正确
3. [ ] 如未显示，去 WP Admin → Snippets 确认 `mhs02` 仍 Active；然后 `curl -X POST https://www.mindhikers.com/api/revalidate -H "x-revalidate-secret: <prod-secret>" -H "Content-Type: application/json" -d '{}'` 手动触发
4. [ ] 把 `m1-rest-v*.zip` 加入 `.gitignore`（P6）

### 应做（中）

5. [ ] 开 Linear issue "P2 · 合并 m1-rest 逻辑到 mindhikers-cms-core 并去掉 Code Snippets"（推荐方案 B）
6. [ ] 开 Linear issue "P1 · Dockerfile COPY mu-plugins，建立 git → 容器部署通道"
7. [ ] 写 `docs/plans/2026-04-2x_WP_Deployment_Channel_Refactor.md` 方案

### 应做（长）

8. [ ] 修 `/api/revalidate` tag 分支（P4）
9. [ ] `wordpress/` 目录加 README 或接入构建（P5）
10. [ ] 修正 `rules.md` + `operations-guide-headless.md`（P3 / P7）

---

## 🔑 关键配置（Production）

| 配置项 | 值 / 位置 |
|---|---|
| Next.js 域名 | `https://www.mindhikers.com` |
| WP CMS 域名 | `https://homepage-manage.mindhikers.com` |
| Railway Next.js 服务 | `Mindhikers-Homepage`（production 环境） |
| Railway WP 服务 | `WordPress-L1ta`（production 环境） |
| `REVALIDATE_SECRET` | Railway Variables + WP options `mh_revalidate_secret`（两侧同步，本窗口已配齐） |
| `WORDPRESS_API_URL` | `https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1`（本窗口新加） |
| `BLOG_SOURCE` | `wordpress`（本窗口新加） |
| 真正的 REST 路由注册点 | `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php:190`（mu-plugin） |
| 当前 production 路由实际源 | WP Code Snippets → `mhs02`（override 注册）🔴 |

---

## 🗺️ 模块维护地图（纠正版）

| 要改的东西 | 在哪改 | 生效方式 |
|---|---|---|
| 首页文案/图片（Hero/About/Contact/Quick Links） | WP Admin → Theme Options | 保存 → `carbon_fields_theme_options_container_saved` 钩子 → Revalidate webhook → 前台 5 秒内更新 |
| 博客文章 | WP Admin → 文章 | 同上（tag: `blog-posts`） |
| 产品 | WP Admin → 产品 | 同上（tag: `product-{slug}` + `homepage-zh/en`） |
| 前台布局/交互 | 本仓库 `src/**` | `main` 分支 push → Railway Next.js 自动构建 |
| 前台样式 | `src/app/globals.css` + Tailwind | 同上 |
| **WP REST API 逻辑** | **真实源：Railway WP 容器 Volume 内的 mu-plugins（不在本仓库）** | **当前唯一手段：Code Snippets 补丁 或 WP Admin 上传插件 ZIP；git push 对 WP 零作用** |
| Revalidate Secret | Railway Variables + WP Admin 或直接 update_option | 改完 Railway 端 → Next.js 重部署；WP 端立即生效 |

---

## 📜 本窗口完整时间线（详版）

### Phase 1 · 诊断（起点）

- 现象：staging 前端 Quick Links 渲染正常，但 production 前端显示旧面板
- 起初猜测：Railway production 未部署最新代码
- **转折 1**：读 `src/components/home-page.tsx` → 发现 main 分支版本不消费 `quickLinks` 字段 → 意识到代码没合过去
- **转折 2**：读 WP production API 响应 → schema 仍是旧的（statusLabel/availabilityLabel），说明 WP 端也没更新

### Phase 2 · 架构考古

- 老卢把 `wordpress/mu-plugins/m1-rest/` 目录误删 → 老杨一度以为是代码遗失
- 读 `ops/mindhikers-cms-runtime/Dockerfile` → 震惊：**5 行，只配 Apache，不 COPY 任何 WP 代码**
- 结论：仓库的 `wordpress/` 目录是展示橱窗，Railway 容器的 WP 代码在持久 Volume 里，永不被 git 更新
- 进一步审查 `/tmp/m1-rest-v1.4.0-inspect/`：发现 v1.4.0 **没有 register_rest_route 调用**

### Phase 3 · 真正注册点定位

- 在 `wordpress/mu-plugins/mindhikers-cms-core/bootstrap.php:190` 找到真正的 `register_rest_route`
- 但此文件也不在容器镜像里，只是本地参考
- 容器内实际运行的 mu-plugin 代码无法 diff，只能 override

### Phase 4 · 紧急方案落地（三个 Code Snippet）

- **mhs**（Run Once）：清理旧 m1-rest 目录（无实际影响但留痕）
- **mhs02**（Active）：核心补丁——`add_action('rest_api_init', ..., 100)` 注册 homepage 路由 `override=true`，调 `m1_build_hero` 返新 schema → production API 立刻返回 quickLinks
- **mhs03**（Run Once）：`update_option('mh_nextjs_revalidate_url', ...)` + `update_option('mh_revalidate_secret', ...)` → Revalidate webhook 配置就位

### Phase 5 · Next.js 端环境补齐 + 合并

- 诊断 webhook 返 500："REVALIDATE_SECRET is not configured"
- 老卢加 `REVALIDATE_SECRET` 到 Railway production → 500 依然（根因是 production 还缺 `WORDPRESS_API_URL` + `BLOG_SOURCE`）
- 补齐这 3 个环境变量，webhook 返 200
- 尝试 `git merge --ff-only staging` 到 main 失败（main 有 `8744c4f refs MIN-110 fix: switch production builder from DOCKERFILE to RAILPACK` 领先）
- 老卢决策 B 方案：`git merge --no-ff staging` → merge commit `95008e6`，推送 origin/main
- Railway production Next.js 自动触发构建

---

## ⚙️ 技术事实速查

- Next.js 16 `revalidateTag` 签名：`(tag: string, profile: string | CacheLifeConfig)`，第二参用 `"default"`
- Cache Tag 清单（`src/lib/cms/constants.ts`）：`blog-posts` / `homepage-zh` / `homepage-en` / `product-{slug}`
- WP mu-plugins 加载早于 plugins，同名 function 冲突会 fatal
- Railway systemic vars（从 staging 继承的那些）在 production 环境页显示"从 staging 带过来"，**不能直接改**；需要用 "+ New Variable" 显式覆盖

---

## 🚧 红线（本轮再次确认）

1. ❌ 不在 `main` 直接开发
2. ❌ 每次 commit 必须 `refs MIN-xx`（本轮所有 commit 都挂 MIN-164）
3. ❌ commit / push / merge 前显式请示老卢（本轮 no-ff merge 是老卢批准的 B 方案）
4. ✅ 治理文档与代码分开 commit
5. ✅ **新增**：绝不删除 production Code Snippets 中的 `mhs02`（会立即打穿产线）

---

## Cache Tag 规范

| 内容 | Tag | 来源 |
|---|---|---|
| Blog（全量） | `blog-posts` | `CACHE_TAG_BLOG` |
| Homepage ZH | `homepage-zh` | `getHomepageCacheTag("zh")` |
| Homepage EN | `homepage-en` | `getHomepageCacheTag("en")` |
| Product | `product-{slug}` | `getProductCacheTag(slug)` |

---

## 📌 Linear

- 本轮主 issue：[MIN-164](https://linear.app/mindhikers/issue/MIN-164)
- 建议新开子 issue：P1（Dockerfile）+ P2（插件合并）+ P3/P4/P5/P7（文档与小修）

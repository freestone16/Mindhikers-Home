# WP 单栈多模板改造 · 一次性彻底迁移方案

- **Plan ID**: 2026-04-23-002
- **Linear**: `MIN-30` 主 issue（父级/归属：`MIN-7 网站开发`；替代本次分支的 MIN-164 延展范围）
- **作者**: OldYang（开发大师）
- **对标诉求**: 老卢 2026-04-23 明确提出的三条——① 健壮易维护的多模板改造 ② 尽快灌内容（但接受改造完再灌）③ 第一性原理、不打补丁
- **路径选择**: **时间线 α**（纯净派、一次性彻底完成、不过渡、不分阶段灌内容）
- **前置决策**: 老卢 2026-04-23 对话中锁定
  - Next.js 前台 **退役**，DNS 切到 WP
  - "多模板" = WP Admin → 外观 → 主题 管理多套主题
  - 灌内容在**全部改造完成后**进行

---

## 一、第一性原理陈述

当前系统的核心矛盾是一个："**git 能管 Next.js，但管不到 WordPress 容器代码**"。

这一个根因派生出所有已知问题：

- Code Snippets 补丁（mhs02）必须存在，因为 mu-plugin 改不到容器
- m1-rest 半成品插件没法修复，因为 git 推送对容器零作用
- staging / production / 本地仓库三处 mu-plugin 代码漂移
- 实验分支做完也上不了 staging 验证
- 多模板改造无从谈起（主题包推不进去）

**奥卡姆剃刀**：修掉 Dockerfile 这一个原罪，所有补丁、漂移、阻塞同时消失。

---

## 二、终态架构

### 2.1 部署拓扑

```
┌─────────────────────────────────────────────────────────┐
│  DNS www.mindhikers.com                                 │
│        │                                                │
│        ▼                                                │
│  Railway WP 服务（production）                           │
│    │                                                    │
│    ├─ Docker image（from git）                          │
│    │    ├─ WordPress core                               │
│    │    ├─ wp-content/mu-plugins/mindhikers-cms-core/  │
│    │    ├─ wp-content/themes/astra-child/               │
│    │    ├─ wp-content/themes/<future-variant>/          │
│    │    └─ wp-content/plugins/<bundled>/ (可选)         │
│    │                                                    │
│    └─ Volume（仅持久化数据）                             │
│         ├─ wp-content/uploads/（用户上传图）              │
│         ├─ wp-config.php（DB 连接、密钥）                 │
│         └─ wp-content/plugins/（非 bundled 的）          │
│                                                         │
│  Railway MariaDB（不变）                                 │
└─────────────────────────────────────────────────────────┘
```

### 2.2 代码治理

- `wordpress/` 目录成为**真源**（source of truth）
- git push → Railway 重建镜像 → WP 代码生效（同 Next.js 部署体验）
- mu-plugin 版本、主题版本均可通过 git 回滚
- 多主题 = `wordpress/themes/<name>/`，WP 后台外观面板自由切换

### 2.3 下线清单

| 对象 | 处理 |
|---|---|
| Next.js 服务（Railway `Mindhikers-Homepage`） | 改造完切 DNS 后**停服**（Railway 保留 7 天可回滚，确认稳定后归档） |
| 仓库 `src/` Next.js 代码 | 改造完移入 `legacy/nextjs-frontend/` 归档，或全量删除（老卢决策） |
| `content/*.mdx` 博客文件 | 迁移进 WP 数据库后归档删除 |
| Code Snippet `mhs02` | Dockerfile 上线、mu-plugin 合入后删除 |
| Code Snippet `mhs`、`mhs03` | 已是 Run Once 状态，改造中一并删除 |
| 插件 `m1-rest` 1.3.0（staging） / 1.4.0（production） | 卸载并从 WP 数据库清理 option |
| `/api/revalidate` 路由 | 随 Next.js 退役而消失 |

---

## 三、工作分解（阶段顺序严格执行）

### Phase 0 · 准备（0.5 天）

**目标**：锁定基线，确保任何一步可回滚。

- [ ] P0.1 备份 production WP Volume 全量（`wp-content/uploads/`、`wp-config.php`、当前 mu-plugins 实际代码）
  - 方法：Railway dashboard → 服务 → Volumes → Download snapshot，或 `railway run tar czf backup.tgz /var/www/html/wp-content`
- [ ] P0.2 备份 production MariaDB 全量（`mysqldump`）
- [ ] P0.3 备份 staging 同样两份（虽然内容空，作为结构对照）
- [ ] P0.4 导出当前 production 插件清单（10 个）和激活状态
- [ ] P0.5 导出当前 production 所有 Code Snippets（mhs / mhs02 / mhs03）原始代码归档到 `docs/archive/2026-04-23_wp_snippets/`
- [ ] P0.6 在 `src/app/page.tsx` 等入口加 `console.log` 或 header 标记本次 Next.js 最后版本号（便于切换前后对比）

**验证点**：所有备份可解压、可读、可在本地 Docker 还原。

### Phase 1 · Dockerfile 改造 + 插件清理（1 天）

**目标**：git push 成为 WP 代码唯一部署通道。

- [ ] P1.1 改写 `ops/mindhikers-cms-runtime/Dockerfile`
  - `COPY wordpress/mu-plugins/ /var/www/html/wp-content/mu-plugins/`
  - `COPY wordpress/themes/ /var/www/html/wp-content/themes/`
  - 保留现有 Apache MPM 修正
  - 加 `VOLUME ["/var/www/html/wp-content/uploads"]` 声明
- [ ] P1.2 决策：哪些插件进 image、哪些留 Volume
  - **进 image**（稳定、版本锁定）：Carbon Fields、Polylang
  - **留 Volume**（可后台管理、不冻结）：Akismet、Elementor、SureForms、Starter Templates、SureRank、WPForms
  - **直接删**：M1 REST API（半成品，逻辑全部回迁 mu-plugin）
- [ ] P1.3 m1-rest 插件彻底下线
  - 把 v1.4.0 里有用的 `m1_build_hero` 等数据格式化函数**搬进** `wordpress/mu-plugins/mindhikers-cms-core/src/formatters/`
  - 确认 `mindhikers-cms-core/bootstrap.php:190` 的 `register_rest_route` 覆盖全部场景（homepage/blog/product）
  - 卸载 staging 和 production 的 M1 REST API 插件
  - 删除仓库根目录 4 个 `m1-rest-v*.zip`（P6）
- [ ] P1.4 staging 先部署验证
  - push Dockerfile 到 `experiment/wp-traditional-mode` 分支
  - Railway staging 重建 WP 镜像
  - 登录 staging WP Admin 确认：
    - 现有插件列表完整
    - uploads 目录图片在
    - 数据库连接正常
    - mu-plugin 版本来自仓库代码（echo 版本号到 header 确认）

**验证点**：staging WP 能起、能登录、能看到原有内容、`/wp-json/mindhikers/v1/homepage/zh` 返回正常 schema。

**回退点**：如任一验证失败，Railway Volume 快照还原 + image 回滚到上一版本。

### Phase 2 · Code Snippets 补丁退役（0.5 天）

**目标**：干掉所有 Code Snippets override，代码由 mu-plugin 正式管理。

- [ ] P2.1 对比 `mhs02` snippet 内容 vs 仓库 `mindhikers-cms-core/bootstrap.php` 的 REST 注册逻辑，确保后者逻辑完整覆盖
- [ ] P2.2 staging 验证通过后，production 也重建 image（Dockerfile 已验证）
- [ ] P2.3 在 production WP Admin → Snippets 停用 `mhs02`，观察前台 5 分钟（此时 Next.js 还在跑）
- [ ] P2.4 API 响应正常 → 删除 `mhs02`、`mhs`、`mhs03`
- [ ] P2.5 卸载 Code Snippets 插件本身（可选，如果后续不打算再依赖）

**验证点**：`curl https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh` 响应新 schema（含 quickLinks），与 mhs02 激活时完全一致。

**回退点**：如果响应 schema 退化，立即重启用 `mhs02`。

### Phase 3 · WP 主题补齐（3-5 天）

**目标**：`astra-child`（或独立的 `mindhikers-homepage` 子主题）能覆盖 Next.js 现有全站功能。

#### 3.1 主题重命名与结构规范（0.5 天）

当前 `astra-child` 目录既承担"实验分支轻量版"又可能误会为"临时"，建议改名：

- 方案 A：保留名字 `astra-child`，但明确为 mindhikers 官方主题
- 方案 B：重命名为 `mindhikers-child`，语义更清

**待老卢决策**。

#### 3.2 二级模板文件补齐（3-4 天）

按 Next.js 现有路由对应补模板：

| Next.js 路由 | 现有 `src/app/*` | WP 主题新增文件 | 工期 |
|---|---|---|---|
| `/`（zh 首页） | `page.tsx` → `home-page.tsx` | `front-page.php` ✅ 已有 | 0（复核） |
| `/en`（en 首页） | `en/page.tsx` | Polylang 路由 + 语言切换逻辑 | 0.5 天 |
| `/blog` | `blog/page.tsx` | `home.php` 或 `archive.php` | 0.5 天 |
| `/blog/[slug]` | `blog/[slug]/page.tsx` | `single.php` | 1-1.5 天（含 MDX→WP 迁移） |
| `/product/[slug]` | `product/[slug]/page.tsx` | `single-product.php` | 0.5 天 |
| `/en/product/[slug]` | `en/product/[slug]/page.tsx` | Polylang 自动处理 | 0 |
| `/golden-crucible` | `golden-crucible/page.tsx` | `page-golden-crucible.php` + 对应 WP page | 0.5 天 |
| `/en/golden-crucible` | `en/golden-crucible/page.tsx` | Polylang 镜像 | 0 |
| `/health` | `health/route.ts` | WP 插件或 `.php` 端点 | 0.1 天（可选） |

#### 3.3 交互元素迁移（1 天）

- [ ] P3.3.1 导航（navbar）：WP Menus + 主题 header.php
- [ ] P3.3.2 深浅色切换：纯 JS（localStorage + CSS variables），不引入 React
- [ ] P3.3.3 BlurFade 进入动画：IntersectionObserver + CSS transition，不引入 framer-motion
- [ ] P3.3.4 图标库：从 `lucide-react` 切到 Lucide 的纯 SVG 版本或 `@wordpress/icons`
- [ ] P3.3.5 shadcn/ui 组件：button / card / badge 等用纯 CSS 重绘（当前主题已用 Tailwind-like 手写 CSS）
- [ ] P3.3.6 字体加载：迁移 Next.js 的 `next/font` → WP 主题 `wp_enqueue_style` 或 @font-face

#### 3.4 双语（Polylang）接入（1 天）

- [ ] P3.4.1 确认 Polylang 已装（从截图看 staging 已装 Polylang 3.8.2）
- [ ] P3.4.2 `mindhikers-cms-core` 的 `mh_homepage` post type 注册 locale 字段
- [ ] P3.4.3 主题 `pll_current_language()` 取语言，传入 `mindhikers_get_homepage_data($locale)`
- [ ] P3.4.4 语言切换器（navbar 右上角）
- [ ] P3.4.5 URL 结构：`/` = zh、`/en/` = en（和 Next.js 保持一致）

### Phase 4 · 博客数据迁移（1 天）

**目标**：`content/*.mdx` 7 篇博客 → WP 文章。

- [ ] P4.1 写一次性脚本 `scripts/migrate-mdx-to-wp.ts`
  - 读 `content/*.mdx` 解析 frontmatter（title, publishedAt, summary, tags）
  - 通过 WP REST API 以 `mindhikers_admin` 身份 POST 文章
  - MDX 内容转 Markdown → WP Gutenberg block（可用 `marked` 或 `remark`）
  - MDX 特殊组件（`<MediaContainer>`, `<CodeBlock>`）映射到 Gutenberg block 或 shortcode
- [ ] P4.2 在 staging 执行迁移，确认 7 篇博客 WP 后台可见、前台 `/blog` 列表正常
- [ ] P4.3 production 执行同样迁移
- [ ] P4.4 迁移成功后，仓库 `content/` 目录归档到 `legacy/mdx-posts/` 或删除

**验证点**：staging `/blog` WP 主题渲染 7 篇博客，每篇详情页可读、代码高亮、图片显示正确。

### Phase 5 · staging 全站验证（1 天）

**目标**：staging 作为 production 的完整镜像，跑一次全站走查。

- [ ] P5.1 首页 zh / en 视觉对比 vs 线上 Next.js（截图比对）
- [ ] P5.2 博客列表 + 7 篇详情页
- [ ] P5.3 产品详情页（至少 2 个产品样本）
- [ ] P5.4 Golden Crucible 专题页
- [ ] P5.5 深浅色切换、语言切换
- [ ] P5.6 移动端响应式
- [ ] P5.7 WP 后台编辑 Hero → 保存 → 前台 5 秒内更新（缓存失效）
- [ ] P5.8 SEO meta（title/description/OG）
- [ ] P5.9 性能检查（Lighthouse 对比 Next.js 基线，可接受范围内 -10% 以内）

**通不过的项 → 回 Phase 3 修复**。

### Phase 6 · production 切换（0.5 天）

**目标**：DNS 切换、Next.js 退役。

- [ ] P6.1 production WP 完成 Dockerfile 部署（Phase 1-2 已做）+ 主题部署（Phase 3 产物）
- [ ] P6.2 production WP 执行博客迁移（Phase 4）
- [ ] P6.3 production WP 快速走查（参考 Phase 5 清单抽查）
- [ ] P6.4 **DNS 切换** `www.mindhikers.com` → Railway WP 服务
  - 降低 TTL 到 60s 提前 24 小时
  - 切换后立即验证
  - 保留 Next.js 服务 72 小时随时回滚
- [ ] P6.5 72 小时无问题后，Next.js 服务停机（Railway 保留实例但 suspend）
- [ ] P6.6 再 7 天后归档删除 Next.js 服务

**回退点**（72 小时内任何一刻）：DNS 切回 Next.js，回滚时间 ≤ 5 分钟。

### Phase 7 · 收尾与文档（0.5 天）

- [ ] P7.1 `src/` 目录归档到 `legacy/nextjs-frontend/` 或删除（老卢决策）
- [ ] P7.2 `content/*.mdx` 归档或删除
- [ ] P7.3 更新 `docs/04_progress/rules.md`：删除"push 即部署 WP"错误描述、新增"多模板切换通过 WP 外观面板"
- [ ] P7.4 更新 `docs/operations-guide-headless.md`（或改名 `docs/operations-guide.md`）
- [ ] P7.5 更新 `AGENTS.md` / `CLAUDE.md`：项目从"双栈"改为"WP 单栈"
- [ ] P7.6 关闭本次主 Linear issue，归档 MIN-164 实验分支

---

## 四、总工期与里程碑

| Phase | 工期 | 累计 |
|---|---|---|
| P0 准备 | 0.5 天 | 0.5 |
| P1 Dockerfile + 插件 | 1 天 | 1.5 |
| P2 Snippets 退役 | 0.5 天 | 2 |
| P3 主题补齐 | 3-5 天 | 5-7 |
| P4 博客迁移 | 1 天 | 6-8 |
| P5 全站验证 | 1 天 | 7-9 |
| P6 切换 | 0.5 天 | 7.5-9.5 |
| P7 收尾 | 0.5 天 | 8-10 |

**总工期：8-10 工作日（1.5-2 周）**。

**老卢灌内容的时间窗**：Phase 6 完成后，即第 8-10 个工作日之后。

---

## 五、风险清单

| 风险 | 概率 | 影响 | 缓解 |
|---|---|---|---|
| Railway Volume 备份不完整 | 低 | 致命 | P0 备份后在本地 Docker 还原一次验证 |
| Dockerfile COPY 后 uploads 丢失 | 中 | 高 | 明确 VOLUME 声明，验证 uploads 持久化 |
| Polylang 双语路由 + mu-plugin 冲突 | 中 | 中 | Phase 3.4 单独验证 |
| MDX 博客迁移格式丢失（代码块、媒体） | 中 | 中 | 迁移脚本先跑 staging，逐篇对比原文 |
| Next.js 退役后 SEO 降权（URL 变化） | 低（URL 保持一致） | 中 | WP 端 URL 结构与 Next.js 完全一致；保留 Next.js 72 小时过渡 |
| WP 主题性能不如 Next.js | 中 | 中 | Phase 5.9 基线比对；必要时加 WP 缓存插件（W3 Total Cache 或 WP Rocket） |
| BlurFade 等动画重写视觉不 1:1 | 中 | 低 | 老卢审美接受即可，不追求像素级一致 |

---

## 六、分支与提交策略

- **主分支**：当前 `experiment/wp-traditional-mode` 继续沿用，改造完成后**合并 main** 即实现切换
- **提交纪律**（遵 OldYang 红线）：
  - 每个 Phase 对应一个或多个 commit，commit message 格式 `refs MIN-30 phase-N: <动作>`
  - 治理文档（本 plan、rules、HANDOFF）与代码变更**分离提交**
  - Dockerfile 改动单独 commit（便于 revert）
  - 每次 commit 前向老卢显式请示
- **Linear issue 归属**：本次改造主 issue 为 `MIN-30`，父级/归属为 `MIN-7 网站开发`，子 issue 按 Phase 拆分

---

## 七、待老卢最终决策事项

以下事项不锁定就不能开工，请老卢逐条回复：

1. **Linear 主 issue**：已定 `MIN-30 · WP 单栈多模板改造`，挂在 `MIN-7 网站开发` 下。
2. **主题命名**：保留 `astra-child` 还是改 `mindhikers-child`？
3. **Phase 1 插件拆分**：Carbon Fields + Polylang 进 image（我的推荐），还是全部留 Volume？
4. **Next.js 代码处置**：改造完 `src/` 归档到 `legacy/nextjs-frontend/` 还是直接 `git rm`？
5. **备份方案**：你自己在 Railway dashboard 做 Volume snapshot + MariaDB dump，还是我通过 `railway` CLI 尝试（需要你授权 token）？
6. **BlurFade 等动画**：像素级复刻还是"接近就行"？
7. **Phase 6 DNS 切换**：你自己操作 Cloudflare / 域名提供商，还是我指导？（这一步我不能代劳）

---

## 八、执行节奏建议

按 α（一次性彻底）原则，建议：

- **今天**：老卢审核本 plan → 回复第七节 7 个决策 → 我开工 P0+P1
- **明天**：完成 P0 备份 + P1 Dockerfile staging 上线验证
- **第 3-4 天**：P2 Snippets 退役 + P3 主题补齐启动
- **第 5-8 天**：P3 主题补齐收尾 + P4 博客迁移 + P5 staging 全站验证
- **第 9 天**：P6 production 切换
- **第 10 天**：P7 收尾

---

## 附录 A · 本次改造与历史方案的关系

| 历史方案 | 关系 |
|---|---|
| `2026-04-12-001-feat-m1-cms-content-model-plan.md` | 数据模型层，本方案**继承不变**（`mh_homepage` post type、JSON meta） |
| `2026-04-18_Mindhikers_Headless_Pivot_Implementation_Plan.md` | Headless 架构方案，本方案**推翻**（改为 WP 单栈） |
| `2026-04-23-001-feat-wp-lightweight-customization-plan.md` | 实验分支的前身，本方案**完成它未完成的 80%** |

---

## 附录 B · 紧急回滚预案

改造过程中任一阶段失败，回滚顺序：

1. **代码回滚**：git revert 对应 commit + Railway 强制重建
2. **Volume 回滚**：Railway 快照恢复（P0 备份）
3. **DB 回滚**：MariaDB restore from dump（P0 备份）
4. **DNS 回退**：Cloudflare / 域名提供商面板切回 Next.js 服务
5. **mhs02 重启**：如果 Snippets 已删，从 `docs/archive/2026-04-23_wp_snippets/` 恢复

每阶段的具体回滚点在上文 Phase 节已标注。

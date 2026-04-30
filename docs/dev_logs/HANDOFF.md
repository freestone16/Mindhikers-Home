🕐 Last updated: 2026-04-30 CST
🌿 Branch: `staging`
📌 Latest commit on staging: `5851cb3` docs(handoff): record Plan B success and production exposure note
🚀 Push status: ✅ Plan B 已 push，staging build/deploy SUCCESS

---

## 给新窗口：5 分钟进入状态

**任务**：对 staging 做"深度验收"，把它调到完美无瑕，再决定 push 到 production 的方案。

**当前事实**（无需重新验证，已实测）：
- staging 前台已修好 502，HTTP 200，Next.js 16.1.7 在跑
- staging 后台 WordPress 在跑
- production 当前在跑 4-24 的旧镜像，**先别动**——等 staging 验收完再讨论合并方案
- 前一份 HANDOFF 把"前台 502"归因为 Railpack pnpm bug 阻塞构建——表面对，但漏看了 Railway 在新部署连续失败时**回滚到 9 小时前那次成功的 WordPress Dockerfile 镜像**。所以"前台 502 + service Online"的真相是：前台容器实际跑的是 Apache+WordPress

**修复路径（已闭环）**：
- 根因：Railpack 0.23.0 内置 pnpm 版本索引不支持 pnpm 9.x 系列
- 修法：`package.json` 加 `"packageManager": "pnpm@9.15.9"`（Corepack 标准），让 Railpack 改走 corepack 路径绕开自身 resolver
- 直接证据：build log 里出现 `copy /opt/corepack`

**关键文件**：
- `package.json` ← 这次修复在这里加了 `packageManager` 字段；之前的 `engines.pnpm` 已删
- `railway.json`（根目录）← `builder: "RAILPACK"`
- `ops/mindhikers-cms-runtime/railway.json` ← WordPress 服务专用配置（DOCKERFILE）
- `.dockerignore` ← 之前在 `8a0bf24` 加过的"排除 pnpm-lock.yaml"——可能已是冗余，验收时 review 一下要不要回退
- `docs/dev_logs/HANDOFF.md` ← 本文件

**Railway CLI 速查**（已在当前 cwd 链接到 `Mindhikers-Homepage` 项目）：
```bash
railway status                                       # 看当前 project/env/service 链接
railway environment <staging|production>             # 切环境
railway service <Mindhikers-Homepage|WordPress-L1ta> # 切服务
railway deployment list --json                       # 列部署 + commit hash + status
railway logs --build <DEPLOYMENT_ID>                 # 看指定部署的 build log
railway logs --build --latest                        # 看最新（即使失败）的 build log
railway logs --deployment <ID>                       # 看 runtime log
railway logs --http --status ">=400" --lines 50      # 看 HTTP 错误
railway domain --service <name>                      # 看绑定的域名
```

---

## 验收域名 & 当前实测状态（2026-04-30）

### Staging（验收目标，✅ baseline 全绿）

| 角色 | 域名 | HTTP | baseline 实测 |
|---|---|---|---|
| 前台 Next.js | https://mindhikers-homepage-staging.up.railway.app | **200** | ✅ |
| 前台 health | https://mindhikers-homepage-staging.up.railway.app/health | **200** | ✅ |
| 后台 WordPress | https://wordpress-l1ta-staging.up.railway.app | 200 | ✅ |
| 后台 wp-admin | https://wordpress-l1ta-staging.up.railway.app/wp-admin | - | 未测，待验收 |
| ZH API | https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/zh | 200 | ✅ |
| EN API | https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/en | 200 | ✅ |

### Production（先不动）

| 角色 | 域名 | HTTP | 备注 |
|---|---|---|---|
| 主域 | https://mindhikers.com | 200 | 跑的是 4-24 旧镜像 commit `a7cabf16` |
| www | https://www.mindhikers.com | 301 → 主域 | |
| Railway 域 | https://mindhikers-homepage-production.up.railway.app | 200 | |
| 后台主域 | https://homepage-manage.mindhikers.com | 302（登录跳转） | |
| 后台 Railway 域 | https://wordpress-l1ta-production.up.railway.app | 200 | |
| ZH API（prod） | https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh | 200 | |

⚠️ production 当前能访问，但 main 分支最新 commit `8744c4f` 已把 builder 切到 RAILPACK，且 main 上的 `package.json` 还**没有** `packageManager` 字段。下次推 main 触发部署就会复现 staging 之前那 5 次失败。先把 staging 调好，再做 production 合并决策。

---

## 深度验收清单（新窗口请按此推进）

> 顺序建议：A → B → C → D → E → F → G。每完成一组，把状态打勾、记录证据。发现问题就修，每修一处单 commit 推 staging。

### A. 功能性 — 页面 & 路由（前台核心）

build log 已知 staging 生成的路由（来自 commit `a57bff5` 的 build 输出）：
```
○ /                              (Static, revalidate 5m)
○ /_not-found
ƒ /api/revalidate
ƒ /blog
● /blog/[slug]
ƒ /blog/[slug]/opengraph-image
ƒ /blog/opengraph-image
○ /en                            (Static, revalidate 5m)
○ /en/golden-crucible
ƒ /en/product/[slug]
○ /golden-crucible
ƒ /health
ƒ /opengraph-image
└ ƒ /product/[slug]
```

- [ ] **A1 中文首页 `/`**：HTML 完整渲染、首屏文案/图片/导航出齐
- [ ] **A2 英文首页 `/en`**：同上，i18n 文案切英
- [ ] **A3 中文 Golden Crucible `/golden-crucible`**：栏目页正常
- [ ] **A4 英文 Golden Crucible `/en/golden-crucible`**：同上
- [ ] **A5 博客列表 `/blog`**：文章列表渲染正常
- [ ] **A6 博客详情 `/blog/<某 slug>`**：随便挑一篇，正文+元数据+OG 图都正常
- [ ] **A7 产品详情 `/product/<某 slug>`** + `/en/product/<同 slug>`：两边数据一致
- [ ] **A8 health `/health`**：✅ 已验，返回 200
- [ ] **A9 404 `/_not-found`**：访问不存在 URL 时优雅降级
- [ ] **A10 OG image `/opengraph-image`**：返回 PNG/JPG 二进制
- [ ] **A11 `/blog/opengraph-image` 和 `/blog/[slug]/opengraph-image`**：博客页 OG 正常

### B. 数据流 — 前后台联通

- [ ] **B1 ZH API → ZH 首页**：直接 curl ZH API 拿 JSON，跟 `/` 渲染出来的内容字段对得上（标题、副标题、CTA、模块列表等）
- [ ] **B2 EN API → EN 首页**：同上
- [ ] **B3 缓存策略**：staging 首页 revalidate 5m。手动改 WordPress 后台某字段 → 等 5 分钟 / 调用 `/api/revalidate` → 验证前台更新
- [ ] **B4 `/api/revalidate` 端点**：测一下能不能正确触发缓存刷新（注意可能需要 secret token）
- [ ] **B5 看 staging deploy log**：`railway logs --deployment` 拉最新一段，看有没有 runtime error / unhandled rejection / 数据库连不上等
- [ ] **B6 image 资产**：首页/博客图片是否能正常加载（CMS 上传的图通常走 WordPress 域，注意 CORS 和 Mixed Content）

### C. 性能 & 资源

- [ ] **C1 Lighthouse**：跑一次 staging 首页（mobile），记录 Performance/Accessibility/Best Practices/SEO 四项分数
- [ ] **C2 首屏 TTFB / FCP / LCP**：Lighthouse 输出里看
- [ ] **C3 Bundle 体积**：build log 里 Next.js 路由表是否有"First Load JS shared by all"提示——看是不是异常大
- [ ] **C4 静态资产 cache-control**：curl `-I` 看 `_next/static/*.js/css` 的 cache header

### D. SEO & Metadata

- [ ] **D1 `<title>` / `<meta description>`**：每个核心页面看 head 是否完整
- [ ] **D2 `og:image` / `og:title` / `og:description`**：分享卡片字段齐全
- [ ] **D3 `<link rel="canonical">`**：避免 i18n 双 URL 重复内容
- [ ] **D4 `hreflang`**：中英双语相互声明
- [ ] **D5 `robots.txt`**：staging 应该是 `Disallow: /` 阻止索引（不要让 staging 进 Google），production 才允许爬
- [ ] **D6 `sitemap.xml`**：是否生成、是否覆盖所有路由
- [ ] **D7 favicon 与 apple-touch-icon**：齐

### E. 安全 & 头部

- [ ] **E1 HTTPS**：✅ Railway 默认提供
- [ ] **E2 HSTS**：curl `-I` 看是否有 `Strict-Transport-Security`
- [ ] **E3 CSP / X-Frame-Options / X-Content-Type-Options / Referrer-Policy**：建议至少有合理的 CSP 和 X-Content-Type-Options nosniff
- [ ] **E4 后台 wp-admin**：登录页是否走 HTTPS、是否有暴力登录限速插件
- [ ] **E5 暴露的环境变量**：检查 Railway service Variables 是否有不该被前台 bundle 进去的 secret（比如 WORDPRESS_REVALIDATE_TOKEN 这类）

### F. CMS 操作链路

- [ ] **F1 wp-admin 登录**：用维护账号登录 staging WordPress
- [ ] **F2 改一篇博客 → 保存草稿 → 预览 → 发布**：流程通畅
- [ ] **F3 媒体库上传**：图片上传后能不能在前台展示（路径、权限、CDN）
- [ ] **F4 双语切换**：Polylang 插件是否正常切换 zh/en
- [ ] **F5 carbon-fields 自定义字段**：编辑首页/产品页的自定义字段，发布后前台能否读到

### G. 部署链路稳健性

- [ ] **G1 触发一次"无修改部署"**：`railway redeploy` 或随便 push 一个 noop commit，验证 build 仍然 SUCCESS（确认 Plan B 修复稳定）
- [ ] **G2 看 build log**：是否还有警告（pnpm overrides / peer deps / next build warnings）需要消除
- [ ] **G3 review `.dockerignore` 的 pnpm-lock.yaml 排除规则**（commit `8a0bf24` 加的）：现在用 corepack 路径，可能已经冗余且会拖累后续 build。验证一下 lockfile 是否被 Railpack 利用、要不要回退这一行
- [ ] **G4 review 历史 5 次失败 commit**（`61f395d` ~ `a3140ba`）：是否值得 squash（看团队习惯，可不动）
- [ ] **G5 看 staging-after-fix 的 deploy log 有没有重启循环**：`railway logs --deployment` 看若干分钟运行下来稳不稳

---

## 验收通过后：production 推送方案（决策点）

**等 staging 全部勾完再做这一步**。三个备选：

| 方案 | 做法 | 优点 | 缺点 |
|---|---|---|---|
| (a) merge staging → main | `git checkout main && git merge staging && git push` | 一次性同步 | 把 staging 上 MIN-30 那批 debug 历史也一起带过去 |
| **(b) cherry-pick 最小修复** | 只挑 `18106e9` + `a57bff5` 到 main | main 历史干净，只引入必要修复 | 需多走一步 |
| (c) 不动 main | 等下次正常 release 一起合 | 最保守 | production 是定时炸弹（任何重新部署都炸） |

**第一性原理判断**应该等 staging 验收通过后再做：
- 如果验收发现 staging 还要改（比如 .dockerignore 回退、metadata 修复），那些改动也得带到 production，更应该一次性 merge / cherry-pick
- 如果 staging 完全 ok，那 (b) 是最干净的——只把"必要修复"带过去

---

## 本窗口完成内容（历史记录）

### 修复路径

1. **诊断纠偏**：上一份 HANDOFF 漏看了 Railway 自动回滚到 9 小时前 SUCCESS 镜像（DOCKERFILE + WordPress）的兜底机制，靠 `railway logs --deployment` 看到 `Apache/2.4.66 ... PHP/8.3.30 configured` 才纠回正确归因
2. **拿到原始 build 错误**（`railway logs --build --latest`）：
   ```
   using build driver railpack-v0.23.0
   ↳ Detected Node
   ↳ Using pnpm package manager
   ✖ Failed to resolve version 9.15.9 of pnpm
   railpack process exited with an error
   ```
3. **Plan A**（commit `18106e9`）：删 `engines.pnpm`。失败，但错误从 "9.15.9" 变 "9"——证实 Railpack 0.23.0 不支持 pnpm 9.x 任何版本
4. **Plan B**（commit `a57bff5`）：加 `"packageManager": "pnpm@9.15.9"`。**SUCCESS**——Railpack 改走 corepack 路径，直接从 npm 拉 pnpm 9.15.9
5. **HANDOFF 更新**（commit `5851cb3`）：本文件

### 关键技术发现（值得长期记忆）

> **Railpack v0.23.0 内置 pnpm 版本索引不包含 pnpm 9.x 系列**。
> 修法：`package.json` 设置 `"packageManager": "pnpm@<version>"`（Corepack 标准字段）。Railpack 看到这个字段会改走 corepack 路径，从 npm 直接装精确版本。
> 注意：`engines.pnpm` 字段反而会触发 Railpack 自身的 resolver，建议在用 Railpack + pnpm 9.x 时**只用 `packageManager`，不要再写 `engines.pnpm`**。

### 关键 commit

| hash | message | 说明 |
|---|---|---|
| `5851cb3` | docs(handoff): record Plan B success... | 本文件历史版本 |
| `a57bff5` | ops(railpack): add packageManager field for corepack-style resolution | ✅ Plan B 修复 |
| `18106e9` | ops(railpack): drop engines.pnpm to bypass Railpack version resolver | Plan A，删除有害的 engines.pnpm |
| `e01c584` | docs(handoff): update Railway build config split and RAILPACK pnpm bug status | 上一份 HANDOFF |
| `a3140ba` ~ `61f395d` | 5 次失败尝试 | 历史，可保留也可后续 squash（见 G4） |
| `125743e` | ops: add WordPress-specific railway.json for per-service build config | WordPress 配置分离，已生效 |

---

## 回退手段（仅 staging）

如果验收过程中需要回退本次修复：
```bash
git checkout staging
git revert a57bff5 18106e9  # 撤销 Plan A + Plan B
git push origin staging
```
> 回退后 staging 会立刻回到 502，因为 RAILPACK 又会被 pnpm 9.x 卡住。无确切理由不要回退。

---

## 给新窗口的快速命令

```bash
# 一行进入工作目录 + 看当前状态
cd /Users/luzhoua/Mindhikers/Mindhikers-Homepage && \
  railway status && \
  git log --oneline -5 && \
  curl -sS -o /dev/null -w "frontend staging: HTTP %{http_code}\n" https://mindhikers-homepage-staging.up.railway.app/ && \
  curl -sS -o /dev/null -w "backend staging:  HTTP %{http_code}\n" https://wordpress-l1ta-staging.up.railway.app/

# 看最新一次部署的 build log
railway logs --build --latest --lines 100

# 看运行时 log（看有没有 runtime error）
railway logs --deployment --lines 200
```

---

(End of file)

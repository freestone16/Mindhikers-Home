# Homepage Golden Test Report

## Metadata

- request_id: `gt-homepage-cms-linkage-2026-04-01`
- executed_at: `2026-04-01`
- actual_model: `N/A - repo does not contain formal OpenCode golden-testing entry; this run used Codex + agent-browser fallback`
- browser_execution: `agent-browser`
- status: `partial_pass`

## Test Summary

本次验证的目标是确认 Mindhikers Homepage 本地环境下是否已经进入“CMS 内容驱动前台 + 可触发 revalidate”的阶段。当前结论比上一轮更进一步：前台本地环境已经接上候选 CMS 地址，`/api/revalidate` 也已经能在携带 secret 时成功返回 `revalidated: true`；但候选 CMS 的 `zh` / `en` Homepage 接口仍是空内容，所以首页目前依然落在静态 fallback，而不是真正由 CMS 内容驱动。

## Evidence

- request 路径：
  - `docs/testing_reports/requests/2026-04-01_homepage_cms_linkage_request.md`
- report 路径：
  - `docs/testing_reports/2026-04-01_homepage_golden_test_report.md`
- status 路径：
  - `docs/testing_reports/status/2026-04-01_homepage_cms_linkage_status.json`
- 关键截图：
  - `docs/testing_artifacts/2026-04-01-homepage-zh.png`
  - `docs/testing_artifacts/2026-04-01-homepage-en.png`
  - `docs/testing_artifacts/2026-04-01-homepage-zh-after-env.png`
  - `docs/testing_artifacts/2026-04-01-homepage-en-after-env.png`
  - `docs/testing_artifacts/2026-04-01-revalidate-success.png`
  - `docs/testing_artifacts/2026-04-01-cms-empty-zh.png`
- 关键运行证据：
  - `agent-browser` 观测到 `/` 中文首页主标题为“把研究、产品与表达，排成一个有呼吸感的品牌入口。”
  - `agent-browser` 观测到 `/en` 英文首页主标题为 “A brand home for research, products, and writing that still feels alive.”
  - `agent-browser` 直接读取本地 `revalidate` 返回：
    - `{"path":"/","revalidated":true,"slug":null,"tag":"blog-posts"}`
  - `agent-browser` 直接读取候选 CMS `zh` 接口返回空内容骨架：
    - `metadata.title` 存在
    - 但 `hero.title`、`navigation.links`、`about`、`product`、`blog`、`contact` 等主要内容为空
  - 本地环境核对结果：
    - 当前已存在 `.env.local`
    - 已配置 `WORDPRESS_API_URL=https://wordpress-l1ta-production.up.railway.app`
    - 已配置本地 `REVALIDATE_SECRET`
  - 当前迁移资产已生成：
    - `ops/wordpress/homepage-seeds/homepage-zh.json`
    - `ops/wordpress/homepage-seeds/homepage-en.json`

## Verification Results

### Expected 1: `/` 可正常渲染中文首页主要内容

- 结果：`PASS`
- 证据：
  - 中文首页可打开
  - 页面含导航、Hero、Product、Blog、Contact 等主要区块
  - 页面截图见 `docs/testing_artifacts/2026-04-01-homepage-zh.png`

### Expected 2: `/en` 可正常渲染英文首页主要内容

- 结果：`PASS`
- 证据：
  - 英文首页可打开
  - 页面含英文 Hero、Product、Blog、Contact 等主要区块
  - 页面截图见 `docs/testing_artifacts/2026-04-01-homepage-en.png`

### Expected 3: `/api/revalidate` 不应再返回“未配置 secret”

- 结果：`PASS`
- 证据：
  - 本地 `.env.local` 已配置 `REVALIDATE_SECRET`
  - 浏览器直接访问带 secret 的 `/api/revalidate?path=/`
  - 返回正文：
    - `{"path":"/","revalidated":true,"slug":null,"tag":"blog-posts"}`
  - 截图见 `docs/testing_artifacts/2026-04-01-revalidate-success.png`

### Expected 4: 环境变量应能指向候选 CMS，并使首页从 CMS 读取内容

- 结果：`FAIL`
- 证据：
  - 当前仓库已有 `.env.local`，且已配置 `WORDPRESS_API_URL`
  - 候选 CMS `https://wordpress-l1ta-production.up.railway.app/wp-json/mindhikers/v1/homepage/zh` 可访问
  - 但该接口返回的主要内容字段仍为空
  - 首页仍渲染静态内容，说明当前读取链路因 CMS 内容不完整而继续走 fallback
- 说明：
  - 这里“当前首页未进入 CMS 驱动”不是因为前台没接上 CMS，而是因为当前 CMS 数据本身还不满足 `src/lib/cms/homepage.ts` 的就绪校验

## Bug Follow-up

### Bug Title

候选 CMS 已接通，但 Homepage 内容仍为空，导致前台继续使用 fallback

### Symptom

1. 首页能打开，但仍停留在静态 fallback 路径
2. 本地 `revalidate` 已成功
3. 候选 CMS 接口可访问，但主要内容字段为空

### Expected

1. 本地环境应配置 `WORDPRESS_API_URL` 指向当前 CMS 候选实例
2. 前台应配置 `REVALIDATE_SECRET`
3. CMS 中应有完整的 `zh` / `en` Homepage 内容
4. WordPress 侧应配置 `MINDHIKERS_REVALIDATE_ENDPOINT` 与 `MINDHIKERS_REVALIDATE_SECRET`
5. 后台修改内容后，前台 `/` 与 `/en` 应可见变化

### Actual

1. 本地已配置有效 CMS 环境变量
2. 本地 `revalidate` 接口已进入可用态
3. 候选 CMS 返回空内容骨架
4. 浏览器当前只能确认静态页面与 fallback 正常

### Reproduction Script / Steps

1. 在仓库根目录运行 `npm run dev`
2. 让 Next.js 加载 `.env.local`
3. 用 `agent-browser` 打开 `http://127.0.0.1:3000/api/revalidate?secret=<local-secret>&path=/`
4. 观察接口返回 `revalidated: true`
5. 用 `agent-browser` 打开 `https://wordpress-l1ta-production.up.railway.app/wp-json/mindhikers/v1/homepage/zh`
6. 观察 `hero.title` 等主要字段仍为空
7. 再打开 `http://127.0.0.1:3000/` 与 `http://127.0.0.1:3000/en`

### Scope / Impact

1. 当前第 3 步“前台接 CMS 地址”已完成，但“CMS 内容迁移”未完成
2. 当前第 4 步“前台 revalidate”已完成，但“WordPress 触发前台 revalidate”仍未验证
3. 因此还不能做真正的“后台改内容 -> 前台实时/准实时看到变化”的完整闭环验收

### Suspected Root Cause

1. `mh_homepage` 的 `zh` / `en` 记录尚未填入真实首页 JSON
2. 当前 WordPress 里虽有记录骨架，但缺少关键内容字段
3. WordPress 侧回调环境变量是否已配置，当前仍未完成实际保存动作验证

### Key Evidence

1. `docs/testing_artifacts/2026-04-01-homepage-zh-after-env.png`
2. `docs/testing_artifacts/2026-04-01-homepage-en-after-env.png`
3. `docs/testing_artifacts/2026-04-01-revalidate-success.png`
4. `docs/testing_artifacts/2026-04-01-cms-empty-zh.png`
5. `ops/wordpress/homepage-seeds/homepage-zh.json`
6. `ops/wordpress/homepage-seeds/homepage-en.json`
7. `docs/testing_reports/status/2026-04-01_homepage_cms_linkage_status.json`

### Suggested Next Action

1. 将 `ops/wordpress/homepage-seeds/homepage-zh.json` 导入候选 CMS 的 `zh` 记录
2. 将 `ops/wordpress/homepage-seeds/homepage-en.json` 导入候选 CMS 的 `en` 记录
3. 在 WordPress 候选实例中配置 `MINDHIKERS_REVALIDATE_ENDPOINT` 与 `MINDHIKERS_REVALIDATE_SECRET`
4. 在后台保存一次内容，重新做“后台改 `zh/en` 内容 -> 前台首页变化”的浏览器闭环验证

## Handoff Notes

1. 下一位接手时先看：
   - `src/lib/cms/homepage.ts`
   - `src/lib/cms/constants.ts`
   - `src/app/api/revalidate/route.ts`
   - `src/data/site-content.ts`
   - `docs/dev_logs/HANDOFF.md`
2. 当前已确认的真结论：
   - 中文首页和英文首页本地都能打开
   - 本地前台 `REVALIDATE_SECRET` 已配置并返回成功
   - 候选 CMS 公共接口已可访问
   - 候选 CMS 当前仍是空内容骨架
   - 页面 fallback 渲染正常
3. 当前不要误判的点：
   - “本地 revalidate 成功”不等于“WordPress 保存时会自动回刷前台”
   - “CMS 接口可访问”不等于“CMS 内容已经可用”
   - “页面内容正常”不等于“后台改动能驱动前台”
4. 下一次复现优先重复本 request，并在 CMS 导入真实 `zh/en` 内容后重跑浏览器验证

# Staging 深度验收报告 — 2026-05-01

执行：OldYang / Codex（refs MIN-167）
分支：`staging`
起始 commit：`9450f83`
终止 commit：`9450f83`
状态：⚠️ 部分通过，需要整改后再进 production 决策

## 验收概览

| 组 | 通过 | 警告 | 失败 | 阻塞/跳过 |
|---|---:|---:|---:|---:|
| A 功能/路由 | 8 | 0 | 2 | 1 |
| B 数据流 | 3 | 2 | 0 | 1 |
| C 性能/资源 | 2 | 2 | 0 | 0 |
| D SEO/Metadata | 1 | 2 | 4 | 0 |
| E 安全/头部 | 4 | 1 | 0 | 0 |
| F CMS REST | 2 | 0 | 0 | 3 |
| G 部署稳健性 | 3 | 1 | 0 | 1 |
| 合计 | 23 | 8 | 6 | 6 |

机器汇总：`docs/testing_artifacts/2026-05-01_staging/acceptance_result_summary.json`

## 已修复红线

| commit | 类型 | 验收项 | 摘要 |
|---|---|---|---|
| `6642c73` | fix | D5 | 新增 `src/app/robots.ts`，非 production 返回 `Disallow: /` |
| `9450f83` | docs | D5/A8 | 记录 staging robots 与 health 验证证据 |

线上验证：

- `/robots.txt`：HTTP 200，`content-type: text/plain`，包含 `User-Agent: *` 与 `Disallow: /`
- `/health`：HTTP 200
- Railway 最新部署：`978c5c8a-c604-400f-b676-473bf3ee4237`，commit `9450f83`，SUCCESS

证据：

- `docs/testing_artifacts/2026-05-01_staging/D5_after_fix.txt`
- `docs/testing_artifacts/2026-05-01_staging/A8_health_after_red1.txt`

## 失败项

1. **A1 中文首页结构断言失败**
   - 页面 HTTP 200，`lang=zh-CN`，导航存在，但 HTML 中未找到 `<footer>` 标签。
   - 影响：不一定是视觉缺失，但不符合验收手册的结构断言。
   - 证据：`A1_body.html`、`A1_summary.json`、`A1_metadata.json`

2. **A2 英文首页语言标记失败**
   - `/en` HTTP 200，但 `<html lang>` 仍为 `zh-CN`，不是 `en`。
   - 影响：英文页可访问，但可访问性、SEO、浏览器语言提示不准确。
   - 证据：`A2_body.html`、`A2_metadata.json`

3. **D1 核心页面 description 不完整**
   - 英文首页 `/en` 缺 `meta description`。
   - 影响：SEO 摘要和分享摘要不完整。
   - 证据：`A2_metadata.json`

4. **D2 OG metadata 不完整**
   - `/en` 和 `/blog` 等页面缺少完整 `og:description`、`og:image`、`og:type`、`og:url` 的组合。
   - 影响：社交分享卡片不稳定。
   - 证据：`A2_metadata.json`、`A5_metadata.json`

5. **D3 canonical 不完整或不准确**
   - `/en` 缺 canonical；`/blog` canonical 指向站点根，而不是 `/blog`。
   - 影响：SEO canonical 信号不准。
   - 证据：`A2_metadata.json`、`A5_metadata.json`

6. **D6 sitemap.xml 失败**
   - `/sitemap.xml` 返回 HTTP 404，且 body 为 HTML 404 页，不是 XML。
   - 影响：搜索引擎发现和收录入口缺失。
   - 证据：`D6_summary.json`、`D6_body.xml`

## 阻塞项

1. **A7 产品详情中英对照**
   - 首页未发现 `/product/...` 链接；无法自动选取产品 slug。
   - 证据：`A7_product_links.json`

2. **B3 缓存 5 分钟 revalidate 验证**
   - 需要 WP REST 写权限更新测试文章 excerpt。
   - 当前 shell 缺 `WP_USER` / `WP_APP_PASSWORD`。

3. **F1/F2/F3 CMS REST 写链路**
   - 用户鉴权、草稿发布、媒体上传均需要 WordPress Application Password。
   - 当前 shell 缺 `WP_USER` / `WP_APP_PASSWORD`。

## 警告项

1. **B4 revalidate 只完成错误鉴权验证**
   - 当前没有可安全写入 artifacts 的真实 secret；已验证错误 secret 返回 401。
   - 证据：`B4_response_redacted_secret.txt`

2. **B5/G5 runtime log 有字体加载错误**
   - 日志包含 `Failed to load fonts: TypeError: fetch failed` 与 `not implemented... yet...`。
   - 当前未观察到 OOM、崩溃或重启循环。
   - 证据：`G5_runtime_long.log`、`G5_error_sample.txt`

3. **C1/C2 Lighthouse/PSI 未完成**
   - Lighthouse CLI 缺失；PageSpeed Insights API 超过 150 秒未返回，已终止。
   - 证据：`C1_timeout.txt`

4. **D4 hreflang 不完整**
   - 有 `zh-Hans` 与 `en`，缺 `x-default`；英文页根 `lang` 也不正确。
   - 证据：`A1_metadata.json`、`A2_metadata.json`

5. **D7 apple-touch-icon 缺失**
   - `favicon.ico` 为 200；`/apple-touch-icon.png` 为 404 HTML。
   - 证据：`D7_favicon.json`、`D7_apple.json`

6. **E2 HSTS 缺失**
   - 未观察到 `Strict-Transport-Security`。
   - 证据：`E_headers.txt`

7. **G4 默认跳过**
   - 按主方案不 squash 历史，记录为 SKIPPED。

## 通过项摘要

- A3/A4 Golden Crucible 中英页面：HTTP 200
- A5/A6 博客列表与详情：列表有 3 个 slug，详情页 HTTP 200
- A8 health：HTTP 200
- A9 404：中英随机不存在路径均 HTTP 404
- A10/A11 OG image：站点与博客 OG image 均 HTTP 200 image/png
- B1/B2 首页 API 与前台字段对照：中英文命中率均约 96.7%
- B6 首页图片 GET：HTTP 200 image/png
- C3 build log：Next build 成功
- C4 静态资源缓存：`max-age=31536000, immutable`
- E1/E3/E4/E5：HTTPS、安全头、错误鉴权、bundle secret 扫描通过
- F4/F5：Polylang REST 可访问，homepage API 关键字段存在
- G1/G2/G3：docs-only redeploy SUCCESS，build warning 可接受，`pnpm-lock.yaml` 已进入 build context

## 建议整改顺序

1. 修 `/en` 页面语言与 metadata：`lang=en`、description、canonical、OG、hreflang。
2. 新增 `src/app/sitemap.ts`，确保 `/sitemap.xml` 返回 XML。
3. 明确是否需要真实 `<footer>` 标签；如果视觉已有 footer 语义，应改为语义标签。
4. 补 `apple-touch-icon.png` 或在 metadata 中声明等价图标。
5. 查 runtime 字体加载错误来源。
6. 拿到 WP Application Password 后补跑 B3、F1、F2、F3。
7. 装 Lighthouse 或使用可用 PSI 环境补跑 C1/C2。

## Production 建议

暂不建议进入 production 推送决策。RED-1 已解除，但 D 组 SEO/metadata 仍有多个失败项，尤其 `/sitemap.xml` 404 和英文页 metadata/语言标记问题，建议先整改后再评估 main 推送策略。

---
title: Mindhikers-Homepage Staging 深度验收 — 外包 AI 执行实施方案
date: 2026-05-01
linear: MIN-167
owner: 老卢（决策） / OldYang（治理） / 外包 AI（执行）
branch: staging
status: 待外包 AI 接手执行
related:
  - docs/dev_logs/HANDOFF.md
  - docs/plans/2026-04-23-003-wp-single-stack-migration-execution-playbook.md
  - docs/04_progress/rules.md
---

# Mindhikers-Homepage Staging 深度验收 — 外包 AI 执行实施方案

> 本方案落盘绝对路径：
> `/Users/luzhoua/Mindhikers/Mindhikers-Homepage/docs/plans/2026-05-01_Staging_Deep_Acceptance_Outsourced_Execution_Plan.md`
>
> Linear 跟踪：[MIN-167](https://linear.app/mindhikers/issue/MIN-167/)

---

## 0. 易懂总览（先看这一节）

### 0.1 你（外包 AI）要做什么

把 staging 环境从"能跑"调到"完美无瑕"，给出**机器可验证**的验收报告，最后由甲方（老卢）决定是否合并到 production。

### 0.2 现状一句话

staging 部署链路已修复（Railpack pnpm 9.x bug → `packageManager` 字段绕过），前台/后台/双语 API baseline 全绿。但深度质量项（SEO、缓存策略、CMS API 链路、安全头、性能基线）尚未系统性验过。

### 0.3 必须最优先处理的红线

**`/robots.txt` 返回首页 HTML，不是 robots**。staging 当前对搜索引擎完全开放，与 production 内容高度重复，造成 SEO 污染。详见第 5.1 节，**第一个 commit 就修这个**。

### 0.4 受众契约（重要：本方案为 AI 执行而写）

- 所有验收项**必须用命令行工具完成**，可复现、有结构化输出
- **禁止依赖**：人眼截图、浏览器 DevTools 手动操作、wp-admin GUI 点击
- **替代方案**：curl + grep、Lighthouse CLI、WP REST API（Application Password 鉴权）、Railway CLI、Playwright/Puppeteer headless（仅在前述方法都不可达时）
- 所有证据落到**纯文本/JSON 文件**，不要保存截图
- 任何"AI 不可达"的项（如必须 GUI 才能验的）→ 立即按第 10 章升级，不要硬上

### 0.5 时间预算

- 顺风（baseline 都达标）：4–6 小时
- 正常（最可能）：1–1.5 个工作日
- 有暗坑：2 个工作日

> AI 执行通常比人快但要等 build（每次 push 后 3–6 分钟）和 revalidate（5 分钟），这部分时间消耗仍然是硬成本。

### 0.6 一句话纪律

**任何一处需要改超过 30 行代码 / 跨多个模块 / 影响 main 或 production / 本方案没明确写如何处理 — 立刻在 [MIN-167](https://linear.app/mindhikers/issue/MIN-167/) 评论 + 写入 `docs/testing_reports/escalations.md`，停手等老卢拍板。**

---

## 1. 任务全景

### 1.1 在范围

- 对 `staging` 分支已部署到 Railway 的 staging 环境做系统性验收（A–G 七组，第 6 章）
- 发现的小问题（≤ 30 行代码、范围明确、无架构影响）直接修，单 commit 推 staging
- 每修一处验证一次，留机器可读证据
- 输出最终验收报告（第 9 章模板）
- 在 [MIN-167](https://linear.app/mindhikers/issue/MIN-167/) 跟踪所有进展与决策

### 1.2 不在范围

- 修改 `main` 分支或动 production 部署 — **绝对禁止**
- 重写 CSP 策略、性能优化、CMS 内容迁移
- 任何"顺手的重构"
- 修改 Railway 项目级配置（service 添加/删除、环境变量改动需老卢同意）
- 触碰 `ops/mindhikers-cms-runtime/` 之外的 WordPress 服务端代码
- 通过 wp-admin GUI 做任何操作（用 WP REST API 替代）

### 1.3 工作完成的定义（DoD）

- A–G 七组共 43 项全部勾选，每项都有机器可验证的证据文件
- 红线 RED-1（robots.txt）已修并验证
- 验收报告落盘 `docs/testing_reports/2026-05-XX_staging_acceptance_report.md`
- HANDOFF.md 覆盖更新，写明终态与遗留问题
- 所有修复 commit 已 push 到 `origin/staging`，每次 push 后 Railway build SUCCESS
- 在 MIN-167 留一条总结评论，附验收报告路径与建议

---

## 2. 角色与协作模型

| 角色 | 谁 | 职责 | 边界 |
|---|---|---|---|
| 决策方 | 老卢 | 拍板分歧、批准 production 推送方案 | 不参与日常验收执行 |
| 治理 | OldYang（Claude Code） | 方案、规则、PR review、卡点裁决 | 通过本文件下单 |
| 执行 | 外包 AI | A–G 全部验收 + 必要修复 | 不擅自扩范围；遇阻立即升级 |

### 2.1 沟通节奏

- **每完成一组（A/B/.../G）** → 把该组的勾选状态、commit hash、证据路径写入验收报告对应小节，并 git commit 推 staging
- **任何阻塞** → 立即按第 10 章升级，不超过 20 分钟自我尝试
- **每个 commit 推送前** 在 commit message 写明 `refs MIN-167`，方便聚合追踪
- **不需要每天简报**；进展通过 commit log 和验收报告体现

---

## 3. 执行环境与约束

### 3.1 仓库与分支

- 工作目录（参考路径）：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`（外包 AI 用自己的 sandbox 路径无所谓）
- **必须工作在 `staging` 分支**。开工前执行：
  ```bash
  git fetch origin
  git checkout staging
  git pull --ff-only origin staging
  test "$(git branch --show-current)" = "staging" || { echo "WRONG BRANCH"; exit 1; }
  ```
- **绝对不允许在 `main` 上做任何修改**。如果不慎切到 main，立即 `git checkout staging` 撤回，并在 MIN-167 留一条 incident 记录

### 3.2 提交规范

- **每修一处单独 commit**，不批量混提交
- **提交分类**（OldYang 治理纪律）：
  - 功能/修复代码 + 与之直接绑定的小型经验记录（rules.md +1~2 行）→ 同一 commit
  - 纯过程文档（验收报告、HANDOFF、escalations.md）→ 独立 commit
  - 依赖变更（package.json/pnpm-lock.yaml）→ 独立 commit
- **commit message 模板**：
  ```
  <type>(<scope>): <短摘要>

  <可选正文：为什么改、风险、回退方式>

  refs MIN-167
  ```
  `<type>` 用 `fix` / `feat` / `ops` / `docs` / `test` / `chore`
- **禁止**：`--no-verify`、amend 已 push 的 commit、`--force` push（除非老卢明确指示）

### 3.3 Push 与部署验证

每次 push 后必须执行以下验证脚本，全绿才算这次 push 通过：

```bash
git push origin staging
# 等 build（约 3–6 分钟），可轮询：
for i in 1 2 3 4 5 6 7 8 9 10; do
  sleep 30
  STATUS=$(railway deployment list --json 2>/dev/null | jq -r '.[0].status')
  echo "[$i/10] build status: $STATUS"
  [ "$STATUS" = "SUCCESS" ] && break
  [ "$STATUS" = "FAILED" ] && { echo "BUILD FAILED"; railway logs --build --latest --lines 80; exit 1; }
done

# build 成功后做 smoke test
curl -fsS -o /dev/null -w "frontend: HTTP %{http_code}\n" https://mindhikers-homepage-staging.up.railway.app/ || exit 1
curl -fsS -o /dev/null -w "health:   HTTP %{http_code}\n" https://mindhikers-homepage-staging.up.railway.app/health || exit 1
curl -fsS -o /dev/null -w "backend:  HTTP %{http_code}\n" https://wordpress-l1ta-staging.up.railway.app/ || exit 1
```

如果 build 失败 → 立即按第 10 章升级，附 build log 关键报错；不要连续推多个修复堆叠。

### 3.4 Linear 跟踪

- 所有 commit message 加 `refs MIN-167`
- 卡点升级写在 MIN-167 评论里
- 每完成一组 A–G 在 MIN-167 留一句进度评论（可选，但建议）

### 3.5 端口与本地服务

- 本次验收**全部对 staging 远端做**，不需要起本地服务
- 如确实需要本地复现 → 用 `pnpm dev` 起在 **3001 端口**（避开 3000 默认占用）
- WordPress **不要本地起**，远端就够

### 3.6 敏感信息纪律

- 不要把任何 token / 密码 / `.env` 内容写入 commit、PR、文档、Linear 评论
- 验收过程中如需引用 token，用占位符 `<TOKEN>`、`<APP_PASSWORD>`
- 所有 secret 应当**只来自环境变量**或外部凭据系统，不内联在代码里

---

## 4. 执行环境预设条件

外包 AI 接手前，以下资源/凭证必须预先配置好（环境变量或凭据存储）。**任何一项缺失，AI 在 MIN-167 留 comment 后停手等待**。

| Key | 说明 | 缺失影响 |
|---|---|---|
| `RAILWAY_TOKEN` | Railway CLI 鉴权 | 整个 G 段、build/runtime log 不可达 |
| `WP_BASE_URL` | 默认 `https://wordpress-l1ta-staging.up.railway.app` | 必备 |
| `WP_USER` | wp-admin 用户名 | F 段全部不可达 |
| `WP_APP_PASSWORD` | WordPress Application Password（不是常规登录密码） | F 段全部不可达 |
| `WORDPRESS_REVALIDATE_TOKEN` | Next.js `/api/revalidate` 鉴权 | B4 不可达 |
| `GITHUB_TOKEN`（push 权限） | push staging 分支 | 无法推修复 |
| Node 18+ + pnpm 9.x + curl + jq | CLI 工具 | 必备 |
| `lighthouse` CLI（`npm i -g lighthouse`）+ 可用 Chromium/Chrome | C 段性能 | C1 不可达，可降级用 PageSpeed Insights API |

### 4.1 自检脚本（开工第一步运行）

```bash
#!/usr/bin/env bash
set -e
echo "=== environment self-check ==="
node --version
pnpm --version
curl --version | head -1
jq --version
git --version
echo "RAILWAY_TOKEN: ${RAILWAY_TOKEN:+SET}${RAILWAY_TOKEN:-MISSING}"
echo "WP_USER: ${WP_USER:+SET}${WP_USER:-MISSING}"
echo "WP_APP_PASSWORD: ${WP_APP_PASSWORD:+SET}${WP_APP_PASSWORD:-MISSING}"
echo "WORDPRESS_REVALIDATE_TOKEN: ${WORDPRESS_REVALIDATE_TOKEN:+SET}${WORDPRESS_REVALIDATE_TOKEN:-MISSING}"
which lighthouse 2>/dev/null && lighthouse --version || echo "lighthouse: MISSING (will degrade C1 to PSI API)"
railway status 2>&1 | head -3
echo "=== branch check ==="
git branch --show-current
test "$(git branch --show-current)" = "staging" && echo "OK" || echo "WRONG BRANCH"
```

任何 MISSING 项不是必备的（如 lighthouse）→ 标注降级方案后继续；
必备项 MISSING（如 `RAILWAY_TOKEN`、`WP_APP_PASSWORD`）→ 立即在 MIN-167 留 comment 后停手。

---

## 5. 已知红线风险（开工前必读）

### 5.1 🚨 RED-1：staging `/robots.txt` 返回首页 HTML 不是 robots（必须第一个修）

**复现**：
```bash
curl -sS -i "https://mindhikers-homepage-staging.up.railway.app/robots.txt" | head -20
# 期望：Content-Type: text/plain，body 为 robots 内容
# 实际：Content-Type: text/html，body 为 Next.js 首页 HTML
```

**为什么严重**：
- staging 与 production 内容高度相似 → Google 判 duplicate content → 稀释主域 SEO
- staging 的 Railway 子域可能被反向链接 → SEO 漏水
- 一旦被索引，恢复需走 Google Search Console 移除流程

**修法（推荐方案 A：基于环境变量动态 robots）**：

1. 新建 `app/robots.ts`：
   ```typescript
   import type { MetadataRoute } from 'next'

   export default function robots(): MetadataRoute.Robots {
     const isProd = process.env.NEXT_PUBLIC_ENV === 'production'
     if (!isProd) {
       return { rules: { userAgent: '*', disallow: '/' } }
     }
     return {
       rules: { userAgent: '*', allow: '/' },
       sitemap: 'https://mindhikers.com/sitemap.xml',
     }
   }
   ```

2. **先验证 Railway staging service Variables 是否有 `NEXT_PUBLIC_ENV`**：
   ```bash
   railway service Mindhikers-Homepage
   railway variables --json 2>&1 | jq -r 'keys[] | select(test("ENV"))'
   ```
   - 如果存在且值非 `production` → 直接进 step 3
   - 如果不存在或值为 `production` → 在 MIN-167 留 comment **请老卢添加** `NEXT_PUBLIC_ENV=staging` 到 staging service。**外包 AI 不要自己改 Railway Variables**

3. push 后验证：
   ```bash
   curl -sS -i "https://mindhikers-homepage-staging.up.railway.app/robots.txt" \
     | tee /tmp/robots_after.txt | head -20
   # 期望：Content-Type: text/plain
   # body：User-Agent: *\nDisallow: /
   grep -i 'content-type: text/plain' /tmp/robots_after.txt && echo "OK" || echo "FAIL"
   grep -E 'Disallow:\s*/' /tmp/robots_after.txt && echo "OK" || echo "FAIL"
   ```

**判定通过**：两个 OK 都打出。

**回退**：删除 `app/robots.ts`，`git revert <hash>`，push。

---

### 5.2 🟡 YEL-1：HSTS 头未观察到

```bash
curl -sS -I "https://mindhikers-homepage-staging.up.railway.app/" | grep -i strict-transport
# 当前无输出
```

**处理**：本次仅在 E2 验收项记录现状，不修。production 推送前再 review。

---

### 5.3 🟡 YEL-2：CSP 含 `'unsafe-inline'` script

**当前**：`script-src 'self' 'unsafe-inline'`

**处理**：本次记录，不改。

---

### 5.4 🟡 YEL-3：main 分支当前还没合并修复

**事实**：main 最新 commit `8744c4f` 已切 RAILPACK，但 main 上 `package.json` **没有** `packageManager` 字段。下次推 main 触发部署会复现失败。

**处理**：保持 main 不变。外包 AI 如不慎触发 main 部署，立即在 MIN-167 留 incident comment。

---

## 6. A–G 验收手册（详细执行步骤）

### 6.0 统一约定

- 每一项验证后，把结果**机器可读**写入 `docs/testing_reports/2026-05-XX_staging_acceptance_report.md` 对应小节
- 命令输出/JSON/log 落到 `docs/testing_artifacts/2026-05-XX_staging/<项目编号>_<场景>.{txt,json,log}`
  - 例：`A1_zh_home_headers.txt`、`A1_zh_home_metadata.json`、`B5_runtime_log_grep.txt`
- 每发现一处需要修的 → 单 commit，commit hash 写到验收报告该项备注
- 当日日志写到 `docs/dev_logs/2026-05-XX.md`，控制 ≤ 80 行，记**事实和决策**不记心情

### 6.1 通用辅助函数（建议在脚本里复用）

```bash
STAGING="https://mindhikers-homepage-staging.up.railway.app"
WP_STAGING="https://wordpress-l1ta-staging.up.railway.app"
ART_DIR="docs/testing_artifacts/$(date +%Y-%m-%d)_staging"
mkdir -p "$ART_DIR"

# 抓 URL，返回 JSON 摘要
fetch_summary() {
  local url="$1" id="$2"
  local hdr="$ART_DIR/${id}_headers.txt"
  local body="$ART_DIR/${id}_body.html"
  curl -sS -D "$hdr" -o "$body" -w '{"http_code":%{http_code},"size":%{size_download},"ttfb":%{time_starttransfer},"total":%{time_total},"content_type":"%{content_type}"}\n' "$url" \
    | tee "$ART_DIR/${id}_summary.json"
}

# 抓 head meta
extract_metadata() {
  local body="$1" out="$2"
  python3 -c "
import re, json, sys
html = open(sys.argv[1]).read()
def find_all(pat): return re.findall(pat, html)
data = {
  'title': (find_all(r'<title[^>]*>([^<]+)</title>') or [None])[0],
  'lang': (find_all(r'<html[^>]*lang=\"([^\"]+)\"') or [None])[0],
  'meta_description': (find_all(r'<meta\s+name=\"description\"\s+content=\"([^\"]*)\"') or [None])[0],
  'og': dict(re.findall(r'<meta\s+property=\"(og:[^\"]+)\"\s+content=\"([^\"]*)\"', html)),
  'canonical': (find_all(r'<link\s+rel=\"canonical\"\s+href=\"([^\"]+)\"') or [None])[0],
  'hreflang': re.findall(r'<link\s+rel=\"alternate\"\s+hreflang=\"([^\"]+)\"\s+href=\"([^\"]+)\"', html),
}
print(json.dumps(data, ensure_ascii=False, indent=2))
" "$body" > "$out"
}
```

---

### A. 功能性 — 页面 & 路由

#### A1 中文首页 `/`

```bash
fetch_summary "$STAGING/" A1
extract_metadata "$ART_DIR/A1_body.html" "$ART_DIR/A1_metadata.json"
```

**判定通过**（程序化）：
```bash
jq -e '.http_code == 200 and .size > 30000 and (.content_type | startswith("text/html"))' "$ART_DIR/A1_summary.json"
jq -e '.lang == "zh-CN" and (.title | length > 0)' "$ART_DIR/A1_metadata.json"
```

**额外断言**（关键文案/结构存在）：
```bash
grep -q '<nav' "$ART_DIR/A1_body.html" && grep -q '<footer' "$ART_DIR/A1_body.html" && echo OK
```

#### A2 英文首页 `/en`

```bash
fetch_summary "$STAGING/en" A2
extract_metadata "$ART_DIR/A2_body.html" "$ART_DIR/A2_metadata.json"
jq -e '.http_code == 200' "$ART_DIR/A2_summary.json"
jq -e '.lang == "en"' "$ART_DIR/A2_metadata.json"
```

#### A3 / A4 Golden Crucible 中英

```bash
fetch_summary "$STAGING/golden-crucible" A3
fetch_summary "$STAGING/en/golden-crucible" A4
jq -e '.http_code == 200' "$ART_DIR/A3_summary.json"
jq -e '.http_code == 200' "$ART_DIR/A4_summary.json"
extract_metadata "$ART_DIR/A3_body.html" "$ART_DIR/A3_metadata.json"
extract_metadata "$ART_DIR/A4_body.html" "$ART_DIR/A4_metadata.json"
```

#### A5 博客列表 `/blog`

```bash
fetch_summary "$STAGING/blog" A5
# 抽取所有博客 slug
grep -oE '/blog/[a-z0-9\-]+' "$ART_DIR/A5_body.html" \
  | grep -v '/opengraph-image' \
  | sort -u > "$ART_DIR/A5_slugs.txt"
SLUG_COUNT=$(wc -l < "$ART_DIR/A5_slugs.txt")
echo "{\"slug_count\": $SLUG_COUNT}" > "$ART_DIR/A5_assertions.json"
test "$SLUG_COUNT" -ge 1
```

#### A6 博客详情

```bash
SLUG=$(head -1 "$ART_DIR/A5_slugs.txt" | sed 's|/blog/||')
fetch_summary "$STAGING/blog/$SLUG" "A6"
echo "{\"slug\": \"$SLUG\"}" > "$ART_DIR/A6_assertions.json"
extract_metadata "$ART_DIR/A6_body.html" "$ART_DIR/A6_metadata.json"
jq -e '.http_code == 200' "$ART_DIR/A6_summary.json"
# 断言正文长度 > 5KB（避免空文章）
test $(jq '.size' "$ART_DIR/A6_summary.json") -ge 5000
```

#### A7 产品详情中英对照

需要先拿到一个有效产品 slug。两种方式：
- 方式 1（首页可能含产品入口）：从 A1 body 抽 `/product/` 链接
- 方式 2（直接问 API）：从 `WP_STAGING/wp-json/wp/v2/<custom_post_type>` 列出（如果项目暴露了）

```bash
# 方式 1
PRODUCT_SLUG=$(grep -oE '/product/[a-z0-9\-]+' "$ART_DIR/A1_body.html" | head -1 | sed 's|/product/||')
if [ -z "$PRODUCT_SLUG" ]; then
  echo "{\"status\": \"BLOCKED\", \"reason\": \"no product link found in homepage\"}" > "$ART_DIR/A7_assertions.json"
  # 在 MIN-167 留 comment，标记 A7 为 BLOCKED，跳过
else
  fetch_summary "$STAGING/product/$PRODUCT_SLUG" A7zh
  fetch_summary "$STAGING/en/product/$PRODUCT_SLUG" A7en
  jq -e '.http_code == 200' "$ART_DIR/A7zh_summary.json"
  jq -e '.http_code == 200' "$ART_DIR/A7en_summary.json"
fi
```

**注意**：如果 EN 产品翻译不全，是数据问题不是代码问题，记录到验收报告，不修。

#### A8 health `/health`

```bash
curl -fsS "$STAGING/health" > "$ART_DIR/A8_health.json"
jq -e '.ok == true and (.timestamp | length > 0)' "$ART_DIR/A8_health.json"
```

#### A9 404 路由

```bash
RAND="not-found-$(date +%s)"
curl -sS -o "$ART_DIR/A9_body.html" -w '{"http_code":%{http_code},"size":%{size_download}}\n' "$STAGING/$RAND" > "$ART_DIR/A9_summary.json"
jq -e '.http_code == 404' "$ART_DIR/A9_summary.json"
# 断言 not-found 页面有友好提示（关键词）
grep -qiE '404|not found|找不到|未找到' "$ART_DIR/A9_body.html"
```

EN 版同样测一次：
```bash
curl -sS -o "$ART_DIR/A9_en_body.html" -w '%{http_code}\n' "$STAGING/en/$RAND" > "$ART_DIR/A9_en_summary.txt"
```

#### A10 OG image `/opengraph-image`

```bash
curl -sS -o "$ART_DIR/A10_og.bin" -w '{"http_code":%{http_code},"content_type":"%{content_type}","size":%{size_download}}\n' "$STAGING/opengraph-image" > "$ART_DIR/A10_summary.json"
jq -e '.http_code == 200 and (.content_type | test("image/"))' "$ART_DIR/A10_summary.json"
file "$ART_DIR/A10_og.bin" | tee "$ART_DIR/A10_file.txt"
```

#### A11 博客 OG image

```bash
curl -sS -o "$ART_DIR/A11_blog_og.bin" -w '%{http_code} %{content_type}\n' "$STAGING/blog/opengraph-image" > "$ART_DIR/A11_blog_summary.txt"
SLUG=$(head -1 "$ART_DIR/A5_slugs.txt" | sed 's|/blog/||')
curl -sS -o "$ART_DIR/A11_blog_slug_og.bin" -w '%{http_code} %{content_type}\n' "$STAGING/blog/$SLUG/opengraph-image" > "$ART_DIR/A11_blog_slug_summary.txt"
```

---

### B. 数据流 — 前后台联通

#### B1 ZH API → ZH 首页字段对照

```bash
curl -fsS "$WP_STAGING/wp-json/mindhikers/v1/homepage/zh" | jq '.' > "$ART_DIR/B1_api_zh.json"

# 抽取 API 中的字符串字段（深度遍历），与首页 HTML 比对
python3 <<'PY' > "$ART_DIR/B1_field_match.json"
import json, re
api = json.load(open("docs/testing_artifacts/$(date +%Y-%m-%d)_staging/B1_api_zh.json".replace("$(date +%Y-%m-%d)", __import__("datetime").date.today().isoformat())))
html = open("docs/testing_artifacts/" + __import__("datetime").date.today().isoformat() + "_staging/A1_body.html").read()

def collect_strings(obj, out):
    if isinstance(obj, str) and len(obj) >= 4:
        out.append(obj)
    elif isinstance(obj, dict):
        for v in obj.values(): collect_strings(v, out)
    elif isinstance(obj, list):
        for v in obj: collect_strings(v, out)

strs = []
collect_strings(api, strs)
sample = strs[:20]
matches = [(s, s in html or re.sub(r'\s+', ' ', s) in re.sub(r'\s+', ' ', html)) for s in sample]
hit = sum(1 for _, ok in matches if ok)
print(json.dumps({"sampled": len(sample), "hit": hit, "ratio": hit/max(1,len(sample)), "details": [{"text": s[:80], "found": ok} for s, ok in matches]}, ensure_ascii=False, indent=2))
PY
```

**判定通过**：`ratio >= 0.5`（至少一半字段在 HTML 中能找到）。低于此阈值 → 升级。

#### B2 EN API → EN 首页字段对照

同 B1，URL 换 `/en`。

#### B3 缓存策略 revalidate 5m

通过 WP REST API 改一个临时字段，等 5 分钟，验证前台刷新。

```bash
# 找一个安全可改的字段：在 staging 找一篇 _测试用_ 文章；如没有，先创建一个
# 注意：carbon-fields 字段不一定能通过标准 WP REST API 读写
# 推荐做法：改一篇博客文章的 excerpt，更新后等 5 分钟看 /blog 列表是否刷新

POST_ID=$(curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" \
  "$WP_STAGING/wp-json/wp/v2/posts?per_page=1&_fields=id" | jq -r '.[0].id')
ORIG_EXCERPT=$(curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" \
  "$WP_STAGING/wp-json/wp/v2/posts/$POST_ID?_fields=excerpt" | jq -r '.excerpt.rendered // ""')

# 加临时标记
MARKER="__cache_test_$(date +%s)__"
curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" -X POST \
  -H "Content-Type: application/json" \
  -d "{\"excerpt\":\"$MARKER\"}" \
  "$WP_STAGING/wp-json/wp/v2/posts/$POST_ID" > "$ART_DIR/B3_update.json"

T0=$(date +%s)
echo "marker: $MARKER, post_id: $POST_ID, t0: $T0" > "$ART_DIR/B3_state.txt"

# 立即验证：旧缓存仍生效
sleep 5
curl -sS "$STAGING/blog" | grep -c "$MARKER" > "$ART_DIR/B3_immediate_count.txt"

# 等待 360 秒（5m + 1m buffer）
sleep 360
curl -sS "$STAGING/blog" | grep -c "$MARKER" > "$ART_DIR/B3_after_count.txt"

# 回滚
curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" -X POST \
  -H "Content-Type: application/json" \
  -d "{\"excerpt\":\"$ORIG_EXCERPT\"}" \
  "$WP_STAGING/wp-json/wp/v2/posts/$POST_ID" > "$ART_DIR/B3_restore.json"
```

**判定通过**：`B3_immediate_count.txt` = 0 且 `B3_after_count.txt` ≥ 1。

#### B4 `/api/revalidate` 端点

需要 `WORDPRESS_REVALIDATE_TOKEN`。

```bash
# 先 grep 代码看端点签名
grep -RH "revalidate" app/api/ src/app/api/ 2>/dev/null | head -20 > "$ART_DIR/B4_route_grep.txt"

# 默认尝试 POST + secret in body（如果代码用别的字段名，按代码调整）
curl -sS -X POST -H "Content-Type: application/json" \
  -d "{\"path\":\"/\",\"secret\":\"$WORDPRESS_REVALIDATE_TOKEN\"}" \
  -w '\nHTTP %{http_code}\n' \
  "$STAGING/api/revalidate" > "$ART_DIR/B4_response.txt"
```

**判定通过**：HTTP 200 + 返回体含 `revalidated: true` 或类似。如签名不一致 → 看 `B4_route_grep.txt` 调整后再测，仍不行升级。

#### B5 Runtime log 健康度

```bash
railway service Mindhikers-Homepage
railway logs --deployment --lines 500 > "$ART_DIR/B5_runtime.log"
grep -ciE 'error|exception|unhandled|ECONNREFUSED|timeout' "$ART_DIR/B5_runtime.log" > "$ART_DIR/B5_error_count.txt"
grep -iE 'error|exception|unhandled' "$ART_DIR/B5_runtime.log" | head -30 > "$ART_DIR/B5_error_sample.txt"
```

**判定通过**：error 总数为 0，或全部为已知偶发警告（人工/AI 判断后记录）。

#### B6 图片资产加载

```bash
python3 <<'PY' > "$ART_DIR/B6_images.json"
import re, json, urllib.request, sys
from datetime import date
art = f"docs/testing_artifacts/{date.today().isoformat()}_staging"
html = open(f"{art}/A1_body.html").read()
imgs = list(set(re.findall(r'<img[^>]+src="([^"]+)"', html) + re.findall(r'srcset="([^"]+)"', html)))
# 简化：只取 src，srcset 第一个
sample = []
for u in imgs[:10]:
    if 'srcset' in u: u = u.split(',')[0].strip().split()[0]
    if u.startswith('/'):
        u = "https://mindhikers-homepage-staging.up.railway.app" + u
    if not u.startswith('http'): continue
    try:
        req = urllib.request.Request(u, method='HEAD')
        with urllib.request.urlopen(req, timeout=10) as r:
            sample.append({"url": u, "status": r.status, "type": r.headers.get('Content-Type')})
    except Exception as e:
        sample.append({"url": u, "error": str(e)})
print(json.dumps(sample, indent=2, ensure_ascii=False))
PY
```

**判定通过**：所有图返回 200，无 error。

---

### C. 性能 & 资源（只记录基线，不优化）

#### C1 Lighthouse（mobile）

```bash
# 优先：本地 lighthouse CLI
if command -v lighthouse >/dev/null 2>&1; then
  lighthouse "$STAGING/" \
    --preset=desktop --output=json --output-path="$ART_DIR/C1_lh_desktop.json" \
    --chrome-flags="--headless --no-sandbox" --quiet || echo "C1 desktop FAIL"
  lighthouse "$STAGING/" \
    --form-factor=mobile --output=json --output-path="$ART_DIR/C1_lh_mobile.json" \
    --chrome-flags="--headless --no-sandbox" --quiet || echo "C1 mobile FAIL"
  jq '{performance: .categories.performance.score, accessibility: .categories.accessibility.score, best_practices: .categories["best-practices"].score, seo: .categories.seo.score}' "$ART_DIR/C1_lh_mobile.json" > "$ART_DIR/C1_scores_mobile.json"
else
  # 降级：PageSpeed Insights API（公开，无需 key）
  curl -sS "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$(python3 -c "import urllib.parse;print(urllib.parse.quote('$STAGING/'))")&strategy=mobile&category=performance&category=accessibility&category=best-practices&category=seo" \
    > "$ART_DIR/C1_psi_mobile.json"
  jq '.lighthouseResult.categories | to_entries | map({(.key): .value.score}) | add' "$ART_DIR/C1_psi_mobile.json" > "$ART_DIR/C1_scores_mobile.json"
fi
cat "$ART_DIR/C1_scores_mobile.json"
```

**判定通过**：四项分数都 ≥ 0.7（PSI 是 0–1，等同于 70）。低于此值的项详细记录但不修。

#### C2 Web Vitals

```bash
jq '.audits | {fcp: ."first-contentful-paint".numericValue, lcp: ."largest-contentful-paint".numericValue, tbt: ."total-blocking-time".numericValue, cls: ."cumulative-layout-shift".numericValue, ttfb: ."server-response-time".numericValue}' "$ART_DIR/C1_lh_mobile.json" 2>/dev/null \
  || jq '.lighthouseResult.audits | {fcp: ."first-contentful-paint".numericValue, lcp: ."largest-contentful-paint".numericValue, tbt: ."total-blocking-time".numericValue, cls: ."cumulative-layout-shift".numericValue}' "$ART_DIR/C1_psi_mobile.json" \
  > "$ART_DIR/C2_vitals.json"
cat "$ART_DIR/C2_vitals.json"
```

**判定通过**：仅记录，无硬阈值。

#### C3 Bundle 体积

```bash
DEPLOY_ID=$(railway deployment list --json | jq -r '[.[] | select(.status == "SUCCESS")][0].id')
railway logs --build "$DEPLOY_ID" --lines 500 > "$ART_DIR/C3_build.log"
grep -iE 'first load js|load js shared|chunk' "$ART_DIR/C3_build.log" > "$ART_DIR/C3_bundle.txt"
```

**判定通过**：First Load JS shared 在 200KB 以内（记录数值即可）。

#### C4 静态资产 cache-control

```bash
ASSET=$(grep -oE '/_next/static/chunks/[a-z0-9]+\.js' "$ART_DIR/A1_body.html" | head -1)
curl -sS -I "$STAGING$ASSET" > "$ART_DIR/C4_asset_headers.txt"
grep -i 'cache-control' "$ART_DIR/C4_asset_headers.txt"
```

**判定通过**：含 `max-age` ≥ 86400（理想 31536000）+ `immutable`。

---

### D. SEO & Metadata

#### D1 `<title>` / `<meta description>`

汇总 5 个核心页面的 metadata（A1–A4 + A5 的 metadata 已抓）：

```bash
python3 <<'PY' > "$ART_DIR/D1_summary.json"
import json
from datetime import date
art = f"docs/testing_artifacts/{date.today().isoformat()}_staging"
result = {}
for key in ["A1", "A2", "A3", "A4"]:
    try:
        m = json.load(open(f"{art}/{key}_metadata.json"))
        result[key] = {"title": m.get("title"), "description": m.get("meta_description"), "title_len": len(m.get("title") or ""), "desc_len": len(m.get("meta_description") or "")}
    except Exception as e:
        result[key] = {"error": str(e)}
print(json.dumps(result, indent=2, ensure_ascii=False))
PY
```

**判定通过**：每个页面 `title` 非 null、`description` 非 null；title 长度 15–60 字符；description 长度 80–160 字符。

**修法 hint**：缺失项一般在 `app/<route>/page.tsx` 加 `export const metadata = {...}`。修复 ≤ 30 行/路由。

#### D2 og:* 系列

```bash
python3 <<'PY' > "$ART_DIR/D2_summary.json"
import json
from datetime import date
art = f"docs/testing_artifacts/{date.today().isoformat()}_staging"
required = {"og:title", "og:description", "og:image", "og:type", "og:url"}
result = {}
for key in ["A1", "A2", "A3", "A4", "A5"]:
    try:
        m = json.load(open(f"{art}/{key}_metadata.json"))
        og = m.get("og", {})
        missing = list(required - set(og.keys()))
        result[key] = {"og": og, "missing": missing}
    except Exception as e:
        result[key] = {"error": str(e)}
print(json.dumps(result, indent=2, ensure_ascii=False))
PY
```

**判定通过**：每个页面 `missing` 数组为空。

#### D3 canonical

```bash
python3 <<'PY' > "$ART_DIR/D3_summary.json"
import json
from datetime import date
art = f"docs/testing_artifacts/{date.today().isoformat()}_staging"
result = {}
for key in ["A1", "A2", "A3", "A4", "A5"]:
    try:
        m = json.load(open(f"{art}/{key}_metadata.json"))
        result[key] = m.get("canonical")
    except Exception as e:
        result[key] = {"error": str(e)}
print(json.dumps(result, indent=2, ensure_ascii=False))
PY
```

**判定通过**：每页都有 canonical。**特别注意**：staging 期间 canonical 应当指向 production 域 `https://mindhikers.com/...` 以避免 staging 被错误索引时影响主域。如果当前是 staging 自己的 URL，记录但不要在本次改 → 升级（涉及与 robots 修复一并设计）。

#### D4 hreflang

```bash
python3 <<'PY' > "$ART_DIR/D4_summary.json"
import json
from datetime import date
art = f"docs/testing_artifacts/{date.today().isoformat()}_staging"
result = {}
for key in ["A1", "A2"]:
    try:
        m = json.load(open(f"{art}/{key}_metadata.json"))
        result[key] = m.get("hreflang", [])
    except Exception as e:
        result[key] = {"error": str(e)}
print(json.dumps(result, indent=2, ensure_ascii=False))
PY
```

**判定通过**：A1 含 `zh-CN` + `en` + `x-default`；A2 反向声明。

**修法 hint**：在 `app/layout.tsx` 或路由 metadata 加 `alternates: { languages: {...} }`。

#### D5 robots.txt

由 RED-1 修法覆盖。修复后再次验证：
```bash
curl -sS -i "$STAGING/robots.txt" | tee "$ART_DIR/D5_after_fix.txt" | head -20
grep -i 'content-type: text/plain' "$ART_DIR/D5_after_fix.txt"
grep -E 'Disallow:\s*/' "$ART_DIR/D5_after_fix.txt"
```

#### D6 sitemap.xml

```bash
curl -sS -i "$STAGING/sitemap.xml" -o "$ART_DIR/D6_body.xml" -D "$ART_DIR/D6_headers.txt"
grep -i 'content-type' "$ART_DIR/D6_headers.txt" | head -1
head -30 "$ART_DIR/D6_body.xml"
```

**判定通过**：Content-Type 为 `application/xml` 或 `text/xml`；body 为合法 XML（含 `<urlset>` 和 ≥ 5 个 `<url>`）。

**修法 hint**：新建 `app/sitemap.ts`：
```typescript
import type { MetadataRoute } from 'next'

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const base = process.env.NEXT_PUBLIC_SITE_URL || 'https://mindhikers.com'
  const staticRoutes = ['', '/en', '/golden-crucible', '/en/golden-crucible', '/blog']
  // 动态：从 WP API 拉博客/产品 slug
  const posts = await fetch(`${process.env.WP_API_BASE}/wp-json/wp/v2/posts?per_page=100&_fields=slug,modified`)
    .then(r => r.ok ? r.json() : [])
    .catch(() => [])
  const blogRoutes = posts.map((p: any) => ({ url: `${base}/blog/${p.slug}`, lastModified: p.modified }))
  return [
    ...staticRoutes.map(r => ({ url: `${base}${r}`, lastModified: new Date() })),
    ...blogRoutes,
  ]
}
```

如修复 > 50 行或需调用未知 WP 接口（如产品 slug 集合）→ 升级。

#### D7 favicon + apple-touch-icon

```bash
curl -sS -o /dev/null -w '{"favicon":{"http_code":%{http_code},"content_type":"%{content_type}","size":%{size_download}}}\n' "$STAGING/favicon.ico" > "$ART_DIR/D7_favicon.json"
curl -sS -o /dev/null -w '{"apple_touch":{"http_code":%{http_code},"content_type":"%{content_type}","size":%{size_download}}}\n' "$STAGING/apple-touch-icon.png" > "$ART_DIR/D7_apple.json"
```

**判定通过**：favicon HTTP 200；apple-touch HTTP 200 或 HTML head 里声明了等价 link。

---

### E. 安全 & 头部

#### E1 HTTPS

Railway 默认提供。验证：
```bash
curl -sS -o /dev/null -w '%{http_code} %{scheme}\n' "$STAGING/" > "$ART_DIR/E1_https.txt"
```

#### E2 HSTS

```bash
curl -sS -I "$STAGING/" | grep -i strict-transport > "$ART_DIR/E2_hsts.txt" || echo "MISSING" > "$ART_DIR/E2_hsts.txt"
```

**判定**：当前已知 MISSING，本次记录不修（YEL-1）。

#### E3 安全头

```bash
curl -sS -I "$STAGING/" \
  | grep -iE 'content-security-policy|x-frame-options|x-content-type-options|referrer-policy|permissions-policy' \
  > "$ART_DIR/E3_security_headers.txt"
wc -l < "$ART_DIR/E3_security_headers.txt"
```

**判定通过**：≥ 5 行（覆盖 CSP / XFO / XCTO / Referrer / Permissions）。

#### E4 wp-admin 登录

通过 REST API 验证错误鉴权返回不泄漏用户存在性：
```bash
curl -sS -u "nobody:wrong" -w '\nHTTP %{http_code}\n' \
  "$WP_STAGING/wp-json/wp/v2/users/me" > "$ART_DIR/E4_wrong_creds.txt"
# 期望：401，且响应体不区分"用户不存在"和"密码错误"
```

**判定通过**：HTTP 401；响应体不含字段提示用户存在/不存在差异。

#### E5 暴露的环境变量

```bash
# 抓主 bundle 看是否含 token/secret
ASSETS=$(grep -oE '/_next/static/chunks/[a-z0-9]+\.js' "$ART_DIR/A1_body.html" | head -3)
mkdir -p "$ART_DIR/E5_assets"
for asset in $ASSETS; do
  fname=$(basename "$asset")
  curl -fsS "$STAGING$asset" -o "$ART_DIR/E5_assets/$fname"
  echo "=== $fname ==="
  grep -oiE '(token|secret|password|api[_-]?key)["\s:=]+[a-z0-9]{16,}' "$ART_DIR/E5_assets/$fname" | head -10
done > "$ART_DIR/E5_grep.txt"
```

**判定通过**：`E5_grep.txt` 无可疑命中。任何命中都升级，由老卢判断是否是 false positive。

---

### F. CMS 操作链路（通过 WP REST API，不走 GUI）

> 全部需要 `WP_USER` + `WP_APP_PASSWORD`。预先创建一条 Application Password：wp-admin → Users → Profile → Application Passwords。**这一步老卢预先做好**，外包 AI 直接用环境变量。

#### F1 wp-admin / API 鉴权

```bash
curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" \
  "$WP_STAGING/wp-json/wp/v2/users/me" > "$ART_DIR/F1_me.json"
jq -e '.id and .name' "$ART_DIR/F1_me.json"
```

**判定通过**：返回当前用户信息。401/403 → 升级凭证问题。

#### F2 博客发布流程（API 模拟）

```bash
# 创建草稿
DRAFT=$(curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" -X POST \
  -H "Content-Type: application/json" \
  -d '{"title":"_acceptance_test_'$(date +%s)'_","content":"acceptance test content","status":"draft"}' \
  "$WP_STAGING/wp-json/wp/v2/posts")
DRAFT_ID=$(echo "$DRAFT" | jq -r '.id')
echo "$DRAFT" > "$ART_DIR/F2_draft.json"

# 发布
curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" -X POST \
  -H "Content-Type: application/json" \
  -d '{"status":"publish"}' \
  "$WP_STAGING/wp-json/wp/v2/posts/$DRAFT_ID" > "$ART_DIR/F2_publish.json"

# 验证前台（注意 5m 缓存，可调 revalidate 加速）
sleep 5
curl -sS "$STAGING/blog" | grep -c "_acceptance_test_" > "$ART_DIR/F2_frontend_check.txt"

# 清理：移到 trash
curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" -X DELETE \
  "$WP_STAGING/wp-json/wp/v2/posts/$DRAFT_ID" > "$ART_DIR/F2_delete.json"
```

**判定通过**：draft 创建成功；publish 成功；trash 成功。前台是否立即可见取决于缓存，记录但不强求。

#### F3 媒体库上传（API）

```bash
# 用一张小测试图（如果项目里没有，curl 抓一张占位图）
curl -fsS "https://via.placeholder.com/100.png" -o "$ART_DIR/F3_test.png" 2>/dev/null \
  || dd if=/dev/urandom of="$ART_DIR/F3_test.png" bs=1024 count=10

UPLOAD=$(curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" -X POST \
  -H "Content-Type: image/png" \
  -H "Content-Disposition: attachment; filename=acceptance_test.png" \
  --data-binary "@$ART_DIR/F3_test.png" \
  "$WP_STAGING/wp-json/wp/v2/media")
MEDIA_ID=$(echo "$UPLOAD" | jq -r '.id')
MEDIA_URL=$(echo "$UPLOAD" | jq -r '.source_url')
echo "$UPLOAD" > "$ART_DIR/F3_upload.json"

# 验证 URL 可访问
curl -fsS -I "$MEDIA_URL" > "$ART_DIR/F3_url_check.txt"

# 清理
curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" -X DELETE \
  "$WP_STAGING/wp-json/wp/v2/media/$MEDIA_ID?force=true" > "$ART_DIR/F3_delete.json"
```

**判定通过**：upload 返回 ID 和 URL；URL HTTP 200。

#### F4 Polylang 双语切换

Polylang 暴露 `/wp-json/pll/v1/*` 端点（如启用 Polylang REST 扩展）。

```bash
curl -sS -u "$WP_USER:$WP_APP_PASSWORD" \
  "$WP_STAGING/wp-json/pll/v1/languages" > "$ART_DIR/F4_languages.json" 2>&1 \
  || echo "Polylang REST not available" > "$ART_DIR/F4_languages.json"
```

**判定**：
- 如果端点可用且返回语言列表 → ✅
- 如果端点 404 → 通过 ZH/EN 首页 API 返回的 `lang` 字段间接验证（B1/B2 已覆盖），标记为 ✅ with note "Polylang REST disabled, verified via i18n API"
- 双语完全异常（如 EN 路由整个挂掉）→ 升级

#### F5 carbon-fields 自定义字段

carbon-fields 没有标准 REST endpoint。验证方式：
- 通过项目自定义的 homepage API（B1/B2 已经在用）字段是否齐全间接验证
- 如果字段缺失或为 null → 升级老卢，确认是 staging 数据未配置还是 carbon-fields 失效

```bash
# 检查 ZH homepage API 关键字段非空
jq -e '.hero // .modules // .sections | length > 0' "$ART_DIR/B1_api_zh.json" \
  > "$ART_DIR/F5_carbon_check.txt" || echo "FAIL: carbon-fields 数据为空" > "$ART_DIR/F5_carbon_check.txt"
```

**判定通过**：homepage API 关键字段非空。

---

### G. 部署链路稳健性

#### G1 无修改 redeploy

```bash
railway service Mindhikers-Homepage
# 推一个 noop commit（最干净的方式，触发 Railway 自动 build）
git commit --allow-empty -m "ops(noop): redeploy verification

refs MIN-167"
git push origin staging

# 用 3.3 节的 build 等待循环
```

**判定通过**：build SUCCESS；前台 curl 仍 200。

#### G2 Build log 警告 review

```bash
DEPLOY_ID=$(railway deployment list --json | jq -r '[.[] | select(.status == "SUCCESS")][0].id')
railway logs --build "$DEPLOY_ID" --lines 500 > "$ART_DIR/G2_build.log"
grep -iE 'warn|deprecated|peer dep' "$ART_DIR/G2_build.log" \
  | sort -u > "$ART_DIR/G2_warnings.txt"
wc -l < "$ART_DIR/G2_warnings.txt"
```

**判定通过**：列出所有 warning。pnpm peer dep 警告通常可忽略；Next.js metadata 警告记录但不修。

#### G3 `.dockerignore` 中 `pnpm-lock.yaml` 排除规则

```bash
grep -n 'pnpm-lock' .dockerignore > "$ART_DIR/G3_dockerignore.txt"
git log --oneline --all -- .dockerignore | head -5 >> "$ART_DIR/G3_dockerignore.txt"
grep -iE 'lockfile|frozen' "$ART_DIR/G2_build.log" > "$ART_DIR/G3_lockfile_usage.txt"
```

**判断逻辑**：
- 如果 build log 显示 `Lockfile is up to date` → lockfile 被使用 → 排除规则**有问题**，应回退
- 如果 build log 显示 `No lockfile found` → lockfile 没被用 → 排除规则**生效但有副作用**，应回退
- 两种情况下，建议都是删除 `.dockerignore` 中的 `pnpm-lock.yaml` 行

**修法**：
```bash
# 编辑 .dockerignore，删除 pnpm-lock.yaml 那一行
git diff .dockerignore
git add .dockerignore
git commit -m "ops(docker): restore pnpm-lock.yaml in build context for reproducible installs

refs MIN-167"
git push origin staging
# 等 build SUCCESS
```

**回退**：失败立即 `git revert`。

#### G4 5 次失败 commit 是否 squash

**默认不 squash**（保留历史利于追溯）。本项跳过。

#### G5 Runtime 长稳

```bash
railway logs --deployment --lines 1000 > "$ART_DIR/G5_runtime_long.log"
grep -ciE 'error|crash|restart|killed|OOM' "$ART_DIR/G5_runtime_long.log" > "$ART_DIR/G5_error_count.txt"
```

**判定通过**：无 OOM、无重启循环、无周期性 error。

---

## 7. 修复执行规范

### 7.1 修复触发条件

**只有以下情况立刻修**：
- 红线问题（第 5 章 RED-x）
- 缺失的标准 metadata（D1/D2/D3/D4/D6/D7）
- 缺失的安全头（不含 CSP 和 HSTS）
- 单点小 bug，修复 < 30 行代码、不跨模块

### 7.2 修复前必做

1. 检查改动是否在本方案范围内（见 1.2）
2. 看 `docs/04_progress/rules.md` 有无相关历史踩坑记录
3. 列出要改的文件（≤ 3 个），确认无副作用

### 7.3 修复后必做

1. 本地能复现 → 本地先测过（如适用）
2. push 到 staging
3. 走 3.3 节 build 等待循环
4. curl 复测对应验收项
5. 把 commit hash 写到验收报告对应项备注

### 7.4 立即升级的情形

任何一项命中，**立即在 MIN-167 评论 + 写入 `docs/testing_reports/escalations.md`，停手**：
- 改动 > 30 行
- 跨 ≥ 2 个模块
- 涉及 `next.config.ts` / `middleware.ts` / WordPress 插件代码
- 涉及 Railway service Variables 增删改
- 影响 production 行为
- 本方案没明确写如何修

---

## 8. 证据与产出物落盘规范

### 8.1 目录结构

```
docs/
├── plans/
│   └── 2026-05-01_Staging_Deep_Acceptance_Outsourced_Execution_Plan.md   ← 本方案
├── testing_reports/
│   ├── 2026-05-XX_staging_acceptance_report.md                            ← 最终报告
│   └── escalations.md                                                     ← 升级记录（按需创建）
├── testing_artifacts/
│   └── 2026-05-XX_staging/                                                ← 全文本证据
│       ├── A1_summary.json
│       ├── A1_headers.txt
│       ├── A1_body.html
│       ├── A1_metadata.json
│       ├── ...
│       ├── C1_lh_mobile.json
│       ├── G2_build.log
│       └── G5_runtime_long.log
├── dev_logs/
│   ├── HANDOFF.md                                                         ← 终态覆盖更新
│   └── 2026-05-XX.md                                                      ← 当日日志
└── 04_progress/
    └── rules.md                                                           ← 仅在踩了新坑时 +1~2 行
```

### 8.2 验收报告模板

新建 `docs/testing_reports/2026-05-XX_staging_acceptance_report.md`：

````markdown
# Staging 深度验收报告 — 2026-05-XX

执行：外包 AI（refs MIN-167）
分支：staging
起始 commit：<开工时 staging HEAD>
终止 commit：<结束时 staging HEAD>
状态：✅ 通过 / ⚠️ 部分通过 / ❌ 不通过

## 验收概览

| 组 | 通过 | 警告 | 失败 | 升级 |
|---|---|---|---|---|
| A 功能/路由 | x/11 | x | x | x |
| B 数据流 | x/6 | x | x | x |
| C 性能 | x/4 | x | x | x |
| D SEO | x/7 | x | x | x |
| E 安全 | x/5 | x | x | x |
| F CMS | x/5 | x | x | x |
| G 部署 | x/5 | x | x | x |
| 合计 | x/43 | x | x | x |

## 修复记录

| commit | 类型 | 验收项 | 摘要 |
|---|---|---|---|
| abc1234 | fix | D5 | 加 app/robots.ts 区分环境 |
| ... | ... | ... | ... |

## 详细结果

### A. 功能性

#### A1 中文首页 /
- 状态：✅
- HTTP：200
- 证据：testing_artifacts/2026-05-XX_staging/A1_summary.json + A1_metadata.json
- 关键断言：lang=zh-CN, title 长度 X, size Y B
- 备注：无

#### A2 ...
（以此类推到 G5）

## 遗留问题（不阻断验收，建议老卢决策）

1. **CSP `'unsafe-inline'`** — E3，行业现状，建议单独立项做 nonce 化
2. **HSTS 未设置** — E2，建议 production 推送前补
3. **<其他遗留项>**

## 升级记录引用

参见 docs/testing_reports/escalations.md（如有）

## 给老卢的 production 推送建议

（基于验收结果，外包给出建议但**不执行**）

- 是否合并到 main：建议（方案 a/b/c 选哪个，理由）
- 必须先在 production 同步的修复：commit list
- 待确认事项：…
````

### 8.3 升级文件模板

新建 `docs/testing_reports/escalations.md`（首次升级时创建）：

```markdown
# 验收升级记录 — refs MIN-167

## E-001 — <YYYY-MM-DD HH:MM> — <一句话>

**触发条件**：<具体哪条触发了 7.4>
**已尝试**：<时间序>
**当前状态**：等待老卢拍板
**推荐方案**：<a/b/c 选项 + 推荐哪个 + 理由>
**仅当老卢确认后才动**：<具体改动范围>
**Linear 评论**：<链接到 MIN-167 对应 comment>

---

## E-002 ...
```

### 8.4 当日日志

`docs/dev_logs/2026-05-XX.md` 控制 ≤ 80 行，记录：勾掉的项 / commit / 卡点 / 升级 / 明天计划。不要写流水账。

### 8.5 HANDOFF 终态

最后一天**覆盖更新** `docs/dev_logs/HANDOFF.md`，前两行必须是：
```
🕐 Last updated: 2026-05-XX HH:MM CST
🌿 Branch: `staging`
```

内容：终态、修复 commit 列表、遗留问题、验收报告路径、production 推送建议。

---

## 9. 完成判定与交付

### 9.1 验收通过的硬指标

- [ ] A1–A11 全部 ✅
- [ ] B1–B6 全部 ✅ 或明确注明（如 EN 翻译不全为数据问题）
- [ ] C1–C4 全部记录基线
- [ ] D1–D7 全部 ✅，**D5 robots.txt 必须修好**
- [ ] E1–E5 全部 ✅ 或现状记录（E2/E3 已知不动项）
- [ ] F1–F5 全部 ✅ 或经升级标注
- [ ] G1–G5 全部 ✅，**G3 必须有明确决策（保留或回退）**
- [ ] 验收报告落盘
- [ ] HANDOFF 更新
- [ ] 所有修复 push 到 staging 且 build SUCCESS
- [ ] MIN-167 留总结评论

### 9.2 交付物清单

1. `docs/testing_reports/2026-05-XX_staging_acceptance_report.md`
2. `docs/testing_artifacts/2026-05-XX_staging/`（全文本证据）
3. `docs/testing_reports/escalations.md`（如有升级）
4. `docs/dev_logs/2026-05-XX.md`（当日日志）
5. `docs/dev_logs/HANDOFF.md`（终态覆盖）
6. `docs/04_progress/rules.md`（如有新沉淀）
7. 所有修复 commit push 到 `origin/staging`，build SUCCESS
8. MIN-167 总结评论

### 9.3 不通过的处理

- 任一组完全无法推进 → 立即升级
- 部分项失败但能继续 → 标记 ⚠️，写明原因和影响，继续推
- 根本性问题（如某 API 完全挂） → 停手等老卢决定

---

## 10. 风险与升级路径

### 10.1 升级触发条件

**任何一项命中，停手不超过 20 分钟**：
- 修改 > 30 行的修复
- 涉及 main 分支或 production
- build 连续失败 ≥ 2 次
- 怀疑影响数据安全/隐私
- 本方案没明确写如何处理

### 10.2 升级动作（双轨）

**轨道 1：Linear 评论**
在 [MIN-167](https://linear.app/mindhikers/issue/MIN-167/) 留 comment：
```
[ESCALATION E-XXX] <一句话>

触发：<具体条件>
已尝试：<时间序>
推荐方案：<a/b/c 选项+推荐+理由>
等老卢确认才动：<具体改动范围>
工件：<链接到 docs/testing_reports/escalations.md 对应条目>
```

**轨道 2：写入 escalations.md**
按 8.3 模板追加一条 E-XXX 记录。

两条都做完后**停手**，处理其他不冲突的验收项（如有）；如果是阻塞性问题，全部停手。

### 10.3 已知风险清单

| ID | 风险 | 概率 | 影响 | 应对 |
|---|---|---|---|---|
| RED-1 | robots.txt SEO 漏 | 已发生 | 高 | 5.1 节方案 A |
| YEL-1 | HSTS 缺 | 中 | 中 | E2 记录，本次不动 |
| YEL-2 | CSP unsafe-inline | 已知 | 中 | E3 记录，本次不动 |
| YEL-3 | main 没合修复 | 已知 | 高（推送时）| 不在外包范围 |
| ??? | EN 内容翻译不全 | 未知 | 中 | A7/B2 记录，不修 |
| ??? | WP_APP_PASSWORD 缺失 | 未知 | F 段全停 | 4.1 自检脚本检测 |
| ??? | Polylang REST 未启用 | 未知 | 低 | F4 降级方案 |

---

## 11. production 推送决策（不在外包范围）

外包 AI **不要触碰**这一步。验收完后由老卢决定：

| 方案 | 做法 | 优点 | 缺点 |
|---|---|---|---|
| (a) merge staging → main | `git checkout main && git merge staging && git push` | 一次性同步 | 把 MIN-30 那批 debug 历史也带过去 |
| **(b) cherry-pick 最小修复** | 只挑 `18106e9` + `a57bff5` + 本次新增的修复 commit 到 main | main 历史干净 | 多走一步 |
| (c) 不动 main | 等下次正常 release | 最保守 | production 是定时炸弹 |

外包 AI 在最终报告"给老卢的 production 推送建议"部分给出建议即可。

---

## 12. 时间预算与里程碑

### 12.1 三档预算

| 档 | 总工时 | 说明 |
|---|---|---|
| 顺风 | 4–6 小时 | 一个工作日内 |
| 正常（最可能） | 1–1.5 工作日 | 含 D 段补全 |
| 有暗坑 | 2 工作日 | CSP/sitemap/缓存逻辑等需排查 |

### 12.2 推荐执行节奏

**第一段（≈ 2h）**：
- 4.1 自检脚本，凭证齐全确认
- 修红线 RED-1（robots.txt）→ push → 验
- 推 A 段（11 项）

**第二段（≈ 2h）**：
- 推 B 段（B3 等 5m，期间并行 C 段）
- 推 C 段（Lighthouse + bundle）

**第三段（≈ 2h）**：
- 推 D 段（重点：补 metadata、sitemap、canonical）
- 推 E 段（主要记录）

**第四段（≈ 2h）**：
- 推 F 段（API 模拟）
- 推 G 段（含 redeploy 验证、.dockerignore 决策）
- 写验收报告 + HANDOFF
- MIN-167 总结评论

---

## 附录 A：常用命令速查

### Railway CLI

```bash
railway status
railway environment <staging|production>
railway service <Mindhikers-Homepage|WordPress-L1ta>
railway deployment list --json
railway logs --build --latest --lines 100
railway logs --build <DEPLOYMENT_ID>
railway logs --deployment --lines 200
railway logs --http --status ">=400" --lines 50
railway redeploy
railway domain --service <name>
railway variables --json
```

### 健康检查一键

```bash
cd <repo> && \
  railway status && \
  git branch --show-current && \
  git log --oneline -5 && \
  curl -sS -o /dev/null -w "frontend: HTTP %{http_code}\n" https://mindhikers-homepage-staging.up.railway.app/ && \
  curl -sS -o /dev/null -w "backend:  HTTP %{http_code}\n" https://wordpress-l1ta-staging.up.railway.app/ && \
  curl -sS "https://mindhikers-homepage-staging.up.railway.app/health"
```

### WP REST API 速查

```bash
# 当前用户
curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" "$WP_STAGING/wp-json/wp/v2/users/me"

# 列文章
curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" "$WP_STAGING/wp-json/wp/v2/posts?per_page=10"

# 创建草稿
curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" -X POST -H "Content-Type: application/json" \
  -d '{"title":"x","content":"y","status":"draft"}' \
  "$WP_STAGING/wp-json/wp/v2/posts"

# 上传媒体
curl -fsS -u "$WP_USER:$WP_APP_PASSWORD" -X POST \
  -H "Content-Type: image/png" -H "Content-Disposition: attachment; filename=x.png" \
  --data-binary "@./x.png" "$WP_STAGING/wp-json/wp/v2/media"
```

---

## 附录 B：关键文件速查

| 文件 | 作用 |
|---|---|
| `package.json` | `packageManager: "pnpm@9.15.9"` 是关键，**别动** |
| `railway.json` | 根目录，`builder: "RAILPACK"` |
| `ops/mindhikers-cms-runtime/railway.json` | WordPress 服务专用，DOCKERFILE |
| `.dockerignore` | G3 待决策项 |
| `next.config.ts` | 全局配置；如要加 headers 在这里 |
| `app/layout.tsx` | 全局 metadata、html lang、字体 |
| `app/robots.ts` | RED-1 修复后新建 |
| `app/sitemap.ts` | D6 修复后新建 |
| `app/api/revalidate/route.ts` | B4 测试目标 |
| `docs/dev_logs/HANDOFF.md` | 会话交接 |
| `docs/04_progress/rules.md` | 经验沉淀 |

---

## 附录 C：紧急回退手段（仅 staging）

```bash
git checkout staging
git revert <bad_commit_hash>
git push origin staging
# 走 3.3 节等待循环
```

如需连续多个回退：
```bash
git log --oneline -20
git reset --hard <开工时 commit>   # 谨慎！会丢工作区
git push --force-with-lease origin staging   # 必须先在 MIN-167 留 comment 等老卢确认
```

**任何形式 force push 必须先得到老卢确认。**

---

## 附录 D：本方案修订记录

| 日期 | 版本 | 修订者 | 摘要 |
|---|---|---|---|
| 2026-05-01 | v1.0 | OldYang | 初稿（人受众） |
| 2026-05-01 | v1.1 | OldYang | 受众切到外包 AI；GUI 操作改 CLI 等价物；增加 Linear 跟踪；预设条件自检 |

---

(End of document)

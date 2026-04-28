🕐 Last updated: 2026-04-28 01:00 CST
🌿 Branch: `staging`
📌 Latest commit: `a3140ba` ops: use exact pnpm version 9.15.9 for RAILPACK
🚀 Push status: ✅ 已 push，前台部署 FAILED

---

## 当前状态：根目录 railway.json 全局 DOCKERFILE 问题已定位，前台仍 502

**一句话**：根目录 `railway.json` 错误地把 WordPress Dockerfile 推给了所有服务，前台 Next.js 因此跑成 WordPress。已做配置分离，但前台改用 RAILPACK builder 后遭遇 Railpack pnpm 解析 bug，连续 4 次部署全部失败。后台 WordPress 正常。

---

## 本窗口完成内容

### ✅ 已完成

1. **诊断前台 502 根因**
   - Railway 项目有 3 个服务：WordPress-L1ta、Mindhikers-Homepage、MariaDB
   - 根目录 `railway.json` 配置 `builder: "DOCKERFILE"`，被所有服务共享
   - 前台 Next.js 服务因此被强制使用 WordPress Dockerfile，启动 PHP+Apache 而非 Next.js
   - 结果：前台返回 502（实际上跑的是 WordPress 镜像，没有前台路由）

2. **分离 WordPress 后台构建配置**
   - 新建 `ops/mindhikers-cms-runtime/railway.json`（内容与原根目录一致）
   - 在 Railway Dashboard → WordPress-L1ta 服务 → Settings → Config File 绑定：`/ops/mindhikers-cms-runtime/railway.json`
   - 验证：WordPress 重新部署 SUCCESS，API 正常 200

3. **修改根目录 `railway.json` 为前台 Next.js 配置**
   - 将 `builder: "DOCKERFILE"` 改为 `builder: "RAILPACK"`
   - 删除 `dockerfilePath`（不再需要）

4. **RAILPACK pnpm 版本解析问题 — 4 次尝试全部失败**
   - 尝试 1：纯 RAILPACK，无额外配置 → `Failed to resolve version 9 of pnpm`
   - 尝试 2：`package.json` 加 `"pnpm": ">=9.0.0"` → 同上
   - 尝试 3：`.dockerignore` 排除 `pnpm-lock.yaml` → 同上
   - 尝试 4：`package.json` 改为 `"pnpm": "9.15.9"` → `Failed to resolve version 9.15.9 of pnpm`
   - **结论**：Railpack v0.23.0 的 pnpm 版本解析器存在根本性 bug，不是配置问题

5. **提交的 commit 序列**
   | hash | message | 说明 |
   |---|---|---|
   | `125743e` | ops: add WordPress-specific railway.json for per-service build config | WordPress 专用配置 |
   | `61f395d` | ops: switch root railway.json to RAILPACK for Next.js frontend | 根目录切 RAILPACK |
   | `eb4ebca` | ops: add pnpm engine version to fix RAILPACK auto-detection | engines 加 pnpm |
   | `8a0bf24` | ops: exclude pnpm-lock.yaml from RAILPACK to bypass version parsing bug | dockerignore 排除 lock |
   | `a3140ba` | ops: use exact pnpm version 9.15.9 for RAILPACK | 精确版本号 |

---

### ❌ 当前阻塞

**RAILPACK pnpm 版本解析 bug**

- 现象：Railpack v0.23.0 在解析 pnpm 版本时始终失败
- 错误信息：`Failed to resolve version 9 of pnpm` / `Failed to resolve version 9.15.9 of pnpm`
- 影响：前台 Next.js 服务无法构建，无法部署
- 根因分析：Railpack 从 `pnpm-lock.yaml` 读取 `lockfileVersion: '9.0'`，误将其当作 pnpm 版本号去请求（如请求 npm 仓库 `pnpm@9` 或 `pnpm@9.15.9`），但该版本不存在导致解析失败。即使 `engines.pnpm` 声明了版本、即使排除 lockfile，Railpack 内部逻辑仍可能保留缓存或解析路径

---

## 验收域名

| 环境 | 域名 | 状态 |
|---|---|---|
| 后台 CMS | `https://wordpress-l1ta-staging.up.railway.app/wp-admin` | ✅ 正常 |
| 前台 Next.js | `https://mindhikers-homepage-staging.up.railway.app` | ❌ 502（RAILPACK 构建失败） |
| ZH API | `https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/zh` | ✅ 正常 |
| EN API | `https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/en` | ✅ 正常 |

---

## 下一步（需要决策）

**核心问题**：如何修复前台 Next.js 服务的构建方式，绕过 RAILPACK pnpm bug。

| 方案 | 做法 | 风险 | 工作量 |
|---|---|---|---|
| **A. 改用 NIXPACKS** | `railway.json` builder 从 `RAILPACK` 改为 `NIXPACKS` | NIXPACKS 是旧版，可能对 pnpm 9 支持也不好；可能同样失败 | 1 分钟改配置 |
| **B. 给前台写 Dockerfile** | 新建 `ops/frontend/Dockerfile`，基于 Node 镜像手动 `pnpm install` → `pnpm build` → `pnpm start` | 最可控，但需要写 Dockerfile；需要测试本地构建 | 10-15 分钟 |
| **C. 改回 DOCKERFILE + 分别配置** | 根目录 `railway.json` 恢复 DOCKERFILE builder，前台另写一个 Next.js Dockerfile | 两个服务都用 DOCKERFILE，最一致；需要写前台 Dockerfile | 10-15 分钟 |
| **D. 删除 `railway.json` 让 Railway 自动检测** | 不指定 builder，看 Railway 自己选什么 | 可能还是选 RAILPACK（检测到 Node.js 后默认用 RAILPACK），继续失败 | 1 分钟 |
| **E. 降级 pnpm 版本** | 尝试 `engines.pnpm: "8.15.0"`，pnpm 8 的 lockfileVersion 不同 | 需要重新生成 lockfile（pnpm install），可能影响依赖兼容性 | 5-10 分钟 |

**推荐方案**：**B**（前台专用 Dockerfile），因为：
1. RAILPACK 的 pnpm 解析器明显有 bug，NIXPACKS 也可能同样问题
2. Dockerfile 最可控，不受 Railway builder 解析器影响
3. 之前 DOCKERFILE builder 对 WordPress 的构建是成功的，说明 DOCKERFILE builder 本身没问题

---

## 给新窗口的上下文

- 当前分支：`staging`
- 当前 commit：`a3140ba`
- WordPress 后台：`Online`，API 正常，Dashboard 上 WordPress-L1ta 服务已绑定 `ops/mindhikers-cms-runtime/railway.json`
- 前台 Next.js：`502`，Mindhikers-Homepage 服务使用根目录 `railway.json`（builder: RAILPACK），连续 4 次部署 FAILED
- Railway 项目：`Mindhikers-Homepage`，staging 环境，3 个服务
- 验证命令：
  - 后台 API：`curl -sL "https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/en"`
  - 前台状态：`curl -sL "https://mindhikers-homepage-staging.up.railway.app"`
- 关键文件变更（未合并到 main）：
  - `ops/mindhikers-cms-runtime/railway.json`（新建）
  - `railway.json`（从 DOCKERFILE 改为 RAILPACK）
  - `package.json`（engines 加 pnpm）
  - `.dockerignore`（排除 pnpm-lock.yaml）

---

## 回退手段

如需回退到 502 之前的已知状态：
```bash
git checkout 8a77167 -- railway.json
git reset --soft 8a77167
git checkout HEAD -- package.json .dockerignore
git push origin staging --force  # ⚠️ 需要老卢确认
```
> 注意：`8a77167` 是上一个已知状态（根目录 railway.json 为 DOCKERFILE，前台 502），回退不会修复前台，只是回到可预测状态。

---

(End of file)

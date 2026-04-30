🕐 Last updated: 2026-04-30 CST
🌿 Branch: `staging`
📌 Latest commit on staging: `a57bff5` ops(railpack): add packageManager field for corepack-style resolution
🚀 Push status: ✅ 已 push，前台 Plan B 部署 SUCCESS

---

## 当前状态：staging 全绿，production 在跑但配置脆弱

**一句话**：staging 前台 502 已修复（Railpack pnpm 9.x 不支持，改用 `packageManager` 字段触发 corepack 路径绕开）。staging 可以验收。Production 当前还在跑 4 天前那次成功部署的旧镜像，但 main 分支当前的配置组合（RAILPACK builder + 缺 `packageManager`）跟修复前的 staging 一样脆弱——下次推任何 commit 到 main 都会复现 pnpm 解析失败。

---

## 验收域名 & 状态（2026-04-30 实测）

### Staging（本次修复目标，✅ 全绿）

| 角色 | 域名 | HTTP | 说明 |
|---|---|---|---|
| 前台 Next.js | https://mindhikers-homepage-staging.up.railway.app | **200** | ✅ Plan B 修复后正常，Next.js 16.1.7 在跑 |
| 后台 WordPress | https://wordpress-l1ta-staging.up.railway.app | 200 | ✅ Apache+PHP 正常 |
| ZH API | https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/zh | 200 | ✅ |
| EN API | https://wordpress-l1ta-staging.up.railway.app/wp-json/mindhikers/v1/homepage/en | 200 | ✅ |

### Production（暂时在跑，⚠️ 配置已脆弱）

| 角色 | 域名 | HTTP | 说明 |
|---|---|---|---|
| 前台主域名 | https://mindhikers.com | 200 | ⚠️ 跑的是 4-24 的旧镜像（commit `a7cabf16`） |
| 前台 www | https://www.mindhikers.com | 301 → mindhikers.com | ⚠️ 同上 |
| 前台 Railway | https://mindhikers-homepage-production.up.railway.app | 200 | ⚠️ 同上 |
| 后台 CMS | https://homepage-manage.mindhikers.com | 302（登录跳转，正常） | ✅ |
| 后台 Railway | https://wordpress-l1ta-production.up.railway.app | 200 | ✅ |
| ZH API（prod） | https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh | 200 | ✅ |

⚠️ 标记说明：production 前台**当前能访问**没问题，但 main 分支的 `railway.json` 已经被 commit `8744c4f` 切到 RAILPACK，且 main 上的 `package.json` 还没有 `packageManager` 字段——下次任何 push 到 main 都会复现 staging 之前那 5 次失败。production 只是"还没触发部署"才暂时活着。

---

## 本窗口完成内容

### ✅ 修复路径（A→B→C→D 递进策略，止步于 B）

1. **重新诊断**：上一份 HANDOFF 把"前台 502"归因为"Railpack pnpm bug 阻塞构建"——表面对，但有遗漏：Railway 在新部署连续失败时**自动回滚到 9 小时前那次 SUCCESS 的镜像**（基于当时根目录 `railway.json` 还是 DOCKERFILE + WordPress Dockerfile），所以前台容器实际跑的是 Apache+WordPress，前端访问当然 502。这点纠偏靠 `railway logs --build`/`railway logs --deployment` 直接拉 ACTIVE 部署日志看到 `Apache/2.4.66 (Debian) PHP/8.3.30 configured` 验证。

2. **拿到 Railpack 真实错误**（用 `railway logs --build --latest`，此前 5 次失败的原始 build log）：
   ```
   using build driver railpack-v0.23.0
   ↳ Detected Node
   ↳ Using pnpm package manager
   ✖ Failed to resolve version 9.15.9 of pnpm
   railpack process exited with an error
   ```

3. **Plan A**（commit `18106e9`）：删除 `package.json` 的 `engines.pnpm: "9.15.9"`。
   - 结果：FAILED，但错误从 "9.15.9" 变成 "**9**"——证明 Railpack 退而读 `pnpm-lock.yaml` 的 `lockfileVersion: '9.0'` 推断主版本，但**连主版本 9 也解析不了**。结论：Railpack 0.23.0 的内置 pnpm 版本索引根本不支持 pnpm 9.x 系列。

4. **Plan B**（commit `a57bff5`）：在 `package.json` 加 `"packageManager": "pnpm@9.15.9"`（Corepack 标准字段）。
   - 结果：**SUCCESS**。Railpack 看到 `packageManager` 后改走 corepack 路径，直接从 npm 拉 pnpm 9.15.9（绕过 Railpack 自己的版本索引）。Build log 出现 `copy /opt/corepack` 是直接证据。Next.js build 全过，所有路由（包括 `/`、`/en`、`/api/revalidate`、`/health` 等）正常生成；deploy log 显示 `▲ Next.js 16.1.7 ✓ Ready in 141ms`。

5. **未启用方案 C（NIXPACKS）和 D（写 Dockerfile）**——B 已成功，无需。

### 关键技术发现（值得长期记忆）

> **Railpack v0.23.0 的内置 pnpm 版本索引不包含 pnpm 9.x 系列**。
> 修法：在 `package.json` 设置 `"packageManager": "pnpm@<version>"`（Corepack 标准字段）。Railpack 会改走 corepack 路径，从 npm 直接安装精确版本。
> 注意：`engines.pnpm` 字段反而会触发 Railpack 自身的 resolver，建议在用 Railpack + pnpm 9.x 时**只用 `packageManager`，不要再写 `engines.pnpm`**。

---

## 现在留下的两组 commit（都在 staging）

| hash | message | 状态 |
|---|---|---|
| `a57bff5` | ops(railpack): add packageManager field for corepack-style resolution | ✅ Plan B，让 staging build 通过 |
| `18106e9` | ops(railpack): drop engines.pnpm to bypass Railpack version resolver | Plan A，删除有害的 engines.pnpm |
| `e01c584` | docs(handoff): update Railway build config split and RAILPACK pnpm bug status | 上一份 HANDOFF |
| `a3140ba` ~ `61f395d` | 之前 5 次失败尝试 | 历史，可保留也可后续 squash |
| `125743e` | ops: add WordPress-specific railway.json for per-service build config | WordPress 配置分离，已生效 |

---

## 下一步建议（需要老卢决策）

### 待办 1：把 Plan B 修复合到 main，让 production 也安全

**为什么必须做**：production 现在能访问，是因为 4-24 那次 SUCCESS 镜像还在 ACTIVE。但 main 分支 `8744c4f` 已经把 builder 切到 RAILPACK，**下次 push 到 main 触发部署就会炸**。

**怎么做**（任选其一）：
- 选项 a：`git checkout main && git merge staging`（带过去全部 staging 的修改，包括 MIN-30 那批 debug 历史）。然后 push origin main，触发 production 自动部署。
- 选项 b：cherry-pick 最小修复集到 main（只挑 `18106e9` + `a57bff5`），让 production 部署只带必需的修复。其他 staging 上的内容延后再合。
- 选项 c：现在不动 main，让 production 继续跑 4-24 的旧镜像，等下一次正常 release 时一起合。**风险**：万一 Railway 出于任何原因（Metal builder 升级、image GC 等）需要重新 build，production 会瞬间挂掉。

我推荐：**选项 b**（cherry-pick 最小修复，立刻合 main 并 push，让 production 重新部署一遍验证它也能 build 出来）。这样 production 配置也进入"健康"状态，不再有定时炸弹。

### 待办 2：HANDOFF 之外的清理（次要）

- staging 上 5 次失败尝试的 commit（`61f395d` ~ `a3140ba`）历史价值已经定格在这份 HANDOFF 里。是否 squash 看团队习惯。
- `.dockerignore` 在 `8a0bf24` 加过的"排除 pnpm-lock.yaml"那行——其实现在已经不需要了（Plan B 走 corepack 不依赖那个排除）。可以反向 review 后再决定是否回退。

---

## 给新窗口的上下文

- **当前分支**：`staging`，最新 commit `a57bff5`，与 origin/staging 同步
- **当前 staging 状态**：前后台都正常，可以验收
- **production 状态**：当前 ACTIVE 部署是 4-24 的 commit `a7cabf16`（基于当时的 main 分支配置），跑得好；但 main 分支当前 HEAD `95008e6` 的配置（RAILPACK + 缺 `packageManager`）一旦触发新部署就会失败
- **Railway 项目**：`Mindhikers-Homepage`，三个服务（Mindhikers-Homepage / WordPress-L1ta / MariaDB）
- **CLI 用法快速参考**：
  - 看任意 deployment 的 build log：`railway logs --build <DEPLOYMENT_ID>`
  - 看最新（即使失败）：`railway logs --build --latest`
  - 列部署：`railway deployment list --json`
  - 切环境：`railway environment <staging|production>`
  - 切 service：`railway service <Mindhikers-Homepage|WordPress-L1ta|MariaDB>`

---

## 回退手段（仅 staging）

如需回退本次 staging 修复：
```bash
git checkout staging
git revert a57bff5 18106e9  # 撤销 Plan A + Plan B
git push origin staging
```
> 回退后 staging 会立刻回到 502，因为 RAILPACK 又会被 pnpm 9.x 卡住。无确切理由不要回退。

---

(End of file)

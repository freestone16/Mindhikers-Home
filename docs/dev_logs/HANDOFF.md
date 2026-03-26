🕐 Last updated: 2026-03-26 13:14
🌿 Branch: codex/cyd-stumpel-home-exploration

## 当前状态
- 当前阶段已基本收口完成，可进入“灌内容 + 细调排版”下一阶段。
- 完成第一批与第二批安全加固：CSP、OG 错误暴露、旧联系人组件空值炸点、博客日期与排序稳定性、MDX 代码块净化、媒体源约束、项目级依赖审计链路。
- `pnpm audit --prod --json` 已清到 0 漏洞。
- 本地开发服务已重启到 `Next 16.1.7`，地址 `http://127.0.0.1:3000`，账本 PID 已更新为 `53150`。
- 当前 Railway 线上部署已更新到 `Next 16.1.7` 并通过浏览器抽检。
- 本地与线上 `/health` 都已验证可用。
- `npm run build` 通过。
- `npm run lint` 通过，只有 1 个既有 warning，来自 `.content-collections/generated/allPosts.js`。

## 本轮改动
- `next.config.mjs` 增加 `Content-Security-Policy`。
- 新增 `src/lib/posts.ts`，统一日期校验、解析与稳定排序。
- `content-collections.ts` 对 `publishedAt` / `updatedAt` 增加 `YYYY-MM-DD` 校验。
- 首页、英文首页、博客列表、博客详情改为复用稳定排序逻辑。
- `src/lib/pagination.ts` 修复无内容时页码可能被压成 `0` 的边界问题。
- 三个 OG 路由不再把内部错误消息回显给外部，同时移除包裹 JSX 的 `try/catch` 以通过 lint。
- `src/components/section/contact-section.tsx` 改为安全读取联系人信息，避免未来误接线时报空值错误。
- 新增项目级 `.npmrc`，固定 registry 到 `https://registry.npmjs.org/`，打通 `pnpm audit`。
- `src/components/mdx/code-block.tsx` 增加 Shiki HTML 的基础净化。
- `src/components/mdx/media-container.tsx` 限制媒体源为相对路径或 `https`，并增加基础降级。
- `package.json` / `pnpm-lock.yaml` 已升级到 `next 16.1.7`、`eslint-config-next 16.1.7`，并通过 `pnpm.overrides` 压住 `picomatch`、`serialize-javascript`、`yaml` 的修复版。
- 删除未使用的旧组件栈：`section/*`、`timeline.tsx`、`project-card.tsx`、`magicui/flickering-grid.tsx`、`magicui/dock.tsx`、`magicui/blur-fade-text.tsx`、`mode-toggle.tsx`。
- 新增 `src/app/health/route.ts` 作为最小健康检查入口。

## WIP
- 还没有把这轮改动做成 commit / push。
- 本地 dev 进程处于运行中，当前有效服务是 `127.0.0.1:3000`。

## 待解决问题
- 下一个阶段建议专注于内容录入、文案梳理与首页/博客排版微调，不必再回头补基础安全设施。
- 如果要继续运维化，可以后续再补 Railway `healthcheckPath` 与更正式的部署检查策略。

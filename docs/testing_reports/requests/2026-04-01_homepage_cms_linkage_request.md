# Homepage CMS Linkage Golden Test Request

## Metadata

- request_id: `gt-homepage-cms-linkage-2026-04-01`
- created_at: `2026-04-01`
- requested_by: `user`
- execution_mode: `golden-testing fallback`

## Goal

验证 Mindhikers Homepage 当前本地环境下的“前后台闭环准备度”：

1. 中文首页 `/` 是否能正常打开
2. 英文首页 `/en` 是否能正常打开
3. revalidate 接口是否已进入可用状态
4. 当前首页内容是否已经进入 CMS 驱动，而不是仍然停留在静态 fallback

## Preconditions

1. 仓库位于 `codex/cyd-stumpel-home-exploration`
2. 本地依赖已安装
3. 本地服务通过 `npm run dev` 启动
4. 页面验证优先使用 `agent-browser`

## Expected

1. `/` 可正常渲染中文首页主要内容
2. `/en` 可正常渲染英文首页主要内容
3. `/api/revalidate` 不应再返回“未配置 secret”
4. 环境变量应能指向候选 CMS，并使首页从 CMS 读取内容

## Evidence Requirements

1. 浏览器快照
2. 页面截图
3. 接口返回文本
4. 本地环境变量与 `.env` 现场核对

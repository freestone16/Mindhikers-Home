# Homepage Staging Smoke Request Template

## Metadata

- request_id: `homepage-staging-smoke-YYYY-MM-DD`
- target_env: `staging`
- module: `homepage`
- browser_execution: `agent-browser`
- model: `zhipuai-coding-plan/glm-5`

## Goal

验证 WordPress staging 首页在当前阶段是否已经达到“可继续导入模板和做内容重建”的基本状态。

## Preconditions

1. staging 环境已建立
2. 已拿到 staging 可访问 URL
3. 如需登录，允许人工协助完成验证码或 Access

## Checks

1. staging 首页是否可打开
2. 导航与主视觉是否正常渲染
3. 页面是否出现明显错误跳转到生产后台域名
4. 页面在手机竖屏下是否仍可读
5. 是否已具备继续导入 `Astra - Interior Designer` 的基本条件

## Evidence Requirements

1. 首页快照
2. 首页截图
3. 手机竖屏截图
4. 如失败，记录具体报错、跳转或白屏现象

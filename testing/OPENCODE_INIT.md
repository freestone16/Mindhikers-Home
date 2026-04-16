# OPENCODE INIT

你正在接手 `Mindhikers-Homepage` 仓库的测试执行。

开始前请严格按以下顺序：

1. 读取 `testing/README.md`
2. 读取目标模块 README
3. 确认当前测试目标环境：
   - local
   - staging
   - production
4. 确认当前测试目标是否涉及页面查看、UI、截图、交互
   - 如涉及，默认优先使用 `agent-browser`
5. 在开始执行前先写 request 文件

## 当前仓库测试边界

1. 当前主线是 WordPress 模版站重建，不再继续扩建旧的 Homepage JSON CMS
2. 旧链路相关验证仅作为迁移期参考，不应误判为长期正式方案
3. 如果 staging 与 production 表现不一致，必须在报告中明确写出环境差异

## 输出要求

1. request 放到 `docs/testing_reports/requests/`
2. report 放到 `docs/testing_reports/`
3. status 放到 `docs/testing_reports/status/`
4. 截图和浏览器证据放到 `docs/testing_artifacts/`

## 报告最低结构

1. Metadata
2. Goal
3. Preconditions
4. Test Summary
5. Verification Results
6. Evidence
7. Follow-up / Next Action

## 浏览器策略

1. 默认优先 `agent-browser`
2. 需要验证首页五区块：
   - `Hero`
   - `About`
   - `Product`
   - `Blog`
   - `Contact`
3. 需要验证 Blog 列表与详情页点击链路
4. 需要验证手机竖屏时的可读性与主要 CTA 可见性

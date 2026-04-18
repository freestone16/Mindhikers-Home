# Mindhikers Homepage Testing Protocol

## 1. 目的

本目录用于给黄金测试和其他接手者提供统一的测试入口，避免继续依赖一次性 fallback 测试。

当前协议重点覆盖：

1. Homepage 五区块可见性
2. Blog 列表与文章详情链路
3. Contact 区块可达性
4. WordPress / 前台联动后的 smoke 与回归

## 2. 执行原则

1. 默认执行模型：`zhipuai-coding-plan/glm-5`
2. 页面查看、UI 验证、截图、交互检查默认优先：`agent-browser`
3. 仅当 `agent-browser` 不适用或不可用时，才退回其他浏览器方案
4. 所有测试都必须先落 request，再产出 report / status / artifacts

## 3. 输出位置

正式测试产物统一落在以下目录：

1. request：
   - `docs/testing_reports/requests/`
2. report：
   - `docs/testing_reports/`
3. status：
   - `docs/testing_reports/status/`
4. artifacts：
   - `docs/testing_artifacts/`

## 4. 执行前必读

1. 先读 `testing/OPENCODE_INIT.md`
2. 再读对应模块 README
3. 明确本次目标环境：
   - local
   - staging
   - production
4. 明确本次目标语言：
   - `zh`
   - `en`
   - 或双语

## 5. 当前模块

1. `testing/homepage/`
   - 首页、博客、联系入口、revalidate 相关验证

## 6. 命名约定

建议使用如下日期前缀：

1. `YYYY-MM-DD_homepage_staging_smoke_request.md`
2. `YYYY-MM-DD_homepage_staging_smoke_report.md`
3. `YYYY-MM-DD_homepage_staging_smoke_status.json`

## 7. 最低证据要求

1. 浏览器快照
2. 至少一张关键页面截图
3. 关键接口返回文本或页面文案证据
4. 明确的 `PASS / FAIL / PARTIAL_PASS`
5. 如失败，必须写出阻塞点和下一步建议

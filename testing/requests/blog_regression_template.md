# Blog Regression Request Template

## Metadata

- request_id: `blog-regression-YYYY-MM-DD`
- target_env: `local-or-staging`
- module: `homepage`
- browser_execution: `agent-browser`
- model: `zhipuai-coding-plan/glm-5`

## Goal

验证 Blog 列表页与文章详情页在当前目标环境下是否保持可访问、可点击、可阅读。

## Checks

1. `/blog` 是否正常打开
2. 文章列表是否可见
3. 至少打开 1 篇文章详情页
4. 标题、日期、正文是否存在
5. 前后篇导航是否正常

## Evidence Requirements

1. 列表页截图
2. 详情页截图
3. 关键标题文本
4. 如失败，写明是列表数据问题、详情问题还是跳转问题

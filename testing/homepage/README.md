# Homepage Module Testing Guide

## 1. 模块范围

本模块覆盖以下路径和能力：

1. 中文首页：`/`
2. 英文首页：`/en`
3. 博客列表：`/blog`
4. 博客详情：`/blog/[slug]`
5. 首页 Contact 区块中的邮箱与外链
6. 必要时验证 `api/revalidate`

## 2. 当前阶段测试目标

当前主线已转向 WordPress 模版站，因此本模块测试分成两类：

1. 迁移期验证
   - 旧前台是否还能作为回滚抓手正常打开
   - 旧 CMS / revalidate 链路是否还能复现
2. 新主线验证
   - staging WordPress 首页是否可访问
   - Astra 模版导入后五区块是否可见
   - Blog 是否逐步统一到 WordPress Posts

## 3. 默认检查项

### 3.1 首页 Smoke

1. 页面是否能打开
2. 导航是否可见
3. `Hero / About / Product / Blog / Contact` 五区块是否都能找到
4. 主 CTA 是否存在且可点击
5. 页底联系入口是否清晰可见

### 3.2 Blog Smoke

1. `/blog` 是否能打开
2. 文章列表是否至少显示 1 条
3. 点击任意一篇文章后，详情页是否能打开
4. 详情页标题、日期、正文是否存在
5. 前后篇导航是否正常

### 3.3 Contact Smoke

1. Contact 区块是否可见
2. 邮箱入口是否正确
3. 联系外链是否存在明显错误

## 4. 环境判定

### local

适用于：

1. 本地 `npm run dev`
2. 回归旧前台与过渡期接口

### staging

适用于：

1. WordPress 模版站首轮验收
2. 模版导入后五区块回归
3. 手机竖屏与基础交互检查

### production

适用于：

1. 切换前 smoke
2. 切换后 smoke
3. 根域与 `www -> apex` 跳转核对

## 5. 当前已知风险

1. 旧前台链路仍可能显示 fallback 内容
2. Blog 在迁移完成前可能仍处于混合来源状态
3. staging 若仍沿用生产后台域名口径，可能出现 URL / 登录跳转混淆

## 6. Request 模板

可直接从以下模板起步：

1. `testing/requests/homepage_staging_smoke_template.md`
2. `testing/requests/blog_regression_template.md`
3. `testing/requests/contact_surface_smoke_template.md`

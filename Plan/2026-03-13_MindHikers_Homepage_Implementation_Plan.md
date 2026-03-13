# MindHikers Homepage Implementation Plan

日期：2026-03-13
仓库：`/Users/luzhoua/Mindhikers-Homepage`
分支：`codex/mindhikers-home-branding`

## 1. 目标

基于当前 Next.js 模板仓库，完成一轮可上线的 `MindHikers` 品牌化改造，重点包括：

1. 去掉原模板中强烈的个人简历叙事。
2. 将首页改造成 `MindHikers` 品牌门户，而非开发者作品集。
3. 提供中英文双版本首页：
   - `/` 中文版
   - `/en` 英文版
4. 首批挂出 `黄金坩埚` 产品入口。
5. 将视觉风格调整为米色、极简、暖棕强调的品牌方向。

## 2. 实施原则

1. 先做轻量双语，不引入重型 i18n 框架。
2. 采用“共享页面组件 + 双语数据文件”模式，控制改动面。
3. 尽量保留当前模板已有的动画与基础组件能力，但移除不符合品牌语境的简历模块。
4. 避免编造外部品牌资产与联系方式，首版以内链和品牌文案为主。

## 3. 计划改造项

### 3.1 信息架构

首页首版调整为以下结构：

1. Hero
   - 品牌标题
   - 品牌一句话说明
   - 两个 CTA：查看 `黄金坩埚`、切换语言
2. About
   - 介绍 `MindHikers` 的内容与产品定位
3. Products
   - 重点展示 `黄金坩埚`
4. Projects
   - 展示品牌当前承载的方向与实验性项目
5. Tools
   - 展示团队方法、工作流或支持能力
6. Closing CTA
   - 引导继续探索品牌与产品

### 3.2 路由方案

1. `src/app/page.tsx` 作为中文首页入口
2. `src/app/en/page.tsx` 作为英文首页入口
3. `src/app/golden-crucible/page.tsx` 提供中文产品入口页
4. `src/app/en/golden-crucible/page.tsx` 提供英文产品入口页

### 3.3 数据与组件拆分

新增或调整：

1. 新建双语数据文件，承载首页文案、导航、产品卡片、模块标题。
2. 新建共享首页渲染组件，避免中英文页面重复布局。
3. 重写导航组件，使其支持语言切换与品牌站导航。
4. 调整 `layout` 和全局样式，使视觉主题切换到米色极简方向。

## 4. 预期修改文件

计划重点涉及：

1. `src/app/layout.tsx`
2. `src/app/globals.css`
3. `src/app/page.tsx`
4. `src/app/en/page.tsx`
5. `src/app/golden-crucible/page.tsx`
6. `src/app/en/golden-crucible/page.tsx`
7. `src/components/navbar.tsx`
8. 新增共享首页与产品页组件
9. 新增中英文数据文件

## 5. 验证方式

完成后至少验证：

1. `pnpm lint`
2. `pnpm build`
3. 中文 `/` 与英文 `/en` 页面均可访问
4. 产品入口 `/golden-crucible` 与 `/en/golden-crucible` 均可访问
5. 导航、语言切换、CTA 链接无死链

## 6. 当前假设

1. 本轮先不处理根域名 `mindhikers.com` 的跳转与 DNS 策略。
2. 本轮先不引入博客双语化，仅处理品牌首页与产品入口。
3. `黄金坩埚` 首版以品牌介绍和入口占位为主，不假设已有完整营销素材。

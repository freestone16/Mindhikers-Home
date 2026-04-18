# MIN-110 生产环境基线对照表

**提取日期**: 2026-04-06  
**来源**: https://mindhikers.com (Next.js 生产站)

---

## 1. 品牌标识

| 元素 | 当前值 | 文件位置 |
|------|--------|----------|
| Logo 文件名 | MindHikers.png | /public/MindHikers.png |
| 品牌名称 | 心行者 Mindhikers | - |
| 品牌英文名 | Mindhikers | - |

---

## 2. 字体系统

| 用途 | 字体名称 | 文件位置 |
|------|----------|----------|
| 正文字体 | CabinetGrotesk | /public/fonts/CabinetGrotesk-Medium.ttf |
| 标题字体 | ClashDisplay | /public/fonts/ClashDisplay-Semibold.ttf |
| CSS Variable | --font-brand-sans / --font-brand-display | globals.css |

---

## 3. 配色方案

| 用途 | OKLCH 值 | 近似 HEX |
|------|----------|----------|
| 主背景 | oklch(0.985 0.002 260) | ~#FAFAFA |
| 前景/文字 | oklch(0.285 0.01 260) | ~#454B47 |
| 主色(深绿) | oklch(0.47 0.06 165) | ~#2D5A45 |
| 渐变绿1 | rgba(205, 221, 212, 0.42) | #CDDDD4 |
| 渐变米 | rgba(233, 220, 201, 0.34) | #E9DCC9 |
| 强调色 | rgba(164, 194, 178, 0.16) | #A4C2B2 |

---

## 4. 导航结构

```
心行者 Mindhikers (Logo 区域)
├── About
├── Product
├── Blog
├── Contact
└── EN (语言切换)
```

---

## 5. 首页文案 - Hero 区域

**标题**: 把研究、产品与表达，排成一个有呼吸感的品牌入口。

**副标题**: 心行者 Mindhikers 正在把长期创作、内容实验与产品化尝试收拢成一个更完整的首页。它不想像简历，也不想像模板，而是像一个持续更新的工作现场。

**CTA 按钮**:
- 查看当前产品入口
- 进入博客
- 双语品牌入口

**标签**:
- 产品化实验
- 长期写作与研究

---

## 6. 首页文案 - About 区域

**标题**: ABOUT

**描述**: 心行者 Mindhikers 不是一张展示履历的页面，而是一个兼顾思考、制作与对外发布的品牌主页。

**目标**: 我们希望首页既能承接产品入口，也能容纳博客、研究线索和下一步动作，而不是把所有信息压成一页静态介绍。

**改版要点**:
- 去掉模板味的自我介绍
- 保留轻量但明确的动效层次
- 让产品、博客、联系入口一眼可见

---

## 7. 首页文案 - Product 区域

**标题**: PRODUCT

**副标题**: 首页中段应该像一个正在播出的栏目，而不是说明书。

**描述**: 先把一个足够真实的产品入口放在首页中央，再围绕它挂出内容、工作流和后续生长点。

**产品卡片 1 - 黄金坩埚**:
- 标签: FEATURED RELEASE
- 标题: 黄金坩埚
- 状态: Live now
- 描述: 围绕研究、写作、表达与创作者工作流展开的首个产品入口。它承担的不只是一个页面，而是 Mindhikers 第一批品牌化实验的落点。
- CTA: 打开产品页

**其他栏目**:
- BRAND SYSTEM: 双语首页结构
- CONTENT FLOW: 博客与研究栏目
- CONTACT SURFACE: 合作与联系窗口

---

## 8. 首页文案 - Blog 区域

**标题**: BLOG

**描述**: 让首页直接露出最近的写作，而不是把内容藏在站点深处。

**说明**: 这里会逐步积累方法、写作和产品思考。首页先展示最近几篇，完整归档放在博客页里。

**CTA**: 查看全部文章

**文章列表** (保留3篇):
1. Testing React Applications: A Practical Guide (2024-12-14)
2. REST API Design Principles That Stand the Test of Time (2024-12-12)
3. Git Workflow Guide: From Chaos to Clarity (2024-12-10)

---

## 9. 首页文案 - Contact 区域

**标题**: CONTACT

**描述**: 把联系入口做得像一段自然的续篇，而不是页面底部的表单义务。

**说明**: 如果你想讨论品牌、内容、产品实验，或者只是想交换一个更清晰的切题方式，这里是最直接的入口。

**联系信息**:
- 邮箱: contactmindhiker@gmail.com
- 地址: Shanghai / Remote
- 合作类型: Editorial collaboration, product experiments, thoughtful internet projects

**CTA**: 发邮件

**底部链接**:
- English home (查看英文版入口)
- Recent writing (先从文章理解我们的工作方式)

---

## 10. Footer 信息

```
MINDHIKERS
心行者 Mindhikers

Current focus: Homepage refresh in progress
Working rhythm: Research, build, write, publish
```

---

## 11. 英文版差异点

英文版 https://mindhikers.com/en 包含：
- EDITORIAL HOMEPAGE 标签
- 英文 Hero 文案
- HOMEPAGE BLOCKS 导航提示

---

## 12. Staging 现状对比

| 检查项 | 生产环境 | Staging 现状 | 优先级 |
|--------|----------|--------------|--------|
| Logo | MindHikers.png | 无 (Header 空白) | P0 |
| Footer 品牌名 | 心行者 Mindhikers | KYLE MILLS (残留) | P0 |
| Hero 文案 | 中文 | 英文模板 | P0 |
| 字体 | CabinetGrotesk/ClashDisplay | 模板字体 | P1 |
| 配色 | 深绿米白渐变 | 模板色 | P1 |
| Blog 内容 | 3篇文章 | 已同步 | ✅ OK |
| 导航结构 | 5项 | 已对齐 | ✅ OK |

---

## 下一步行动

1. **Unit 3**: 上传 Logo 替换 Header/Footer
2. **Unit 4**: 注入配色与字体 CSS
3. **Unit 5**: 主文案中文化替换
4. **Unit 6**: 清理模板残留 (社交链接、统计数字)
5. **Unit 7**: 最终 Smoke 验证

# MindHikers Homepage 运维手册（Headless 架构）

> **适用范围**：Mindhikers Homepage Headless 架构（WordPress CMS + Next.js 前台）staging / production
> **最后更新**：2026-04-20
> **维护者**：老卢 + 运营协作者
> **关联 issue**：[MIN-163](https://linear.app/mindhikers/issue/MIN-163)

---

## 目录

1. [架构总览](#1-架构总览)
2. [环境信息速查](#2-环境信息速查)
3. [模块维护地图](#3-模块维护地图)
4. [内容编辑日常流程](#4-内容编辑日常流程)
5. [Revalidate 链路工作原理与故障排查](#5-revalidate-链路工作原理与故障排查)
6. [m1-rest 插件部署流程](#6-m1-rest-插件部署流程)
7. [前台代码开发流程](#7-前台代码开发流程)
8. [变更生效对照表](#8-变更生效对照表)
9. [常见问题排错](#9-常见问题排错)
10. [紧急回滚](#10-紧急回滚)
11. [红线与禁忌](#11-红线与禁忌)
12. [字段速查表](#12-字段速查表)

---

## 1. 架构总览

### 1.1 服务拓扑

Mindhikers Homepage 当前采用 **Headless 架构**：

```
┌─────────────────────────────────────────────────────────────┐
│                         用户浏览器                            │
└──────────────────────────┬──────────────────────────────────┘
                           │ HTTPS
                           ▼
┌─────────────────────────────────────────────────────────────┐
│              Next.js 前台（Railway 容器）                     │
│         服务名：Mindhikers-Homepage                          │
│    职责：页面渲染、ISR 缓存、Revalidate 端点、API 路由        │
│         源码：本仓库 src/                                    │
└──────────────────────────┬──────────────────────────────────┘
                           │ REST API / Webhook
                           ▼
┌─────────────────────────────────────────────────────────────┐
│              WordPress CMS（Railway 容器）                    │
│          服务名：WordPress-L1ta                              │
│    职责：内容管理、Carbon Fields、Polylang、REST API 插件    │
│         入口：WP Admin 后台                                  │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 数据流向

```
WP Admin 编辑保存
    │
    ▼
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  Carbon Fields  │────▶│  m1-rest 插件    │────▶│  /wp-json/...   │
│  (wp_options)   │     │  (自定义 REST)   │     │  (REST API)     │
└─────────────────┘     └──────────────────┘     └─────────────────┘
                                                          │
                    ┌─────────────────────────────────────┘
                    │ fetch() + { next: { tags } }
                    ▼
           ┌─────────────────┐
           │  Next.js ISR    │
           │  (前台渲染)     │
           └─────────────────┘
```

### 1.3 两个服务的职责边界

| 服务 | 职责 | 不职责 |
|------|------|--------|
| **Mindhikers-Homepage** (Next.js) | 页面渲染、路由、样式、交互、ISR 缓存管理 | 不存储内容数据 |
| **WordPress-L1ta** (WP CMS) | 内容编辑、字段定义、多语言、媒体库 | 不渲染前台页面 |

**关键原则**：
- 内容（文字/图片/博客/产品）在 **WP Admin**
- 前台代码（布局/交互/样式）在 **Next.js 仓库**（即本 repo）
- WP 插件代码（REST API、字段定义）在仓库 `wordpress/mu-plugins/`，但**部署通道特殊**——必须打 ZIP 走 WP Admin 上传
- 配置（URL/Secret/环境变量）在 **Railway Dashboard**

---

## 2. 环境信息速查

### 2.1 双环境对照表

| 项目 | Staging | Production |
|------|---------|------------|
| **Next.js 前台域名** | `mindhikers-homepage-staging.up.railway.app` | `www.mindhikers.com` |
| **WP CMS 域名** | `wordpress-l1ta-staging.up.railway.app` | `homepage-manage.mindhikers.com` |
| **Next.js 服务名** | `Mindhikers-Homepage` | `Mindhikers-Homepage` |
| **WP 服务名** | `WordPress-L1ta` | `WordPress-L1ta` |
| **Revalidate 端点** | `https://mindhikers-homepage-staging.up.railway.app/api/revalidate` | `https://www.mindhikers.com/api/revalidate` |
| **REVALIDATE_SECRET** | `YOUR_SECRET_HERE` | `YOUR_SECRET_HERE` |
| **WP 后台账号** | `mindhikers_admin` | `mindhikers_admin` |
| **WP 密码** | 通过安全渠道获取 | 通过安全渠道获取 |
| **平台** | Railway（WordPress 官方镜像 + Next.js） | Railway（WordPress 官方镜像 + Next.js） |
| **PHP 版本** | 8.x（容器默认） | 8.x（容器默认） |
| **WP 版本** | 最新稳定版（容器自动更新） | 最新稳定版（容器自动更新） |
| **Next.js 版本** | 16.1.7 | 16.1.7 |

### 2.2 当前语言配置

| 语言 | Slug | URL 前缀 | 默认 |
|------|------|----------|------|
| 中文 | `zh` | 无（隐藏） | ✅ |
| English | `en` | `/en/` | — |

**URL 示例**：
- 中文首页：`/`
- 英文首页：`/en/`
- 中文产品：`/product/golden-crucible/`
- 英文产品：`/en/product/golden-crucible/`
- 中文博客：`/blog/`
- 英文博客：`/en/blog/`

### 2.3 后台登录

1. 打开 `{WP_CMS域名}/wp-admin`
2. 输入用户名：`mindhikers_admin`
3. 输入密码
4. 点击「登录」

---

## 3. 模块维护地图

| 要改的东西 | 在哪改 | 生效方式 |
|---|---|---|
| 首页文案/图片（Hero/About/Contact） | WP Admin → 对应"管理"菜单 | 保存后自动触发 Revalidate webhook → 前台 5 秒内更新 |
| 博客文章 | WP Admin → 文章 | 同上（tag: `blog-posts`） |
| 产品 | WP Admin → 产品 | 同上（tag: `product-{slug}` + `homepage-zh/en`） |
| 前台外观/布局/交互 | 代码仓库 `src/components/**/*.tsx` + `src/app/**/*.tsx` | git push → Railway 自动构建 Next.js |
| 前台样式/配色/字体 | `src/app/globals.css` + Tailwind 配置 | 同上 |
| WP REST API 逻辑（返回字段、查询条件） | 仓库 `wordpress/mu-plugins/m1-rest/` | ⚠️ 改完必须重新打 ZIP 从 WP Admin 上传；容器封闭，git push 不生效 |
| WP CPT / Carbon Fields 字段定义 | 仓库 `wordpress/mu-plugins/mindhikers-m1-core.php` | ⚠️ 同上，需走独立插件上传路径 |
| Revalidate Secret | **两处同步**：Railway `Mindhikers-Homepage` → Variables `REVALIDATE_SECRET` + WP Admin → Revalidate 配置 → Secret 字段 | 改完 Railway 端触发 Next.js 重部署 |
| 域名 / SSL | Railway Dashboard → 对应服务 → Settings → Domains | 立即生效 |
| 环境变量 | Railway Dashboard → 各服务 → Variables 标签 | 改完触发重部署 |

---

## 4. 内容编辑日常流程

### 4.1 首页五大区块

首页由五个区块组成：**Hero → About → Product → Blog → Contact**。前三个和最后一个通过 Carbon Fields Theme Options 管理，Product 区通过 CPT 管理，Blog 区通过文章管理。

#### Hero 管理

**入口**：WP Admin 侧边栏 → 「Hero 管理」（喇叭图标 📢）

**可编辑字段**：

| 字段名 | 中文用途 | 英文用途 | 类型 | 说明 |
|--------|----------|----------|------|------|
| `眉标 (ZH)` | 页面顶部小标签 | — | 文本 | 如 "Editorial homepage" |
| `眉标 (EN)` | — | 英文版眉标 | 文本 | |
| `标题 (ZH)` | Hero 主标题 | — | 文本 | 页面最大字号 |
| `标题 (EN)` | — | 英文版标题 | 文本 | |
| `描述 (ZH)` | Hero 描述文字 | — | 文本域 | 品牌定位一句话 |
| `描述 (EN)` | — | 英文版描述 | 文本域 | |
| `主按钮文字 (ZH)` | 主 CTA 按钮文字 | — | 文本 | |
| `主按钮文字 (EN)` | — | 英文版 | 文本 | |
| `主按钮链接` | 主按钮跳转地址 | 双语共用 | URL | 如 `#product` 或 `/blog` |
| `次按钮文字 (ZH)` | 次 CTA 按钮文字 | — | 文本 | |
| `次按钮文字 (EN)` | — | 英文版 | 文本 | |
| `次按钮链接` | 次按钮跳转地址 | 双语共用 | URL | |
| `配图` | Hero 背景或主视觉 | 双语共用 | 图片 | 从媒体库选择 |
| `Quick Links` | 右侧快捷链接列表 | 双语 | complex | 可添加多个链接，每个链接包含 ZH/EN 文字、URL、标签 |

**Quick Links 字段说明**（每条链接包含）：

| 字段名 | 用途 | 说明 |
|--------|------|------|
| `链接文字 (ZH)` | 中文链接文字 | 如 "黄金坩埚" |
| `链接文字 (EN)` | 英文链接文字 | 如 "Golden Crucible" |
| `链接地址` | 跳转 URL | 如 `/golden-crucible` 或 `https://...` |
| `标签 (ZH)` | 中文标签 | 如 "产品"、"内容"、"服务" |
| `标签 (EN)` | 英文标签 | 如 "Product"、"Content"、"Service" |

**操作流程**：

1. 侧边栏 → 「Hero 管理」
2. ZH 和 EN 字段在同一页面，无需切换语言
3. 修改字段 → 点击「保存」或「更新」
4. **Quick Links 管理**：
   - 点击「添加条目」新增链接
   - 填写 ZH/EN 文字、URL、标签
   - 拖拽条目可调整顺序
   - 点击条目标题展开编辑
5. 保存后自动触发 Revalidate，前台 5 秒内更新

#### About 管理

**入口**：WP Admin 侧边栏 → 「About 管理」（ℹ️ 图标）

**可编辑字段**：

| 字段名 | 用途 | 类型 | 说明 |
|--------|------|------|------|
| `标题 (ZH)` | About 区块标题 | 文本 | 如 "关于心行者" |
| `标题 (EN)` | 英文版标题 | 文本 | 如 "About Mindhikers" |
| `内容 (ZH)` | 品牌叙述全文 | 富文本 | 支持 HTML |
| `内容 (EN)` | 英文版叙述 | 富文本 | |
| `配图` | 可选图片 | 图片 | 目前未在模板中使用 |

**注意事项**：
- 「内容」字段是富文本编辑器，可以插入段落、加粗、链接等
- 保存后自动触发 Revalidate，前台即时更新

#### Contact 管理

**入口**：WP Admin 侧边栏 → 「Contact 管理」（✉️ 图标）

**可编辑字段**：

| 字段名 | 用途 | 类型 | 语言 |
|--------|------|------|------|
| `邮箱` | 联系邮箱 | 文本 | 共用 |
| `位置 (ZH)` | 中文地址/区域 | 文本 | ZH |
| `位置 (EN)` | 英文地址/区域 | 文本 | EN |
| `区块标题 (ZH)` | Contact 区块标题 | 文本 | ZH |
| `区块标题 (EN)` | 英文版标题 | 文本 | EN |
| `区块描述 (ZH)` | 区块描述文字 | 文本域 | ZH |
| `区块描述 (EN)` | 英文版描述 | 文本域 | EN |
| `社交矩阵` | 社交平台列表 | 复合字段 | — |

**社交矩阵**（可增删改排序）：

每条社交记录包含：
- `平台名称 (ZH)`：如 "Twitter / X"
- `平台名称 (EN)`：如 "Twitter / X"
- `链接`：平台 URL
- `二维码图片`：可选，用于微信公众号等需要展示二维码的平台

**操作流程**：
1. 侧边栏 → 「Contact 管理」
2. 编辑邮箱、位置信息
3. 社交矩阵：点击「添加条目」新增，点击条目标题展开编辑，拖拽排序
4. 保存后自动触发 Revalidate

### 4.2 产品管理（Product CPT）

#### 产品列表

**入口**：WP Admin 侧边栏 → 「产品」（📦 图标）

当前产品：
- **黄金坩埚**（ZH） / **Golden Crucible**（EN）— 状态：构思中

#### 新增产品

1. 侧边栏 → 「产品」→ 「新建」
2. 填写以下字段：

**WordPress 原生字段**：

| 字段 | 说明 |
|------|------|
| 标题 | 产品名称（如 "黄金坩埚"） |
| 正文（Gutenberg 编辑器） | 产品完整描述 |
| 摘要 | 一句话简介 |
| 特色图片 | 产品 Logo 或主视觉 |

**Carbon Fields 自定义字段**（编辑页下方「产品信息」面板）：

| 字段 | 类型 | 说明 |
|------|------|------|
| `副标题 / 一句话定位` | 文本 | 如 "你的个人 AI 战略伙伴" |
| `状态` | 下拉选择 | 构思中 / 开发中 / 公测 / 正式发布 / 已下线 |
| `产品入口链接` | URL | 点击后跳转的产品地址 |
| `Featured 产品` | 复选框 | 是否在首页高亮展示 |

3. 右侧「语言」面板选择语言（zh 或 en）
4. 发布 → 自动触发 Revalidate

#### 创建双语产品

1. 先创建中文版产品 → 发布
2. 在产品编辑页右侧 Polylang 面板点击「+」添加英文翻译
3. 填写英文版内容 → 发布
4. Polylang 自动关联 ZH ↔ EN 翻译关系

#### 修改产品状态

产品状态有 5 个选项，前台会显示对应中文标签：

| 状态值 | 前台显示 | 说明 |
|--------|----------|------|
| `idea` | 构思中 | 产品还在规划阶段 |
| `dev` | 开发中 | 正在开发 |
| `beta` | 公测 | 已开放公测 |
| `live` | 正式发布 | 已正式上线 |
| `sunset` | 已下线 | 产品已退役 |

**重要**：`live` 状态的产品卡片会有特殊的绿色高亮样式（CSS class `mh-product-card-status--active`）。

#### 删除产品

1. 「产品」列表 → 鼠标悬停 → 「移至回收站」
2. 如需永久删除：回收站 → 「永久删除」
3. **注意**：删除中文版不会自动删除英文翻译版本，需要分别删除

### 4.3 博客管理（Blog）

#### 博客分类体系

当前使用 WordPress 原生分类目录（Category）实现双层分类：

**3 个主分类**：

| 主分类 | Slug |
|--------|------|
| AI 技术 | `ai-technology` |
| 碳硅共生 | `carbon-silicon-symbiosis` |
| 脑神经科学 | `neuroscience` |

**4 个次级分类**（挂在每个主分类下）：

| 次级分类 | Slug 模式 |
|----------|-----------|
| 深度 | `{主分类}-deep` |
| 速记 | `{主分类}-notes` |
| 视频 | `{主分类}-video` |
| 工具 | `{主分类}-tools` |

#### 新增博客文章

1. WP Admin → 「文章」→ 「写文章」
2. 填写标题、正文
3. 右侧设置：
   - **分类目录**：勾选一个主分类 + 一个次级分类
   - **特色图片**：上传封面图
   - **摘要**：填写文章摘要
   - **语言**：选择 zh 或 en
4. 发布 → 自动触发 Revalidate（tag: `blog-posts`）

#### 创建双语文章

1. 先写中文版 → 发布
2. 编辑页右侧 Polylang 面板点击「+」添加英文翻译
3. 写英文版正文 → 发布

**注意**：英文首页 Blog 区只显示有英文翻译的文章。如果只有中文版，英文首页会显示"暂无文章"。

#### 修改文章分类

1. 「文章」列表 → 点击文章标题进入编辑
2. 右侧「分类目录」面板重新勾选
3. 保存

#### 新增分类

1. 「文章」→ 「分类目录」
2. 填写名称、Slug
3. 选择父级分类（主分类留空，次级分类选择对应主分类）
4. 右侧 Polylang 面板设置语言
5. 点击「添加新分类目录」
6. 如需双语，创建对应英文翻译分类

### 4.4 多语言 / 双语操作

#### Polylang 核心概念

| 概念 | 说明 |
|------|------|
| 语言 | 当前站点的语言列表（ZH 默认 + EN） |
| 翻译关系 | 两个不同语言的 post/term 之间的对应关系 |
| URL 模式 | 目录模式，中文隐藏前缀，英文 `/en/` 前缀 |
| 语言切换器 | 前台导航中的语言切换链接 |

#### Polylang 管理入口

**WP Admin → 「语言」**（多国旗帜图标）

- **语言**：查看/修改可用语言列表
- **设置**：URL 模式、默认语言、隐藏默认语言前缀等
- **字符串翻译**：翻译站点标题、描述等全局文本

#### 内容的双语管理策略

**单例数据（Hero / About / Contact）**：
- 在同一个 Carbon Fields 管理页面内同时编辑 ZH 和 EN 字段
- 字段名以 `_zh` / `_en` 后缀区分
- **无需切换语言**，一页搞定

**多实例数据（Product / Blog）**：
- 使用 Polylang 的 post-level 翻译
- 每种语言是独立的 post，通过 Polylang 关联
- 在编辑页右侧 Polylang 面板操作

### 4.5 导航菜单管理

#### 菜单结构

Polylang 使用"每语言独立菜单"模式：

**中文菜单**：
- 关于 → `#about`
- 产品 → `#product`
- 博客 → `/blog`
- 联系 → `#contact`

**英文菜单**：
- About → `#about`
- Product → `#product`
- Blog → `/en/blog`
- Contact → `#contact`

#### 修改菜单

1. WP Admin → 「外观」→ 「菜单」
2. 顶部「选择要编辑的菜单」切换语言
3. 拖拽调整顺序、添加/删除菜单项
4. 保存菜单

#### 新增菜单项

1. 左侧「添加菜单项」面板
2. 选择页面/自定义链接/分类
3. 点击「添加到菜单」
4. 拖拽到目标位置
5. 保存

### 4.6 SEO 管理

当前使用 SureRank 管理 SEO 元数据。

编辑任何页面/文章时，下方可见 SureRank 面板：
- **SEO 标题**：浏览器标签和搜索引擎显示的标题
- **Meta Description**：搜索结果摘要
- **Open Graph**：社交媒体分享标题/描述/图片
- **Twitter Card**：Twitter 分享卡片

**⚠️ 危险 API**：`GET /surerank/v1/post/settings?post_id=&post_type=` **绝对不要调用**——会触发 PHP 致命错误。

---

## 5. Revalidate 链路工作原理与故障排查

### 5.1 工作原理

当在 WP Admin 保存内容时，Revalidate 链路按以下顺序工作：

```
WP Admin 保存
    │
    ▼
m1-rest 插件检测到保存动作
    │
    ▼
读取 Carbon Fields "Revalidate 配置"中的 URL + Secret
    │
    ▼
向 Next.js /api/revalidate 发送 POST 请求
    │
    ▼
Next.js 校验 Secret → 调用 revalidateTag(tag, "default")
    │
    ▼
ISR 缓存失效 → 下次访问时重新生成页面
```

### 5.2 配置位置

**WP 侧**：
- WP Admin → 「Revalidate 配置」
- 字段：
  - `Revalidate URL`：Next.js 前台域名 + `/api/revalidate`
  - `Revalidate Secret`：与 Railway 环境变量 `REVALIDATE_SECRET` 保持一致

**Next.js 侧**：
- Railway Dashboard → `Mindhikers-Homepage` → Variables → `REVALIDATE_SECRET`
- 代码：`src/lib/cms/constants.ts` 中定义 Cache Tag 白名单

### 5.3 Cache Tag 白名单

| 内容 | Tag | 来源 |
|------|-----|------|
| Blog（全量） | `blog-posts` | `CACHE_TAG_BLOG` in constants.ts |
| Homepage ZH | `homepage-zh` | `getHomepageCacheTag("zh")` |
| Homepage EN | `homepage-en` | `getHomepageCacheTag("en")` |
| Product | `product-{slug}` | `getProductCacheTag(slug)` |

### 5.4 手动触发 Revalidate

如需手动触发（调试或应急）：

```bash
curl -X POST "{frontend_url}/api/revalidate" \
  -H "Content-Type: application/json" \
  -H "x-revalidate-secret: YOUR_SECRET_HERE" \
  -d '{"tag":"blog-posts"}'
```

预期响应：
```json
{"ok":true,"revalidated":true,"tag":"blog-posts"}
```

### 5.5 故障排查

| 现象 | 可能原因 | 解决方案 |
|------|----------|----------|
| 保存后前台未更新 | Revalidate URL 配置错误 | 检查 WP Admin → Revalidate 配置 → URL 是否为正确的 `{frontend_url}/api/revalidate` |
| 保存后前台未更新 | Secret 不匹配 | 确认 WP 侧 Secret 与 Railway `REVALIDATE_SECRET` 完全一致 |
| 保存后前台未更新 | 网络不通 | 检查 WP 容器是否能访问 Next.js 前台域名 |
| Revalidate 返回 401 | Secret 错误 | 核对 Secret，注意大小写和前后空格 |
| Revalidate 返回 400 | Tag 不在白名单 | 检查 tag 名称是否与 `constants.ts` 中定义一致 |
| 手动 curl 成功但保存不触发 | m1-rest 插件未正确安装 | 检查插件是否启用，见 §6 |

---

## 6. m1-rest 插件部署流程

### 6.1 插件作用

m1-rest 插件是连接 WP CMS 与 Next.js 前台的核心桥梁，提供：
- 自定义 REST API 路由（`/wp-json/mindhikers/v1/*`）
- Carbon Fields 字段注册
- Revalidate webhook 触发逻辑

### 6.2 当前部署状态（⚠️ 重要）

**当前 staging 的 m1-rest 插件是手工上传的独立 ZIP（v1.1.0），未纳入仓库自动构建。**

- 插件源码位置（本地）：`/tmp/m1-rest-pkg/m1-rest/`
- 当前运行版本（本地）：`/tmp/m1-rest.zip`（SHA `0d05227b`）
- **风险**：WP 容器若被 Railway 重建，插件会丢失，需重新上传

### 6.3 部署流程（独立 ZIP 通道）

1. **准备 ZIP**：
   ```bash
   cd /tmp/m1-rest-pkg
   zip -r m1-rest.zip m1-rest/
   ```

2. **WP Admin 上传**：
   - 登录 WP Admin
   - 「插件」→ 「安装插件」→ 「上传插件」
   - 选择 `m1-rest.zip` → 「立即安装」
   - 「启用插件」

3. **验证安装**：
   - 访问 `{WP_CMS域名}/wp-json/`
   - 在 `namespaces` 中查找 `mindhikers/v1`
   - 存在 → 路由注册成功

### 6.4 函数名唯一前缀策略

m1-rest 插件中的所有函数必须使用独特前缀，避免与旧版 `mindhikers-m1-core.php` 冲突：

- ✅ `mh_m1rest_standalone_register_routes`
- ❌ `m1_register_rest_routes`（可能与旧版冲突）

**如果上传后路由仍 404**：
1. 检查 `/wp-json/` 的 `namespaces` 字段
2. 如果找不到 `mindhikers/v1`，可能是函数名冲突
3. 检查插件代码中是否有 `function_exists` guard 阻止了注册

### 6.5 长期固化方案（待执行）

当前手工上传是临时方案，长期应将 m1-rest 纳入：
- 仓库 `wordpress/plugins/m1-rest/`
- Dockerfile 中 `COPY` 到 `wp-content/plugins/`
- 由专门 issue 跟踪（见 MIN-110 子任务）

---

## 7. 前台代码开发流程

### 7.1 分支纪律

```
feature/xxx  ──▶  staging  ──▶  main  ──▶  production
     │              │            │            │
   开发中        测试验证      稳定基线      线上环境
```

- **开发**：从 `staging` 切出 feature 分支
- **验证**：合并到 `staging`，Railway 自动部署到 staging 环境
- **上线**：`staging` → `main` → production

### 7.2 环境变量

参考 `.env.example`：

| 变量 | 说明 | 示例值 |
|------|------|--------|
| `BLOG_SOURCE` | 博客数据来源 | `mdx` 或 `wordpress` |
| `WORDPRESS_API_URL` | WP REST API 地址 | `https://wordpress-l1ta-staging.up.railway.app/wp-json` |
| `REVALIDATE_SECRET` | Revalidate 接口密钥 | `YOUR_SECRET_HERE` |

### 7.3 Railway 自动构建

Next.js 前台：
- git push 到 `staging` 分支 → Railway 自动构建并部署
- 构建日志在 Railway Dashboard → `Mindhikers-Homepage` → Deployments

WP CMS：
- 使用 Railway WordPress 官方镜像
- 文件变更需通过 WP Admin 或 SSH 手动操作
- **注意**：容器重建会丢失未持久化的文件（如手工上传的插件）

---

## 8. 变更生效对照表

| 变更类型 | 修改位置 | 生效方式 | 生效时间 |
|----------|----------|----------|----------|
| **文案/图片**（Hero/About/Contact） | WP Admin → 对应管理菜单 | 保存 → Revalidate webhook → ISR 失效 | ~5 秒 |
| **博客文章** | WP Admin → 文章 | 同上（tag: `blog-posts`） | ~5 秒 |
| **产品信息** | WP Admin → 产品 | 同上（tag: `product-{slug}`） | ~5 秒 |
| **前台布局/交互** | `src/components/**/*.tsx` | git push → Railway 构建 | ~2 分钟 |
| **前台样式** | `src/app/globals.css` / Tailwind | git push → Railway 构建 | ~2 分钟 |
| **WP REST API 逻辑** | `wordpress/mu-plugins/m1-rest/` | 打 ZIP → WP Admin 上传 → 启用 | 即时 |
| **WP 字段定义** | `wordpress/mu-plugins/mindhikers-m1-core.php` | 同上 | 即时 |
| **Revalidate Secret** | Railway Variables + WP Admin | Railway 重部署 + WP 保存 | ~2 分钟 |
| **域名 / SSL** | Railway Dashboard → Domains | 即时 | 即时 |
| **环境变量** | Railway Dashboard → Variables | 触发重部署 | ~2 分钟 |

---

## 9. 常见问题排错

### 9.1 Blog 区显示"暂无文章"

**症状**：首页 Blog 区块显示"暂无文章"，但后台确有文章。

**原因排查**：
1. 文章是否标记了语言？（Polylang 会按语言过滤）
   - 编辑文章 → 右侧「语言」面板确认
2. 文章是否已发布？（草稿不会出现在前台）
3. 英文首页是否只有中文版文章？（英文首页只显示有英文翻译的文章）

**解决**：
- 确保每篇文章都通过 Polylang 设置了语言标记
- 如果文章只有中文版，确保中文首页能查到

### 9.2 保存后前台不更新

**症状**：WP Admin 保存内容后，前台页面未刷新。

**原因排查**：
1. **Revalidate 链路故障**：参见 §5.5
2. **浏览器缓存**：强制刷新（Ctrl+Shift+R / Cmd+Shift+R）
3. **CDN/代理缓存**：Railway 可能有 CDN 缓存，等待几分钟

**解决**：
- 先检查 Revalidate 配置（URL + Secret）
- 尝试手动触发 Revalidate（§5.4）
- 强制刷新浏览器

### 9.3 `/wp-json/mindhikers/v1/blog` 返回 404

**症状**：Next.js 前台拉不到博客数据。

**原因排查**：
1. m1-rest 插件是否已启用？
2. 访问 `{WP_CMS域名}/wp-json/`，检查 `namespaces` 中是否有 `mindhikers/v1`
3. 如果没有，可能是函数名冲突或插件未正确加载

**解决**：
- 重新上传并启用 m1-rest 插件（§6.3）
- 检查插件代码中的函数名是否有唯一前缀

### 9.4 前台 500 / 白屏

**症状**：Next.js 前台返回 500 或白屏。

**排查步骤**：
1. 检查 Next.js 构建日志（Railway Dashboard → Deployments）
2. 检查 WP REST API 是否正常响应
3. 检查环境变量是否配置正确（`WORDPRESS_API_URL`、`REVALIDATE_SECRET`）

**快速恢复**：
1. Railway Dashboard → `Mindhikers-Homepage` → 回滚到上一个成功部署
2. 或本地修复后重新 push

### 9.5 产品详情页 404

**症状**：访问 `/product/golden-crucible/` 返回 404。

**解决**：
1. WP Admin → 「设置」→ 「固定链接」→ 直接点「保存更改」（刷新 rewrite 规则）
2. 确认 `mh_product` CPT 已注册（检查 m1-rest 插件是否启用）
3. 确认产品的 post_status 为 `publish`

### 9.6 后台出现 "Carbon Fields 插件未激活" 警告

**症状**：WP Admin 顶部黄色警告条。

**解决**：
1. WP Admin → 「插件」→ 确认 Carbon Fields 已安装且激活
2. 如果已激活但仍然警告，可能是 mu-plugin 加载顺序问题
3. 刷新页面，Carbon Fields 通常在第二次加载时正常

---

## 10. 紧急回滚

### 10.1 Next.js 前台回滚

**场景**：前台代码部署后出现严重问题。

**步骤**：
1. Railway Dashboard → `Mindhikers-Homepage` → Deployments
2. 找到上一个成功的部署
3. 点击「Rollback」
4. 或本地 `git revert` 问题 commit → push 到对应分支

### 10.2 WP 侧回滚

**场景**：m1-rest 插件或 WP 配置导致问题。

**步骤**：
1. WP Admin → 「插件」→ 停用 m1-rest 插件
2. 或 WP Admin → 「外观」→ 「主题」→ 切换回 Astra 父主题
3. 排查并修复问题后重新启用

### 10.3 数据库回滚

Railway WordPress 使用 volume 持久化数据库。如果数据库损坏：

1. 联系平台管理员检查 volume 快照
2. 重新运行 `m1-seed.php` 恢复内容基线

---

## 11. 红线与禁忌

### 绝对不要做的事

| # | 禁止操作 | 原因 |
|---|---|---|
| 1 | 卸载 Elementor | 其他页面可能仍依赖它 |
| 2 | 在生产环境直接试错 | 先 staging 验证 |
| 3 | 取消 staging 的 `noindex` | 避免搜索引擎收录测试内容 |
| 4 | 调用 `GET /surerank/v1/post/settings` 空参数 | 触发 PHP 致命错误 |
| 5 | 盲改 SureRank 字段名 | 必须用 `page-seo-checks/fix` 路径 |
| 6 | 删除 `mindhikers-cms-core.php` | 旧 headless 插件，保留不启用即可 |
| 7 | 把 `/` 和 `/en/` 的语言职责混在一起 | Polylang 双语隔离 |
| 8 | 回到旧的 Next.js 前台路线 | 当前主线是 WordPress 模版站 |
| 9 | 删除 `m1-seed.php` | 内容重建的唯一基线脚本 |
| 10 | 修改 `wp_options` 中的 Polylang 核心配置而不备份 | 可能导致双语系统崩溃 |
| 11 | **在 WP 容器重建后忘记重新上传 m1-rest** | 插件会丢失，前台数据链路断裂 |
| 12 | **在示例或文档中写入真实 REVALIDATE_SECRET** | 凭证泄漏风险 |
| 13 | **直接修改 WP 容器内的插件文件** | 容器封闭，重启后丢失 |

### 操作前必须确认

| 场景 | 必须确认 |
|------|----------|
| 修改 PHP 文件 | staging 验证通过后再部署 |
| 修改固定链接 | 理解对 SEO 的影响 |
| 新增插件 | 检查与 Polylang + Carbon Fields 的兼容性 |
| 修改域名配置 | 跨项目域名占用排查（见 `docs/rules.md`） |
| 修改 Revalidate Secret | 同时更新 Railway + WP Admin 两处 |
| 重建 WP 容器 | 提前备份 m1-rest ZIP，重建后重新上传 |

---

## 12. 字段速查表

### 12.1 Carbon Fields Theme Options（`wp_options` 表）

**Hero**：

| Option Key | 用途 | 类型 |
|------------|------|------|
| `hero_eyebrow_zh` / `hero_eyebrow_en` | 眉标 | text |
| `hero_title_zh` / `hero_title_en` | 标题 | text |
| `hero_desc_zh` / `hero_desc_en` | 描述 | textarea |
| `hero_cta_primary_text_zh` / `hero_cta_primary_text_en` | 主按钮文字 | text |
| `hero_cta_primary_url` | 主按钮链接 | text |
| `hero_cta_secondary_text_zh` / `hero_cta_secondary_text_en` | 次按钮文字 | text |
| `hero_cta_secondary_url` | 次按钮链接 | text |
| `hero_image` | 配图 | image |

**About**：

| Option Key | 用途 | 类型 |
|------------|------|------|
| `about_title_zh` / `about_title_en` | 标题 | text |
| `about_content_zh` / `about_content_en` | 内容 | rich_text |
| `about_image` | 配图 | image |

**Contact**：

| Option Key | 用途 | 类型 |
|------------|------|------|
| `contact_email` | 邮箱 | text |
| `contact_location_zh` / `contact_location_en` | 位置 | text |
| `contact_title_zh` / `contact_title_en` | 区块标题 | text |
| `contact_desc_zh` / `contact_desc_en` | 区块描述 | textarea |
| `contact_social_matrix` | 社交矩阵 | complex |

**Product 区块标题**：

| Option Key | 用途 | 类型 |
|------------|------|------|
| `product_title_zh` / `product_title_en` | 区块标题 | text |
| `product_desc_zh` / `product_desc_en` | 区块描述 | text |

**Blog 区块标题**：

| Option Key | 用途 | 类型 |
|------------|------|------|
| `blog_title_zh` / `blog_title_en` | 区块标题 | text |
| `blog_desc_zh` / `blog_desc_en` | 区块描述 | text |

### 12.2 Product CPT Post Meta（`wp_postmeta` 表）

| Meta Key | 用途 | 类型 | 值域 |
|----------|------|------|------|
| `product_subtitle` | 副标题 | text | 自由文本 |
| `product_status` | 状态 | select | `idea` / `dev` / `beta` / `live` / `sunset` |
| `product_entry_url` | 产品入口链接 | text | URL |
| `product_is_featured` | 是否 Featured | checkbox | `yes` / 空 |

### 12.3 数据源映射（新架构）

| 区块 | Next.js 组件 | 数据来源 | 读取函数/Hook |
|------|-------------|----------|---------------|
| Hero | `src/components/home/HeroSection.tsx` | `/wp-json/mindhikers/v1/homepage/{zh,en}` | `fetchHomepageData(lang)` |
| About | `src/components/home/AboutSection.tsx` | 同上 | 同上 |
| Product | `src/components/home/ProductSection.tsx` | `/wp-json/mindhikers/v1/products` | `fetchProducts(lang)` |
| Blog | `src/components/home/BlogSection.tsx` | `/wp-json/mindhikers/v1/blog` | `fetchBlogPosts(lang)` |
| Contact | `src/components/home/ContactSection.tsx` | `/wp-json/mindhikers/v1/homepage/{zh,en}` | `fetchHomepageData(lang)` |

---

## 附录 A：关键文件路径

| 文件 | 绝对路径 | 说明 |
|------|----------|------|
| Next.js 前台源码 | `src/` | 页面、组件、样式 |
| CMS 数据拉取 | `src/lib/cms/` | WordPress REST API 封装 |
| Cache Tag 常量 | `src/lib/cms/constants.ts` | ISR tag 白名单 |
| Revalidate API | `src/app/api/revalidate/route.ts` | 缓存回刷端点 |
| MU Plugin（核心） | `wordpress/mu-plugins/mindhikers-m1-core.php` | CPT + 字段定义 |
| m1-rest 插件源码 | `wordpress/mu-plugins/m1-rest/` | REST API 路由（需 ZIP 上传） |
| Seed 脚本 | `wordpress/mu-plugins/m1-seed.php` | 内容初始化 |
| 品牌样式 | `src/app/globals.css` | Tailwind + 自定义 CSS |

## 附录 B：常用操作速查

| 我想... | 操作 |
|---------|------|
| 修改首页 Hero 标题 | 侧边栏 → 「Hero 管理」→ 修改「标题 (ZH/EN)」→ 保存（自动触发 Revalidate） |
| 新增一个产品 | 侧边栏 → 「产品」→ 「新建」→ 填写字段 → 发布 |
| 添加一篇博客 | 「文章」→ 「写文章」→ 选分类 → 发布 |
| 修改联系邮箱 | 侧边栏 → 「Contact 管理」→ 修改「邮箱」→ 保存 |
| 添加新的社交平台 | 侧边栏 → 「Contact 管理」→ 社交矩阵 → 「添加条目」 |
| 切换导航菜单语言 | 「外观」→ 「菜单」→ 顶部切换语言 → 编辑 |
| 修改产品状态 | 「产品」→ 编辑 → 下方「产品信息」→ 修改「状态」下拉 |
| 刷新固定链接 | 「设置」→ 「固定链接」→ 直接点「保存更改」 |
| 手动触发 Revalidate | curl POST `/api/revalidate` 带 Secret 和 tag |
| 修改前台样式 | 编辑 `src/app/globals.css` → git push → Railway 自动构建 |
| 回滚 Next.js 部署 | Railway Dashboard → Deployments → Rollback |
| 重新上传 m1-rest 插件 | 打 ZIP → WP Admin → 插件 → 上传 → 启用 |

## 附录 C：相关文档索引

| 文档 | 路径 | 说明 |
|------|------|------|
| 旧版运维手册（历史） | `docs/operations-guide.md` | 基于旧 WP 全栈架构，已过时 |
| 项目规则 | `docs/rules.md` | 技术经验与约束 |
| 域名边界 | `docs/domain-boundary.md` | 子域名治理 |
| 经验教训 | `docs/lessons.md` | 踩坑记录 |
| 交接文档 | `docs/dev_logs/HANDOFF.md` | 最新会话状态 |
| 开发计划 | `docs/plans/` | 实施方案与 PRD |

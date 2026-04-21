# ⚠️ 本手册已过时（历史保留）

> **状态**：基于旧"WordPress 全栈模板渲染"架构，与当前 **Headless 架构** 不匹配。
>
> **请使用新版手册**：[`docs/operations-guide-headless.md`](./operations-guide-headless.md)
>
> **保留原因**：历史参考、字段速查表部分仍有价值。

---

# MindHikers Homepage 日常运营指南

> **适用范围**：Mindhikers Homepage WordPress CMS staging / production
> **最后更新**：2026-04-16
> **维护者**：老卢 + 运营协作者

---

## 目录

1. [环境信息速查](#1-环境信息速查)
2. [后台登录](#2-后台登录)
3. [首页五大区块内容管理](#3-首页五大区块内容管理)
4. [产品管理（Product CPT）](#4-产品管理product-cpt)
5. [博客管理（Blog）](#5-博客管理blog)
6. [多语言 / 双语操作](#6-多语言--双语操作)
7. [SEO 管理](#7-seo-管理)
8. [导航菜单管理](#8-导航菜单管理)
9. [样式与品牌视觉维护](#9-样式与品牌视觉维护)
10. [部署流程](#10-部署流程)
11. [常见问题排错](#11-常见问题排错)
12. [紧急回滚](#12-紧急回滚)
13. [红线与禁忌](#13-红线与禁忌)
14. [字段速查表](#14-字段速查表)

---

## 1. 环境信息速查

| 项目 | 值 |
|------|-----|
| **Staging 地址** | `https://wordpress-l1ta-staging.up.railway.app` |
| **后台地址** | `{站点地址}/wp-admin` |
| **生产域名（规划中）** | `www.mindhikers.com` |
| **CMS 管理域名（规划中）** | `homepage-manage.mindhikers.com` |
| **平台** | Railway（WordPress 官方镜像） |
| **主题** | Astra Child（子主题） |
| **字段插件** | Carbon Fields v3.6.9 |
| **多语言插件** | Polylang 3.8.2（Free） |
| **SEO 插件** | SureRank |
| **PHP 版本** | 8.x（容器默认） |
| **WP 版本** | 最新稳定版（容器自动更新） |

### 当前语言配置

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

---

## 2. 后台登录

### 2.1 正常登录流程

1. 打开 `{站点地址}/wp-admin`
2. 输入用户名：`mindhikers_admin`
3. 输入密码
4. 点击「登录」

### 2.2 Cookie 登录（API / 脚本用）

```bash
# 登录获取 cookie
curl -v -X POST '{站点地址}/wp-login.php' \
  -d 'log=mindhikers_admin&pwd=YOUR_PASSWORD&wp-submit=Log+In' \
  -c cookies.txt \
  -L

# 使用 cookie 访问后台
curl -b cookies.txt '{站点地址}/wp-admin/'
```

> **注意**：密码中的 `+` 字符需要 URL encode 为 `%2B`。

### 2.3 登录失败排查

| 现象 | 可能原因 | 解决方案 |
|------|----------|----------|
| 页面空白 / 500 | PHP Fatal Error | 检查 `wp-content/debug.log` |
| 404 Not Found | Apache 未正确路由 | 确认 WordPress 固定链接设置 |
| 不断重定向 | `siteurl` / `home` 配置错误 | 检查 `wp_options` 表 |
| 密码错误 | 密码被修改或过期 | 使用 `ops/wordpress/reset-admin-password.mjs` 重置 |

---

## 3. 首页五大区块内容管理

首页由五个区块组成：**Hero → About → Product → Blog → Contact**。前三个和最后一个通过 Carbon Fields Theme Options 管理，Product 区通过 CPT 管理，Blog 区通过文章管理。

### 3.1 Hero 管理

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

**操作流程**：

1. 侧边栏 → 「Hero 管理」
2. ZH 和 EN 字段在同一页面，无需切换语言
3. 修改字段 → 点击「保存」或「更新」
4. 打开前台首页刷新验证

**当前内容底稿（seed 数据）**：

- **眉标**：`Editorial homepage`（ZH/EN 相同）
- **标题 ZH**：`把研究、产品与表达，排成一个有呼吸感的品牌入口。`
- **标题 EN**：`A brand home for research, products, and writing that still feels alive.`
- **描述 ZH**：`心行者 Mindhikers 正在把长期创作、内容实验与产品化尝试收拢成一个更完整的首页...`
- **主按钮 ZH**：`查看当前产品入口` → `#product`
- **次按钮 ZH**：`进入博客` → `/blog`

### 3.2 About 管理

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
- 保存后前台 About 区块即时更新
- 当前 About 内容使用品牌定位原文打底

### 3.3 Contact 管理

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

**当前社交矩阵**：
1. Twitter / X → `https://x.com/mindhikers`
2. Bilibili → `https://space.bilibili.com/mindhikers`
3. 微信公众号 → `#`（URL 待补充）

**操作流程**：
1. 侧边栏 → 「Contact 管理」
2. 编辑邮箱、位置信息
3. 社交矩阵：点击「添加条目」新增，点击条目标题展开编辑，拖拽排序
4. 保存

---

## 4. 产品管理（Product CPT）

### 4.1 产品列表

**入口**：WP Admin 侧边栏 → 「产品」（📦 图标）

当前产品：
- **黄金坩埚**（ZH） / **Golden Crucible**（EN）— 状态：构思中

### 4.2 新增产品

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
4. 发布

### 4.3 创建双语产品

1. 先创建中文版产品 → 发布
2. 在产品编辑页右侧 Polylang 面板点击「+」添加英文翻译
3. 填写英文版内容 → 发布
4. Polylang 自动关联 ZH ↔ EN 翻译关系

### 4.4 修改产品状态

产品状态有 5 个选项，前台会显示对应中文标签：

| 状态值 | 前台显示 | 说明 |
|--------|----------|------|
| `idea` | 构思中 | 产品还在规划阶段 |
| `dev` | 开发中 | 正在开发 |
| `beta` | 公测 | 已开放公测 |
| `live` | 正式发布 | 已正式上线 |
| `sunset` | 已下线 | 产品已退役 |

**重要**：`live` 状态的产品卡片会有特殊的绿色高亮样式（CSS class `mh-product-card-status--active`）。

### 4.5 删除产品

1. 「产品」列表 → 鼠标悬停 → 「移至回收站」
2. 如需永久删除：回收站 → 「永久删除」
3. **注意**：删除中文版不会自动删除英文翻译版本，需要分别删除

---

## 5. 博客管理（Blog）

### 5.1 博客分类体系

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

### 5.2 新增博客文章

1. WP Admin → 「文章」→ 「写文章」
2. 填写标题、正文
3. 右侧设置：
   - **分类目录**：勾选一个主分类 + 一个次级分类
   - **特色图片**：上传封面图
   - **摘要**：填写文章摘要
   - **语言**：选择 zh 或 en
4. 发布

### 5.3 创建双语文章

1. 先写中文版 → 发布
2. 编辑页右侧 Polylang 面板点击「+」添加英文翻译
3. 写英文版正文 → 发布

**注意**：英文首页 Blog 区只显示有英文翻译的文章。如果只有中文版，英文首页会显示"暂无文章"。

### 5.4 修改文章分类

1. 「文章」列表 → 点击文章标题进入编辑
2. 右侧「分类目录」面板重新勾选
3. 保存

### 5.5 新增分类

1. 「文章」→ 「分类目录」
2. 填写名称、Slug
3. 选择父级分类（主分类留空，次级分类选择对应主分类）
4. 右侧 Polylang 面板设置语言
5. 点击「添加新分类目录」
6. 如需双语，创建对应英文翻译分类

---

## 6. 多语言 / 双语操作

### 6.1 Polylang 核心概念

| 概念 | 说明 |
|------|------|
| 语言 | 当前站点的语言列表（ZH 默认 + EN） |
| 翻译关系 | 两个不同语言的 post/term 之间的对应关系 |
| URL 模式 | 目录模式，中文隐藏前缀，英文 `/en/` 前缀 |
| 语言切换器 | 前台导航中的语言切换链接 |

### 6.2 Polylang 管理入口

**WP Admin → 「语言」**（多国旗帜图标）

- **语言**：查看/修改可用语言列表
- **设置**：URL 模式、默认语言、隐藏默认语言前缀等
- **字符串翻译**：翻译站点标题、描述等全局文本

### 6.3 内容的双语管理策略

**单例数据（Hero / About / Contact）**：
- 在同一个 Carbon Fields 管理页面内同时编辑 ZH 和 EN 字段
- 字段名以 `_zh` / `_en` 后缀区分
- **无需切换语言**，一页搞定

**多实例数据（Product / Blog）**：
- 使用 Polylang 的 post-level 翻译
- 每种语言是独立的 post，通过 Polylang 关联
- 在编辑页右侧 Polylang 面板操作

### 6.4 配置英文首页（Unit 6 待完成）

当前 `/en/` 未显示英文首页，需要：

1. WP Admin → 「页面」→ 确认是否有英文版首页页面
2. 如果没有：新建一个空页面（标题如 "Home EN"），语言设为 EN
3. WP Admin → 「设置」→ 「阅读」→ 确认首页设置
4. WP Admin → 「语言」→ 「设置」→ 确认首页翻译关联
5. 验证 `/en/` 显示英文首页

---

## 7. SEO 管理

### 7.1 SureRank 插件

当前使用 SureRank 管理 SEO 元数据。

### 7.2 页面 SEO 设置

编辑任何页面/文章时，下方可见 SureRank 面板：
- **SEO 标题**：浏览器标签和搜索引擎显示的标题
- **Meta Description**：搜索结果摘要
- **Open Graph**：社交媒体分享标题/描述/图片
- **Twitter Card**：Twitter 分享卡片

### 7.3 API 操作 SEO（高级）

**⚠️ 危险 API**：`GET /surerank/v1/post/settings?post_id=&post_type=` **绝对不要调用**——会触发 PHP 致命错误。

**可用 API**：

| 目的 | 端点 | 方法 |
|------|------|------|
| 写 `<title>` / meta description | `/surerank/v1/page-seo-checks/fix` | POST |
| 写 og/twitter 字段 | `/surerank/v1/post/settings` | POST |
| 全局 SEO 设置 | `/surerank/v1/admin/global-settings` | GET/POST |

---

## 8. 导航菜单管理

### 8.1 菜单结构

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

### 8.2 修改菜单

1. WP Admin → 「外观」→ 「菜单」
2. 顶部「选择要编辑的菜单」切换语言
3. 拖拽调整顺序、添加/删除菜单项
4. 保存菜单

### 8.3 新增菜单项

1. 左侧「添加菜单项」面板
2. 选择页面/自定义链接/分类
3. 点击「添加到菜单」
4. 拖拽到目标位置
5. 保存

---

## 9. 样式与品牌视觉维护

### 9.1 样式文件位置

| 文件 | 位置 | 用途 |
|------|------|------|
| Child Theme style.css | `wordpress/themes/astra-child/style.css` | 品牌配色 + 区块样式 |
| Astra Additional CSS | WP Customizer → Additional CSS | 历史样式（已迁移到 child theme） |

### 9.2 修改样式

1. **首选方式**：编辑 `wordpress/themes/astra-child/style.css` → 重新部署
2. **快速方式**：WP Admin → 「外观」→ 「自定义」→ 「Additional CSS」→ 临时修改
3. **注意**：Additional CSS 的修改不进入 git 版本控制

### 9.3 品牌色彩

配色方案由视觉打底层定义，关键颜色：
- 主色调、背景色、文字色均定义在 child theme `style.css`
- 修改配色应同时更新 child theme 文件并重新部署

### 9.4 Logo

- Logo 由 Astra 主题设置管理
- WP Admin → 「外观」→ 「自定义」→ 「Header Builder」→ 「Logo」
- 当前旧 `km-logo.svg` 已被 CSS `display: none` 隐藏，可在 Header Builder 中替换

---

## 10. 部署流程

### 10.1 文件清单

| 文件/目录 | 部署目标 | 说明 |
|-----------|----------|------|
| `wordpress/themes/astra-child/` | `wp-content/themes/astra-child/` | 子主题（模板+样式） |
| `wordpress/mu-plugins/mindhikers-m1-core.php` | `wp-content/mu-plugins/` | CPT + Carbon Fields 字段定义 |
| `wordpress/mu-plugins/m1-seed.php` | 仅 staging 初始化用 | 内容种子脚本 |

### 10.2 通过 Railway SSH 部署

```bash
# 1. 连接到 Railway
railway link  # 选择 wordpress-l1ta-staging 服务
railway ssh

# 2. 在容器内操作
cd /var/www/html/wp-content

# 3. 上传/更新 child theme
# （先在本地打包 astra-child.zip，然后通过 Railway volume 或其他方式传输）

# 4. 更新 MU Plugin
# 将新的 mindhikers-m1-core.php 内容写入对应路径
```

### 10.3 通过 WP Admin 部署（Child Theme）

1. 本地将 `wordpress/themes/astra-child/` 打包为 `astra-child.zip`
2. WP Admin → 「外观」→ 「主题」→ 「添加新主题」→ 「上传主题」
3. 上传 zip → 安装 → 激活

### 10.4 数据库内容不随文件迁移

**重要**：Carbon Fields Theme Options 的数据存在 `wp_options` 表中，不随主题文件迁移。如果重建环境，需要：
1. 重新运行 seed 脚本，或
2. 手动在后台重新录入内容

---

## 11. 常见问题排错

### 11.1 Blog 区显示"暂无文章"

**症状**：首页 Blog 区块显示"暂无文章"，但后台确有文章。

**原因排查**：
1. 文章是否标记了语言？（Polylang 会按语言过滤）
   - 编辑文章 → 右侧「语言」面板确认
2. WP_Query 的 `lang` 参数是否正确？
   - 模板中传入 `'lang' => $lang`，如果 Polylang 未安装则 `$lang` 默认为 `zh`
3. 文章是否已发布？（草稿不会出现在前台）
4. 文章状态是否为 `publish`？

**解决**：
- 确保每篇文章都通过 Polylang 设置了语言标记
- 如果文章只有中文版，确保中文首页能查到（`lang=zh`）

### 11.2 `/en/` 显示 Blog 页面而非英文首页

**症状**：访问 `/en/` 看到的是博客列表而非英文版首页。

**原因**：Polylang 尚未配置英文首页翻译。

**解决**：参见 [6.4 配置英文首页](#64-配置英文首页unit-6-待完成)

### 11.3 后台出现 "Carbon Fields 插件未激活" 警告

**症状**：WP Admin 顶部黄色警告条。

**解决**：
1. WP Admin → 「插件」→ 确认 Carbon Fields 已安装且激活
2. 如果已激活但仍然警告，可能是 mu-plugin 加载顺序问题
3. 刷新页面，Carbon Fields 通常在第二次加载时正常

### 11.4 页面 500 / 白屏

**排查步骤**：
1. 检查 `wp-content/debug.log`（如已启用 WP_DEBUG）
2. 检查最近是否修改了 PHP 文件（child theme / mu-plugin）
3. 检查 Carbon Fields 是否正常加载
4. 如果是首页 500，检查 `front-page.php` 语法

**快速恢复**：
1. WP Admin → 「外观」→ 「主题」→ 切换回 Astra 父主题
2. 排查并修复 child theme 代码
3. 重新切换回 Astra Child

### 11.5 产品详情页 404

**症状**：访问 `/product/golden-crucible/` 返回 404。

**解决**：
1. WP Admin → 「设置」→ 「固定链接」→ 直接点「保存更改」（刷新 rewrite 规则）
2. 确认 `mh_product` CPT 已注册（检查 mu-plugin 是否存在）
3. 确认产品的 post_status 为 `publish`

### 11.6 Footer 显示旧邮箱

**症状**：Footer 仍显示 `contactmindhiker@gmail.com`。

**原因**：Astra 主题的 Footer 内容由 Astra Customizer 管理，不由 Carbon Fields 控制。

**解决**：
1. WP Admin → 「外观」→ 「自定义」→ 「Footer Builder」
2. 修改 Footer 中的邮箱文字为 `ops@mindhikers.com`
3. 保存并发布

### 11.7 修改内容后前台不更新

**可能原因**：
1. **浏览器缓存**：强制刷新（Ctrl+Shift+R / Cmd+Shift+R）
2. **CDN/代理缓存**：Railway 可能有 CDN 缓存，等待几分钟
3. **WordPress 缓存插件**：如有安装缓存插件，清除缓存
4. **对象缓存**：WordPress 的 object cache 可能缓存了旧数据

---

## 12. 紧急回滚

### 12.1 回滚到 Elementor 版本

如果 child theme 出现严重问题：

1. WP Admin → 「外观」→ 「主题」→ 切换回 **Astra**（父主题）
2. 首页恢复使用 Elementor 渲染
3. 旧的 Elementor 数据仍在数据库中，未被删除
4. 排查并修复 child theme 代码后重新激活

### 12.2 回滚 MU Plugin

如果 `mindhikers-m1-core.php` 导致问题：

1. 通过 Railway SSH 进入容器
2. 删除或重命名 mu-plugin：
   ```bash
   mv /var/www/html/wp-content/mu-plugins/mindhikers-m1-core.php \
       /var/www/html/wp-content/mu-plugins/mindhikers-m1-core.php.bak
   ```
3. Product CPT 和 Carbon Fields 字段会消失，但不会导致 PHP Fatal
4. 首页 `front-page.php` 会 fallback 到空内容

### 12.3 数据库回滚

Railway WordPress 使用 volume 持久化数据库。如果数据库损坏：

1. 联系平台管理员检查 volume 快照
2. 重新运行 `m1-seed.php` 恢复内容基线

---

## 13. 红线与禁忌

### 绝对不要做的事

| # | 禁止操作 | 原因 |
|---|----------|------|
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

### 操作前必须确认

| 场景 | 必须确认 |
|------|----------|
| 修改 PHP 文件 | staging 验证通过后再部署 |
| 修改固定链接 | 理解对 SEO 的影响 |
| 新增插件 | 检查与 Polylang + Carbon Fields 的兼容性 |
| 修改域名配置 | 跨项目域名占用排查（见 `docs/rules.md`） |

---

## 14. 字段速查表

### 14.1 Carbon Fields Theme Options（`wp_options` 表）

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

### 14.2 Product CPT Post Meta（`wp_postmeta` 表）

| Meta Key | 用途 | 类型 | 值域 |
|----------|------|------|------|
| `product_subtitle` | 副标题 | text | 自由文本 |
| `product_status` | 状态 | select | `idea` / `dev` / `beta` / `live` / `sunset` |
| `product_entry_url` | 产品入口链接 | text | URL |
| `product_is_featured` | 是否 Featured | checkbox | `yes` / 空 |

### 14.3 模板文件与数据源映射

| 区块 | 模板文件 | 数据来源 | 读取函数 |
|------|----------|----------|----------|
| Hero | `template-parts/hero.php` | Carbon Fields Theme Options | `carbon_get_theme_option("hero_*_{$lang}")` |
| About | `template-parts/about.php` | Carbon Fields Theme Options | `carbon_get_theme_option("about_*_{$lang}")` |
| Product | `template-parts/product.php` | `WP_Query('mh_product')` + Polylang 过滤 | `carbon_get_the_post_meta()` |
| Blog | `template-parts/blog.php` | `WP_Query('post')` + `'lang' => $lang` | WordPress 原生函数 |
| Contact | `template-parts/contact.php` | Carbon Fields Theme Options | `carbon_get_theme_option("contact_*")` |

---

## 附录 A：关键文件路径

| 文件 | 绝对路径 | 说明 |
|------|----------|------|
| MU Plugin（核心） | `wordpress/mu-plugins/mindhikers-m1-core.php` | CPT + 字段定义 |
| Seed 脚本 | `wordpress/mu-plugins/m1-seed.php` | 内容初始化 |
| Child Theme 入口 | `wordpress/themes/astra-child/functions.php` | 主题功能 |
| 首页模板 | `wordpress/themes/astra-child/front-page.php` | 五区块骨架 |
| Hero 区块 | `wordpress/themes/astra-child/template-parts/hero.php` | |
| About 区块 | `wordpress/themes/astra-child/template-parts/about.php` | |
| Product 区块 | `wordpress/themes/astra-child/template-parts/product.php` | |
| Blog 区块 | `wordpress/themes/astra-child/template-parts/blog.php` | |
| Contact 区块 | `wordpress/themes/astra-child/template-parts/contact.php` | |
| 品牌样式 | `wordpress/themes/astra-child/style.css` | 配色 + 区块 CSS |

## 附录 B：常用操作速查

| 我想... | 操作 |
|---------|------|
| 修改首页 Hero 标题 | 侧边栏 → 「Hero 管理」→ 修改「标题 (ZH/EN)」→ 保存 |
| 新增一个产品 | 侧边栏 → 「产品」→ 「新建」→ 填写字段 → 发布 |
| 添加一篇博客 | 「文章」→ 「写文章」→ 选分类 → 发布 |
| 修改联系邮箱 | 侧边栏 → 「Contact 管理」→ 修改「邮箱」→ 保存 |
| 添加新的社交平台 | 侧边栏 → 「Contact 管理」→ 社交矩阵 → 「添加条目」 |
| 切换导航菜单语言 | 「外观」→ 「菜单」→ 顶部切换语言 → 编辑 |
| 修改产品状态 | 「产品」→ 编辑 → 下方「产品信息」→ 修改「状态」下拉 |
| 刷新固定链接 | 「设置」→ 「固定链接」→ 直接点「保存更改」 |
| 修改 Footer 邮箱 | 「外观」→ 「自定义」→ 「Footer Builder」→ 修改文字 |
| 修改品牌配色 | 编辑 `style.css` → 重新部署 child theme |
| 回滚到 Elementor | 「外观」→ 「主题」→ 切换回 Astra 父主题 |

# MIN-110 Elementor 首页渲染阻塞记录

日期：2026-04-06  
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`  
分支：`codex/cyd-stumpel-home-exploration`  
关联 Issue：`MIN-110`

## 0. 状态更新（2026-04-06 10:20）

该阻塞现已解除，保留本文档作为历史诊断记录。

最终收口路径如下：

1. 外部专家建议执行 `Elementor > Tools > Regenerate CSS & Data`
2. 执行后，首页正文已从 `Kyle Mills / Interior Designer` 切到 Mindhikers 文案
3. 后续又定位到首页 SEO 的来源是首页 `1807` 的 `surerank_settings_general`
4. 去掉默认 `%title% / %site_name% / %content%` token 后，首页 `meta description / og:description / twitter:description` 已切干净
5. footer 模板残留来源为 `widget_text`，已将 `Kyle Mills / New York` 替换为 Mindhikers 联系信息与 `Shanghai / Remote`

当前它不再是活跃阻塞，而是“已定位并修复”的案例。

## 1. 问题描述

当前 `staging` 中，WordPress 数据层与 Elementor 元数据已经被改到 Mindhikers 方向，但公开首页 `https://wordpress-l1ta-staging.up.railway.app/` 仍继续渲染模板默认的 `Kyle Mills / Interior Designer` 文案与 SEO 描述，没有把新内容外显出来。

这属于一个**局部阻塞**：

1. 不影响 `Blog` 统一到 WordPress Posts
2. 不影响默认示例内容清理
3. 影响的是“首页五区块最终外显收敛”这一步

## 2. 本轮已完成的非阻塞主线

1. 已清理默认示例内容：
   - `Hello world!` 已移入 Trash
   - 默认评论已清空
   - `Sample Page` 已移入 Trash
2. 已把导航收敛为：
   - `About`
   - `Product`
   - `Blog`
   - `Contact`
3. 已把 `Blog` 统一到 WordPress Posts：
   - `page_on_front = 1807`
   - `page_for_posts = 1809`
   - 当前公开 `/blog/` 已显示 3 篇文章
4. 已导入首批 3 篇博客：
   - `Testing React Applications: A Practical Guide`
   - `REST API Design Principles That Stand the Test of Time`
   - `Git Workflow Guide: From Chaos to Clarity`

## 3. 已尝试路径

### 尝试 1：浏览器内直接改 Elementor

1. 使用 `agent-browser` 进入 `Edit with Elementor`
2. 试图通过编辑器 UI 直接定位并修改首页区块
3. 结果：
   - 编辑器结构面板与真实内容定位不稳定
   - 交互路径成本高，不适合继续盲点

### 尝试 2：直接修改 WordPress / Elementor 数据

1. 通过 `railway ssh` 进入 `staging` 的 `WordPress-L1ta`
2. 读取 `Home` 页（ID `1807`）的 `_elementor_data`
3. 已确认以下字段已被写入新值：
   - Hero eyebrow / title / description
   - About 文案
   - `Product` 区标题与 3 个卡片文案
   - `Blog` 区标题与 CTA 文案
   - `custom_logo = 0`
4. 数据层回查显示这些值**已写成功**

### 尝试 3：外部公开 HTML 回查

1. 对首页做公开 HTML 抓取
2. 即使加 query 参数重新请求，页面仍返回旧模板文案：
   - `I'm Kyle Mills, Interior Designer.`
   - `Kyle Mills`
   - 旧的 homepage SEO description
3. 同时 `/blog/` 已正确显示 3 篇文章，说明 WordPress 外部站点本身可正常更新，不是全站不可写问题

## 4. 当前观察

1. 数据层与外显层不一致
2. `custom_logo` 已清为 `0`，但公开首页仍显示模板 logo / 人设
3. `_elementor_data` 中目标 widget 已变更，但前台 HTML 仍是旧内容
4. 更像是以下方向之一：
   - Elementor 渲染缓存 / 文件缓存未刷新
   - Astra / Elementor 首页实际渲染来源不是当前修改的那组 widget
   - 首页存在另一层模板源或导入后的结构映射
   - SEO / 页面输出存在独立缓存

## 5. 怀疑原因

当前最可疑的是 **Elementor / Astra 模板导入后的真实渲染源与 `_elementor_data` 修改点不一致，或仍有缓存层没有被正确失效**。

## 6. 外部专家类型

建议请以下任一专家接手：

1. `WordPress + Elementor` 模板结构专家
2. `Astra Starter Templates` 导入机制专家
3. 熟悉 Elementor 前台渲染 / 缓存 / 文件生成链路的专家

## 7. 专家接手建议

专家接手时，优先核对这 4 件事：

1. `Home` 页 `1807` 的真实前台渲染是否直接来自 `_elementor_data`
2. 是否存在需要显式执行的 Elementor regenerate / cache clear / CSS file rebuild
3. 是否存在 Astra 模板导入后的额外全局模板覆盖首页输出
4. 首页 SEO description 的来源是否来自页面摘要、SureRank，还是模板生成内容

## 8. 结论

按照“单一局部问题止损”规则，本问题先完成了止损、落盘与专家升级，随后在专家建议下完成修复。

当前合理状态已变为：

1. 保留已完成的主线推进：
   - 默认内容清理
   - Blog 统一
   - 导航收敛
2. 首页外显渲染、SEO 与 footer 残留均已修复
3. 本文档作为历史证据保留，供后续遇到类似 Elementor / SureRank / 模板缓存问题时参考

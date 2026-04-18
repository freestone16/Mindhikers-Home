# 🔴 M1 完成状态与核心问题 — 供外部专家审查

> **文档用途**：保存当前进度快照，明确技术决策与遗留问题，供外部专家快速理解上下文
> **最后更新**：2026-04-18
> **撰写者**：OldYang (AI Agent)

---

## 1. 项目背景（一句话）

把 `https://mindhikers.com/`（Next.js 静态站）迁移到 **WordPress CMS**，让非技术运营人员能自主编辑首页内容。

---

## 2. 已完成工作（M1）

### 2.1 后台数据层 ✅

| 组件 | 状态 | 说明 |
|------|------|------|
| WordPress 容器 | ✅ | Railway 部署，Docker 构建 |
| Astra Child Theme | ✅ | 子主题框架已创建 |
| Carbon Fields | ✅ | Hero / About / Contact / Product / Blog 字段定义 |
| Product CPT | ✅ | 自定义产品类型，5 种状态 |
| Polylang | ✅ | 中英双语配置完成 |
| Seed 数据 | ✅ | 中英文首页内容已填充 |

### 2.2 数据读写逻辑 ✅

- PHP 模板正确读取 Carbon Fields 字段
- 中英双语通过 `$lang` 参数切换
- Product / Blog 通过 Polylang 过滤

### 2.3 部署链路 ✅

- `railway.json` 强制 Dockerfile 构建
- 通过 SSH 上传文件到容器
- staging 环境可访问

---

## 3. 🔴 核心问题：前台视觉与生产环境差距巨大

### 3.1 当前 Staging 效果

**URL**: `https://wordpress-l1ta-staging.up.railway.app/`

**截图特征**（用户提供的 production 对比图）：
- ❌ 左上角没有品牌 Logo
- ❌ 导航只有 "Home" + "开始联系"，没有 About/Product/Blog/Contact
- ❌ Hero 区域没有右侧信息面板（Current focus / Working rhythm / Homepage blocks）
- ❌ 整体布局是简陋的上下堆叠，没有精致排版
- ❌ 没有卡片式设计、没有网格布局
- ❌ 没有动效和交互细节

### 3.2 目标 Production 效果

**URL**: `https://mindhikers.com/`

**截图特征**（用户期望的效果）：
- ✅ 左上角有 "心行者 Mindhikers" Logo
- ✅ 导航：About / Product / Blog / Contact / EN 语言切换
- ✅ Hero 左侧大标题 + 右侧信息面板（Current focus / Working rhythm / Homepage blocks）
- ✅ About 区域有卡片式设计
- ✅ Product 区域有 Featured release 卡片
- ✅ Blog 区域有文章卡片网格
- ✅ 整体有精致排版、间距、品牌视觉

### 3.3 差距本质

**M1 只完成了"数据模型"，完全没有做"前端视觉还原"。**

当前模板只是把字段读出来、简单堆叠，没有：
- 精细的 CSS 布局（flex/grid、响应式）
- 右侧信息面板组件
- 卡片式设计系统
- 品牌字体和间距体系
- Astra Header Builder 配置（Logo + 导航）

---

## 4. 技术决策记录

### 4.1 为什么选择 WordPress 模板路线？

**原计划**：
- M1：搭建 WordPress 后台 + 基础模板（数据可编辑）
- M2：精细化前端视觉还原

**为什么没有直接用 Elementor / 现成模板？**
- 决策来自 `docs/plans/2026-04-12-001-feat-m1-cms-content-model-plan.md`
- 目的是让内容完全字段化（Carbon Fields），便于长期维护
- Elementor 模板虽然好看，但内容可能写死在页面里，换内容需要重新编辑页面

### 4.2 当前技术栈

| 层级 | 技术 | 状态 |
|------|------|------|
| 容器 | Railway + Docker (WordPress 官方镜像) | ✅ |
| 主题 | Astra (父) + Child Theme (自定义) | ✅ |
| 字段 | Carbon Fields v3.6.9 | ✅ |
| 多语言 | Polylang 3.8.2 Free | ✅ |
| 前端 | 纯 PHP 模板 + CSS（无框架） | ⚠️ 只写了基础样式 |

### 4.3 关键文件位置

```
wordpress/themes/astra-child/
├── front-page.php           # 首页骨架（5 个区块引入）
├── functions.php            # 主题功能 + EN 输出缓冲替换
├── style.css                # 品牌样式（但只有基础覆盖，无精细布局）
└── template-parts/
    ├── hero.php             # Hero 区块（简单上下堆叠）
    ├── about.php            # About 区块（标题 + 内容）
    ├── product.php          # Product 区块（列表式）
    ├── blog.php             # Blog 区块（简单条目）
    └── contact.php          # Contact 区块（卡片式）
```

---

## 5. 为什么用户觉得"无法验收"

### 5.1 期望 vs 现实

| 用户期望 | 实际交付 |
|----------|----------|
"CMS 后台 + 前台效果" | 只有 CMS 后台 + 简陋前台 |
"能媲美现有生产站" | 差距巨大，像半成品 |
"快速上线" | 前端视觉还没开始做 |

### 5.2 根本原因

**M1 计划定义的范围过窄**：只定义了"数据模型"（字段 + 读写），没有定义"前端视觉还原"。

PHP 模板把字段读出来 ≠ 有品牌视觉效果。

---

## 6. 可选解决方案

### 方案 A：套现成 Astra Starter Template（最快）

**做法**：
1. 在 Astra 主题库选一个接近品牌风格的 Starter Template
2. 用 Elementor / Block Editor 搭建首页
3. 把 Carbon Fields 数据填入模板对应位置
4. 保留双语逻辑

**优点**：
- 几小时到 1 天就能看到精致效果
- 无需从零写 CSS

**缺点**：
- 内容可能部分写死在 Elementor 里，换内容需重新编辑页面
- 和"字段化维护"的初衷有冲突

### 方案 B：重写前端模板 + CSS（最符合初衷）

**做法**：
1. 按照 production 效果，重写 `front-page.php` 和 5 个区块模板
2. 添加右侧信息面板组件
3. 写大量 CSS：布局、卡片、响应式、动效
4. 配置 Astra Header Builder（Logo + 导航菜单）

**优点**：
- 内容完全字段化，长期可维护
- 和品牌设计 100% 匹配

**缺点**：
- 工作量大（3-5 天）
- 需要专业前端开发能力

### 方案 C：回到 Next.js Headless（用户已否决）

**做法**：保持 `mindhikers.com` 的 Next.js 前端，只把数据源换成 WordPress API。

**用户态度**：已明确否决，要坚持 WordPress 模板路线。

### 方案 D：放弃当前项目，用腾讯云 WordPress 镜像

**用户提问**：有什么区别？

**回答**：
- 腾讯云镜像：外观可能更好（有现成主题），但无自定义字段、无双语、无产品 CPT
- 当前方案：功能完整（字段化 + 双语 + CPT），但外观简陋
- **本质区别**：腾讯云镜像是"通用 WordPress"，当前方案是"定制 CMS"，只是前端没做完

---

## 7. 遗留问题清单

| # | 问题 | 优先级 | 说明 |
|---|------|--------|------|
| 1 | 前端视觉还原 | 🔴 P0 | 当前最大 blocker |
| 2 | Logo 配置 | 🔴 P0 | Astra Header Builder 未配置 |
| 3 | 导航菜单完善 | 🔴 P0 | 只有 Home + 开始联系 |
| 4 | 右侧信息面板 | 🔴 P0 | Hero 区域缺少组件 |
| 5 | 运营手册修订 | 🟡 P1 | 6.4 / 11.2 等章节已过时 |
| 6 | 表单插件选择 | 🟡 P1 | SureForms vs WPForms，留到后续 |
| 7 | 生产部署 | 🟡 P1 | staging → production 流程 |

---

## 8. 外部专家需要关注的核心问题

**请外部专家帮用户决策**：

1. **路线选择**：是坚持"字段化自定义主题"（方案 B），还是转向"现成模板 + 可视化编辑"（方案 A）？
2. **工作量评估**：如果选方案 B，前端视觉还原需要多少工作量？
3. **技术债务**：当前 Carbon Fields + 自定义模板的架构是否合理？
4. **替代方案**：是否有更好的 WordPress 技术栈（如 Full Site Editing、Bricks Builder 等）？

---

## 9. 关键资源

| 资源 | 地址 |
|------|------|
| Staging 前台 | `https://wordpress-l1ta-staging.up.railway.app/` |
| Staging 后台 | `https://wordpress-l1ta-staging.up.railway.app/wp-admin/` |
| 生产目标 | `https://mindhikers.com/` |
| 生产英文 | `https://mindhikers.com/en` |
| 后台账号 | `mindhikers_admin` / `IW0pGAFhiydfFg3GC5xxgl+L` |
| M1 执行方案 | `docs/plans/2026-04-12-001-feat-m1-cms-content-model-plan.md` |
| 运营手册 | `docs/operations-guide.md` |
| Git 仓库 | `https://github.com/freestone16/Mindhikers-Home` |
| 当前分支 | `main` |

---

## 10. 当前不要做的事（红线）

1. 不要回到旧的 Next.js 前台路线（用户已否决）
2. 不要在生产环境直接试错
3. 不要提前取消 staging 的 `noindex`
4. 不要把 `/` 和 `/en` 的语言职责重新混在一起
5. 不要盲改 SureRank 字段名——必须用 `page-seo-checks/fix` 路径
6. 不要删除 `wordpress/mu-plugins/mindhikers-cms-core.php`（旧 headless 插件），只保留不启用即可

---

*文档结束。供外部专家审查使用。*

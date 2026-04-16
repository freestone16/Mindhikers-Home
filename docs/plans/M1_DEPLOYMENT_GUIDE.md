# M1 部署指南

## 已创建文件

### 1. Child Theme
- **文件**: `astra-child.zip`
- **包含**: 
  - `style.css` — 品牌样式 + 区块样式
  - `functions.php` — 加载父主题样式 + Carbon Fields 检查
  - `front-page.php` — 首页五大区块骨架
  - `template-parts/hero.php` — Hero 区块
  - `template-parts/about.php` — About 区块
  - `template-parts/product.php` — Product 区块
  - `template-parts/blog.php` — Blog 区块
  - `template-parts/contact.php` — Contact 区块

### 2. MU Plugin
- **文件**: `wordpress/mu-plugins/mindhikers-m1-core.php`
- **功能**:
  - 注册 `mh_product` CPT
  - Carbon Fields Theme Options (Hero / About / Contact)
  - Carbon Fields Post Meta (Product 字段)

---

## 部署步骤

### 步骤 1: 安装 Carbon Fields 插件

1. 登录 WP Admin: `https://wordpress-l1ta-staging.up.railway.app/wp-admin`
2. 导航到 **插件 → 安装插件**
3. 搜索 "Carbon Fields"
4. 安装并激活 **Carbon Fields** (由 htmlburger 开发)

### 步骤 2: 上传 Child Theme

1. 导航到 **外观 → 主题 → 添加新主题 → 上传主题**
2. 选择 `astra-child.zip` 文件
3. 点击 **立即安装**
4. 安装完成后点击 **激活**

### 步骤 3: 上传 MU Plugin

通过 Railway CLI 或 SFTP 上传:

```bash
# 使用 Railway CLI
railway link  # 选择 wordpress-l1ta-staging 服务
railway ssh   # 进入容器

# 在容器内
mkdir -p /var/www/html/wp-content/mu-plugins
cat > /var/www/html/wp-content/mu-plugins/mindhikers-m1-core.php << 'PHPEOF'
# [粘贴 mindhikers-m1-core.php 内容]
PHPEOF
```

或者通过 WP Admin → 插件编辑器手动创建（不推荐用于 mu-plugin）。

### 步骤 4: 激活后配置

1. **设置静态首页**:
   - 导航到 **设置 → 阅读**
   - 选择 "首页显示 → 一个静态页面"
   - 首页选择现有首页（Home）

2. **检查 Polylang**:
   - 确认 `/en/` 路由正常
   - 确认语言切换器可见

---

## 内容录入（Unit 6 验证用）

### Hero 管理
- 导航到 **Hero 管理** (侧边栏菜单)
- 录入 ZH 和 EN 字段内容
- 参考数据源: `src/data/site-content.ts` 中的 `hero` 字段

### About 管理
- 导航到 **About 管理**
- 使用品牌定位原文作为底稿

### Contact 管理
- 导航到 **Contact 管理**
- 邮箱: `ops@mindhikers.com` (或待确认的最新邮箱)
- 社交矩阵: Twitter, Bilibili, 微信公众号

### 产品
- 导航到 **产品 → 新建**
- 创建黄金坩埚 ZH 版本:
  - 标题: 黄金坩埚
  - 副标题: 你的个人 AI 战略伙伴
  - 状态: 构思中 (重要: 不是 "Live now")
  - 简介: [从 site-content.ts 复制]
- 创建黄金坩埚 EN 版本:
  - 标题: Golden Crucible
  - 副标题: Your Personal AI Strategy Partner
  - 状态: Idea Stage
- 使用 Polylang 关联 ZH/EN 为翻译对

### Blog 分类
- 导航到 **文章 → 分类目录**
- 创建 3 个主分类 (父级):
  1. AI 技术 (ai-technology)
  2. 碳硅共生 (carbon-silicon-symbiosis)
  3. 脑神经科学 (neuroscience)
- 为每个主分类创建 4 个子分类:
  1. 深度 (deep)
  2. 速记 (notes)
  3. 视频 (video)
  4. 工具 (tools)
- 将现有 3 篇文章分配到对应分类

---

## 验证清单

### Unit 6 验证项

- [ ] `/` 首页显示中文五大区块
- [ ] `/en/` 首页显示英文五大区块
- [ ] 首页渲染绕过 Elementor（页面源码无 `elementor` class）
- [ ] Hero 内容来自 Carbon Fields
- [ ] Product 区显示黄金坩埚
- [ ] 产品详情页 `/product/golden-crucible/` 可访问
- [ ] 英文产品详情页 `/en/product/golden-crucible/` 可访问
- [ ] Blog 区显示 3 篇文章
- [ ] 导航菜单双语正确
- [ ] 语言切换器功能正常
- [ ] 无 PHP 错误

---

## 技术栈变更

| 组件 | 变更前 | 变更后 |
|------|--------|--------|
| 首页渲染 | Elementor `_elementor_data` | Astra Child Theme `front-page.php` |
| 单例数据 | JSON meta | Carbon Fields Theme Options |
| Product | 硬编码卡片 | `mh_product` CPT |
| 双语 | CSS/JS 隐藏 | Polylang 目录模式 |

---

## 回滚方案

如需回滚到 Elementor 版本:
1. WP Admin → 外观 → 主题 → 切换回 Astra 父主题
2. 首页恢复使用 Elementor 渲染
3. 删除或停用 `mindhikers-m1-core.php`

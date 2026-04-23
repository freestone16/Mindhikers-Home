# WordPress 传统模式可行性评估报告

## 1. 执行摘要

在 `experiment/wp-traditional-mode` 分支上，对将 Mindhikers-Homepage 从 **Headless 架构（Next.js + WP CMS）** 切换为 **传统 WordPress 模式** 的可行性进行了评估。

**结论**：技术上可行，但存在**数据层不兼容**的核心障碍，需要决定数据存储策略。

---

## 2. 当前架构 vs 目标架构

### 2.1 当前 Headless 架构

```
mindhikers.com (Next.js 16 + React 19 + Tailwind CSS 4)
    │
    ▼ GET /wp-json/mindhikers/v1/homepage/zh-CN
homepage-manage.mindhikers.com (WordPress + mu-plugins)
    │
    ├── mh_homepage post (public: false)
    │   └── meta: mindhikers_homepage_payload (JSON)
    │
    └── mindhikers_site_settings_payload (JSON option)
```

**数据流**：
1. WP Admin 编辑 → 保存 JSON 到 post meta
2. Next.js 请求 REST API → 获取 JSON
3. React 组件渲染 → 输出 HTML

### 2.2 目标传统 WP 架构

```
mindhikers.com (WordPress 直接渲染)
    │
    ├── WP Theme (PHP 模板)
    │   └── front-page.php → template-parts/*.php
    │
    └── WP Admin 管理内容和主题
```

**数据流**：
1. WP Admin 编辑 → 保存到 post/page/meta
2. PHP 模板直接读取 → 输出 HTML

---

## 3. 关键发现

### 3.1 现有 WP 主题已存在

**主题**：`wordpress/themes/astra-child/`（Mindhikers Astra Child）

| 文件 | 用途 | 状态 |
|---|---|---|
| `style.css` | 品牌色彩、字体、全局样式 | ✅ 完整 |
| `functions.php` | 加载父主题、Carbon Fields 集成 | ⚠️ 需调整 |
| `front-page.php` | 首页模板，加载 5 个区块 | ⚠️ 数据不兼容 |
| `template-parts/hero.php` | Hero 区块 | ⚠️ 使用 Carbon Fields |
| `template-parts/about.php` | About 区块 | ⚠️ 使用 Carbon Fields |
| `template-parts/product.php` | Product 区块 | ⚠️ 使用 Carbon Fields |
| `template-parts/blog.php` | Blog 区块 | ⚠️ 使用 Carbon Fields |
| `template-parts/contact.php` | Contact 区块 | ⚠️ 使用 Carbon Fields |

### 3.2 数据存储双轨制（核心障碍）

| 系统 | 存储位置 | 读取方式 | 用途 |
|---|---|---|---|
| **CMS Core** (Headless) | `mh_homepage` post meta JSON | REST API | Next.js 前台 |
| **Carbon Fields** (传统) | `wp_options` / post meta | `carbon_get_theme_option()` | Astra Child 主题 |

**问题**：两套数据系统**完全不互通**
- CMS Core 的 JSON 数据 Astra Child 读不到
- Carbon Fields 的数据 CMS Core 不写入

### 3.3 `mh_homepage` Post Type 限制

```php
// CMS Core 定义
register_post_type('mh_homepage', [
    'public' => false,        // ❌ 不公开，无法直接访问
    'show_in_rest' => true,   // ✅ REST API 可用
]);
```

传统 WP 模式下，`public: false` 的 post type 无法被主题模板直接渲染为页面。

### 3.4 主题切换可行性

WP 原生支持主题切换（外观 → 主题），但：
- 每个主题需要**对接同一套数据存储**
- 当前 Astra Child 依赖 Carbon Fields，其他主题可能依赖 ACF/自定义字段
- **切换主题 = 数据丢失风险**，除非统一数据层

---

## 4. 数据互通方案对比

### 方案 A：统一数据层（推荐）

**思路**：让 CMS Core 同时写入 Carbon Fields 格式，或让主题读取 CMS Core 的 JSON。

**实现方式**：
```php
// 在 CMS Core 保存时，同步写入 Carbon Fields 格式
add_action('save_post_mh_homepage', function($post_id) {
    $payload = json_decode(get_post_meta($post_id, 'mindhikers_homepage_payload', true), true);
    
    // 同步到 Carbon Fields
    carbon_set_theme_option('hero_title_zh', $payload['hero']['title']);
    carbon_set_theme_option('hero_description_zh', $payload['hero']['description']);
    // ... 其他字段
});
```

**工作量**：中等（需要映射所有字段）
**优点**：数据统一，主题切换安全
**缺点**：需要维护字段映射表

### 方案 B：主题直接读取 JSON

**思路**：修改 Astra Child 主题，直接读取 `mh_homepage` 的 JSON meta。

**实现方式**：
```php
// front-page.php
$post = get_posts(['post_type' => 'mh_homepage', 'posts_per_page' => 1])[0];
$payload = json_decode(get_post_meta($post->ID, 'mindhikers_homepage_payload', true), true);

// template-parts/hero.php
$hero = $payload['hero'];
?>
<h1><?php echo esc_html($hero['title']); ?></h1>
```

**工作量**：低（只需修改主题）
**优点**：最小改动，直接利用现有数据
**缺点**：其他主题也需要同样修改才能切换

### 方案 C：WP 页面 + 块编辑器

**思路**：放弃 `mh_homepage` post type，改用标准 WP 页面 + Gutenberg 块。

**工作量**：高（需要重建所有内容）
**优点**：最符合 WP 原生生态，主题切换最自由
**缺点**：与现有 CMS Core 完全不兼容

---

## 5. 最小工作量实施路径

基于"尽可能少的工作量"原则，推荐 **方案 B（主题直接读取 JSON）** 作为实验第一步。

### 5.1 实施步骤

| 步骤 | 任务 | 预估工作量 | 文件 |
|---|---|---|---|
| 1 | 修改 `mh_homepage` post type 为 `public => true` | 5 min | `bootstrap.php` |
| 2 | 重写 `front-page.php` 读取 JSON payload | 30 min | `front-page.php` |
| 3 | 重写 5 个 template-parts 使用 JSON 数据 | 2-3 hr | `template-parts/*.php` |
| 4 | 本地 Docker 验证前台渲染 | 30 min | `docker-compose.yml` |
| 5 | 验证 WP Admin 编辑后前台更新 | 15 min | - |
| 6 | 测试主题切换（准备第二个主题） | 2 hr | 新主题目录 |

**总预估**：1 个工作日（不含主题切换）

### 5.2 需要修改的关键代码

#### CMS Core (`bootstrap.php`)
```php
// 第 ~120 行
register_post_type('mh_homepage', [
    'public' => true,  // 改为 true，让主题可以查询
    'has_archive' => false,
    'rewrite' => ['slug' => 'homepage'],
]);
```

#### Astra Child (`front-page.php`)
```php
<?php
// 获取 homepage 数据
$posts = get_posts([
    'post_type' => 'mh_homepage',
    'posts_per_page' => 1,
    'post_status' => 'publish',
]);

if (empty($posts)) {
    get_header();
    echo '<p>Homepage data not found.</p>';
    get_footer();
    exit;
}

$payload = json_decode(
    get_post_meta($posts[0]->ID, 'mindhikers_homepage_payload', true),
    true
);

get_header();
get_template_part('template-parts/hero', null, ['payload' => $payload]);
get_template_part('template-parts/about', null, ['payload' => $payload]);
get_template_part('template-parts/product', null, ['payload' => $payload]);
get_template_part('template-parts/blog', null, ['payload' => $payload]);
get_template_part('template-parts/contact', null, ['payload' => $payload]);
get_footer();
```

---

## 6. 风险评估

| 风险 | 概率 | 影响 | 缓解措施 |
|---|---|---|---|
| JSON 数据结构变更导致主题崩溃 | 中 | 高 | 版本化 payload schema |
| 多语言支持复杂化 | 中 | 中 | 保持 `locale` 参数逻辑 |
| 主题切换时数据丢失 | 高 | 高 | 所有主题统一读取 JSON |
| 性能问题（PHP 解析 JSON） | 低 | 低 | 使用 WP Transient 缓存 |
| SEO 差异（vs Next.js SSR） | 中 | 中 | WP 也有 SSR，需验证 meta |

---

## 7. 建议

### 7.1 短期（实验验证）

1. **实施方案 B**：让 Astra Child 直接读取 CMS Core 的 JSON
2. **本地验证**：跑通 WP 前台渲染 + 后台编辑 + 数据更新
3. **不做主题切换**：先验证单主题可行性

### 7.2 中期（如需生产化）

1. **评估方案 A**：建立数据同步层，支持多主题
2. **性能优化**：添加缓存层（Redis/WP Transient）
3. **SEO 对比**：对比 Next.js vs WP 的 Lighthouse 分数

### 7.3 长期（如需完全迁移）

1. **考虑方案 C**：逐步迁移到标准 WP 页面 + 块编辑器
2. **保留 CMS Core**：作为向后兼容的 API 层

---

## 8. 下一步行动

需要您决策：

1. **是否继续实验？**
   - ✅ 是 → 我可以开始实施方案 B（修改主题读取 JSON）
   - ❌ 否 → 保留当前 Headless 架构

2. **数据层策略？**
   - A. 主题直接读 JSON（最小工作量，实验首选）
   - B. 建立数据同步层（中等工作量，长期更好）
   - C. 迁移到标准 WP 页面（最大工作量，最原生）

3. **主题切换是否必须验证？**
   - 是 → 需要准备第二个主题
   - 否 → 先验证单主题可行性

---

*评估完成时间：2026-04-22*
*分支：`experiment/wp-traditional-mode`*

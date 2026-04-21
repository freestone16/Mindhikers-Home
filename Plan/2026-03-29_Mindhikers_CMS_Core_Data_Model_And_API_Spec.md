# MindHikers CMS Core 数据模型与 API 规范

日期：2026-03-29
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
分支：`codex/cyd-stumpel-home-exploration`
目标插件：`mindhikers-cms-core`

## 1. 目标

这份文档定义 `mindhikers-cms-core` 的职责、数据模型、后台边界、REST 协议和 webhook 机制。

它的目标不是“再写一个通用 WordPress 插件”，而是作为 `Mindhikers` 的全站内容核心层，满足以下要求：

1. 让 `WordPress` 可以后台管理整个 homepage
2. 让前端拿到稳定、结构化、可长期维护的 JSON
3. 避免把主站交给 page builder 或自由 HTML
4. 让内容编辑和页面实现彻底解耦
5. 为后续 Railway 三服务实施提供字段级蓝图

## 2. 插件定位

`mindhikers-cms-core` 是一个“轻量、业务专用、长期维护”的核心插件。

它负责：

1. 注册内容类型
2. 注册业务字段
3. 输出自定义 REST API
4. 统一做字段清洗与校验
5. 在关键内容变更后回调前台 revalidate 接口

它不负责：

1. 页面渲染
2. 前台主题逻辑
3. 可视化页面拼装
4. SEO 前端渲染实现

## 3. 插件部署形态

推荐以 `mu-plugin` 形态部署。

推荐路径：

1. `wp-content/mu-plugins/mindhikers-cms-core.php`
2. `wp-content/mu-plugins/mindhikers-cms-core/`

推荐原因：

1. 自动加载
2. 不依赖后台手工启用
3. 核心内容模型不会被误关
4. 对生产稳定性更友好

如果后续实现复杂，也可以采用：

1. `mu-plugin` 入口文件
2. 真正逻辑放在 `wp-content/plugins/mindhikers-cms-core/`

由 `mu-plugin` 负责引导加载。

## 4. 数据域划分

`Mindhikers` 全站 CMS 数据分为四个域：

1. `Site Settings`
2. `Homepage`
3. `Product Pages`
4. `Blog Posts`

### 4.1 Site Settings

这是站点级配置，建议为单例对象。

包含：

1. 品牌名称
2. 默认 SEO 标题模板
3. 默认 SEO 描述
4. 默认站点 URL
5. 联系邮箱
6. 社交链接
7. 默认 OG 图片
8. 版权信息

### 4.2 Homepage

按语言拆为两个实体：

1. `homepage-zh`
2. `homepage-en`

每个实体都完整描述对应语言首页所需的结构化字段。

### 4.3 Product Pages

产品页按语言、slug 管理。

第一阶段至少有：

1. `golden-crucible / zh`
2. `golden-crucible / en`

### 4.4 Blog Posts

博客仍然采用原生 `post`。

原因：

1. WordPress 原生文章能力成熟
2. revision、分类、标签、媒体、发布时间都现成
3. 后台体验对内容团队最熟悉

## 5. 内容类型定义

### 5.1 `mh_homepage`

类型：

1. Custom Post Type

用途：

1. 存储首页内容

记录规则：

1. 每种语言一条记录
2. slug 固定为：
   - `homepage-zh`
   - `homepage-en`

关键字段：

1. `locale`
   - `zh` | `en`
2. `metadata_title`
3. `metadata_description`
4. `navigation_brand`
5. `navigation_links`
6. `navigation_switch_language_label`
7. `navigation_switch_language_href`
8. `hero_*`
9. `about_*`
10. `product_section_*`
11. `blog_section_*`
12. `contact_section_*`

### 5.2 `mh_product_page`

类型：

1. Custom Post Type

用途：

1. 存储产品页内容

记录规则：

1. 每个产品每种语言一条记录
2. 组合键：
   - `product_slug`
   - `locale`

第一阶段：

1. `golden-crucible / zh`
2. `golden-crucible / en`

### 5.3 `mh_site_settings`

实现建议：

1. 不单独做 CPT
2. 使用单例 options 存储

理由：

1. 这是站点级配置，不是内容集合
2. 更适合 option 语义

推荐 option key：

1. `mindhikers_site_settings`

### 5.4 原生 `post`

沿用 WordPress 原生 `post`。

可补充的自定义字段：

1. `locale`
2. `featured_on_homepage`
3. `seo_title`
4. `seo_description`

## 6. 字段规范

### 6.1 首页字段规范

当前前台首页所需字段，可以直接映射为以下结构：

```ts
type HomepagePayload = {
  locale: "zh" | "en";
  metadata: {
    title: string;
    description: string;
  };
  navigation: {
    brand: string;
    links: Array<{
      href: string;
      label: string;
    }>;
    switchLanguage: {
      href: string;
      label: string;
    };
  };
  hero: {
    eyebrow: string;
    title: string;
    description: string;
    primaryAction: {
      href: string;
      label: string;
    };
    secondaryAction: {
      href: string;
      label: string;
    };
    highlights: string[];
    statusLabel: string;
    statusValue: string;
    availabilityLabel: string;
    availabilityValue: string;
    panelTitle: string;
  };
  about: {
    title: string;
    intro: string;
    paragraphs: string[];
    notes: string[];
  };
  product: {
    title: string;
    description: string;
    headline: string;
    featured: {
      eyebrow: string;
      title: string;
      description: string;
      href?: string;
      ctaLabel?: string;
      meta?: string;
    };
    items: Array<{
      eyebrow: string;
      title: string;
      description: string;
      href?: string;
      ctaLabel?: string;
      meta?: string;
    }>;
  };
  blog: {
    title: string;
    description: string;
    headline: string;
    cta: {
      href: string;
      label: string;
    };
    emptyLabel: string;
    readArticleLabel: string;
  };
  contact: {
    title: string;
    description: string;
    headline: string;
    emailLabel: string;
    email: string;
    locationLabel: string;
    location: string;
    availabilityLabel: string;
    availability: string;
    links: Array<{
      href: string;
      label: string;
      note: string;
    }>;
  };
};
```

### 6.2 产品页字段规范

当前产品页所需字段建议映射为：

```ts
type ProductPagePayload = {
  slug: string;
  locale: "zh" | "en";
  metadata: {
    title: string;
    description: string;
  };
  eyebrow: string;
  title: string;
  summary: string;
  bullets: string[];
  stageLabel: string;
  stageValue: string;
  returnHome: {
    href: string;
    label: string;
  };
  switchLanguage: {
    href: string;
    label: string;
  };
};
```

### 6.3 Site Settings 字段规范

建议结构：

```ts
type SiteSettingsPayload = {
  brandName: string;
  siteUrl: string;
  defaultSeoTitle: string;
  defaultSeoDescription: string;
  defaultOgImage?: string;
  email: string;
  socials: Array<{
    name: string;
    label: string;
    url: string;
  }>;
};
```

## 7. 字段存储策略

### 7.1 存储原则

建议使用：

1. `post_meta`
2. `options`

不建议依赖复杂的第三方字段系统作为唯一真相源。

### 7.2 复杂数组的存储方式

对于：

1. `navigation_links`
2. `hero_highlights`
3. `about_paragraphs`
4. `about_notes`
5. `product_items`
6. `contact_links`
7. `product_bullets`

建议存成 JSON 字符串或受控结构数组，再由插件在 REST 输出时归一化。

关键要求：

1. REST 输出必须是强结构
2. 后台存储实现可以隐藏在插件内部

## 8. 后台编辑体验设计

### 8.1 编辑界面原则

后台编辑界面要满足：

1. 结构清楚
2. 字段命名明确
3. 内容编辑不用懂前端代码
4. 不让内容编辑自由拼整页 HTML

### 8.2 推荐字段分组

`mh_homepage` 编辑页建议分为以下面板：

1. `Metadata`
2. `Navigation`
3. `Hero`
4. `About`
5. `Product Section`
6. `Blog Section`
7. `Contact Section`

`mh_product_page` 编辑页建议分为：

1. `Metadata`
2. `Core Content`
3. `Bullets`
4. `Actions`
5. `Stage`

### 8.3 受控列表字段

对于多项数组字段，后台应提供受控 repeater 风格编辑器。

例如：

1. navigation links
2. highlights
3. notes
4. product items
5. contact links
6. product bullets

要求：

1. 限制条目数量
2. 限制单条字段长度
3. 后台保存前校验

## 9. 权限设计

### 9.1 角色

推荐角色模型：

1. `Administrator`
   - 系统配置、插件、域名、安全
2. `Editor`
   - 编辑首页、产品页、博客文章
3. `Author`
   - 仅写博客草稿

### 9.2 内容权限建议

建议：

1. `Editor` 可以编辑：
   - `mh_homepage`
   - `mh_product_page`
   - `post`
2. `Administrator` 才能编辑：
   - `Site Settings`
   - 插件配置
   - webhook 密钥

原因：

1. 站点级设置属于高风险配置
2. 首页与产品页可以由内容负责人维护

## 10. REST API 设计

### 10.1 设计原则

REST 层必须：

1. 稳定
2. 扁平
3. 可缓存
4. 可校验
5. 不暴露后台内部 meta 细节

### 10.2 路由清单

建议输出以下接口：

1. `GET /wp-json/mindhikers/v1/site-settings`
2. `GET /wp-json/mindhikers/v1/homepage/zh`
3. `GET /wp-json/mindhikers/v1/homepage/en`
4. `GET /wp-json/mindhikers/v1/product/golden-crucible?locale=zh`
5. `GET /wp-json/mindhikers/v1/product/golden-crucible?locale=en`
6. `GET /wp-json/mindhikers/v1/homepage-slugs`
7. `GET /wp-json/mindhikers/v1/products`

博客维持：

1. `GET /wp-json/wp/v2/posts`
2. `GET /wp-json/wp/v2/posts?slug=...`

### 10.3 返回规范

统一返回：

1. 不包含后台富余字段
2. 字段命名和前端类型一致
3. 空数组而不是 `null`
4. 缺省对象结构仍保持完整

例如 `homepage/zh` 即使 `contact.links` 为空，也应返回：

```json
{
  "contact": {
    "links": []
  }
}
```

## 11. 字段校验与净化

### 11.1 保存时校验

插件保存内容时必须校验：

1. 必填字段是否存在
2. `locale` 是否合法
3. href 是否是允许的路径或 URL
4. email 是否合法
5. 数组条目数是否超限
6. 文本长度是否超限

### 11.2 输出时净化

REST 输出前应：

1. 清理空值
2. 统一数组结构
3. 统一字符串 trim
4. 对 URL 做协议限制

## 12. Webhook 与缓存刷新

### 12.1 什么时候触发

以下内容发生变更时，应通知前台刷新：

1. homepage 发布或更新
2. product page 发布或更新
3. post 发布或更新
4. site settings 更新

### 12.2 调用目标

调用：

1. `https://www.mindhikers.com/api/revalidate`

请求携带：

1. `REVALIDATE_SECRET`
2. 可选 `path`
3. 可选 `slug`
4. 可选 `type`

### 12.3 刷新策略建议

如果变更的是：

1. `homepage`
   - 刷新 `/`
   - 刷新 `/en`
2. `product page`
   - 刷新对应产品页
3. `post`
   - 刷新 `/blog`
   - 刷新详情页
   - 刷新首页最近文章区域
4. `site settings`
   - 刷新全站共享页面

## 13. 前端对应改造点

当前前端已经做了博客 CMS 抽象，但还需要继续做全站接入。

后续前端要新增：

1. `src/lib/cms/site-settings.ts`
2. `src/lib/cms/homepage.ts`
3. `src/lib/cms/product-pages.ts`

并逐步替换：

1. `src/data/site-content.ts`
2. `src/components/navbar.tsx`
3. `src/app/page.tsx`
4. `src/app/en/page.tsx`
5. `src/app/golden-crucible/page.tsx`
6. `src/app/en/golden-crucible/page.tsx`
7. `src/app/layout.tsx` 的共享 metadata 来源

## 14. 最小可行版本

为了避免一次吃太大，`mindhikers-cms-core` 的最小可行版本建议是：

1. 提供 `Site Settings`
2. 提供 `Homepage (ZH/EN)`
3. 提供 `Product Page (Golden Crucible ZH/EN)`
4. 提供最小 REST 输出
5. 提供 webhook 回调

这就足够支撑整站首页进入 CMS。

博客仍然可以继续使用现有的 WordPress 原生 posts 能力，不必和首页模型一起重造。

## 15. 长期稳定性结论

如果目标是：

1. Railway 上有一个 WordPress 后端服务
2. 可以后台管理整个 homepage
3. 安全
4. 健壮
5. 长期稳定

那么 `mindhikers-cms-core` 是必须存在的。

因为只有这样，才能把：

1. 全站内容模型
2. 后台编辑体验
3. 前端 API 协议
4. 缓存刷新逻辑

全部收口在你们自己可控的一层，而不是散落在：

1. 主题
2. page builder
3. 一堆第三方字段插件

## 16. 下一步建议

在你确认这份规格后，下一步最合理的顺序是：

1. 先用 Railway 创建 `mindhikers-db`
2. 再创建 `mindhikers-cms`
3. 再按本规格设计 `mindhikers-cms-core`
4. 然后让前端从博客 CMS 进一步升级为全站 CMS

这会比“先盲目建一个 WordPress，再临时想字段”稳得多。

🕐 Last updated: 2026-04-18 07:25
🌿 Branch: codex/cyd-stumpel-home-exploration
📌 Latest commit: `66bfd2d`
🚀 Push status: 已推送至 origin

## 交接入口

- 工作目录：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- Linear 主线：`MIN-8`（网站上线）→ `MIN-110`（CMS 内容模型）
- 执行方案：`docs/plans/2026-04-12-001-feat-m1-cms-content-model-plan.md`
- staging 地址：`https://wordpress-l1ta-staging.up.railway.app`

## 当前状态：M1 Unit 1-7 全部完成 ✅

### 本次会话完成

| Unit | 名称 | 状态 | 说明 |
|------|------|------|------|
| 1-5 | 修复完成 | ✅ | 布局修复、按钮文字、Blog 显示、Footer 清理、调试脚本删除 |
| 6 | 双语渲染验证 + EN 页面收口 | ✅ | `/en/` 完整英文渲染，中文首页不受影响 |
| 7 | M1 端到端验收 | ✅ | 首页/Blog/Contact 链路验证通过 |

### Unit 6 详细完成项

| 修复项 | 状态 | 说明 |
|--------|------|------|
| `/en/` 英文首页渲染 | ✅ | 五大区块全部正常显示英文内容 |
| Product 状态标签双语 | ✅ | `product.php` 根据 `$lang` 切换：构思中→Ideating |
| Header 按钮双语 | ✅ | `functions.php` 输出缓冲替换：开始联系→Get in Touch |
| Footer Widget 双语 | ✅ | Widget 数据库保持中文，`functions.php` 对 EN 页面替换为 Contact/Location |
| 站点标题双语 | ✅ | WP Settings 改为 MindHikers，`functions.php` 输出缓冲处理 meta 标签 |
| 中文首页不受影响 | ✅ | `/` 页面完整保留中文内容 |

### Unit 7 验收结果

| 检查项 | 结果 |
|--------|------|
| 中文首页 `/` 200 | ✅ |
| 英文首页 `/en/` 200 | ✅ |
| 中文首页五大区块 + 按钮 + Footer 全中文 | ✅ |
| 英文首页五大区块 + 按钮 + Footer 全英文 | ✅ |
| Blog 列表 `/blog/` 200 | ✅ |
| Blog 详情链路正常 | ✅ |
| Product 页面 `/product/golden-crucible/` 200 | ✅ |
| Contact 区块可达 | ✅ |
| 无 PHP Fatal / 500 | ✅ |

### 新增/变更文件

| 文件 | 位置 | 说明 |
|------|------|------|
| Child Theme style.css | `wordpress/themes/astra-child/style.css` | flex 覆盖修复 + 按钮颜色 `!important` |
| Blog 模板 | `wordpress/themes/astra-child/template-parts/blog.php` | 手动 Polylang 过滤 |
| Product 模板 | `wordpress/themes/astra-child/template-parts/product.php` | 状态标签双语支持 |
| Functions | `wordpress/themes/astra-child/functions.php` | EN 页面输出缓冲替换逻辑 |
| Railway 配置 | `railway.json` | 强制 Dockerfile 构建 |
| 运营指南 | `docs/operations-guide.md` | CMS 日常运营文档 |

### 已删除的临时文件

- `wordpress/mu-plugins/fix-blog-posts.php`

### 关键认证信息

- 用户名：`mindhikers_admin`
- 密码：`IW0pGAFhiydfFg3GC5xxgl+L`

### 下一窗口建议

#### 优先级 P0：M1 收尾提交
1. 创建 PR 将 `codex/cyd-stumpel-home-exploration` 合并到 `main`
2. 生产环境部署前验证

#### 优先级 P1：M2 规划
3. 根据 `docs/plans/2026-04-12-001-feat-m1-cms-content-model-plan.md` 规划 M2 内容

### 当前不要做的事

1. 不要回到旧的 Next.js 前台路线
2. 不要在生产环境直接试错
3. 不要提前取消 staging 的 `noindex`
4. 不要把 `/` 和 `/en` 的语言职责重新混在一起
5. 不要盲改 SureRank 字段名——必须用 `page-seo-checks/fix` 路径
6. 不要删除 `wordpress/mu-plugins/mindhikers-cms-core.php`（旧 headless 插件），只保留不启用即可

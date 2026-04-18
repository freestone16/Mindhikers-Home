# MIN-110 执行方案：CMS 内容灌装清单与主页正式域名发布

日期：2026-04-02  
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`  
分支：`codex/cyd-stumpel-home-exploration`  
关联 Issue：`MIN-110`

## 1. 目标与边界

本阶段只做两件事：

1. 用 CMS 接管 Homepage 内容运营（`zh` / `en`）
2. 为主页正式域名发布准备可执行的 smoke 与回滚方案

本阶段明确不做：

1. 不再重复 DNS / Cloudflare Access / 根路径跳转排障
2. 不把 CMS 运营接管和前台大规模代码改造混在一个发布窗口
3. 不在未验证回滚口径前进行高风险平台改动

## 2. 当前基线（已确认）

1. 管理域名：`https://homepage-manage.mindhikers.com`
2. 管理域名根路径：`/ -> /wp-admin/`（302）
3. Access 保护：`/wp-admin/*`、`/wp-login.php`
4. 公开接口：`/wp-json/mindhikers/v1/homepage/{locale}` 可访问
5. 前台静态兜底：`src/data/site-content.ts`
6. 前台 CMS 读取：`src/lib/cms/homepage.ts`（依赖 `WORDPRESS_API_URL`）

## 3. CMS 内容灌装清单（执行版）

### 3.1 数据源与落地原则

1. 初始内容基线：
   - `ops/wordpress/homepage-seeds/homepage-zh.json`
   - `ops/wordpress/homepage-seeds/homepage-en.json`
2. CMS 中每个 locale 仅保留一条主记录（建议 slug：`homepage-zh`、`homepage-en`）
3. 先“全量可用”，再“文案润色”；避免字段缺失导致前台回退静态兜底

### 3.2 最小可用字段（必须非空）

以下字段必须通过校验，否则 `getManagedHomeContent` 会回退到本地静态内容：

1. `locale`
2. `metadata.title`
3. `metadata.description`
4. `navigation.brand`
5. `navigation.links[]`
6. `navigation.switchLanguage.href`
7. `navigation.switchLanguage.label`
8. `hero.title`
9. `hero.description`
10. `hero.primaryAction.href`
11. `hero.primaryAction.label`
12. `hero.secondaryAction.href`
13. `hero.secondaryAction.label`

### 3.3 运营字段清单（建议逐项打勾）

1. `hero.highlights[]`
2. `hero.statusLabel` / `hero.statusValue`
3. `hero.availabilityLabel` / `hero.availabilityValue`
4. `hero.panelTitle`
5. `about.title` / `about.intro` / `about.paragraphs[]` / `about.notes[]`
6. `product.title` / `product.description` / `product.headline`
7. `product.featured.*`
8. `product.items[]`
9. `blog.title` / `blog.description` / `blog.headline`
10. `blog.cta.href` / `blog.cta.label`
11. `blog.emptyLabel` / `blog.readArticleLabel`
12. `contact.title` / `contact.description` / `contact.headline`
13. `contact.emailLabel` / `contact.email`
14. `contact.locationLabel` / `contact.location`
15. `contact.availabilityLabel` / `contact.availability`
16. `contact.links[]`
17. `productDetail.eyebrow` / `productDetail.title` / `productDetail.summary`
18. `productDetail.bullets[]`
19. `productDetail.stageLabel` / `productDetail.stageValue`
20. `productDetail.returnHome.*` / `productDetail.switchLanguage.*`

### 3.4 乱码清理清单（发布前必须完成）

1. 检查接口返回是否出现异常字符（如 `�`、错码标点、异常换行）
2. 优先在 CMS 后台修正文案源，不在前台做“显示层修补”
3. 每次保存后验证：

```bash
curl -s https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh
curl -s https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/en
```

4. 如发现错码，记录“字段路径 + 旧值 + 新值 + 操作人 + 时间”

## 4. 前台切 CMS 正式读取（窗口方案）

### 4.1 切换前置条件

1. `zh` / `en` 两套 payload 均完整可读
2. 上一节乱码清理项全部完成
3. 已确认 `REVALIDATE_SECRET` 与 WordPress 侧 `MINDHIKERS_REVALIDATE_SECRET` 对齐
4. 发布窗口内可执行 smoke 与回滚

### 4.2 切换动作

1. 前台环境变量设置：
   - `WORDPRESS_API_URL=https://homepage-manage.mindhikers.com`
2. 保持 `REVALIDATE_SECRET` 不变（仅做值对齐核验）
3. 如有缓存，触发一次 revalidate（header 传 secret）

### 4.3 切换后验收（最小）

1. `https://www.mindhikers.com/` 展示 CMS 最新 `zh` 内容
2. `https://www.mindhikers.com/en` 展示 CMS 最新 `en` 内容
3. `wp-json/mindhikers/v1/homepage/zh`、`/en` 均返回 200
4. 管理入口继续受 Access 保护

## 5. 主页正式域名发布方案

### 5.1 域名角色（本次固定口径）

1. 对外主页域名：`https://www.mindhikers.com`
2. CMS 管理域名：`https://homepage-manage.mindhikers.com`
3. 禁止把 CMS 管理域名直接作为对外首页域名

### 5.2 发布前检查

1. 确认跨项目域名占用无冲突（遵循 `docs/rules.md`）
2. 确认首页、CMS、产品域名边界不混用
3. 确认发布窗口内有人值守并具备回滚权限

### 5.3 发布日 Smoke 清单

```bash
# 公开页面
curl -I https://www.mindhikers.com/
curl -I https://www.mindhikers.com/en

# CMS 接口
curl -I https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/zh
curl -I https://homepage-manage.mindhikers.com/wp-json/mindhikers/v1/homepage/en

# 管理入口保护
curl -I https://homepage-manage.mindhikers.com/wp-admin/

# 可选：revalidate 探活（需替换 secret）
curl -X POST https://www.mindhikers.com/api/revalidate \
  -H "Content-Type: application/json" \
  -H "x-revalidate-secret: <REVALIDATE_SECRET>" \
  -d '{"path":"/"}'
```

成功标准：

1. 公开主页可访问且内容来自 CMS
2. 管理入口仍受 Access 限制
3. revalidate 返回 `revalidated: true`
4. 未影响其他子域服务

## 6. 回滚口径（分级）

### Level 1：内容回滚（不改域名）

1. CMS 直接回滚到上一版 payload（`zh` / `en`）
2. 执行 revalidate
3. 复验首页

### Level 2：读取链路回滚（快速止血）

1. 将前台 `WORDPRESS_API_URL` 回退到上一稳定值（或暂时清空以启用静态兜底）
2. 执行 revalidate
3. 复验 `/` 与 `/en`

### Level 3：发布窗口冻结

1. 暂停域名层后续动作
2. 保持 `homepage-manage` 管理链路与 Access 策略不变
3. 记录事故并进入下一窗口复盘

## 7. 立即执行顺序（给本窗口）

1. 在 CMS 完成 `zh` / `en` 全量内容灌装
2. 逐项做乱码清理与接口核验
3. 确认前台发布窗口并执行 `WORDPRESS_API_URL` 切换
4. 按 smoke 清单验收
5. 如异常按 Level 1 -> Level 2 -> Level 3 回滚


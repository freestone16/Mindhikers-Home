# MIN-110 执行方案：Astra 模版站重建 Homepage 与 CMS 接管计划

日期：2026-04-04  
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`  
分支：`codex/cyd-stumpel-home-exploration`  
关联 Issue：`MIN-110`

## 1. 决策结论

本轮确认放弃继续演进当前 `Next.js + Headless WordPress + Homepage JSON 自定义后台` 方案，改为：

1. 前台直接使用 WordPress 模版渲染
2. 后台直接使用 WordPress 原生 GUI 管理
3. 模版选择：`Astra - Interior Designer`
4. 首轮上线范围只保留 5 个首页区块：
   - `Hero`
   - `About`
   - `Product`
   - `Blog`
   - `Contact`

这条路线的目标不是“更炫”，而是明确围绕以下四个原则重建：

1. 安全
2. 稳定
3. 健壮
4. 简单易维护

## 2. 为什么要推倒重来

当前方案的主要问题已经明确：

1. 前台由 Next.js 渲染，后台由 WordPress 存内容，中间依赖自定义 JSON payload 和接口校验
2. Homepage 虽然已能被 CMS 数据接管，但后台编辑体验不是 GUI，而是大段 JSON 文本框
3. Blog 仍存在 `MDX / WordPress Posts` 双来源风险，内容口径不统一
4. WordPress 原生的 `文章 / 媒体 / 页面 / 菜单 / 外观` 没有形成对生产站真正可用的运营闭环
5. 后续任何小变更都容易演变成“代码 + 数据 + 发布链路”三处联动

因此当前方案不满足“简单、健壮、低维护成本”的目标，不应继续追加复杂度。

## 3. 新方案的目标边界

### 3.1 本次必须做到

1. 使用 `Astra - Interior Designer` 重建首页
2. 使用 WordPress 原生 GUI 完成首页与博客的内容运营
3. 首轮首页只保留 5 个区块
4. 生产切换时保留回滚抓手，不直接覆盖现有生产站
5. 把测试协议补齐，让黄金测试后续可以接手 UI / smoke / 回归验证

### 3.2 本次明确不做

1. 不继续补当前 `Mindhikers Homepages` 自定义 JSON GUI
2. 不继续投资当前 `Next.js homepage` 作为正式生产首页
3. 不做高复杂度 page builder 二次定制
4. 不同时追求“首轮上线”和“极致品牌定制视觉”
5. 不在生产环境直接导入模版和试错

## 4. 当前可复用资产

### 4.1 内容资产

1. 当前首页中英内容基线：
   - `ops/wordpress/homepage-seeds/homepage-zh.json`
   - `ops/wordpress/homepage-seeds/homepage-en.json`
2. 现有 Next.js 首页结构可作为文案与模块拆分参考
3. 当前博客 MDX 内容目录可作为 WordPress Posts 迁移底稿：
   - `content/`

### 4.2 平台资产

1. 正式域名已经收口：
   - `https://mindhikers.com`
   - `https://www.mindhikers.com` -> 301 到根域
2. CMS 管理域名已可用：
   - `https://homepage-manage.mindhikers.com`
3. Railway 生产资源已收敛：
   - 保留服务：`Mindhikers-Homepage`、`WordPress-L1ta`、`MariaDB-94P8`
   - 保留 volumes：`mariadb-volume-x1on`、`wordpress-volume-vRzA`

### 4.3 测试资产

1. 当前仓库没有正式 `testing/` 协议目录
2. 但已有一次黄金测试 fallback 产物，可作为补协议起点：
   - `docs/testing_reports/2026-04-01_homepage_golden_test_report.md`
   - `docs/testing_reports/requests/2026-04-01_homepage_cms_linkage_request.md`
   - `docs/testing_reports/status/2026-04-01_homepage_cms_linkage_status.json`
   - `docs/testing_artifacts/`

## 5. 总体实施策略

采用“三环境、双轨验证、一次切换”的保守策略：

1. 生产旧站继续保留，不立即下线
2. 先在新的 WordPress staging 环境导入 Astra 模版并完成内容迁移
3. 通过黄金测试和人工验收后，再进入正式切换
4. 切换完成后保留旧站作为短期回滚抓手

## 6. 分阶段执行清单

## Phase 0：冻结旧方案与建账

### 目标

把旧方案冻结为“可回看、可回滚、但不再扩建”的状态。

### 任务

1. 冻结当前 `Next.js homepage` 方案，不再新增 homepage CMS 功能
2. 记录当前生产拓扑、域名拓扑、服务清单和回滚抓手
3. 导出当前首页文案、导航、产品、联系信息为迁移底稿
4. 导出当前博客文章列表，区分：
   - MDX 文章
   - WordPress Posts
5. 记录当前 SEO 关键信息：
   - title
   - description
   - canonical
   - OG
   - sitemap / robots

### 验收标准

1. 所有人对“旧站是回滚抓手，不是未来方向”没有歧义
2. 有一份完整迁移资产清单可供后续使用

## Phase 1：搭建新的 WordPress Staging 环境

### 目标

建立一套干净、安全、可试错的 WordPress 模版站，不污染生产。

### 任务

1. 新建 staging 环境
2. 为 staging 分配独立域名，建议：
   - `homepage-staging.mindhikers.com`
3. 安装并启用：
   - Astra Theme
   - Astra Starter Templates
4. 导入 `Astra - Interior Designer`
5. 配置最小访问控制：
   - staging 可公开预览或受限访问，二选一
6. 验证后台账号、权限、媒体库、文章、页面都可正常使用

### 验收标准

1. staging 不影响当前 `homepage-manage` 生产后台
2. 模版可成功导入
3. 桌面与手机竖屏初步可访问

## Phase 2：重建首页信息架构

### 目标

把模板首页改造成 Mindhikers 首页，不追求大改视觉，只追求结构稳、后台能管。

### 任务

1. 首页只保留 5 个块：
   - Hero
   - About
   - Product
   - Blog
   - Contact
2. 删除无关 demo 区块和示例页面
3. 统一导航：
   - `About`
   - `Product`
   - `Blog`
   - `Contact`
4. 确定 Product 的首轮形态：
   - 首页一个主推产品区块
   - 一个产品详情页入口
5. 确定 Contact 的首轮形态：
   - 邮箱
   - 必要外链
   - 视需要加简单表单

### 验收标准

1. 后台编辑者可以在 GUI 中独立维护首页五大区块
2. 不需要再碰 JSON 文本框

## Phase 3：内容迁移与 Blog 统一

### 目标

把实际内容迁到新站，同时统一 Blog 来源，只保留一套运营入口。

### 任务

1. 将当前首页核心文案迁入新模版首页
2. 决定首轮语言策略：
   - 方案 A：先只上中文
   - 方案 B：中文 + 英文一起上
3. 建立 Blog 正式运营入口：
   - 统一使用 WordPress `文章`
4. 将当前需要保留的 MDX 文章迁入 WordPress Posts
5. 为文章补齐：
   - 标题
   - 摘要
   - 封面
   - 分类
   - 发布时间
6. 配置首页 Blog 区为：
   - 最新 3 篇，或
   - 精选 3 篇

### 验收标准

1. Blog 不再是双轨来源
2. 内容团队以后只需要在 WordPress 原生后台维护博客

## Phase 4：安全、稳定、健壮性加固

### 目标

让新方案具备可上线的基础质量，而不是“看起来能跑”。

### 任务

1. 后台安全：
   - 保持 Cloudflare Access 或其他入口保护策略
   - 检查管理员权限、密码、邮件找回流程
2. 插件治理：
   - 只保留最少必要插件
   - 建立插件白名单
3. 备份与回滚：
   - 数据库备份
   - 文件 / 媒体备份
   - 切换前快照
4. 媒体治理：
   - 图片尺寸规范
   - 压缩规范
   - 封面图规范
5. SEO 基线：
   - 首页 title / description
   - Blog SEO 基线
   - canonical / OG / sitemap / robots
6. 性能基线：
   - 首页首屏
   - 图片加载
   - 手机竖屏导航和 CTA

### 验收标准

1. 新站不是“只在桌面好看”
2. 后台账号、内容、媒体、SEO、备份都有基本保障

## Phase 5：测试协议补齐与黄金测试接管

### 目标

让黄金测试后续可以稳定接管该仓库的关键验证，而不是继续靠临时人工脚本。

### 任务

1. 在仓库中补齐最小测试协议目录：
   - `testing/README.md`
   - `testing/OPENCODE_INIT.md`
   - 至少一个模块级 `testing/<module>/README.md`
2. 增加首页 / Blog / 联系方式的 request 模板
3. 指定执行模型固定为：
   - `zhipuai-coding-plan/glm-5`
4. 明确浏览器执行默认使用：
   - `agent-browser`
5. 定义输出位置：
   - `docs/testing_reports/`
   - `docs/testing_artifacts/`
   - `docs/testing_reports/status/`

### 建议外包给黄金测试的环节

1. staging 首页 GUI 操作后的页面回归
2. `Hero / About / Product / Blog / Contact` 五区块可见性验证
3. Blog 列表与文章详情页点击链路验证
4. 手机竖屏视觉与交互抽检
5. 切换前 smoke
6. 切换后 smoke

### 不建议外包给黄金测试的环节

1. 模版挑选
2. 实际内容写作
3. 账号登录、验证码、人机验证
4. 复杂的视觉判断与品牌审美拍板

### 验收标准

1. 黄金测试对这仓不再是一次性 fallback
2. 后续任何回归都可以复用 request 和报告路径

## Phase 6：生产切换与回滚

### 目标

把新 WordPress 模版站安全切到 `mindhikers.com`，同时保留可回退能力。

### 切换前检查

1. 新站 staging 已完成全部人工验收
2. 黄金测试 smoke 通过
3. 回滚路径明确
4. 旧站仍保持可恢复

### 切换动作

1. 将 `mindhikers.com` 切到新的 WordPress 前台
2. 保持 `www -> apex` 跳转规则不变
3. 检查 `homepage-manage` 管理域名是否仍符合最终后台定位

### 回滚策略

1. 如切换后出现严重问题，优先回退域名解析或前台服务入口
2. 保留旧站短期存活，不立即销毁
3. 切换后一段时间内不立刻删除旧前台资源

### 验收标准

1. `mindhikers.com` 可访问
2. 后台可登录、可编辑、可更新
3. Blog 正常
4. 手机竖屏正常
5. 基础 SEO 正常

## 7. 你可以人工配合的低 token / 低技术环节

这些环节通常很简单，但如果全靠我通过网页自动化或多轮解释执行，会大量消耗 token，且收益不高。建议由你人工完成，我负责给精确指令和最后核对。

### 建议你来做

1. 模版导入时的点击确认
2. 购买 / 启用 Premium 模版或插件（如有）
3. 登录后台、过验证码、过 Cloudflare Access
4. 在 WordPress 页面编辑器里做纯内容替换
5. 选择最终图片、封面、品牌图
6. 做最后一轮“好不好看”的主观拍板
7. 切换前后的人眼验收：
   - 首页是不是像样
   - 手机上是不是顺手
   - 文案有没有违和

### 我负责

1. 方案与执行顺序
2. 结构搭建建议
3. 数据迁移脚本或迁移清单
4. 配置核对
5. 风险识别与回滚策略
6. 测试协议补齐
7. 验收标准定义

## 8. 安全 / 稳定 / 健壮 / 易维护原则落实清单

### 安全

1. 后台入口必须受保护
2. 不新增不必要插件
3. 不在生产站直接做模版试错
4. 任何切换前先备份

### 稳定

1. staging 先走通，再切 production
2. 只保留一套 Blog 正式来源
3. 首页模块数控制在 5 个
4. 首轮不做复杂互动效果

### 健壮

1. 旧站保留短期回滚抓手
2. 所有关键路径要有 smoke
3. 后台编辑必须是 GUI，而不是继续靠 JSON
4. 手机端必须作为首轮验收项

### 易维护

1. 尽量使用 WordPress 原生能力：
   - 页面
   - 文章
   - 媒体
   - 菜单
2. 少定制、少插件、少二次开发
3. 测试协议标准化，后续交给黄金测试复用

## 9. 预计工作量

### 最小可用版

1. staging 环境
2. Astra 模版导入
3. 首页五区块重建
4. Blog 统一到 WordPress
5. 基础安全与 smoke

粗估：`3 - 5 个工作日`

### 稳妥版

在最小可用版基础上，再补：

1. 双语策略
2. SEO 收口
3. 测试协议补齐
4. 黄金测试回归
5. 切换演练

粗估：`5 - 7 个工作日`

## 10. 本轮之后的执行顺序建议

1. 先确认本计划
2. 决定首轮语言策略：
   - 只上中文
   - 还是中英一起
3. 建 staging
4. 导入 `Astra - Interior Designer`
5. 做五区块首页
6. 统一 Blog
7. 补测试协议
8. 让黄金测试接手回归
9. 切生产

## 11. 本轮产出定义

本轮只做计划，不做实际切换。

本轮完成标准是：

1. 模版路线已经定板
2. 执行阶段和边界已经写清楚
3. 你与我、黄金测试之间的协作边界已经划分清楚
4. 后续可以直接按阶段推进，而不是再回头争论路线

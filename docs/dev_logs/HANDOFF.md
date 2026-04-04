🕐 Last updated: 2026-04-04 12:48
🌿 Branch: codex/cyd-stumpel-home-exploration

## 交接入口

- 工作目录：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- Linear 主线：`MIN-110`
- 今日日志：`docs/dev_logs/2026-04-04.md`
- 当前执行方案：`docs/plans/2026-04-04_MIN-110_WordPress_Template_Rebuild_Execution_Plan.md`

## 当前状态（最新路线）

1. 正式域名状态保持不变：
   - `https://mindhikers.com/` 已上线
   - `https://www.mindhikers.com/` 已 301 到根域
   - `https://homepage-manage.mindhikers.com/` 仍为当前管理域名
2. 当前 Railway 生产资源仍保持收敛状态：
   - 服务：`Mindhikers-Homepage`、`WordPress-L1ta`、`MariaDB-94P8`
   - volumes：`mariadb-volume-x1on`、`wordpress-volume-vRzA`
3. 已确认放弃继续演进当前 `Next.js + Headless WordPress + Homepage JSON 自定义后台` 方案
4. 已确认新主线：
   - 前台直接用 WordPress 模版渲染
   - 后台直接用 WordPress 原生 GUI 管理
   - 模版选择：`Astra - Interior Designer`
5. 首轮首页范围固定为 5 个模块：
   - `Hero`
   - `About`
   - `Product`
   - `Blog`
   - `Contact`

## 当前 WIP

1. 已完成详细重建计划文档
2. 等待按新计划进入 staging 搭建与模版导入阶段
3. 准备补齐本仓的黄金测试协议目录，让后续回归测试可外包

## 待解决问题

1. 首轮语言策略尚未最终锁定：
   - 先中文
   - 或中英一起
2. 新的 WordPress staging 环境尚未建立
3. 当前仓库没有正式 `testing/` 协议目录，黄金测试还不能直接标准化接手
4. 旧 Next.js 前台未来何时下线，尚未进入执行窗口

## 下一窗口直接做

1. 先读本文件，再读：
   - `docs/plans/2026-04-04_MIN-110_WordPress_Template_Rebuild_Execution_Plan.md`
2. 不要再回头补当前 Homepage JSON CMS
3. 直接进入以下顺序：
   - 建 WordPress staging
   - 导入 `Astra - Interior Designer`
   - 用 GUI 重建五区块首页
   - 统一 Blog 到 WordPress Posts
   - 补齐测试协议并接黄金测试

## 当前不要做的事

1. 不要继续为当前 `Mindhikers Homepages` JSON 后台追加 GUI 功能
2. 不要再把当前 Next.js 首页当作正式长期路线
3. 不要在生产环境直接导入 WordPress 模版试错
4. 不要在 Blog 方案上继续保留 `MDX + WordPress` 双来源作为长期状态

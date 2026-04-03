🕐 Last updated: 2026-04-03 17:27
🌿 Branch: codex/cyd-stumpel-home-exploration

## 交接入口

- 工作目录：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- Linear 主线：`MIN-110`
- 今日日志：`docs/dev_logs/2026-04-03.md`
- 执行方案：`docs/plans/2026-04-02_MIN-110_CMS_Operate_And_Homepage_Domain_Release_Plan.md`

## 当前状态（最终口径）

1. Homepage 正式域名已上线：
   - `https://mindhikers.com/` 为当前正式首页入口
   - `https://www.mindhikers.com/` 已通过 Cloudflare Redirect Rule 永久跳转到根域
2. CMS 管理链路保持可用：
   - `https://homepage-manage.mindhikers.com/` 继续作为管理域名
   - 当前 CMS 生产服务仍是 `WordPress-L1ta` + `MariaDB-94P8`
3. CMS 内容基线已完成：
   - `Homepage zh/en` 已按本地 seed 回灌
   - 已确认乱码修复保持生效
4. Railway 生产资源已收敛完成：
   - 保留服务仅 3 个：`Mindhikers-Homepage`、`WordPress-L1ta`、`MariaDB-94P8`
   - 保留 volume 仅 2 个：`mariadb-volume-x1on`、`wordpress-volume-vRzA`
   - 旧服务与孤儿 volume 已清理完毕

## 当前 WIP

1. 将“域名已正式上线 + 生产资源已收敛”的结果写入日志并提交到仓库
2. 后续窗口若继续发布，应围绕：
   - 前台 smoke / revalidate
   - canonical / OG 域名口径复核
   - `WORDPRESS_API_URL` 生产值守与回滚演练

## 待解决问题

1. 当前会话尚未执行 git 提交与推远；提交前需要按老杨流程再次确认
2. 公开站的 canonical / OG / sitemap 等是否已完全统一到 `mindhikers.com`，仍建议单开一个核验窗口
3. 若后续继续做 CMS 运营接管，需要补充正式 smoke 结果与值守记录

## 下一窗口直接做

1. 先读本文件，不要再重复排查“域名是否已上线”
2. 以当前事实为准继续工作：
   - 正式首页域名：`mindhikers.com`
   - `www` 只做跳转，不再作为主口径
   - CMS 管理域名：`homepage-manage.mindhikers.com`
3. 若要继续发布验证，优先做：
   - `/`、`/en`、`/blog` 公开页 smoke
   - `homepage-manage` 的 `wp-json` 对照
   - revalidate 验证
4. 不要恢复或重建已删除的旧 Railway 服务 / volumes

## 当前不要做的事

1. 不要把已下线的 `Primary`、`mindhikers-cms-v2`、旧 `WordPress` / `MySQL` / `MariaDB` 重新接回生产
2. 不要再把 `www.mindhikers.com` 当作主站正式口径
3. 未做新一轮 smoke 前，不要声称 CMS 读取链路已完成最终验收

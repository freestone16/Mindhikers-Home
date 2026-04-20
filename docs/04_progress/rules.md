# 技术规则 / 经验积累（rules.md）

> 本文件是**技术踩坑经验**的 SSOT，按时间倒序追加条目。
> 每条规则简短一句话 + 展开案例链接。详细案例放在 `docs/dev_logs/YYYY-MM-DD.md`。
> 按老杨协议 §3.3 控制：50~80 条，只保留"下次一定有用的规则"。

---

## WordPress / 插件加载

### WP-001 · 插件静默失效排查顺序 `2026-04-20`

遇到"插件已启用但功能没生效"时，按序诊断，**别一上来怀疑 PHP fatal**：

1. 访问 `/wp-json/` 搜目标命名空间（如 `mindhikers/v1`）
   - 有 → 路由注册成功，往下查路径拼写
   - 无 → `add_action` 没挂上或 callback 被跳过
2. 查**函数名冲突**（同一进程里函数名是全局单例，旧插件先声明 → 新插件 `add_action` 会调用到旧定义）
3. 查 PHP 版本（8.0 语法在 7.4 下 fatal，但 fatal 会让 WP 自动停用插件 → 状态栏会标红）
4. 查硬冲突（`function_exists` guard、同名 `register_rest_route` 覆盖）

展开案例：[dev_logs/2026-04-20.md](../dev_logs/2026-04-20.md) Lesson 1

### WP-002 · 防冲突不要靠 `function_exists` 闸门 `2026-04-20`

写 guard 时，前提是你**清楚预期冲突的函数**是什么。预设"旧版不含 X → 旧版没 X 的符号"不成立。

更稳做法：**给自己所有符号加独特前缀**（`mh_m1rest_*`），靠命名空间隔离，不靠对方检查。

展开案例：[dev_logs/2026-04-20.md](../dev_logs/2026-04-20.md) Lesson 1

### WP-003 · Carbon Fields 容器要通过 hook 注册 `2026-04-20`

Carbon Fields 的 `Container::make()` 必须在 `carbon_fields_register_fields` action 回调里调用，**不能在文件顶层直接调用**（类未加载）。插件主文件顶部加 `use Carbon_Fields\Container\Container; use Carbon_Fields\Field\Field;`。

### WP-004 · 插件通过 WP Admin ZIP 上传是容器封闭环境下的应急通道 `2026-04-20`

当 Railway CLI ssh、git clone、GitHub raw、Web Shell 全部不可用时，**WordPress 后台「上传插件」是唯一绕开容器封闭性的官方通道**，对 `wp-content/plugins/` 有写权限。

限制：手工上传的插件在容器重建时会丢失，必须同时规划 Dockerfile / 仓库固化路径。

---

## 安全 / 凭证

### SEC-001 · 凭证出现在会话即视为已泄漏 `2026-04-20`

任何 secret / token / API key 只要在聊天、commit、PR 评论里出现过一次，就视为泄漏。处置流程：

1. 验证结束后**立即在密钥源（Railway / 1Password / CI）轮换**
2. 新值同步到所有消费方
3. 不在任何持久介质留原值

展开案例：[dev_logs/2026-04-20.md](../dev_logs/2026-04-20.md) Lesson 2

---

## Next.js 16

### NXT-001 · `revalidateTag` 需要两个参数 `2026-04-19`

Next.js 16 的 `revalidateTag(tag, profile)` 必须传 `profile` 第二参数（`string | CacheLifeConfig`）。缺省会 TS 报错。常用值：`"default"`。

---

## 分支 / 提交纪律

### GIT-001 · 治理文档与代码变更分开 commit `2026-04-20`

老杨红线：
- 治理修复单元（代码 + rules/dev_log/HANDOFF 对应文档）→ 一个 commit（保 revert 一致性）
- 功能代码 vs 纯过程治理文档（AGENTS、plans、reviews）→ 分开 commit
- 其他类别单独请示老卢

---

_(追加新条目请保持 ID 格式：`{分类}-{三位序号} · 标题 \`日期\``)_

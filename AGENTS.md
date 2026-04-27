# Mindhikers-Homepage Agent Rules

## 1. Scope

当前目录是 `Mindhikers` 官网项目：

- 路径：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`
- 项目性质：独立官网 / 内容与品牌展示站

这里是当前项目自己的入口文件。
除非用户明确要求，不要把其他项目的规则、计划文档或运行口径带进当前仓。

## 2. OldYang First

凡是工程与治理任务，默认先经 `OldYang`。

当前仓入口只负责：

- 说明 homepage 自己的读取顺序
- 指向当前 handoff、规则、计划、测试与交付入口
- 保留 homepage 自己的产品、环境和发布边界

## 3. Branch Discipline

### 分支命名与用途

1. `main`
   - 唯一生产分支，只接受经过 staging 验证的合并
   - 禁止直接在 main 上开发或推送代码
   
2. `staging`
   - 唯一预发/验收分支，与云端 staging 环境一一对应
   - 所有进入 staging 的代码必须先通过本地验证
   - 曾经的 `experiment/*`、`feature/*` 等临时分支在验证通过后应合并到 staging 并删除
   
3. `experiment/*` / `feature/*` / `fix/*`
   - 本地开发分支，命名格式：`{type}/ brief-description`
   - 完成后必须合并到 staging，不得长期存在

### 分支生命周期

1. 开发新功能：从 `staging` checkout 临时分支
2. 本地开发与验证
3. 合并到 `staging`，push 触发 staging 环境自动部署
4. staging 验收通过后，由 staging 合并到 `main`
5. 删除临时分支

### 红线

1. 禁止在 `main` 分支直接开发
2. 禁止长期保留已合并的临时分支
3. `staging` 分支必须与云端 staging 环境保持同步

---

## 4. Read Order

进入当前仓后，默认按下面顺序读取：

1. 当前文件 `AGENTS.md`
2. `README.md`
3. `docs/dev_logs/HANDOFF.md`
4. `docs/rules.md`
5. 如任务涉及域名、环境边界或发布路径，再读：
   - `docs/domain-boundary.md`
   - `docs/lessons.md`
6. 如任务涉及实施方案、变更方案或故障排查，再读 `docs/plans/` 下与当前任务最相关的文档
7. 如任务涉及测试，再读：
   - `testing/README.md`
   - `testing/OPENCODE_INIT.md`
   - `testing/homepage/README.md`

当前层足够时停止下钻，不为“更完整”而盲目扩读。

## 5. Local Red Lines

1. 当前主线是 WordPress 模版站重建，不要回到旧的 Homepage JSON CMS 路线继续扩建。
2. staging 与 production 不一致时，必须在报告与 handoff 里明确写出环境差异。
3. 页面查看、UI 验证、截图、交互检查默认优先 `agent-browser`。
4. 当前仓规则文件是 `docs/rules.md`，不要假设它和别的项目使用同一套目录结构。
5. 当前项目是独立官网项目；不要把无关项目的分支纪律、设计索引、测试协议或治理阶段直接套进来。
6. `README.md` 仍保留模板仓说明，当前实施与治理以仓内 `AGENTS.md`、`docs/`、`testing/` 为准。

## 6. Documentation

当前仓的文档口径如下：

1. 当前状态与交接看 `docs/dev_logs/HANDOFF.md`
2. 稳定规则与平台约束看 `docs/rules.md`
3. 域名与环境边界看 `docs/domain-boundary.md`
4. 经验沉淀看 `docs/lessons.md`
5. 需求、变更、实施方案优先看 `docs/plans/*.md`
6. 当前仓尚未建立 `docs/02_design/`；如果后续要建设正式设计索引，应在当前仓内单独建立

## 7. Testing And Browser

1. 用户说“协调opencode测试”时，默认读取：
   - `testing/README.md`
   - `testing/OPENCODE_INIT.md`
   - `testing/homepage/README.md`
2. 完成 ready 后等待用户明确说明测什么，不自动发起 request。
3. 默认优先验证：
   - 首页五区块
   - Blog 列表与详情链路
   - Contact 区块可达性
   - 手机竖屏可读性与主要 CTA

## 8. References

1. 项目说明：`README.md`
2. 当前交接：`docs/dev_logs/HANDOFF.md`
3. 当前规则：`docs/rules.md`
4. 域名边界：`docs/domain-boundary.md`
5. 经验沉淀：`docs/lessons.md`
6. 当前计划目录：`docs/plans/`
7. 测试总协议：`testing/README.md`
8. 测试初始化：`testing/OPENCODE_INIT.md`
9. 模块测试入口：`testing/homepage/README.md`

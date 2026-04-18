# Global And Project Skills Inventory Report

更新时间：2026-04-12  
调研范围：`/Users/luzhoua/.codex/skills`、`/Users/luzhoua/.claude/skills`、`/Users/luzhoua/.agents/skills`，以及主要项目内的 `.claude/skills`

## 1. 概览结论

这台机器当前的 skill 体系，不是“Claude Code 全局 skill 为主，Codex 通过软链复用”。

实际结构更接近：

1. `Codex` 全局 skill 库是主仓
2. `Claude` 全局 skill 库是一个较小的兼容层 / 混合层
3. 主要项目里还存在一批项目私有 skill，和全局 skill 并行存在
4. 另有极少量公共 skill 放在 `~/.agents/skills`

一句话总结：

> 当前是“三层并存”结构：`~/.codex/skills` 为主库，`~/.claude/skills` 为兼容层，项目内 `.claude/skills` 为私有扩展层。

---

## 2. 统计口径

本报告按下面规则统计：

1. 只统计 active 目录，不把 `.DS_Store` 算作 skill
2. “目录数”指顶层 skill 入口目录数
3. 软链单独统计
4. 仅把本次扫描到的主要项目纳入“项目级 skill”范围
5. `~/.claude/skills-archive-BK` 视为归档，不计入 active skill 总量

---

## 3. 全局 Skill Inventory

### 3.1 全局总表

| 位置 | active 顶层目录数 | 其中软链数 | 说明 |
|---|---:|---:|---|
| `~/.codex/skills` | 98 | 0 | 主 skill 仓 |
| `~/.claude/skills` | 8 | 2 | Claude 全局兼容层 |
| `~/.agents/skills` | 1 | 0 | 公共共享 skill 源 |
| `~/.claude/skills-archive-BK` | 63 | 0 | 归档，不是 active |

补充说明：

1. `~/.codex/skills` 的 98 个目录中，`97` 个是常规 skill，另有 `1` 个 `.system` 目录
2. `.system` 下还有 `5` 个系统 skill：
   - `imagegen`
   - `openai-docs`
   - `plugin-creator`
   - `skill-creator`
   - `skill-installer`

### 3.2 `~/.codex/skills`

结论：

1. 这是当前机器上最大、最完整的 skill 主库
2. 目录本身不是软链
3. `OldYang` 明确在 skill 文案里声明自己是 SSOT，其他端通过 wrapper 指向这里

代表性证据：

1. `OldYang` 位于 `~/.codex/skills/OldYang/SKILL.md`
2. `golden-testing` 位于 `~/.codex/skills/golden-testing`
3. 大量工程类、review 类、workflow 类 skill 只存在于这里

### 3.3 `~/.claude/skills`

active 入口分成两类：

#### A. 真实目录（8 个）

1. `OldYang`
2. `SkillCreator`
3. `Skills Auditor`
4. `brand-guidelines`
5. `data-analysis`
6. `frontend-design`
7. `mcp-builder`
8. `newtype`

#### B. 顶层软链（2 个）

1. `agent-browser` -> `../../.agents/skills/agent-browser`
2. `golden-testing` -> `/Users/luzhoua/.codex/skills/golden-testing`

其中值得注意的关系：

1. `OldYang` 目录本身在 `.claude` 里，但里面的 `SKILL.md` 是软链到 `~/.codex/skills/OldYang/SKILL.md`
2. `golden-testing` 是整目录软链到 `~/.codex/skills/golden-testing`
3. `agent-browser` 是整目录软链到 `~/.agents/skills/agent-browser`

### 3.4 `~/.agents/skills`

目前 active 只有 `1` 个目录：

1. `agent-browser`

这说明 `agent-browser` 更像一个跨端共享 skill 源，再由别的端侧目录挂过来。

---

## 4. 全局两边的来源关系

### 4.1 明确从 `.claude` 指向 `.codex` 的项

1. `OldYang`
2. `golden-testing`

其中：

1. `OldYang` 是“目录在 `.claude`，核心 `SKILL.md` 指到 `.codex`”
2. `golden-testing` 是“整个目录都指到 `.codex`”

### 4.2 明确从 `.claude` 指向 `.agents` 的项

1. `agent-browser`

### 4.3 重名但不是同一份的项

1. `frontend-design`
2. `newtype`

详细判断：

1. `frontend-design`
   - `.claude` 与 `.codex` 都有
   - `SKILL.md` 哈希不同
   - 说明是两份独立维护版本，不是软链复用
2. `newtype`
   - `.claude` 与 `.codex` 都有
   - 内容哈希相同
   - 但不是软链，更像“复制同步”而不是“单源复用”

### 4.4 名称交集

按顶层目录名看，当前 `.claude` 和 `.codex` 的 active 交集主要是：

1. `OldYang`
2. `frontend-design`
3. `newtype`

按实际来源看，还应补上一个顶层软链：

4. `golden-testing`

---

## 5. 主要项目 Skill Inventory

本次扫描到的项目级 `.claude/skills` 如下：

1. `/Users/luzhoua/MHSDC/.claude/skills`
2. `/Users/luzhoua/Mindhikers/.claude/skills`
3. `/Users/luzhoua/Mindhikers/Mindhikers_workspace/MHS-demo/.claude/skills`
4. `/Users/luzhoua/Blaw_notes/.claude/skills`
5. `/Users/luzhoua/Mylife_lawrence/.claude/skills`

其中后两者目前为空目录。

### 5.1 项目级总表

| 项目路径 | 顶层 skill 目录数 | 真实 `SKILL.md` 数 | 软链 `SKILL.md` 数 | 备注 |
|---|---:|---:|---:|---|
| `MHSDC/.claude/skills` | 15 | 9 | 6 | 混合型，含 `gstack` 别名层 |
| `Mindhikers/.claude/skills` | 18 | 18 | 0 | 内容型私有 skill 仓 |
| `Mindhikers_workspace/MHS-demo/.claude/skills` | 1 | 1 | 0 | 单项目特化 skill |
| `Blaw_notes/.claude/skills` | 0 | 0 | 0 | 空 |
| `Mylife_lawrence/.claude/skills` | 0 | 0 | 0 | 空 |

---

## 6. 各主要项目详情

### 6.1 `MHSDC/.claude/skills`

顶层共有 15 个目录：

1. `ce-brainstorm`
2. `ce-compound`
3. `ce-compound-refresh`
4. `ce-ideate`
5. `ce-plan`
6. `ce-review`
7. `ce-work`
8. `ce-work-beta`
9. `gstack`
10. `gstack-plan-ceo-review`
11. `gstack-plan-eng-review`
12. `gstack-qa`
13. `gstack-qa-only`
14. `gstack-review`
15. `gstack-upgrade`

结构判断：

1. `ce-*` 这 8 个 skill 都是**真实文件目录**
2. 它们与 `~/.codex/skills` 对应 skill 的 `SKILL.md` 哈希一致
3. 但它们不是软链，而是**复制副本**

说明：

> `MHSDC` 在 `ce-*` 这批 skill 上，采用的是“项目内复制一份全局 skill”的策略，而不是直接挂软链。

`gstack` 部分则是另一种结构：

1. `gstack` 本体是一个大型真实目录，像一个完整 skill 包 / 子仓
2. `gstack-plan-ceo-review`、`gstack-plan-eng-review`、`gstack-qa`、`gstack-qa-only`、`gstack-review`、`gstack-upgrade` 这 6 个目录，内部 `SKILL.md` 都是软链
3. 这 6 个软链当前都指向 `/Users/luzhoua/.claude/skills/gstack/...`
4. 但本机当前**不存在** `/Users/luzhoua/.claude/skills/gstack`
5. 结果是：这 6 个别名 skill 现在都是**断链**

这意味着：

> `MHSDC` 里 `gstack` 本体是存在的，但这 6 个快捷别名 skill 当前不可直接用，至少按文件系统状态看是断的。

### 6.2 `Mindhikers/.claude/skills`

顶层共有 18 个目录：

1. `Analyst`
2. `DialogueWeaver`
3. `Director`
4. `Editor`
5. `FactChecker`
6. `Humanizer_zh`
7. `LiteraryCritic`
8. `MarketingMaster`
9. `MusicDirector`
10. `OldZhang`
11. `OperationDirector`
12. `Researcher`
13. `ShortsMaster`
14. `Socrates`
15. `Style DNA Extractor`
16. `UniversalDownloader`
17. `Writer`
18. `WritingMaster`

结构判断：

1. 这 18 个都是项目私有真实目录
2. 没有软链
3. 这是一个内容与增长体系专用的项目 skill 仓

和全局的关系：

1. 与 `~/.codex/skills` 的显著重名项主要是 `OldZhang`
2. 但 `Mindhikers/.claude/skills/OldZhang/SKILL.md` 与 `~/.codex/skills/OldZhang/SKILL.md` 哈希不同
3. 说明 `Mindhikers` 使用的是更厚、更项目化的一版 `OldZhang`

### 6.3 `Mindhikers_workspace/MHS-demo/.claude/skills`

当前只有 1 个 skill：

1. `remotion-best-practices`

它是一个项目特化 skill，不是软链。

### 6.4 `Blaw_notes/.claude/skills`

当前为空目录。

### 6.5 `Mylife_lawrence/.claude/skills`

当前为空目录。

---

## 7. 关键发现

### 7.1 真正的主仓是 `.codex`

这是本次最明确的结论。

证据链：

1. `.codex` 体量远大于 `.claude`
2. `OldYang` 自身声明 SSOT 在 `.codex`
3. `golden-testing` 由 `.claude` 直接软链回 `.codex`
4. `MHSDC` 的 `ce-*` 也是从 `.codex` 复制出去的同内容副本

### 7.2 `.claude` 不是单一策略，而是混合层

`.claude` 当前同时包含：

1. 软链到 `.codex` 的 skill
2. 软链到 `.agents` 的 skill
3. 自己独立维护的本地 skill
4. 与 `.codex` 重名但不同版本的 skill
5. 归档历史 skill

也就是说：

> `.claude` 不是一个“纯主仓”，也不是一个“纯软链镜像”，它是兼容层 + 历史层 + 一部分自维护 skill 的混合体。

### 7.3 项目级 skill 主要分成两类

第一类：复制全局工程 skill 到项目里  
代表：`MHSDC` 的 `ce-*`

第二类：项目私有能力 skill 仓  
代表：`Mindhikers` 的内容生产与增长 skill 集

### 7.4 当前最明显的治理风险

风险 1：`MHSDC` 存在 6 个断链 gstack 别名 skill

风险 2：全局存在“重名但非同源”的 skill

代表：

1. `frontend-design`
2. `OldZhang`

这会带来两个问题：

1. 使用者以为同名 skill 是同一份
2. 实际上不同端可能命中不同实现，行为口径漂移

风险 3：`newtype` 这类“内容相同但不是软链”的 skill，容易后续静默漂移

---

## 8. 治理建议

### 建议 1：明确三层来源口径

建议以后统一用下面三类标签描述 skill：

1. `global-codex`
2. `global-claude-local`
3. `shared-agents`
4. `project-private`
5. `archived`

### 建议 2：给重名 skill 建“来源登记表”

至少先登记这些名字：

1. `OldYang`
2. `OldZhang`
3. `frontend-design`
4. `newtype`
5. `golden-testing`
6. `agent-browser`

每个名字至少记录：

1. 主仓位置
2. 是否软链
3. 是否复制副本
4. 当前推荐入口

### 建议 3：修掉 `MHSDC` 的 6 个断链 alias

当前最值得先修的是：

1. `gstack-plan-ceo-review`
2. `gstack-plan-eng-review`
3. `gstack-qa`
4. `gstack-qa-only`
5. `gstack-review`
6. `gstack-upgrade`

否则你在 `MHSDC` 里以为这些 skill 可用，但实际上会命中断链。

### 建议 4：决定 `frontend-design` 和 `OldZhang` 的主版本

这两个现在都存在“同名双版本”问题，建议明确：

1. 哪个是主仓
2. 哪个只是项目特化分支
3. 是否需要重命名以避免误触发

---

## 9. 最终结论

如果只回答最核心的问题，结论如下：

1. **全局主 skill 仓是 `~/.codex/skills`**
2. **`~/.claude/skills` 不是主仓，而是兼容层 / 混合层**
3. **主要项目里确实还维护着各自的项目级 skill 仓**
4. **当前最需要治理的问题不是数量，而是来源不透明、重名漂移、以及断链 alias**

---

## 10. 本次扫描涉及的主要路径

1. `/Users/luzhoua/.codex/skills`
2. `/Users/luzhoua/.claude/skills`
3. `/Users/luzhoua/.claude/skills-archive-BK`
4. `/Users/luzhoua/.agents/skills`
5. `/Users/luzhoua/MHSDC/.claude/skills`
6. `/Users/luzhoua/Mindhikers/.claude/skills`
7. `/Users/luzhoua/Mindhikers/Mindhikers_workspace/MHS-demo/.claude/skills`
8. `/Users/luzhoua/Blaw_notes/.claude/skills`
9. `/Users/luzhoua/Mylife_lawrence/.claude/skills`

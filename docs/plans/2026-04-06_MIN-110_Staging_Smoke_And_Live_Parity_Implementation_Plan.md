# MIN-110 实施方案：Staging Smoke 验收与线上内容对齐计划

日期：2026-04-06  
仓库：`Mindhikers-Homepage`  
分支：`codex/cyd-stumpel-home-exploration`  
关联 Issue：`MIN-110`  
状态：`active`

## 1. 问题框定

当前 `staging` 的 WordPress 模版站已经完成了最关键的打底工作：

1. `Astra - Interior Designer` 已导入
2. 默认垃圾内容已清理
3. 导航已收敛为 `About / Product / Blog / Contact`
4. `Blog` 已统一到 WordPress Posts
5. 首批 3 篇文章已导入
6. 首页正文、SEO、footer 的 `Kyle Mills / Interior Designer` 模板残留已基本清走

但这还不等于“可交给业务方继续细调”。目前还缺两类工作：

1. **Staging smoke checklist**
   - 确认当前站点处于“可继续编辑、可继续验收、没有明显破损”的状态
2. **Live parity pass**
   - 尽可能把当前线上 `mindhikers.com` 的主观感受迁到 staging
   - 本轮重点不是像素级还原，而是先对齐：
     - logo
     - 配色
     - 字体气质
     - 首页主要文字

这份方案的目标，是让新同事可以在不重复前面排查的前提下，直接接手这两个工作流，并把结果做成“可继续人工细调”的 staging 版本。

## 2. 当前事实基线

### 2.1 当前线上 `mindhikers.com`

已确认线上站点当前仍然是原来的 Next.js 风格首页，主要特征如下：

1. 字体体系：
   - 正文字体：`CabinetGrotesk`
   - 展示字体：`ClashDisplay`
2. 颜色体系：
   - 主背景：偏浅灰白
   - 主色：偏深绿
   - 按钮和强调色围绕深绿展开
3. 品牌资产：
   - logo 使用 `public/MindHikers.png`
4. 首页文案以当前仓库为准：
   - 中文基线：`src/data/site-content.ts`
   - 当前线上根域为中文首页风格

### 2.2 当前 staging

当前 staging 地址：

- `https://wordpress-l1ta-staging.up.railway.app`

当前 staging 已知状态：

1. 首页返回 `200`
2. `/blog/` 返回 `200`
3. 首页 SEO 描述已切到 Mindhikers 文案
4. footer 联系信息已不再是模板默认人设
5. 但视觉与线上仍有明显差异：
   - 仍使用 Astra 模版的默认字体体系（`Poppins / Raleway`）
   - header 站点 logo 为空
   - footer logo 仍是模板外链 `km-logo.svg`
   - Hero 与区块文案仍偏英文和模板化
   - 仍保留室内设计模板的图片、轮播和局部空链接按钮

### 2.3 本轮任务边界

本轮要做的是：

1. 跑通 smoke checklist
2. 尽可能对齐线上首页的：
   - logo
   - 主色 / 背景 / 字体气质
   - Hero / About / Product / Blog / Contact 的主要文案

本轮明确不做的是：

1. 不追求把 staging 结构完全改成线上 Next.js 的 DOM/组件结构
2. 不做高复杂度 Elementor 重排版
3. 不在这一轮清空所有模板图片和交互
4. 不在生产环境动手
5. 不切正式域名

## 3. 本轮目标

### 3.1 必达目标

1. staging 首页、博客列表、至少 1 篇文章详情通过 smoke 验收
2. staging 首页不再出现明显的模板品牌残留：
   - `Kyle Mills`
   - `Interior Designer`
   - `km-logo.svg`
3. staging 首页的主视觉气质明显向线上靠拢：
   - 使用 Mindhikers logo
   - 使用更接近线上站的主色、背景、字体
4. staging 首页主文案尽量对齐当前线上首页中文口径
5. 所有变更结果都补充到：
   - `docs/dev_logs/2026-04-06.md`
   - `docs/dev_logs/HANDOFF.md`

### 3.2 理想目标

如果时间允许，再进一步做：

1. 去掉明显无意义的模板按钮和空社交链接
2. 让 footer 结构更接近线上的“联系卡片”感觉
3. 让 Hero CTA 更接近线上的双按钮结构

## 4. 参考资料与事实来源

新同事开始前，必须先读这些文件：

1. `docs/dev_logs/HANDOFF.md`
2. `docs/dev_logs/2026-04-06.md`
3. `docs/plans/2026-04-04_MIN-110_WordPress_Template_Rebuild_Execution_Plan.md`
4. `docs/plans/2026-04-05_MIN-110_APlus_Migration_Checklist.md`
5. `docs/plans/2026-04-06_MIN-110_Elementor_Homepage_Render_Blocker.md`

另外，把这几份代码/资源当作“线上视觉与文案基线”：

1. `src/data/site-content.ts`
2. `src/components/home-page.tsx`
3. `src/components/navbar.tsx`
4. `src/app/globals.css`
5. `public/MindHikers.png`

## 5. 总体执行策略

采用“**先验收，再对齐，再复验**”的顺序：

1. 先确认当前 staging 没坏
2. 再抓线上内容和视觉基线
3. 再改 staging
4. 改完后做第二轮 smoke
5. 最后补日志和 handoff

不要一上来就改视觉，否则很容易把“历史问题”与“本轮引入问题”混在一起，后面不好追责。

## 6. 实施分工建议

如果只有一个人做，按本文档顺序串行执行。

如果两个人并行做，建议拆成：

### 角色 A：验收与日志

负责：

1. 跑 smoke checklist
2. 记录发现
3. 复查前台源码
4. 更新 `docs/dev_logs/2026-04-06.md` 与 `docs/dev_logs/HANDOFF.md`

### 角色 B：视觉与内容对齐

负责：

1. logo 对齐
2. 配色与字体对齐
3. 首页主文案迁移
4. 模板残留清理

## 7. 实施单元

## Unit 1：建立 smoke 基线

- [ ] 确认 staging 当前返回状态正常
- [ ] 确认首页、博客列表、文章详情可访问
- [ ] 确认后台仍可登录

### 目标

确定当前站点不是“半坏状态”，为后续视觉改动建立基线。

### 涉及面

1. 公网前台：
   - `/`
   - `/blog/`
   - 1 篇文章详情
2. 后台：
   - `wp-admin`

### 验收项

1. 首页 `200`
2. `/blog/` `200`
3. 文章详情可访问
4. 页面不存在 PHP fatal、空白页、严重布局断裂
5. 后台仍可进入

### 推荐检查口径

1. 结构级：
   - 首页存在 `Hero / Product / Blog / Contact`
2. 内容级：
   - `/blog/` 能看到 3 篇文章
3. 基础可用性：
   - 首页 CTA 不应全部 404

### 已知现象

当前已确认：

1. 首页 `200`
2. `/blog/` `200`
3. 某篇详情访问路径会从 `/blog/<slug>/` 301 到 `/<slug>/`

这一点需要记录清楚，但本轮不一定要修。先判断它是否属于本轮必须收口项。

### Test scenarios

1. 打开首页，确认五区块仍存在
2. 打开 `/blog/`，确认文章列表可见
3. 打开一篇文章详情，确认正文可读、不是空白
4. 从首页点击 `Blog`，确认能进入博客页

## Unit 2：抓取线上视觉与内容基线

- [ ] 抓线上首页的字体、颜色、logo、Hero 文案
- [ ] 建立 staging 对照清单

### 目标

确保后续 staging 改动有“明确对照物”，而不是凭印象改。

### 参考来源

1. `https://mindhikers.com/`
2. `src/data/site-content.ts`
3. `src/components/home-page.tsx`
4. `src/app/globals.css`
5. `public/MindHikers.png`

### 必须提取的信息

1. logo：
   - `public/MindHikers.png`
2. 字体：
   - `CabinetGrotesk`
   - `ClashDisplay`
3. 颜色：
   - `--background: #f9fafb`
   - `--foreground: #272a2f`
   - `--primary: #386652`
   - `--primary-foreground: #f9fafb`
   - `--border: #e2e3e5`
   - `--ring: #5e8473`
4. 中文首页核心文案：
   - Hero eyebrow
   - Hero title
   - Hero description
   - Hero CTA
   - About / Product / Blog / Contact 关键句

### 产出

一份“线上 vs staging 差异表”，最少包含：

1. logo
2. 字体
3. 主色
4. Hero 标题
5. Hero 描述
6. Product 区标题与 CTA
7. Blog 区标题与 CTA
8. Contact 文案

### Test scenarios

1. 线上基线提取结果与 `src/data/site-content.ts` 大方向一致
2. 不使用猜测文本替代线上真实文本

## Unit 3：替换 staging logo

- [ ] 把 Mindhikers logo 注入 staging
- [ ] 清掉模板默认 footer logo

### 目标

让 staging 第一眼看上去就是 Mindhikers，而不是一个去掉了名字的 Astra 模版。

### 实施方向

优先走 WordPress 数据层，而不是纯 CSS 假装有 logo。

### 推荐做法

1. 把 `https://mindhikers.com/MindHikers.png` 导入 staging 媒体库
2. 设置为 `custom_logo`
3. 检查 header 的 `.site-logo-img` 是否开始显示
4. 同步替换 footer `widget_block` 中的模板 logo：
   - 当前来源：`widget_block[7]`
   - 当前是 `km-logo.svg`

### 如果走程序方式

应优先修改这些数据面：

1. `custom_logo`
2. `widget_block`

### 验收标准

1. header 不再为空 logo
2. footer 不再出现 `km-logo.svg`
3. 页面第一屏与 footer 都能看到 Mindhikers 品牌图像

### Test scenarios

1. 首页 header 显示 Mindhikers logo
2. footer logo 不再是模板默认图
3. logo 在移动端未被压扁或超宽

## Unit 4：注入接近线上的主题色与字体体系

- [ ] 给 staging 加一层“线上风格覆盖”
- [ ] 保持可回退，不深改模板结构

### 目标

用最小改动，让 staging 从“室内设计模板风格”切到“Mindhikers 当前品牌气质”。

### 推荐实现

优先使用一层集中式覆盖，而不是四处点改。

可选路径：

1. WordPress Additional CSS
2. Astra 自定义器中的全局颜色 / 字体设置
3. 必要时，附加少量全局 CSS 覆盖

### 推荐覆盖内容

1. 颜色变量
2. body 背景
3. 主按钮色
4. 边框色
5. 标题字体
6. 正文字体
7. header 半透明玻璃感

### 建议覆盖的视觉方向

参考线上：

1. 主背景接近浅灰白
2. 主按钮为深绿色
3. 标题字形更有展示感
4. 卡片保持浅色半透明 / 毛玻璃感

### 推荐控制方式

如果无法完整接管 Astra token，至少做以下“肉眼优先级最高”的覆盖：

1. `body` 背景渐变
2. `h1~h4` 使用展示字体
3. `body, button, input, textarea` 使用正文品牌字体
4. 主按钮、链接 hover、重点 icon 改成线上绿
5. 导航容器和卡片加浅阴影与轻微透明度

### 验收标准

1. 不再一眼看出是 `Poppins + Raleway` 模版站
2. CTA 与重点元素颜色接近线上风格
3. 改动集中在单一配置面，便于后续回退

### Test scenarios

1. 首页主按钮颜色接近线上主色
2. 标题与正文字体已明显不同于模板默认值
3. 背景、卡片、边框层级更接近线上当前风格

## Unit 5：把首页主文案改成“中文首页 + 独立英文页”双轨口径

- [ ] 把 staging 根路径首页 `/` 改成完整中文首页
- [ ] 明确定义并保留独立英文页 `/en`
- [ ] 保留模板结构，不强行改成线上组件结构

### 目标

做到“语言先分轨清楚，再进入细调”：

1. `/` 是中文首页，不再出现大段英文正文
2. `/en` 是英文页，不再混入中文正文
3. 两个版本先把语言边界理顺，再慢慢修结构

### 参考来源

1. `src/data/site-content.ts`
2. 当前线上 `mindhikers.com/`
3. 当前线上 `mindhikers.com/en`

### 本轮口径澄清（用户确认版）

这一步的验收口径以“语言分轨”优先，不再用“仓库里 `zh` 文案里哪些词本来保留英文”来放宽标准。

也就是说：

1. staging 根路径 `/` 的目标是**完整中文首页**
2. staging `/en` 的目标是**独立英文页**
3. 中文页与英文页都允许保留这些非翻译对象：
   - 品牌名：`Mindhikers` / `心行者 Mindhikers`
   - 产品名：如 `黄金坩埚`
   - 邮箱、URL、日期
   - 文章原始标题（如果本轮不改文章内容）
4. 除上述对象外，页面可见正文、区块标题、按钮、辅助说明，不应再出现“中英混排但无明确语言意图”的状态

### 语言验收拆分

先把验收拆成两个页面，不再混在一个“中文化”词里：

1. 中文首页 `/`
   - 目标：用户第一眼看到的是完整中文品牌首页
   - 原则：主内容区、辅助文案、按钮文案都以中文为主
2. 英文页 `/en`
   - 目标：提供独立英文入口，服务英文读者
   - 原则：主内容区、辅助文案、按钮文案都以英文为主
3. 两页关系
   - 必须存在清晰的语言切换入口
   - 两页信息架构可保持大体对应
   - 不要求本轮做到像素级一致，但不允许出现语言职责不清

### 推荐优先级

优先改这些高可见文字：

1. Hero eyebrow
2. Hero title
3. Hero description
4. Hero 主 CTA
5. About 标题与第一段
6. Product 标题、说明、3 个子卡片标题
7. Blog 标题、headline、CTA
8. Contact 标题、说明、联系信息
9. Header / Footer 中仍可见的按钮、说明、辅助标题

### 当前 staging 已知不一致点

1. Hero title 仍是英文
2. Hero description 仍是英文混写
3. Hero CTA 仍是英文
4. Hero 统计项仍是模板英文
5. Header 仍有 `Let's Talk`
6. Product 区仍有模板化英文引导词
7. Blog 区主文案仍是英文
8. Footer / Contact 仍有 `Get In Touch`、`Base` 等英文辅助标题

### 推荐做法

优先直接改 `Home (1807)` 的 Elementor 数据层：

1. 不强依赖浏览器里逐点拖拽编辑
2. 优先替换明确可定位的文本值
3. 少做结构移动，多做文案替换

### 中文首页 `/` 验收标准

当前这一小轮，以中文首页通过为主验收门槛。只有满足下面条件，`/` 才算通过：

1. Hero 全部完成中文化：
   - eyebrow、标题、描述、主按钮、次按钮、统计项/标签
2. About / Product / Blog / Contact 四个主区块的可见标题、主句、说明文案为中文
3. Header 与 Footer 中仍可见的高频交互文案为中文：
   - 例如 `Let's Talk`、`Get In Touch` 这类高可见文本不能保留英文
4. 页面中不再出现明显模板行业话术：
   - 如室内设计、客户项目、Happy Clients 等
5. 页面中允许保留的英文仅限：
   - 品牌名、产品名、邮箱、URL、文章原始标题等非翻译对象

### 英文页 `/en` 验收定义

`/en` 不和中文首页混验，但必须在文档里先定义清楚。后续开始做英文页时，以此为准：

1. `/en` 必须是可访问的独立英文页
2. Hero / About / Product / Blog / Contact 的可见主文案为英文
3. 页面中不应混入大段中文正文
4. 必须有清晰的语言切换入口，可从中文页进入英文页，也能从英文页回到中文页
5. 英文页的区块结构允许与中文页同构，不要求本轮先完成视觉精修

### 可以接受的阶段性结果

如果本轮无法把每个区块都彻底中文化，至少要保证：

1. Hero 完成中文化
2. Product / Blog / Contact 标题和主句完成中文化
3. Header 里的主要 CTA 不再保留英文
4. 不再有明显模板行业语境

### Test scenarios

1. 打开 `/`，确认首屏到 Footer 的高可见文案已以中文为主
2. 检查 Hero / Product / Blog / Contact，确认无大段英文模板正文残留
3. 检查 Header CTA、Footer 标题、统计项，确认不再保留模板英文文案
4. 打开 `/en`，确认英文入口与英文页目标已在页面结构或路由层面可定义、可验证

## Unit 6：清理高干扰模板残留

- [ ] 去掉最影响观感的模板痕迹
- [ ] 保留低风险、可后调的部分

### 目标

把“最容易让人出戏”的残留先清掉，但避免过度装修。

### 本轮建议优先清的点

1. footer 空社交链接
2. header / mobile drawer 里的 `Let's Talk` 空按钮
3. 模板默认统计项：
   - `400+ Projects Done`
   - `100+ Happy Clients`
4. 模板默认 logo 外链

### 本轮可以后置的点

1. Hero 人像图
2. 背景轮播图
3. 更深的区块重排

原因：

1. 这些更像“第二轮精修”
2. 本轮先抓品牌识别和文案对齐，收益更高

### Test scenarios

1. 首页不再出现空的无意义 CTA
2. 不再出现与 Mindhikers 无关的业务统计
3. 不再出现模板品牌图

## Unit 7：第二轮 smoke 与交付收口

- [ ] 对改后的 staging 再做一次 smoke
- [ ] 更新日志与交接

### 目标

把这轮改动从“做了”变成“可交接、可继续做”。

### 必做复检

1. 首页访问与主要文案
2. `/blog/`
3. 1 篇文章详情
4. header logo
5. footer 联系方式
6. 首页 SEO 元信息
7. staging 仍保持 `noindex`

### 必须更新的文档

1. `docs/dev_logs/2026-04-06.md`
2. `docs/dev_logs/HANDOFF.md`

### 日志里必须说明

1. 本轮改了哪些配置面
2. 哪些内容已对齐线上
3. 哪些模板残留故意没动
4. 下一轮最该继续做什么

### Test scenarios

1. 首页源码里不再出现模板品牌残留
2. 首页 header 与 footer 的品牌感一致
3. 日志与 handoff 足以支撑下一位同事继续接手

## 8. 建议执行顺序

按下面顺序做，不要跳：

1. 先做 Unit 1：smoke 基线
2. 再做 Unit 2：线上基线抓取
3. 再做 Unit 3：logo
4. 再做 Unit 4：配色与字体
5. 再做 Unit 5：主文案对齐
6. 再做 Unit 6：高干扰模板残留清理
7. 最后做 Unit 7：第二轮 smoke 与日志收口

## 9. 风险与注意事项

| 风险 | 影响 | 建议 |
|------|------|------|
| 直接在 Elementor UI 里大面积拖拽 | 容易引入不可控结构变化 | 优先改数据层与集中式主题配置 |
| 同时改结构、图片、动画、颜色 | 很难定位回归问题 | 本轮只抓 logo / 配色 / 字体 / 主要文字 |
| 不先做 smoke 就开始美化 | 容易把历史问题算到本轮头上 | 先验收，再动手 |
| 追求一次性完全复刻线上 | 成本高，且不利于后续 handoff | 先做到“明显更像线上” |
| 提前关闭 staging 的 noindex | 有被搜索引擎收录风险 | staging 阶段不要动 |

## 10. 回滚策略

本轮所有改动应尽量集中在以下配置面，便于回滚：

1. `Home (1807)` 的 Elementor 数据
2. `custom_logo`
3. `widget_block`
4. `widget_text`
5. SureRank 首页元信息
6. Additional CSS / Astra 全局样式

开始改之前，建议先备份这些值：

1. `_elementor_data`
2. `widget_block`
3. `widget_text`
4. `blogdescription`
5. 主题相关配置

如果本轮改崩：

1. 先回滚 logo / widget / CSS
2. 再回滚首页 Elementor 数据
3. 不要先动博客和 SEO 已经稳定的部分

## 11. 阻塞止损规则

执行本方案时，遵守老杨的全局止损规则：

1. 单一局部问题如果已经做了 `3 次有效且有区分度的尝试`
2. 或连续 `20 分钟` 没有实质进展

则必须：

1. 停止纠缠
2. 记录：
   - 问题背景
   - 已尝试路径
   - 失败现象
   - 怀疑原因
   - 相关证据
   - 需要什么类型的专家
3. 请用户协调外部支持
4. 继续推进其他不受阻任务

## 12. 交付完成标准

这份计划对应的实施工作完成后，应达到下面这个状态：

1. staging smoke 已跑过一轮
2. logo 已从模板品牌切到 Mindhikers
3. 主色与字体气质明显向线上靠拢
4. 首页主要文字已尽可能对齐当前线上中文口径
5. 最影响观感的模板残留已清掉
6. 日志和 handoff 已更新
7. 下一位同事无需重新考古就能继续推进

## 13. 对新同事的直接要求

开始前先说清楚三件事：

1. 你这轮不是“重做网站”，而是“让 staging 更像当前线上站，并确认它没坏”
2. 你这轮不要追求结构重建，只抓：
   - logo
   - 配色
   - 字体
   - 文字
3. 你每完成一个 Unit，就立刻做一次最小复查，不要一路改到底

如果新同事能把这份计划按顺序稳定执行下来，说明他至少具备：

1. 理解上下文的能力
2. 控制变更范围的能力
3. 做 staged delivery 的能力
4. 交接与落盘意识

## Sources & References

- `docs/dev_logs/HANDOFF.md`
- `docs/dev_logs/2026-04-06.md`
- `docs/plans/2026-04-04_MIN-110_WordPress_Template_Rebuild_Execution_Plan.md`
- `docs/plans/2026-04-05_MIN-110_APlus_Migration_Checklist.md`
- `docs/plans/2026-04-06_MIN-110_Elementor_Homepage_Render_Blocker.md`
- `src/data/site-content.ts`
- `src/components/home-page.tsx`
- `src/components/navbar.tsx`
- `src/app/globals.css`
- `public/MindHikers.png`

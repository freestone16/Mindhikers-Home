# MIN-110 A+ 定向迁移清单

日期：2026-04-05  
仓库：`/Users/luzhoua/Mindhikers/Mindhikers-Homepage`  
分支：`codex/cyd-stumpel-home-exploration`  
关联 Issue：`MIN-110`

## 1. 路线定义

本清单对应当前已确认的 `A+` 路线：

1. staging 作为全新 WordPress 实例初始化
2. 不做整站数据恢复
3. 只迁真正需要的内容、媒体和配置

## 2. 首页迁移资产

### 必迁

1. 中文首页结构与文案基线
   - `ops/wordpress/homepage-seeds/homepage-zh.json`
2. 英文首页结构与文案基线
   - `ops/wordpress/homepage-seeds/homepage-en.json`
3. 现有导航结构参考
   - `src/data/site-content.ts`
4. 联系方式与站点基础描述
   - `src/data/site-content.ts`
   - `src/data/resume.tsx`

### 可后迁

1. 产品页细节文案
2. 双语完整收口
3. 更细的视觉润色

## 3. Blog 迁移资产

### 首批候选

1. `content/building-design-systems.mdx`
2. `content/nextjs-performance-tips.mdx`
3. `content/typescript-best-practices.mdx`
4. `content/git-workflow-guide.mdx`
5. `content/api-design-principles.mdx`
6. `content/testing-react-apps.mdx`
7. `content/remote-work-productivity.mdx`

### 首轮迁移建议

先迁 3 篇即可，不必一次性全上：

1. `testing-react-apps`
2. `api-design-principles`
3. `git-workflow-guide`

原因：

1. 三篇结构稳定
2. 足够形成首页 Blog 区的首批内容
3. 能尽快把“WordPress Posts 成为唯一正式入口”跑通

## 4. 必核对配置

1. 站点标题与描述
2. 首页是否设为静态首页
3. 固定链接结构
4. 搜索引擎索引开关
5. `www / apex / staging` 域名口径

## 5. 当前明确不迁

1. 旧 Homepage JSON CMS 的编辑方式
2. 旧 Next.js 首页实现细节
3. 旧的混合 Blog 来源逻辑
4. 非必要插件配置与历史后台杂项

## 6. 下一步操作顺序

1. 导入 `Astra - Interior Designer`
2. 清理默认示例内容
3. 建首页五区块
4. 迁中文首页内容
5. 再决定是否同步补英文
6. 迁首批 3 篇博客
7. 做 staging smoke

# Mindhikers Homepage

`Mindhikers-Homepage` 是 `Mindhikers` 的官网项目。

它是一个独立项目，用于承载品牌首页、双语首页、博客内容展示和对外产品入口，不从属于其他项目族。

## 当前状态

当前主线是 WordPress 模版站重建与验收。

现阶段的工作重点包括：

1. 首页内容与视觉收口
2. 中英文首页可用性
3. Blog 列表与详情链路
4. staging 与 production 的环境边界核对
5. Homepage CMS 与前台联动验证

## 技术栈

1. Next.js 16
2. React 19
3. TypeScript
4. Tailwind CSS 4
5. MDX / Content Collections
6. WordPress API 集成

## 主要能力

当前仓主要覆盖：

1. 中文首页：`/`
2. 英文首页：`/en`
3. 博客列表：`/blog`
4. 博客详情：`/blog/[slug]`
5. 产品展示页，例如：`/golden-crucible`
6. 健康检查：`/health`
7. Homepage CMS 内容拉取与渲染
8. WordPress homepage seed 导出

## 项目结构

核心目录如下：

1. `src/app/`
   - App Router 路由入口
2. `src/components/`
   - 首页、博客、产品页和通用 UI 组件
3. `src/lib/cms/`
   - WordPress / CMS 数据拉取、转换和渲染逻辑
4. `src/data/`
   - 站点内容与静态数据
5. `content/`
   - 博客等内容资源
6. `ops/wordpress/`
   - WordPress 相关脚本与 seed 数据
7. `docs/`
   - handoff、规则、边界、计划与经验文档
8. `testing/`
   - OpenCode 测试协议与模块测试入口

## 本地开发

### 环境要求

1. Node.js `>=20.9.0`
2. `pnpm`

### 安装依赖

```bash
pnpm install
```

### 本地启动

```bash
pnpm dev
```

默认会启动 Next.js 开发服务器。

### 生产构建

```bash
pnpm build
pnpm start
```

## 环境变量

参考：

[`/.env.example`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/.env.example)

当前最关键的变量：

1. `BLOG_SOURCE`
   - 博客数据来源
   - 当前示例值：`mdx`
2. `WORDPRESS_API_URL`
   - Homepage CMS / WordPress API 地址
3. `REVALIDATE_SECRET`
   - revalidate 接口密钥

## 常用脚本

1. `pnpm dev`
   - 本地开发
2. `pnpm build`
   - 生产构建
3. `pnpm start`
   - 启动生产构建产物
4. `pnpm lint`
   - 运行 ESLint
5. `pnpm lint:fix`
   - 自动修复可修复的 lint 问题
6. `pnpm export:homepage-seeds`
   - 导出 WordPress homepage seed 数据

## 运维与内容脚本

相关文件位于：

1. [`ops/wordpress/README.md`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/ops/wordpress/README.md)
2. [`ops/wordpress/export-homepage-seeds.ts`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/ops/wordpress/export-homepage-seeds.ts)
3. [`ops/wordpress/reset-admin-password.mjs`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/ops/wordpress/reset-admin-password.mjs)
4. [`ops/mindhikers-cms-runtime/Dockerfile`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/ops/mindhikers-cms-runtime/Dockerfile)

## 文档入口

如果要继续接手这个项目，建议按下面顺序读：

1. [`AGENTS.md`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/AGENTS.md)
2. [`docs/dev_logs/HANDOFF.md`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/docs/dev_logs/HANDOFF.md)
3. [`docs/rules.md`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/docs/rules.md)
4. [`docs/domain-boundary.md`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/docs/domain-boundary.md)
5. [`docs/lessons.md`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/docs/lessons.md)
6. [`docs/plans/`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/docs/plans/)

## 测试入口

测试协议和模块入口在这里：

1. [`testing/README.md`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/testing/README.md)
2. [`testing/OPENCODE_INIT.md`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/testing/OPENCODE_INIT.md)
3. [`testing/homepage/README.md`](/Users/luzhoua/Mindhikers/Mindhikers-Homepage/testing/homepage/README.md)

默认测试重点：

1. 首页五区块可见性
2. Blog 列表与详情链路
3. Contact 区块可达性
4. staging / production 关键路径 smoke

## 治理说明

1. 当前仓是独立官网项目
2. 当前治理与运行口径以本仓 `AGENTS.md`、`docs/`、`testing/` 为准
3. 不要默认引用其他项目的规则、计划或设计索引

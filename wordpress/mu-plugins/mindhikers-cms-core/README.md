# Mindhikers CMS Core

`mindhikers-cms-core` 是 Mindhikers Homepage CMS 的最小可行核心层。

当前版本先提供：

1. `mh_homepage` 内容类型
2. `Mindhikers CMS` 站点设置页
3. `mindhikers/v1/site-settings`
4. `mindhikers/v1/homepage/{locale}`
5. 通过环境变量触发前台 revalidate

## 目录结构

1. `../mindhikers-cms-core.php`
   - MU Plugin 入口文件
2. `bootstrap.php`
   - 当前插件主逻辑

## 需要的环境变量

1. `MINDHIKERS_REVALIDATE_ENDPOINT`
   - 例如 `https://www.mindhikers.com/api/revalidate`
2. `MINDHIKERS_REVALIDATE_SECRET`
   - 与前台 `REVALIDATE_SECRET` 对应

## 当前限制

1. Homepage 编辑暂时采用结构化 JSON 文本框，不是最终编辑体验
2. Product Page、Site Settings 表单细化与 webhook 粒度仍待补齐
3. 插件当前先落在仓库中，后续需要部署到 WordPress 的 `mu-plugins`

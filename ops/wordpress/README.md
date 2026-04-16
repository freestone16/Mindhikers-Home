# WordPress Ops

## Reset admin password

当 WordPress 后台密码因初始化兼容问题不可控时，可以用下面的方式直接重置管理员密码：

```bash
npm install mysql2 --no-save --legacy-peer-deps

MARIADB_PUBLIC_URL='mariadb://user:pass@host:port/db' \
WP_ADMIN_USERNAME='mindhikers_admin' \
WP_ADMIN_PASSWORD='replace-with-a-strong-password' \
node ops/wordpress/reset-admin-password.mjs
```

说明：

1. 脚本会把 `wp_users.user_pass` 更新为目标密码的 MD5 值
2. WordPress 首次成功登录后会自动把旧 MD5 升级为更安全的哈希
3. 这条链路只用于恢复后台接管，不应代替正常的后台改密流程

## Export homepage JSON seeds

当需要把当前静态首页内容迁入 `mh_homepage` 的 `zh` / `en` 两条记录时，可以先导出仓库里的现有内容：

```bash
npm run export:homepage-seeds
```

导出结果会落到：

1. `ops/wordpress/homepage-seeds/homepage-zh.json`
2. `ops/wordpress/homepage-seeds/homepage-en.json`

这两份文件可直接作为当前 CMS 后台 JSON 文本框的迁移底稿。

<?php
/**
 * Plugin Name: Mindhikers CMS Core
 * Description: Mindhikers Homepage 的结构化 CMS 核心层，负责 Homepage 内容模型、站点设置、REST 输出与前台刷新钩子。
 * Author: Mindhikers
 * Version: 0.1.0
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

require_once __DIR__ . '/mindhikers-cms-core/bootstrap.php';

Mindhikers_Cms_Core::boot();

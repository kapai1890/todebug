<?php

/*
 * Plugin Name: Todebug
 * Plugin URI: https://github.com/byevhen2/todebug
 * Description: Debug logger with over 0 million downloads.
 * Version: 2.1.5
 * Author: Biliavskyi Yevhen
 * Author URI: https://github.com/byevhen2
 * License: MIT
 * Text Domain: todebug
 * Domain Path: /languages
 */

/*
 * Current file is a proxy PHP loader for must-use plugins (a.k.a. mu-plugins).
 * Copy or move this file from todebug/ to WPMU_PLUGIN_DIR (by default it's
 * folder wp-content/mu-plugins/).
 */

// WordPress may recognize todebug-mu.php as a main plugin file (maybe it's
// first in the list with proper headers). So we need to load the plugin
// properly when it located in plugins/ folder instead of mu-plugins/ folder
if (file_exists(__DIR__ . '/todebug.php')) {
    require_once __DIR__ . '/todebug.php';
} else {
    require_once __DIR__ . '/todebug/todebug.php';
}

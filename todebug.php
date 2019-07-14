<?php

/*
 * Plugin Name: Todebug
 * Plugin URI: https://github.com/byevhen2/todebug
 * Description: Debug logger with over 0 million downloads.
 * Version: 2.2.7
 * Author: Biliavskyi Yevhen
 * Author URI: https://github.com/byevhen2
 * License: MIT
 * Text Domain: todebug
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit('Press Enter to proceed...');
}

if (!class_exists('\todebug\Plugin')) {
    // Load NSCL (WordPress Settings Fields and functions)
    require_once __DIR__ . '/vendors/nscl/functions.php';
    require_once __DIR__ . '/vendors/nscl/autoload.php';

    // Load ToStr
    require_once __DIR__ . '/vendors/tostr/main.php';

    // Load plugin functions
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/includes/control-functions.php';

    // Load plugin
    require_once __DIR__ . '/classes/Stringifier.php';
    require_once __DIR__ . '/classes/StringBuilder.php';
    require_once __DIR__ . '/classes/User.php';
    require_once __DIR__ . '/classes/Settings.php';
    require_once __DIR__ . '/classes/AssetsLoader.php';
    require_once __DIR__ . '/classes/AdminBar.php';
    require_once __DIR__ . '/classes/LogsPrinter.php';
    require_once __DIR__ . '/classes/Plugin.php';

    define('todebug\PLUGIN_VERSION', '2.2.7');

    define('todebug\PLUGIN_DIR', plugin_dir_path(__FILE__)); // With trailing slash
    define('todebug\PLUGIN_URL', plugin_dir_url(__FILE__));

    global $todebug;
    $todebug = new \todebug\Plugin();
}

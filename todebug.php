<?php

/*
 * Plugin Name: Todebug
 * Plugin URI: https://github.com/kapai1890/todebug
 * Description: Debug logger with over 0 million downloads.
 * Version: 1.6.37
 * Author: Biliavskyi Yevhen
 * Author URI: https://github.com/kapai1890
 * License: MIT
 * Text Domain: todebug
 */

if (!defined('ABSPATH')) {
    exit('Use standalone version instead of WordPress plugin.');
}

define('TODEBUG_PLUGIN_FILE', __FILE__);

require_once 'main.php';

<?php

/*
 * Plugin Name: Todebug
 * Plugin URI: https://github.com/kapai1890/todebug
 * Description: Debug logger with over 0 million downloads.
 * Version: 18.17.1
 * Author: kapai1890
 * Author URI: https://github.com/kapai1890
 * License: MIT
 * Text Domain: todebug
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit('Use standalone version instead of WordPress plugin.');
}

define('TODEBUG_PLUGIN_FILE', __FILE__);

require_once 'main.php';

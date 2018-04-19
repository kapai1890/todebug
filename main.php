<?php

declare(strict_types = 1);

if (class_exists('\todebug\Todebug')) {
    return;
}

define('TODEBUG_ROOT', __DIR__);

require_once 'utils/array-util.php';
require_once 'utils/regex-util.php';
require_once 'utils/stringify-util.php';

require_once 'plugins/plugin-base.php';

if (defined('ABSPATH') && defined('TODEBUG_PLUGIN_FILE')) {
    require_once 'plugins/wordpress-plugin.php';
} else {
    require_once 'plugins/standalone-plugin.php';
}

/**
 * Write debug message: output strings as is and use space " " as a separator
 * between message parts.
 */
function todebug(...$vars)
{
    \todebug\Todebug::write($vars);
}

/**
 * Translate all types of variables, including string type.
 */
function todebugs(...$vars)
{
    \todebug\Todebug::writeStrict($vars);
}

/**
 * @param mixed $var
 * @param string $type
 *
 * @example todebugAs("count", "function");
 */
function todebugx($var, string $type)
{
    \todebug\Todebug::writeAs($var, $type);
}

if (!function_exists('tostring')) {
    function tostring(...$vars): string
    {
        return \todebug\Todebug::buildMessage($vars);
    }
}

if (!function_exists('tostrings')) {
    function tostrings(...$vars): string
    {
        return \todebug\Todebug::buildStrings($vars);
    }
}

<?php

declare(strict_types = 1);

// Do not try to redeclare existing functions and classes
if (!class_exists('\todebug\Todebug')) {

    define('TODEBUG_ROOT', __DIR__);

    require_once 'utils/array-util.php';
    require_once 'utils/regex-util.php';
    require_once 'utils/stringify-util.php';

    require_once 'plugins/plugin-base.php';

    $isWordpress = (defined('ABSPATH') && defined('TODEBUG_PLUGIN_FILE'));
    if ($isWordpress) {
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
            $message = \todebug\Todebug::buildMessage($vars);
            return rtrim($message); // Remove PHP_EOL from the end of file
        }
    }

    if (!function_exists('tostrings')) {
        function tostrings(...$vars): string
        {    
            $message = \todebug\Todebug::buildStrings($vars);
            return rtrim($message); // Remove PHP_EOL from the end of file
        }
    }

    if (!function_exists('tostringx')) {
        function tostringx($var, string $type): string
        {
            $message = \todebug\Todebug::buildStringAs($var, $type);
            return rtrim($message); // Remove PHP_EOL from the end of file
        }
    }

    function todebug_output_file(string $outputFile)
    {
        \todebug\Todebug::$outputFile = $outputFile;
    }

    function todebug_array_length(int $length)
    {
        \todebug\utils\StringifyUtil::$maxInlineArrayLength = $length;
    }

    if ($isWordpress) {
        function todebug_log_to_file()
        {
            add_filter('todebug_silent_debugging', '__return_false');
            add_filter('todebug_skip_ajax_logs', '__return_false');
        }

        function todebug_dont_log_to_file()
        {
            add_filter('todebug_silent_debugging', '__return_true');
            add_filter('todebug_skip_ajax_logs', '__return_true');
        }

        function todebug_reset_options()
        {
            remove_filter('todebug_silent_debugging', '__return_true');
            remove_filter('todebug_silent_debugging', '__return_false');
            remove_filter('todebug_skip_ajax_logs', '__return_true');
            remove_filter('todebug_skip_ajax_logs', '__return_false');
        }
    }

} // if not class exists \todebug\Todebug

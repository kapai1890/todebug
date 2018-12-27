<?php

declare(strict_types = 1);

// Don't try to redeclare existing functions and classes
if (!class_exists('\todebug\Logger')) {

    global $todebug;

    $isWordpress = defined('ABSPATH');
    $todebug = ($isWordpress) ? \todebug\WordPressLogger::class : \todebug\Logger::class;

    require 'includes/functions.php';
    require 'includes/Reflection.php';
    require 'includes/Stringify.php';

    require 'includes/Logger.php';

    if ($isWordpress) {
        require 'includes/WordPressLogger.php';

        // Create an instance of the plugin
        \todebug\WordPressLogger::create();
    }

    function todebug_file(string $outputFile) {
        global $todebug;
        $todebug::$outputFile = $outputFile;
    }

    if ($isWordpress) {
        function todebug_clear($clearFile = true) {
            global $todebug;

            $todebug::clearExecutionLogs();

            if ($clearFile) {
                $todebug::clearFile();
            }
        }

        function log_todebugs() {
            add_filter('silent_todebug', '__return_false');
            add_filter('todebug_noajax', '__return_false');
        }

        function skip_todebugs() {
            add_filter('silent_todebug', '__return_true');
            add_filter('todebug_noajax', '__return_true');
        }

        function reset_todebugs() {
            remove_filter('silent_todebug', '__return_true');
            remove_filter('silent_todebug', '__return_false');
            remove_filter('todebug_noajax', '__return_true');
            remove_filter('todebug_noajax', '__return_false');
        }
    } else {
        function todebug_clear() {
            global $todebug;
            $todebug::clearFile();
        }
    }

    /**
     * Write debug message: output strings as is and use space " " as a
     * separator between message parts.
     *
     * @param mixed $vars
     * @return string
     *
     * @global \todebug\Logger|\todebug\WordPressLogger $todebug
     */
    function todebug(...$vars): string {
        global $todebug;
        return $todebug::write($vars);
    }

    /**
     * Translate all types of variables, including string type.
     *
     * @param mixed $vars
     * @return string
     *
     * @global \todebug\Logger|\todebug\WordPressLogger $todebug
     */
    function todebugs(...$vars): string {
        global $todebug;
        return $todebug::writeStrict($vars);
    }

    /**
     * @param mixed $var
     * @param string $type
     * @return string
     *
     * @global \todebug\Logger|\todebug\WordPressLogger $todebug
     */
    function todebugx($var, string $type): string {
        global $todebug;
        return $todebug::writeAs($var, $type);
    }

    /**
     * @param mixed $vars
     * @return string
     *
     * @global \todebug\Logger|\todebug\WordPressLogger $todebug
     */
    function tostring(...$vars): string {
        global $todebug;

        $message = $todebug::buildMessage($vars);
        return rtrim($message); // Remove PHP_EOL from the end of file
    }

    /**
     * @param mixed $vars
     * @return string
     *
     * @global \todebug\Logger|\todebug\WordPressLogger $todebug
     */
    function tostrings(...$vars): string {
        global $todebug;

        $message = $todebug::buildStrict($vars);
        return rtrim($message); // Remove PHP_EOL from the end of file
    }

    /**
     * @param mixed $var
     * @param string $type
     *
     * @global \todebug\Logger|\todebug\WordPressLogger $todebug
     */
    function tostringx($var, string $type): string {
        global $todebug;

        $message = $todebug::buildMessageAs($var, $type);
        return rtrim($message); // Remove PHP_EOL from the end of file
    }

} // If class Logger not exists

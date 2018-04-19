<?php

declare(strict_types = 1);

namespace todebug;

use \todebug\utils\StringifyUtil;

abstract class Plugin
{
    protected static $outputFile = null;

    public static function write(array $vars)
    {
        $message    = static::buildMessage($vars);
        $outputFile = static::outputFile();

        static::log($message, $outputFile);
    }

    public static function writeStrict(array $vars)
    {
        $message    = static::buildStrings($vars);
        $outputFile = static::outputFile();

        static::log($message, $outputFile);
    }

    /**
     * @param mixed $var
     * @param string $type
     */
    public static function writeAs($var, string $type)
    {
        $message    = StringifyUtil::stringifyAs($var, $type) . PHP_EOL;
        $outputFile = static::outputFile();

        static::log($message, $outputFile);
    }

    public static function clear()
    {
        $outputFile = $this->outputFile();
        file_put_contents($outputFile, '');
    }

    protected static function log(string $message, string $outputFile)
    {
        // Message type 3 - append text to the specified file
        error_log($message, 3, $outputFile);
    }

    public static function buildMessage(array $vars): string
    {
        $texts = [];

        foreach ($vars as $var) {
            $type = StringifyUtil::getType($var);
            $texts[] = ($type == 'string') ? trim($var) : StringifyUtil::stringifyAs($var, $type);
        }

        $message = implode(' ', $texts) . PHP_EOL;
        return $message;
    }

    public static function buildStrings(array $vars): string
    {
        $texts = array_map(['\todebug\utils\StringifyUtil', 'stringify'], $vars);

        $strings = implode(' ', $texts) . PHP_EOL;
        return $strings;
    }

    public static function outputFile(): string
    {
        if (is_null(static::$outputFile)) {
            static::$outputFile = static::proposeOutputFile();

            // Strill null?
            if (is_null(static::$outputFile)) {
                static::$outputFile = static::defaultOutputFile();
            }
        }

        return static::$outputFile;
    }

    /**
     * @return string|null
     */
    protected static function proposeOutputFile()
    {
        if (defined('TODEBUG_OUTPUT_FILE')) {
            return TODEBUG_OUTPUT_FILE;
        } else {
            return TODEBUG_ROOT . '/logs/' . date('Y-m-d') . '.log';
        }
    }

    protected static function defaultOutputFile()
    {
        $dir = __DIR__ . '/../logs';
        $dir = realpath($dir); // ".." can be disabled in safe mode
        $dir = $dir ?: __DIR__ . '/../logs'; // realpath() can return FALSE
        $dir = rtrim($dir, '/\\');

        $outputFile = $dir . '/debug.log';
        return $outputFile;
    }
}

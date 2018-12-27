<?php

declare(strict_types = 1);

namespace todebug;

class Logger
{
    public static $outputFile = null;

    public static function write(array $vars): string
    {
        $message = static::buildMessage($vars);
        $file    = static::outputFile();

        static::log($message, $file);

        return $message;
    }

    public static function writeStrict(array $vars): string
    {
        $message = static::buildStrict($vars);
        $file    = static::outputFile();

        static::log($message, $file);

        return $message;
    }

    public static function writeAs($var, string $type): string
    {
        $message = static::buildMessageAs($var, $type);
        $file    = static::outputFile();

        static::log($message, $file);

        return $message;
    }

    public static function log(string $message, string $toFile)
    {
        // Message type 3 - append text tot the specified file
        error_log($message, 3, $toFile);
    }

    public static function clearFile()
    {
        $file = static::outputFile();
        file_put_contents($file, '');
    }

    public static function buildMessage(array $vars): string
    {
        $strings = [];

        foreach ($vars as $var) {
            $type = Stringify::getType($var);

            if ($type == 'string' && !is_numeric($var)) {
                $trimmed = trim($var);

                // If the string is empty or consist only of spaces - add ""
                if (empty($trimmed)) {
                    $strings[] = Stringify::stringifyAs($var, $type);
                } else {
                    $strings[] = $trimmed;
                }

            } else {
                $strings[] = Stringify::stringifyAs($var, $type);
            }
        }

        $message = implode(' ', $strings) . PHP_EOL;

        return $message;
    }

    public static function buildStrict(array $vars): string
    {
        $strings = array_map(['\todebug\Stringify', 'stringify'], $vars);
        $message = implode(' ', $strings) . PHP_EOL;

        return $message;
    }

    public static function buildMessageAs($var, string $type): string
    {
        $message = Stringify::stringifyAs($var, $type) . PHP_EOL;
        return $message;
    }

    public static function outputFile(): string
    {
        if (is_null(static::$outputFile)) {
            static::$outputFile = static::suggestFile();

            // Still null?
            if (is_null(static::$outputFile)) {
                static::$outputFile = static::defaultFile();
            }
        }

        return static::$outputFile;
    }

    public static function suggestFile()
    {
        if (defined('TODEBUG_LOG_FILE')) {
            return TODEBUG_LOG_FILE;
        }

        // No more variants to suggest
        return null;
    }

    public static function defaultFile(): string
    {
        $pluginRoot = real_path(__DIR__ . '/../');

        // Add trailing slash
        $pluginRoot = rtrim($pluginRoot, '\/') . '/';

        // .../todebug/logs/2018-09-05.log
        $file = $pluginRoot . 'logs/' . date('Y-m-d') . '.log';

        return $file;
    }
}

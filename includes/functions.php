<?php

/**
 * Build message, but don't wrap root strings with "".
 *
 * @param mixed[] $vars
 * @return string
 *
 * @global \todebug\Plugin $todebug
 */
function todebug(...$vars)
{
    global $todebug;

    $string = call_user_func_array('tostr', $vars);
    $todebug->logMessage($string);

    return $string;
}

/**
 * Build message in strict mode (wrap all string with ""), except for the first
 * message.
 *
 * @param string $message The message to print not strictly.
 * @param mixed[] $vars
 * @return string
 *
 * @global \todebug\Plugin $todebug
 */
function todebugms($message, ...$vars)
{
    global $todebug;

    $vars = array($message) + $vars;
    $string = call_user_func_array('tostrms', $vars);
    $todebug->logMessage($string);

    return $string;
}

/**
 * Build message in strict mode (wrap all string with "").
 *
 * @param mixed[] $vars
 * @return string
 *
 * @global \todebug\Plugin $todebug
 */
function todebugs(...$vars)
{
    global $todebug;

    $string = call_user_func_array('tostrs', $vars);
    $todebug->logMessage($string);

    return $string;
}

/**
 * Build the message also going into the nested objects.
 *
 * @param mixed $var Any object.
 * @param int $maxDepth Optional. -1 by default (auto detect).
 * @param array $recursiveClasses Optional. [stdClass] by default.
 * @return string
 *
 * @global \todebug\Plugin $todebug
 */
function todebugu($var, $maxDepth = -1, $recursiveClasses = array())
{
    global $todebug;

    $string = call_user_func('tostru', $var, $maxDepth, $recursiveClasses);
    $todebug->logMessage($string);

    return $string;
}

/**
 * Convert value to string, indicating it's type manually.
 *
 * @param mixed $var
 * @param string $type
 * @param int $maxDepth Optional. -1 by default (auto detect).
 * @return string
 *
 * @global \todebug\Plugin $todebug
 */
function todebugx($var, $type, $maxDepth = -1)
{
    global $todebug;

    $string = call_user_func('tostrx', $var, $type, $maxDepth);
    $todebug->logMessage($string);

    return $string;
}

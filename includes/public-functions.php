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
    return $todebug->logMessage($vars);
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
    return $todebug->logStrict($vars);
}

/**
 * Convert value to string indicating it's type manually.
 *
 * @param mixed $var
 * @param string $type
 * @return string
 *
 * @global \todebug\Plugin $todebug
 */
function todebugx($var, $type) {
    global $todebug;
    return $todebug->logAs($var, $type);
}

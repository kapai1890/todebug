<?php

/*
 * All this functions will force the plugin to enable or disable different types
 * of requests, no matter what the settings are.
 */

namespace todebug;

/**
 * Clear all logged messages in admin bar.
 *
 * @global \todebug\Plugin $todebug
 */
function clear()
{
    global $todebug;
    $todebug->clearLogs();
}

/**
 * Manually enable logs for all types of request.
 */
function on()
{
    reset();
    add_filter('todebug/enable-channel/general', '__return_true');
    add_filter('todebug/enable-channel/ajax', '__return_true');
    add_filter('todebug/enable-channel/cron', '__return_true');
}

/**
 * Manually disable logs for all types of request.
 */
function off()
{
    reset();
    add_filter('todebug/enable-channel/general', '__return_false');
    add_filter('todebug/enable-channel/ajax', '__return_false');
    add_filter('todebug/enable-channel/cron', '__return_false');
}

/**
 * Manually enable logs only for general requests.
 */
function log()
{
    reset('general');
    add_filter('todebug/enable-channel/general', '__return_true');
}

/**
 * Manually disable logs for general requests.
 */
function nologs()
{
    reset('general');
    add_filter('todebug/enable-channel/general', '__return_false');
}

/**
 * Manually enable logs only for AJAX requests.
 */
function ajax()
{
    reset('ajax');
    add_filter('todebug/enable-channel/ajax', '__return_true');
}

/**
 * Manually disable logs for AJAX requests.
 */
function noajax()
{
    reset('ajax');
    add_filter('todebug/enable-channel/ajax', '__return_false');
}

/**
 * Manually enable logs only for cron requests.
 */
function cron()
{
    reset('cron');
    add_filter('todebug/enable-channel/cron', '__return_true');
}

/**
 * Manually disable logs for cron requests.
 */
function nocron()
{
    reset('cron');
    add_filter('todebug/enable-channel/cron', '__return_false');
}

/**
 * Reset all manually enabled/disabled requests.
 *
 * @param string|array $requests Optional. Request types to reset. All by default.
 *                               Available request types: "general", "ajax" and "cron".
 */
function reset($requests = ['general', 'ajax', 'cron'])
{
    if (is_string($requests)) {
        $requests = [$requests];
    }

    // Remove possible filters
    foreach ($requests as $requestType) {
        if (has_filter("todebug/enable-channel/{$requestType}", '__return_true')) {
            remove_filter("todebug/enable-channel/{$requestType}", '__return_true');
        }
        if (has_filter("todebug/enable-channel/{$requestType}", '__return_false')) {
            remove_filter("todebug/enable-channel/{$requestType}", '__return_false');
        }
    }
}

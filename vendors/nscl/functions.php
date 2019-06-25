<?php

if (!function_exists('regex_match')) {
    /**
     * Searches for a value in the subject string by passed pattern.
     *
     * @param string $pattern
     * @param string $subject
     * @param mixed $default Return value if nothing found.
     * @param int $index The index of the result group.
     * @return mixed The matched or default value.
     */
    function regex_match($pattern, $subject, $default = '', $index = 0)
    {
        preg_match($pattern, $subject, $matches);
        return isset($matches[$index]) ? $matches[$index] : $default;
    }
}

<?php

declare(strict_types = 1);

namespace todebug\utils;

class RegexUtil
{
    /**
     * Combine results of two groups into single array.
     *
     * @param string $pattern
     * @param string $subject
     * @param int $keyIndex If there are no such index in matched array, then
     *                      the result will be a numeric array with appropriate
     *                      values.
     * @param int $valueIndex If there are no such index in matched array, then
     *                        the result will be an array with appropriate keys,
     *                        but with empty null values.
     *
     * @return array
     */
    public static function combine(string $pattern, string $subject, int $keyIndex = -1, int $valueIndex = 0): array
    {
        $count  = (int)preg_match_all($pattern, $subject, $matches);
        $keys   = $matches[$keyIndex] ?? [];
        $values = $matches[$valueIndex] ?? array_fill(0, $count, null);

        if (!empty($keys)) {
            return array_combine($keys, $values);
        } else {
            return $values;
        }
    }
}

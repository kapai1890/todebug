<?php

declare(strict_types = 1);

namespace todebug;

function is_0ton_array(array $array): bool
{
    $searchedIndex = 0;

    foreach (array_keys($array) as $index) {
        if ($index !== $searchedIndex) {
            return false;
        }

        $searchedIndex++;
    }

    return true;
}

/**
 * Returns canonicalized absolute pathname.
 *
 * @param string $path
 * @return string
 */
function real_path(string $path): string
{
    $real = realpath($path);

    if ($real === false) {
        // If you want something done right, do it yourself
        // Remove "."
        $real = preg_replace('/\.(\/)?/', '', $real);
        // Remove ".."
        $real = preg_replace('/[^\/]+[\/]\.\.(\/)?/', '', $real);
        // Remove ".." from the beginning of the string
        $real = preg_replace('/^\.\.[\/]?/', '', $real);
    }

    return $real;
}

/**
 * Combine results of two subpatterns into single array.
 *
 * @param string $pattern
 * @param string $subject
 * @param int $keyIndex If there are no such index in matches then the result
 *                      will be a numeric array with appropriate values.
 * @param int $valueIndex If there are no such index in matches then the result
 *                        will be an array with appropriate keys but with empty
 *                        values (empty strings "").
 * @return array
 */
function regex_combine(string $pattern, string $subject, int $keyIndex = -1, int $valueIndex = 0): array
{
    $count  = (int)preg_match_all($pattern, $subject, $matches);
    $keys   = ($matches[$keyIndex] ?? []);
    $values = ($matches[$valueIndex] ?? array_fill(0, $count, ''));

    if (!empty($values) && !empty($keys)) {
        return array_combine($keys, $values);
    } else {
        // Only $keys can be empty at this point (because we used array_fill()
        // for values)
        return $values;
    }
}

/**
 * Searches for a value in the subject string by passed pattern.
 *
 * @param string $pattern
 * @param string $subject
 * @param mixed $default Return value if nothing found.
 * @param int $index The index of the result group.
 * @return mixed The matched or default value.
 */
function regex_match(string $pattern, string $subject, $default = '', int $index = 0)
{
    preg_match($pattern, $subject, $matches);
    return ($matches[$index] ?? $default);
}

/**
 * @param string $pattern
 * @param string $subject
 * @param int $index The index of the result group.
 * @return bool
 */
function regex_test(string $pattern, string $subject, int $index = 0): bool
{
    $found = preg_match($pattern, $subject, $matches);
    return ($found && isset($matches[$index]));
}

/**
 * Add number starting from the most right position of the string.
 */
function strradd(string $str, int $add = 1): string
{
    for ($i = strlen($str) - 1; $i >= 0; $i--) {
        if ($str[$i] == '.' || $str[$i] == ',') {
            continue;
        }

        $sum  = (int)$str[$i] + $add;
        $tens = floor($sum / 10);

        $str[$i] = $sum - ($tens * 10);

        $add = $tens;

        if ($add == 0) {
            break;
        }
    }

    if ($add != 0) {
        $str = $add . $str;
    }

    return $str;
}

<?php

declare(strict_types = 1);

namespace todebug\utils;

class ArrayUtil
{
    public static function isNumeric0ToN(array $array): bool
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
}

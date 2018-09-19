<?php

declare(strict_types = 1);

namespace todebug;

class Stringify
{
    const INDENT = '    ';

    protected static $currentIndent = '';

    protected static $isRenderingObject = false;

    /**
     * @param mixed $var
     * @return string
     */
    public static function stringify($var): string
    {
        $type = static::getType($var);
        return static::stringifyAs($var, $type);
    }

    /**
     * @param mixed $var
     * @param string $type
     * @return string
     */
    public static function stringifyAs($var, string $type): string
    {
        $method = 'stringify' . ucfirst($type);

        if (method_exists(__CLASS__, $method)) {
            return static::$method($var);
        } else {
            return static::stringifyUndefined($var);
        }
    }

    public static function getType($var): string
    {
        $type = strtolower(gettype($var));

        // Generalize or change the name of some types
        switch ($type) {
            case 'boolean':
                $type = 'bool';
                break;

            case 'integer':
                $type = 'int';
                break;

            case 'double':
                $type = 'float';
                break;

            case 'array':
                if (is_callable($var)) {
                    // $var = [%Object or class%, %Method name%]
                    $type = 'method';
                } else if (!is_0ton_array($var)) {
                    $type = 'hashmap';
                }
                break;

            case 'object':
                if (is_callable($var)) {
                    $type = 'closure';
                } else if ($var instanceof \DateTime) {
                    $type = 'date';
                }
                break;

            case 'callable':
                if (is_string($var)) {
                    $type = 'function';
                } else {
                    $type = 'closure';
                }
                break;
        }

        return $type;
    }

    public static function stringifyBool(bool $value): string
    {
        return ($value ? 'true' : 'false');
    }

    public static function stringifyInt(int $number): string
    {
        return (string)$number;
    }

    public static function stringifyFloat(float $number): string
    {
        // 14 - the approximate maximum number of decimal digits in PHP
        $string  = number_format($number, 14, '.', '');
        $decimal = strstr($string, '.');

        if ($decimal === false) {
            // The $number is integer and does not have a decimal part
            return $string;
        }

        $integer = strstr($string, '.', true);
        $decimal = substr($decimal, 1); // Remove comma
        $radd    = 0; // The number we need to add to the most right position

        if (regex_test('/9+\d$/', $decimal)) {
            // Trim suffix like "557.05999999999995"
            $decimal = preg_replace('/9+\d$/', '', $decimal);
            $radd = 1;
        } else {
            // Try to trim suffix like "557.07000000000005"
            $decimal = preg_replace('/0+\d$/', '', $decimal);
        }

        // Trim ending zeros
        $decimal = rtrim($decimal, '0');

        $string = !empty($decimal) ? $integer . '.' . $decimal : $integer;

        if ($radd > 0) {
            $string = strradd($string, $radd);
        }

        return $string;
    }

    public static function stringifyString(string $string): string
    {
        return '"' . $string . '"';
    }

    public static function stringifyArray(array $array): string
    {
        $values = array_map([__CLASS__, 'stringify'], $array);
        return static::stringifyArrayValues($values);
    }

    public static function stringifyHashmap(array $map): string
    {
        $values = array_map(function ($key, $value) {
            return static::stringify($key) . ' => ' . static::stringify($value);
        }, array_keys($map), $map);

        return static::stringifyArrayValues($values);
    }

    public static function stringifyIterable(iterable $iterable): string
    {
        $values = [];

        foreach ($iterable as $value) {
            $values[] = static::stringify($value);
        }

        $string = static::stringifyArrayValues($values);
        // Remove wrapping square brackets
        $string = preg_replace('/^\[|\]$/', '', $string);
        $string = '{' . $string . '}';

        return $string;
    }

    public static function stringifyArrayValues(array $values): string
    {
        $result = '[' . implode(', ', $values) . ']';

        if (strlen($result) + strlen(static::$currentIndent) > 120) {
            $indent = static::increaseIndent();

            // Increase indent in multiline elements (like object strings)
            $values = array_map([__CLASS__, 'increaseIndentIn'], $values);

            $result = '[' . PHP_EOL;
                $result .= $indent . implode(',' . PHP_EOL . $indent, $values);
            $result .= PHP_EOL . ']';

            static::decreaseIndent();
        }

        return $result;
    }

    /**
     * @param array $arguments ["required" => ["x"], "optional" => ["y"]]
     * @return string "x[, y]"
     */
    public static function stringifyArguments(array $arguments): string
    {
        $required = $arguments['required'];
        $optional = $arguments['optional'];

        // "x"
        $arguments = implode(', ', $required);

        if (!empty($optional)) {
            // "x["
            $arguments .= '[';

            // "x[, "
            if (!empty($required)) {
                $arguments .= ', ';
            }

            // "x[, y"
            $arguments .= implode(', ', $optional);

            // "x[, y]"
            $arguments .= ']';
        }

        return $arguments;
    }

    public static function stringifyClosure(\Closure $closure): string
    {
        $reflection = Reflection::reflectClosure($closure);
        $arguments  = static::stringifyArguments($reflection['arguments']);

        return 'function (' . $arguments . ') { ... }';
    }

    public static function stringifyFunction(string $function): string
    {
        $reflection = Reflection::reflectFunction($function);
        $name       = $reflection['name'];
        $arguments  = static::stringifyArguments($reflection['arguments']);

        return 'function ' . $name . '(' . $arguments . ') { ... }';
    }

    public static function stringifyCallback(array $callback): string
    {
        $reflection = Reflection::reflectCallback($callback);
        $name       = $reflection['name'];
        $arguments  = static::stringifyArguments($reflection['arguments']);

        return $name . '(' . $arguments . ') { ... }';
    }

    /**
     * @param array $visibility ["level" => public|protected|private,
     *                          bool "is-abstract", bool "is-final", bool "is-static"]
     * @return string "final public static"
     */
    public static function stringifyVisibility(array $visibility): string
    {
        $level    = $visibility['level'];
        $isFinal  = $visibility['is-final'];
        $isStatic = $visibility['is-static'];

        // Don't check "is-abstract" - created instance can't hold abstract properties/methods
        $visibility = '';

        // "final "
        if ($isFinal) {
            $visibility .= 'final ';
        }

        // "final public "
        $visibility .= $level . ' ';

        // "final public static "
        if ($isStatic) {
            $visibility .= 'static ';
        }

        return rtrim($visibility);
    }

    /**
     * @param array $constants ["name", "value"]
     * @return string
     */
    public static function stringifyConstants(array $constants): string
    {
        $indent = static::increaseIndent();

        $constants = array_map(function ($constant) use ($indent) {
            $name  = $constant['name'];
            $value = static::stringify($constant['value']);

            return $indent . sprintf('const %s = %s;', $name, $value);
        }, $constants);

        static::decreaseIndent();

        return implode(PHP_EOL, $constants);
    }

    /**
     * @param array $properties ["name", "value", "visibility" => ["level" => public|protected|private,
     *                          bool "is-abstract", bool "is-final", bool "is-static"]]
     * @return string
     */
    public static function stringifyProperties(array $properties): string
    {
        $indent = static::increaseIndent();

        $properties = array_map(function ($property) use ($indent) {
            $name       = $property['name'];
            $value      = $property['value'];
            $visibility = static::stringifyVisibility($property['visibility']);

            // "final public static $x"
            $property = $indent . $visibility . ' $' . $name;

            // "final public static $x = 5"
            if (!is_null($value)) {
                $value = static::stringify($value);
                $value = static::increaseIndentIn($value);

                $property .= ' = ' . $value;
            }

            // "final public static $x = 5;"
            $property .= ';';

            return $property;
        }, $properties);

        static::decreaseIndent();

        return implode(PHP_EOL, $properties);
    }

    /**
     * @param array $methods ["name", "arguments" => ["required", "optional"],
     *                       "visibility" => ["level", "is-abstract", "is-final", "is-static"]]
     * @return string
     */
    public static function stringifyMethods(array $methods): string
    {
        $indent = static::increaseIndent();

        $methods = array_map(function ($method) use ($indent) {
            $name       = $method['name'];
            $arguments  = static::stringifyArguments($method['arguments']);
            $visibility = static::stringifyVisibility($method['visibility']);

            // "final public static function f"
            $method = $indent . $visibility . ' function ' . $name;
            // "final public static function f(x[, y]) { ... }"
            $method .= '(' . $arguments . ') { ... }';

            return $method;
        }, $methods);

        static::decreaseIndent();

        return implode(PHP_EOL, $methods);
    }

    public static function stringifyObject($object): string
    {
        // No infinite ercursions
        if (static::$isRenderingObject) {
            return '{%Instance of ' . get_class($object) . '%}';
        }

        // Temporarily forbid objects render to prevent excessive nesting and
        // infinite loops
        static::$isRenderingObject = true;

        /**
         * @var array ["name", array "visibility", string[] "implements",
         *            array "constants", array "properties", array "methods"]
         */
        $reflection = Reflection::reflectObject($object);

        // "final class X"
        $declaration = ($reflection['visibility']['is-final'] ? 'final class ' : 'class ') . $reflection['name'];

        // "final class X implements Y, Z"
        if (!empty($reflection['implements'])) {
            $declaration .= ' implements ' . implode(', ', $reflection['implements']);
        }

        $constants  = static::stringifyConstants($reflection['constants']);
        $properties = static::stringifyProperties($reflection['properties']);
        $methods    = static::stringifyMethods($reflection['methods']);

        $beforeConstants  = '';
        $beforeProperties = (!empty($properties) && !empty($constants)) ? PHP_EOL : '';
        $beforeMethods    = (!empty($methods) && (!empty($constants) || !empty($properties))) ? PHP_EOL : '';

        $afterConstants  = !empty($constants) ? PHP_EOL : '';
        $afterProperties = !empty($properties) ? PHP_EOL : '';
        $afterMethods    = !empty($methods) ? PHP_EOL : '';

        $string  = $declaration . PHP_EOL;
        $string .= '{' . PHP_EOL;
        $string .= $beforeConstants . $constants . $afterConstants;
        $string .= $beforeProperties . $properties . $afterProperties;
        $string .= $beforeMethods . $methods . $afterMethods;
        $string .= '}';

        // Restore the old value
        static::$isRenderingObject = false;

        return $string;
    }

    public static function stringifyResource($resource): string
    {
        return static::stringifyUndefined($resource);
    }

    public static function stringifyNull($_ = null): string
    {
        return 'null';
    }

    public static function stringifyUndefined($var): string
    {
        $string = print_r($var, true);
        $string = trim($string);
        $string = preg_replace('/\s+/', ' ', $string);

        return $string;
    }

    public static function stringifyDate(\DateTime $date): string
    {
        return $date->format('{j F, Y (Y-m-d)}'); // {31 December, 2017 (2017-12-31)}
    }

    protected static function increaseIndent(): string
    {
        static::$currentIndent .= static::INDENT;
        return static::$currentIndent;
    }

    public static function increaseIndentIn(string $value): string
    {
        // Increase indent in multiline elements (like object strings)
        $value = preg_replace('/\n([^\n]*)/', "\n" . static::$currentIndent . '$1', $value);
        return $value;
    }

    protected static function decreaseIndent(): string
    {
        static::$currentIndent = substr( static::$currentIndent, 0, -(strlen(static::INDENT)) );
        return static::$currentIndent;
    }
}

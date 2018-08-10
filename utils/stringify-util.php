<?php

declare(strict_types = 1);

namespace todebug\utils;

class StringifyUtil
{
    const INDENT = '    ';
    const DEFAULT_INLINE_ARRAY_LENGTH = 3;

    public static $maxInlineArrayLength = -1; // Here will be custom value or
                                              // DEFAULT_INLINE_ARRAY_LENGTH

    private static $forbidObjectsRender = false;

    /**
     * @param mixed $var
     *
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
     *
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
                    $type = 'method';
                } else if (!ArrayUtil::isNumeric0ToN($var)) {
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
        $text = number_format($number, 3, '.', '');

        // Remove zeros from the end. But don't use rtrim($text, ".0") or
        // rtrim($text, "0") with rtrim($text, "."), it will also remove zeros
        // on the left side of decimal point. For example, "100.000" will become
        // "1" and "0.000" will become "" with first variant, and "100" become
        // "1" with second variant
        return preg_replace('/\.0+$|(\.\d*[1-9])0+$/', '$1', $text);
    }

    public static function stringifyString(string $string): string
    {
        return '"' . $string . '"';
    }

    public static function stringifyArray(array $array, $indent = self::INDENT): string
    {
        $values = array_map([__CLASS__, 'stringify'], $array);
        return static::stringifyArrayValues($values, $indent);
    }

    public static function stringifyArrayValues(array $values, $indent = self::INDENT): string
    {
        if (count($values) <= static::maxInlineArrayLength()) {
            return '[' . implode(', ', $values) . ']';
        } else {
            $values = array_map(['\todebug\utils\StringifyUtil', 'increaseIndent'], $values);
            return '[' . PHP_EOL
                . $indent . implode(',' . PHP_EOL . $indent, $values)
            . PHP_EOL . ']';
        }
    }

    public static function stringifyHashmap(array $map, $indent = self::INDENT): string
    {
        $values = array_map(function ($key, $value) {
            return static::stringify($key) . ' => ' . static::stringify($value);
        }, array_keys($map), $map);

        return static::stringifyArrayValues($values, $indent);
    }

    public static function stringifyObject($instance, $indent = self::INDENT): string
    {
        // No infinite recursions
        if ( static::$forbidObjectsRender ) {
            return '{%Instance of ' . get_class($instance) . '%}';
        }

        // Temporarily forbid objects render to prevent excessive nesting and
        // infinite loops
        static::$forbidObjectsRender = true;

        $class       = new \ReflectionClass($instance);
        $declaration = ($class->isFinal() ? 'final class ' : 'class ') . $class->getName();
        $constants   = static::stringifyClassConstants($class->getConstants(), $indent);
        $properties  = static::stringifyClassProperties($class->getProperties(), $instance, $indent);
        $methods     = static::stringifyClassMethods($class->getMethods(), $instance, $indent);

        $interfaces = $class->getInterfaceNames();
        if (!empty($interfaces)) {
            $declaration .= ' implements ' . implode(', ', $interfaces);
        }

        $beforeConstants  = '';
        $beforeProperties = (!empty($properties) && !empty($constants) ? PHP_EOL : '');
        $beforeMethods    = (!empty($methods) && (!empty($constants) || !empty($properties)) ? PHP_EOL : '');

        $afterConstants  = (!empty($constants) ? PHP_EOL : '');
        $afterMethods    = (!empty($methods) ? PHP_EOL : '');
        $afterProperties = (!empty($properties) ? PHP_EOL : '');

        $text = '';

        $text .= $declaration . PHP_EOL;
        $text .= '{' . PHP_EOL;
        $text .= $beforeConstants . $constants . $afterConstants;
        $text .= $beforeProperties . $properties . $afterProperties;
        $text .= $beforeMethods . $methods . $afterMethods;
        $text .= '}';

        // Restore the old value
        static::$forbidObjectsRender = false;

        return $text;
    }

    public static function stringifyDate(\DateTime $date): string
    {
        return $date->format('{j F, Y (Y-m-d)}'); // {31 December, 2017 (2017-12-31)}
    }

    public static function stringifyIterable(iterable $iterable): string
    {
        $values = [];

        foreach ($iterable as $value) {
            $values[] = static::stringify($value);
        }

        return '{' . implode(', ', $values) . '}';
    }

    public static function stringifyFunction(string $functionName): string
    {
        $function = new \ReflectionFunction($functionName);
        $params   = static::stringifyParams($function->getClosure());

        return 'function ' . $functionName . '(' . $params . ') { ... }';
    }

    public static function stringifyMethod(array $callback): string
    {
        $class  = (is_object($callback[0]) ? get_class($callback[0]) : $callback[0]);
        $method = $callback[1];

        $function = new \ReflectionMethod($class, $method);
        $params   = static::stringifyParams($function->getClosure($callback[0]));

        return $class . '::' . $method . '(' . $params . ') { ... }';
    }

    public static function stringifyClosure(\Closure $closure): string
    {
        $params = static::stringifyParams($closure);
        return 'function (' . $params . ') { ... }';
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
        $text = print_r($var, true);
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text);

        return $text;
    }

    public static function stringifyParams(\Closure $closure): string
    {
        // $params = "Closure Object( [parameter] => Array ( [$x] => <required> [$y] => <optional> ... ) )"
        $params = print_r($closure, true);
        // $params = ["$x" => "required", "$y" => "optional", ...]
        $params = RegexUtil::combine('/\[(\$\w+)\] => <(\w+)>/', $params, 1, 2); // Pattern: "[\w+] => <\w+>"

        $requiredParams = [];
        $optionalParams = [];

        foreach ($params as $name => $status) {
            if ($status == 'required') {
                $requiredParams[] = $name;
            } else {
                $optionalParams[] = $name;
            }
        }

        // $text = "$x"
        $text = implode(', ', $requiredParams);

        // $text = "$x[, $y]"
        if (!empty($optionalParams)) {
            $text .= '[' . (!empty($requiredParams) ? ', ' : '') . implode(', ', $optionalParams) . ']';
        }

        return $text;
    }

    public static function stringifyVisibility(\Reflector $reflection)
    {
        $text = '';

        if ($reflection->isPrivate()) {
            $text .= 'private';
        } else if ($reflection->isProtected()) {
            $text .= 'protected';
        } else {
            $text .= 'public';
        }

        if (method_exists($reflection, 'isAbstract') && $reflection->isAbstract()) {
            $text = 'abstract ' . $text;
        } else if (method_exists($reflection, 'isFinal') && $reflection->isFinal()) {
            $text = 'final ' . $text;
        }

        if ($reflection->isStatic()) {
            $text .= ' static';
        }

        return $text;
    }

    /**
     * @param array $constants
     * @param string $indent
     *
     * @return string
     */
    public static function stringifyClassConstants(array $constants, string $indent = ''): string
    {
        array_walk($constants, function (&$value, $name) use ($indent) {
            $value = static::stringify($value);
            $value = $indent . sprintf('const %1$s = %2$s;', $name, $value);
        });

        return implode(PHP_EOL, $constants);
    }

    /**
     * @param \ReflectionProperty[] $properties
     * @param mixed $instance
     * @param string $indent
     *
     * @return string
     */
    public static function stringifyClassProperties(array $properties, $instance, string $indent = ''): string
    {
        $properties = array_map(function (\ReflectionProperty $property) use ($instance, $indent) {
            $visibility = static::stringifyVisibility($property);

            $text = $indent . $visibility . ' $' . $property->getName();

            // Try to print the value
            $property->setAccessible(true); // For protected and private properties
            $value = $property->getValue($instance);
            if (!is_null($value)) {
                $value = static::stringify($value);
                $value = static::increaseIndent($value);
                $text .= ' = ' . $value;
            }

            $text .= ';';

            return $text;
        }, $properties);

        return implode(PHP_EOL, $properties);
    }

    /**
     * @param \ReflectionMethod[] $methods
     * @param mixed $instance
     * @param string $indent
     *
     * @return string
     */
    public static function stringifyClassMethods(array $methods, $instance, string $indent = ''): string
    {
        $methods = array_map(function (\ReflectionMethod $method) use ($instance, $indent) {
            $visibility = static::stringifyVisibility($method);
            $params     = static::stringifyParams($method->getClosure($instance));

            return $indent . $visibility . ' function ' . $method->getName() . '(' . $params . ') { ... }';
        }, $methods);

        return implode(PHP_EOL, $methods);
    }

    public static function increaseIndent(string $value)
    {
        $value = preg_replace('/\n(\s*)/', "\n" . static::INDENT . '$1', $value);
        return $value;
    }

    public static function maxInlineArrayLength(): int
    {
        if ( static::$maxInlineArrayLength < 0 ) {
            static::$maxInlineArrayLength = static::DEFAULT_INLINE_ARRAY_LENGTH;
        }
        return static::$maxInlineArrayLength;
    }
}

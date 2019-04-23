<?php

namespace tostr;

class MessageBuilder
{
    /** @var \tostr\Reflector */
    protected $reflector;

    /** @var \tostr\Stringifier */
    protected $stringifier;

    /** @var string */
    protected $indent;

    public function __construct(Reflector $reflector, Stringifier $stringifier, $indent = '    ')
    {
        $this->reflector = $reflector;
        $this->stringifier = $stringifier;
        $this->indent = $indent;
    }

    /**
     * @param mixed[] $vars
     * @param int $maxDepth
     * @return string
     */
    public function buildMessage(array $vars, $maxDepth)
    {
        $strings = [];

        foreach ($vars as $var) {
            $type = typeof($var);
            $strings[] = $this->buildString($var, $type, 1, $maxDepth);
        }

        $message = implode(' ', $strings) . PHP_EOL;

        return $message;
    }

    /**
     * @param mixed $var
     * @param string $type
     * @param int $maxDepth
     * @return string
     */
    public function buildAs($var, $type, $maxDepth)
    {
        return $this->buildString($var, $type, 1, $maxDepth);
    }

    /**
     * @param mixed $var
     * @param string $type
     * @param int $depth
     * @param int $maxDepth
     * @return string
     */
    protected function buildString($var, $type, $depth, $maxDepth)
    {
        if ($depth < $maxDepth) {
            if ($type == 'array') {
                return $this->buildArrayString($var, $depth, $maxDepth);
            } else if ($type == 'iterable') {
                return $this->buildIterableString($var, $depth, $maxDepth);
            } else if ($type == 'object') {
                return $this->buildObjectString($var, $depth, $maxDepth);
            }
        }

        // Max depth or type not is array|iterable|object
        return $this->stringifier->stringifyAs($var, $type);
    }

    /**
     * Stringify all the items in the array.
     *
     * @param array $values
     * @param int $depth
     * @param int $maxDepth
     * @return string
     */
    protected function buildArrayString(array $values, $depth, $maxDepth) {
        $strings   = [];
        $isHashmap = !is_numeric_natural_array($values);

        foreach ($values as $index => $value) {
            $type   = typeof($value);
            $string = $this->buildString($value, $type, $depth + 1, $maxDepth);

            if ($isHashmap) {
                // Also stringify key/index
                $string = $this->stringifier->stringify($index) . ' => ' . $string;
            }

            $strings[] = $string;
        }

        $result = $this->concatenateStrings($strings);

        return '[' . $result . ']';
    }

    /**
     * Stringify all the items in the iterable object.
     *
     * @param mixed $values
     * @param int $depth
     * @param int $maxDepth
     * @return string
     */
    protected function buildIterableString($values, $depth, $maxDepth)
    {
        $strings = [];

        foreach ($values as $value) {
            $type = typeof($value);
            $strings[] = $this->buildString($value, $type, $depth + 1, $maxDepth);
        }

        $result = $this->concatenateStrings($strings);

        return '{' . $results . '}';
    }

    /**
     * Concatenate all values of the array/iterable into one-line or multiline
     * string.
     *
     * @param array $strings
     * @return string
     */
    protected function concatenateStrings($strings)
    {
        $result = implode(', ', $strings);

        if (strlen($result) > 100) { // ~ 120 (the soft limit in programming) - brackets - some indent
            // Increase indents in multiline elements
            $strings = array_map([$this, 'increaseIndent'], $strings);

            // Place each value on new line
            $result = PHP_EOL . $this->indent;
            $result .= implode(',' . PHP_EOL . $this->indent, $strings);
            $result .= PHP_EOL;
        }

        return $result;
    }

    /**
     * Build object string with all its constants, properties and methods.
     *
     * @param mixed $object
     * @param int $depth
     * @param int $maxDepth
     * @return string
     */
    protected function buildObjectString($object, $depth, $maxDepth)
    {
        $reflection = $this->reflector->reflectObject($object);

        if ($depth <= ($maxDepth - 2)) {
            // If we have 2+ more levels, then stringify the values of the
            // constants and properties properly
            $this->preprocessObjectChildren($reflection['constants'], $depth, $maxDepth);
            $this->preprocessObjectChildren($reflection['properties'], $depth, $maxDepth);
        }

        return $this->stringifier->stringifyRefobject($reflection);
    }

    /**
     * Convert nested arrays/iterable objects/objects.
     *
     * @param array $children
     * @param int $depth
     * @param int $maxDepth
     */
    protected function preprocessObjectChildren(&$children, $depth, $maxDepth)
    {
        foreach ($children as &$child) {
            $type = typeof($child['value']);

            if (in_array($type, ['array', 'iterable', 'object'])) {
                $stringValue = $this->buildString($child['value'], $type, $depth + 1, $maxDepth);
                $stringValue = $this->increaseIndent($stringValue);

                $child['value'] = new AsIs($stringValue);
            }
        }

        unset($child);
    }

    /**
     * Increase indents in multiline elements (like objects).
     *
     * @param string $value
     * @return string
     */
    public function increaseIndent($value)
    {
        return preg_replace('/\n([^\n]*)/', "\n" . $this->indent . '$1', $value);
    }
}

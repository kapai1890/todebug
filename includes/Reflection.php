<?php

declare(strict_types = 1);

namespace todebug;

class Reflection
{
    /**
     * @param \Closure $closure
     * @return array ["required" => ["x"], "optional" => ["y"]]
     */
    public static function reflectArguments(\Closure $closure): array
    {
        // $arguments = "Closure Object( [parameter] => Array ( [$x] => <required> [$y] => <optional> ... ) )"
        $args = print_r($closure, true);
        // $arguments = ["x" => "required", "y" => "optional", ...]
        $args = regex_combine('/\[\$(\w+)\] => <(\w+)>/', $args, 1, 2);

        $arguments = ['required' => [], 'optional' => []];

        foreach ($args as $name => $status) {
            $arguments[$status][] = $name;
        }

        return $arguments;
    }

    /**
     * @param \Closure $closure
     * @return array ["name", "arguments" => ["required", "optional"]]
     */
    public static function reflectClosure(\Closure $closure): array
    {
        $arguments = static::reflectArguments($closure);

        return ['name' => '', 'arguments' => $arguments];
    }

    /**
     * @param string $function
     * @return array ["name", "arguments" => ["required", "optional"]]
     */
    public static function reflectFunction(string $function): array
    {
        $reflection = new \ReflectionFunction($function);
        $arguments  = static::reflectArguments($reflection->getClosure());

        return ['name' => $function, 'arguments' => $arguments];
    }

    /**
     * Reflection callback like [%Class or object%, %Method name%].
     *
     * @param array $callback
     * @return array ["name", "arguments" => ["required", "optional"]], where
     *               <b>name</b> is <i>Class::method</i>.
     */
    public static function reflectCallback(array $callback): array
    {
        $holder     = $callback[0];
        $class      = is_object($holder) ? get_class($holder) : $holder;
        $method     = $callback[1];
        $reflection = new \ReflectionMethod($class, $method);
        $arguments  = static::reflectArguments($reflection->getClosure($holder));

        return ['name' => $class . '::' . $method, 'arguments' => $arguments];
    }

    /**
     * Reflect visibility settings of the class property/method.
     *
     * @param \Reflector $reflection
     * @return array ["level" => public|protected|private, bool "is-abstract",
     *               bool "is-final", bool "is-static"]
     */
    public static function reflectVisibility(\Reflector $reflection)
    {
        $visibility = [
            'level'       => 'public',
            'is-abstract' => false,
            'is-final'    => false,
            'is-static'   => false
        ];

        if ($reflection->isPrivate()) {
            $visibility['level'] = 'private';
        } else if ($reflection->isProtected()) {
            $visibility['level'] = 'protected';
        }

        if (method_exists($reflection, 'isAbstract') && $reflection->isAbstract()) {
            $visibility['is-abstract'] = true;
        } else if (method_exists($reflection, 'isFinal') && $reflection->isFinal()) {
            $visibility['is-final'] = true;
        }

        if ($reflection->isStatic()) {
            $visibility['is-static'] = true;
        }

        return $visibility;
    }

    /**
     * Reflect class constants.
     *
     * @param mixed $object An instance of any class.
     * @return array ["name", "value"]
     */
    public static function reflectConstants($object)
    {
        $class = new \ReflectionClass($object);
        $const = $class->getConstants();

        $constants = [];

        foreach ($const as $name => $value) {
            $constants[] = ['name' => $name, 'value' => $value];
        }

        return $constants;
    }

    /**
     * Reflect class properies (fields).
     *
     * @param mixed $object An instance of any class.
     * @return array ["name", "value", "visibility" => [...]]
     */
    public static function reflectProperties($object): array
    {
        $class = new \ReflectionClass($object);
        $props = $class->getProperties();

        $properties = [];

        foreach ($props as $prop) { // Type of $prop is \ReflectionProperty
            $visibility = static::reflectVisibility($prop);

            // Set the property accessible to get the value of private and
            // protected properties
            $prop->setAccessible(true);
            $value = $prop->getValue($object);

            $properties[$prop->getName()] = [
                'name'       => $prop->getName(),
                'value'      => $value,
                'visibility' => $visibility
            ];
        }

        // Get also properties added as "$stdClass->newProperty = ...;"
        foreach ($object as $property => $value) {
            if (!array_key_exists($property, $properties)) {
                $properties[$property] = [
                    'name'       => $property,
                    'value'      => $value,
                    'visibility' => [
                        'level'       => 'public',
                        'is-final'    => false,
                        'is-abstract' => false,
                        'is-static'   => false
                    ]
                ];
            }
        }

        return $properties;
    }

    /**
     * Reflect class methods.
     *
     * @param mixed $object An instance of any class.
     * @return array ["name", "arguments" => [...], "visibility" => [...]]
     */
    public static function reflectMethods($object): array
    {
        $class    = new \ReflectionClass($object);
        $_methods = $class->getMethods();

        $methods = [];

        foreach ($_methods as $method) { // Type of $method is \ReflectionMethod
            $visibility = static::reflectVisibility($method);
            $arguments  = static::reflectArguments($method->getClosure($object));

            $methods[] = [
                'name'       => $method->getName(),
                'arguments'  => $arguments,
                'visibility' => $visibility
            ];
        }

        return $methods;
    }

    /**
     * @param mixed $object An instance of any class.
     * @return array ["name", array "visibility", string[] "implements",
     *               array "constants", array "properties", array "methods"]
     */
    public static function reflectObject($object)
    {
        $class = new \ReflectionClass($object);

        $constants  = static::reflectConstants($object);
        $properties = static::reflectProperties($object);
        $methods    = static::reflectMethods($object);

        return [
            'name'       => $class->getName(),
            'visibility' => [
                'level'       => 'public',
                // It can't be abstract - it's already an instance of
                // non-abstract class
                'is-abstract' => false,
                'is-final'    => $class->isFinal(),
                'is-static'   => false
            ],
            'implements' => $class->getInterfaceNames(),
            'constants'  => $constants,
            'properties' => $properties,
            'methods'    => $methods
        ];
    }
}

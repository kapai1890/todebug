<?php

namespace todebug;

use tostr\MessageBuilder as BasicMessageBuilder;
use tostr\Reflector;

use todebug\Stringifier;

class MessageBuilder extends BasicMessageBuilder
{
    protected $defaultRecursiveClasses = ['stdClass'];

    /**
     * Updates on any call of method buildObjectsHierarchy();
     *
     * @var array
     */
    protected $recursiveClasses = [];

    protected $isObjectsHierarchyMode = false;

    /**
     * @param mixed $var Any object.
     * @param int $maxDepth
     * @return string
     */
    public function buildObjectsHierarchy($var, $maxDepth)
    {
        $type = is_object($var) ? 'object' : \tostr\typeof($var);

        $this->recursiveClasses = apply_filters('todebug/message-builder/recursive-classes', $this->defaultRecursiveClasses);

        $this->isObjectsHierarchyMode = true;
        $string = $this->varToString($var, $type, 1, $maxDepth);
        $this->isObjectsHierarchyMode = false;

        return $string;
    }

    /**
     * @param string $currentClass
     * @param int $depth
     * @param int $maxDepth
     * @param string[] $parents Stringified classes on upper layers.
     * @return bool
     */
    protected function canGoDeeper($currentClass, $depth, $maxDepth, $parents)
    {
        if ($this->isObjectsHierarchyMode) {
            return !in_array($currentClass, $parents) || in_array($currentClass, $this->recursiveClasses);
        } else {
            return parent::canGoDeeper($currentClass, $depth, $maxDepth, $parents);
        }
    }
}

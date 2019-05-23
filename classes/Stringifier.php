<?php

namespace todebug;

class Stringifier extends \tostr\Stringifier
{
    /**
     * @param string $context
     * @param string $text
     * @return string
     */
    protected function translate($context, $text)
    {
        switch ($context) {
            // translators: %s - the name of the class
            case 'Instance of a class': return esc_html__('Instance of %s', 'todebug'); break;
            default: return parent::translate($context, $text); break;
        }
    }

    /**
     * @param string $context
     * @param string $singular
     * @param string $plural
     * @param int $n
     * @return string
     */
    protected function translatePlural($context, $singular, $plural, $n)
    {
        switch ($context) {
            // translators: %d - the number of items in the array
            case 'Array of n items': return esc_html(_n('Array of %d item', 'Array of %d items', $n, 'todebug')); break;
            // translators: %d - the number of items in the iterable object
            case 'Iterable with n items': return esc_html(_n('Iterable with %d item', 'Iterable with %d items', $n, 'todebug')); break;
            default: return parent::translatePlural($context, $singular, $plural, $n); break;
        }
    }
}

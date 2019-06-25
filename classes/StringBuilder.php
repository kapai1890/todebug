<?php

namespace todebug;

class StringBuilder extends \NSCL\ToStr\StringBuilder
{
    protected function getMaxDepth()
    {
        $defaultDepth = parent::getMaxDepth();

        $depth = get_option('todebug_max_depth', $defaultDepth);

        if ($depth !== '') {
            return (int)$depth;
        } else {
            return $defaultDepth;
        }
    }
}

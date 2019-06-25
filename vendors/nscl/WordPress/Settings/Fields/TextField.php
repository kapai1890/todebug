<?php

namespace NSCL\WordPress\Settings\Fields;

use NSCL\WordPress\Settings\SettingsField;

class TextField extends SettingsField
{
    protected function getDefaultArgs()
    {
        return array_merge(parent::getDefaultArgs(), [
            'size'        => 'regular', // tiny|small|regular|large or empty string
            'placeholder' => ''
        ]);
    }

    protected function displayInput()
    {
        $class       = $this->args['class'];
        $size        = $this->args['size'];
        $placeholder = $this->args['placeholder'];

        if (!empty($size)) {
            $class .= " {$size}-text";
        }

        echo '<input type="text" name="', esc_attr($this->name), '" id="', esc_attr($this->name), '" class="', esc_attr(trim($class)), '" value="', esc_attr($this->getValue()), '" placeholder="', esc_attr($placeholder), '" />';
    }
}

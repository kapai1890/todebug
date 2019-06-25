<?php

namespace NSCL\WordPress\Settings\Fields;

use NSCL\WordPress\Settings\SettingsField;

class CheckboxField extends SettingsField
{
    protected function getDefaultArgs()
    {
        return array_merge(parent::getDefaultArgs(), [
            'label' => ''
        ]);
    }

    protected function displayInput()
    {
        $class   = $this->args['class'];
        $label   = $this->args['label'];
        $checked = (bool)$this->getValue();

        echo '<label>';
            echo '<input type="checkbox" name="', esc_attr($this->name), '" id="', esc_attr($this->name), '" class="', esc_attr($class), '" value="1"', checked(true, $checked, false), ' />';
            // The label must be properly escaped __before__ passing to the
            // instance of the settings field
            echo ' ', $label;
        echo '</label>';
    }
}

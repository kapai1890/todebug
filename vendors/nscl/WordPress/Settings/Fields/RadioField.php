<?php

namespace NSCL\WordPress\Settings\Fields;

use NSCL\WordPress\Settings\SettingsField;

class RadioField extends MultioptionField
{
    protected function displayInput()
    {
        $class   = $this->args['class'];
        $options = $this->args['options'];
        $checked = $this->getSelectedValue();

        foreach ($options as $value => $label) {
            echo '<label>';
                // Don't put the id="$this->name" here, it will not be unique
                echo '<input type="radio" name="', esc_attr($this->name), '" class="', esc_attr($class), '" value="', esc_attr($value), '"', checked($checked, $value, false), ' />';
                // All labels must be properly escaped __before__ passing to the
                // instance of the settings field
                echo ' ', $label;
            echo '</label>';
            echo '<br />';
        }
    }
}

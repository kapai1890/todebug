<?php

namespace WordPress\Settings;

class MulticheckField extends MultivalueField
{
    protected function displayInput()
    {
        $class = $this->args['class'];
        $values = $this->getValue();

        foreach ($this->args['values'] as $value => $label) {
            $isChecked = in_array($value, $values);

            echo '<label>';
                echo '<input type="checkbox" name="', esc_attr($this->name), '[]" id="', esc_attr($this->name . '-' . $value), '" class="', esc_attr($class), '" value="', esc_attr($value), '" ', checked(true, $isChecked, false), ' />';
                // The label must be properly escaped __before__ passing to the
                // instance of the settings field
                echo ' ', $label;
            echo '</label>';
            echo '<br />';
        }
    }
}

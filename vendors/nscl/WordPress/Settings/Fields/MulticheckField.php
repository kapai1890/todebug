<?php

namespace NSCL\WordPress\Settings\Fields;

class MulticheckField extends MultivalueField
{
    protected function displayInput()
    {
        $class  = $this->args['class'];
        $values = $this->getValue();

        foreach ($this->args['values'] as $value => $label) {
            $isChecked = in_array($value, $values);

            echo '<label>';
                // Don't put the id="$this->name" here, it will not be unique
                echo '<input type="checkbox" name="', esc_attr($this->name), '[]" class="', esc_attr($class), '" value="', esc_attr($value), '" ', checked(true, $isChecked, false), ' />';
                // The label must be properly escaped __before__ passing to the
                // instance of the settings field
                echo ' ', $label;
            echo '</label>';
            echo '<br />';
        }
    }
}

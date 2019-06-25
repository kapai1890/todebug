<?php

namespace NSCL\WordPress\Settings\Fields;

class SelectField extends MultioptionField
{
    protected function displayInput()
    {
        $class    = $this->args['class'];
        $options  = $this->args['options'];
        $selected = $this->getSelectedValue();

        echo '<select name="', esc_attr($this->name), '" id="', esc_attr($this->name), '" class="', esc_attr($class), '">';

        foreach ($options as $value => $label) {
            echo '<option value="', esc_attr($value), '"', selected($selected, $value, false), '>';
                echo esc_html($label);
            echo '</option>';
        }

        echo '</select>';
    }
}

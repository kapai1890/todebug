<?php

namespace WordPress\Settings;

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
        $class = $this->args['class'];
        $label = $this->args['label'];

        echo '<label>';
            echo '<input type="checkbox" name="', esc_attr($this->name), '" id="', esc_attr($this->name), '" class="', esc_attr($class), '" value="', esc_attr($this->getValue()), '" />';
            // The label must be properly escaped __before__ passing to the
            // instance of the settings field
            echo ' ', $label;
        echo '</label>';
    }
}
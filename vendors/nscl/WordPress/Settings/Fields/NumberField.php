<?php

namespace NSCL\WordPress\Settings\Fields;

use NSCL\WordPress\Settings\SettingsField;

class NumberField extends SettingsField
{
    protected function getDefaultArgs()
    {
        return array_merge(parent::getDefaultArgs(), [
            'size'  => 'small', // tiny|small|regular|large or empty string
            'label' => '',
            'min'   => '',
            'max'   => '',
            'step'  => ''
        ]);
    }

    protected function displayInput()
    {
        $class = $this->args['class'];
        $size  = $this->args['size'];
        $label = $this->args['label'];
        $min   = $this->args['min'];
        $max   = $this->args['max'];
        $step  = $this->args['step'];

        if (!empty($size)) {
            $class .= " {$size}-text";
        }

        $controls = '';
        $controls .= $min  !== '' ? ' min="'  . esc_attr($min)  . '"' : '';
        $controls .= $max  !== '' ? ' max="'  . esc_attr($max)  . '"' : '';
        $controls .= $step !== '' ? ' step="' . esc_attr($step) . '"' : '';

        echo '<input type="number" name="', esc_attr($this->name), '" id="', esc_attr($this->name), '" class="', esc_attr(trim($class)), '" value="', esc_attr($this->getValue()), '"', $controls, ' />';

        if (!empty($label)) {
            // The label must be properly escaped __before__ passing to the
            // instance of the settings field
            echo ' ', $label;
        }
    }

    public function sanitizeValue($value)
    {
        $value = parent::sanitizeValue($value);

        if ($value === '') {
            return $value;
        }

        if ($this->type == 'number' || $this->type == 'integer') {
            $min = $this->args['min'];
            $max = $this->args['max'];

            if ($min !== '') {
                $value = max($min, $value);
            }

            if ($max !== '') {
                $value = min($value, $max);
            }
        }

        return $value;
    }
}

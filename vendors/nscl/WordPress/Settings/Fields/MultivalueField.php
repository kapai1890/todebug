<?php

namespace NSCL\WordPress\Settings\Fields;

use NSCL\WordPress\Settings\SettingsField;

abstract class MultivalueField extends SettingsField
{
    protected function getDefaultArgs()
    {
        return array_merge(parent::getDefaultArgs(), [
            'values' => [] // [value => label]
        ]);
    }

    /**
     * @param string|array $value
     * @return string
     */
    public function sanitizeValue($value)
    {
        $value = maybe_unserialize($value);
        $availableValues = array_keys($this->args['values']);

        if (is_array($value)) {
            // Fixed: "Notice:  use of undefined constant parent" (in callback)
            $values = array_map(function ($value) { return parent::sanitizeValue($value); }, $value);
            // parent::sanitizeValue() can return ununique values; filter them
            $values = array_unique($values);
            // Filter empty values ("" = empty value or sanitization error)
            $values = array_filter($values, function ($value) { return $value !== ''; });
            $values = array_intersect($values, $availableValues);

        } else {
            $value = parent::sanitizeValue($value);

            if (in_array($value, $availableValues)) {
                $values = [$value];
            } else {
                $values = $this->getDefaultValue();
            }
        }

        return $values;
    }

    public function getValue()
    {
        $value = get_option($this->name, null);

        if (!is_null($value) && $value !== '') {
            $values = maybe_unserialize($value);
        } else {
            $values = $this->getDefaultValue();
        }

        $values = array_map([$this, 'sanitizeType'], $values);

        return $values;
    }

    public function getDefaultValue()
    {
        if (is_array($this->default)) {
            return $this->default;
        } else if ($this->default !== '') {
            return [$this->default];
        } else {
            return [];
        }
    }
}

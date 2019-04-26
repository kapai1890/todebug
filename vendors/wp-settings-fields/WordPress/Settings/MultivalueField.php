<?php

namespace WordPress\Settings;

abstract class MultivalueField extends SettingsField
{
    protected function getDefaultArgs()
    {
        return array_merge(parent::getDefaultArgs(), [
            'values'    => [], // [value => label]
            'separator' => ',' // Values separator in wp_options
        ]);
    }

    /**
     * @param string|array $value
     * @return string
     */
    public function sanitizeValue($value)
    {
        $availableValues = array_keys($this->args['values']);

        if (is_array($value)) {
            $values = array_map([parent, 'sanitizeValue'], $value);
            // parent::sanitizeValue() can return ununique values; filter them
            $values = array_unique($values);
            // Filter empty values ("")
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

        return implode($this->args['separator'], $values);
    }

    public function getValue()
    {
        $value = get_option($this->name, null);

        if (!is_null($value) && $value !== '') {
            $values = explode($this->args['separator'], $value);
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

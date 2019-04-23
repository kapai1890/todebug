<?php

namespace WordPress\Settings;

abstract class MultioptionField extends SettingsField
{
    protected function getDefaults()
    {
        return array_merge(parent::getDefaults(), [
            'options' => [] // [value => label]
        ]);
    }

    /**
     * @return mixed
     */
    protected function getSelectedValue()
    {
        $options = $this->args['options'];
        $checked = $this->getValue();

        // No need to correct the checked value when there are no options at all
        if (empty($options)) {
            return $checked;
        }

        if (!array_key_exists($checked, $options)) {
            $checked = $this->default;
        }

        if (!array_key_exists($checked, $options)) {
            $values = array_keys($options);
            $checked = reset($values);
        }

        return $checked;
    }

    public function sanitizeValue($value)
    {
        $value = parent::sanitizeValue($value);
        $options = $this->args['options'];

        if (!array_key_exists($value, $options)) {
            $value = $this->default;
        }

        return $value;
    }
}

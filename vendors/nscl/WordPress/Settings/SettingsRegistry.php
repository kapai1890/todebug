<?php

namespace NSCL\WordPress\Settings;

class SettingsRegistry
{
    /**
     * @param string $page general|writing|reading|discussion|media|permalink
     * @param array $settigns
     * @return \NSCL\WordPress\Settings\SettingsSection[] All registered sections.
     */
    public static function registerSettings($page, $settigns)
    {
        $sections = [];

        foreach ($settigns as $sectionName => $fields) {

            $sectionTitle = isset($fields[0]) ? $fields[0] : '';
            $section = new SettingsSection($sectionName, $sectionTitle);

            foreach ($fields as $fieldName => $args) {
                if ($fieldName === 0) {
                    continue; // It's a title, not a field
                }

                // boolean|number|integer|string
                $dataType = isset($args['type']) ? $args['type'] : 'string';

                // text|number|checkbox|select|radio|multicheck
                $inputType = 'text';

                if (isset($args['input_type'])) {
                    $allowedInputTypes = ['text', 'number', 'checkbox', 'select', 'radio', 'multicheck'];
                    $allowedInputTypes = apply_filters('wp-settings-fields/allowed-input-types', $allowedInputTypes);

                    if (in_array($args['input_type'], $allowedInputTypes)) {
                        $inputType = $args['input_type'];
                    }
                } else if (isset($args['options'])) {
                    $inputType = 'select';
                } else if ($dataType == 'boolean') {
                    $inputType = 'checkbox';
                } else if ($dataType == 'number' || $dataType == 'integer') {
                    $inputType = 'number';
                }

                $class = ucfirst($inputType) . 'Field';
                // The namespace is also required when instantiating from variable
                $class = __NAMESPACE__ . '\Fields\\' . $class;

                if (class_exists($class)) {
                    $fieldTitle = isset($args['title']) ? $args['title'] : '';
                    $field = new $class($fieldName, $fieldTitle, $args);

                    $section->addField($field);
                }
            } // For each field

            $sections[$sectionName] = $section;

            // Register only after adding all fields
            $section->register($page);

        } // For each settings section

        return $sections;
    } // End of registerSettings()
}

<?php

/*
 * Project Name: WordPress Settings Fields
 * Project URI: https://github.com/biliavskyi.yevhen/wp-settings-fields
 * Description: Workaround for functions add_settings_xxx()/register_setting().
 * Version: 1.0
 * Author: Biliavskyi Yevhen
 * Author URI: https://github.com/biliavskyi.yevhen
 * License: MIT
 * Text Domain: none
 */

if (!class_exists('WordPress\Settings\SettingsField')) {
    require __DIR__ . '/WordPress/Settings/SettingsField.php';
    require __DIR__ . '/WordPress/Settings/SettingsSection.php';
    require __DIR__ . '/WordPress/Settings/SettingsRegistry.php';

    require __DIR__ . '/WordPress/Settings/TextField.php';
    require __DIR__ . '/WordPress/Settings/NumberField.php';
    require __DIR__ . '/WordPress/Settings/CheckboxField.php';
    require __DIR__ . '/WordPress/Settings/MultioptionField.php';
    require __DIR__ . '/WordPress/Settings/SelectField.php';
    require __DIR__ . '/WordPress/Settings/RadioField.php';
}

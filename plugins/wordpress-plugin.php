<?php

declare(strict_types = 1);

namespace todebug;

if (!defined('ABSPATH')) {
    exit('Use standalone version instead of WordPress plugin.');
}

final class Todebug extends Plugin
{
    /**
     * All the messages of the current execution.
     *
     * @var array
     */
    private static $executionMessages = [];

    public static function saveMessage(string $message)
    {
        static::$executionMessages[] = $message;
    }

    protected static function log(string $message, string $outputFile)
    {
        $isAjax = (defined('DOING_AJAX') && DOING_AJAX);
        $isSilentDebugging = (bool)get_option('todebug_silent_debugging', false);

        if ($isAjax || !$isSilentDebugging) {
            parent::log($message, $outputFile);
        }
    }

    public static function buildMessage(array $vars): string
    {
        $message = parent::buildMessage($vars);

        // Save messages here to also handle functions tostring() and tostrings()
        static::saveMessage($message);

        return $message;
    }

    public static function buildStrings(array $vars): string
    {
        $strings = parent::buildStrings($vars);

        // Save messages here to also handle functions tostring() and tostrings()
        static::saveMessage($strings);

        return $strings;
    }

    public static function buildStringAs($var, string $type): string
    {
        $text = parent::buildStringAs($var, $type);

        // Save messages here to also handle functions tostring() and tostrings()
        static::saveMessage($text);

        return $text;
    }

    /**
     * @return string|null
     */
    protected static function proposeOutputFile()
    {
        $outputFile = get_option('todebug_output_file', null);

        if (empty($outputFile) || !file_exists($outputFile)) {
            $outputFile = parent::proposeOutputFile();
        }

        return $outputFile;
    }

    /** @var \todebug\Todebug */
    private static $instance = null;

    /** @var string */
    private $version = '';

    private function __construct()
    {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            $this->version = $this->readVersion();
            $this->addActions();
        }
    }

    private function readVersion()
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $pluginData = get_plugin_data(TODEBUG_PLUGIN_FILE, false, false);

        if (isset($pluginData['Version'])) {
            return $pluginData['Version'];
        } else {
            return '';
        }
    }

    private function addActions()
    {
        /**
         * Fires after WordPress has finished loading but before any headers are
         * sent.
         *
         * @requires WordPress 1.5
         *
         * @see https://developer.wordpress.org/reference/hooks/init/
         */
        add_action('init', [$this, 'loadTranslations']);

        /**
         * Fires as an admin screen or script is being initialized.
         *
         * @requires WordPress 2.5
         *
         * @see https://developer.wordpress.org/reference/hooks/admin_init/
         */
        add_action('admin_init', [$this, 'addSettings']);

        /**
         * Enqueue scripts for all admin pages.
         *
         * @requires WordPress 2.8
         *
         * @see https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
         */
        add_action('admin_enqueue_scripts', [$this, 'loadScripts']);

        /**
         * Fires when scripts and styles are enqueued.
         *
         * @requires WordPress 2.8
         *
         * @see https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/
         */
        add_action('wp_enqueue_scripts', [$this, 'loadScripts']);

        /**
         * Load all necessary admin bar items.
         *
         * @requires WordPress 3.1
         *
         * @see https://developer.wordpress.org/reference/hooks/admin_bar_menu/
         */
        add_action('admin_bar_menu', [$this, 'addAdminBarActions'], 100);

        /**
         * Fires before the admin bar is rendered.
         *
         * @requires WordPress 3.1
         *
         * @see https://developer.wordpress.org/reference/hooks/wp_before_admin_bar_render/
         */
        add_action('wp_before_admin_bar_render', [$this, 'renderLogs']);
    }

    public function loadTranslations()
    {
        load_plugin_textdomain('todebug', false, 'todebug/languages');
    }

    public function addSettings()
    {
        add_settings_section('todebug_section', __('Todebug', 'todebug'), function () {}, 'general');

        add_settings_field('todebug_output_file', __('Output File', 'todebug'), [$this, 'renderOutputFileSetting'], 'general', 'todebug_section');
        add_settings_field('todebug_silent_debugging', __('Silent Debugging', 'todebug'), [$this, 'renderSilentDebuggingSetting'], 'general', 'todebug_section');

        register_setting('general', 'todebug_output_file', ['type' => 'string', 'default' => '']);
        register_setting('general', 'todebug_silent_debugging', ['type' => 'boolean', 'default' => false]);
    }

    public function renderOutputFileSetting()
    {
        $outputFile = get_option('todebug_output_file', '');
        $defaultOutputFile = parent::proposeOutputFile();

        echo '<input name="todebug_output_file" type="text" id="todebug_output_file" value="' . esc_attr($outputFile) . '" class="regular-text" />';
        echo '<p class="description" id="todebug_output_file-description">' . sprintf(__('By default it will be the file %s.', 'todebug'), '<code>' . $defaultOutputFile . '</code>') . '</p>';
    }

    public function renderSilentDebuggingSetting()
    {
        $isSilentDebugging = (bool)get_option('todebug_silent_debugging', false);

        echo '<label>';
            echo '<input name="todebug_silent_debugging" type="checkbox" id="todebug_silent_debugging" value="1" ' . checked($isSilentDebugging, true, false) . ' />';
            echo __('Enable silent debugging');
        echo '</label>';
        echo '<p class="description" id="todebug_silent_debugging-description">' . __('Push messages to log file only on AJAX calls; otherwise save messages only for rendering.', 'todebug') . '</p>';
    }

    public function loadScripts($page = '')
    {
        $pluginUrl = plugin_dir_url(TODEBUG_PLUGIN_FILE);

        wp_enqueue_style('todebug-css', $pluginUrl . 'assets/wordpress-admin.css', [], $this->version);
        wp_enqueue_script('todebug-js', $pluginUrl . 'assets/wordpress-admin.js', ['jquery'], $this->version, true);
    }

    /**
     * @param \WP_Admin_Bar $wpAdminBar
     */
    public function addAdminBarActions($wpAdminBar)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $wpAdminBar->add_node([
            'id'    => 'todebug',
            'title' => __('Todebug', 'todebug'),
            'href'  => admin_url('index.php'), // See real usage in assets/wordpress-admin.js
            'meta'  => [
                'title' => __('Display last logs', 'todebug')
            ]
        ]);
    }

    public function renderLogs()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $messages = static::$executionMessages;

        // Render messages of the current execution
        echo '<div id="todebug-last-logs" style="display: none;">';
            echo '<div class="inner-wrapper">';
                if (empty($messages)) {
                    echo '<p>' . __('No logs in the current execution.', 'todebug') . '</p>';
                } else {
                    foreach ($messages as $message) {
                        $message = trim($message);
                        if (empty($message)) {
                            echo '<hr />';
                        } else {
                            echo '<pre>' . esc_html($message) . '</pre>';
                        }
                    }
                }
            echo '</div>';
        echo '</div>';
    }

    /**
     * Cloning of the object.
     */
    public function __clone() {
        // Cloning instances of the class is forbidden
        // (Leave the version, it's when it was added)
        $this->terminate(__FUNCTION__, __('Do not clone the \todebug\Todebug class.', 'todebug'), '18.16.1');
    }

    /**
     * Unserializing of the class.
     */
    public function __wakeup() {
        // Unserializing instances of the class is forbidden
        // (Leave the version, it's when it was added)
        $this->terminate(__FUNCTION__, __('Do not clone the \todebug\Todebug class.', 'todebug'), '18.16.1');
    }

    private function terminate($function, $message, $version)
    {
        if (function_exists('_doing_it_wrong')) {
            _doing_it_wrong($function, $message, $version);
        } else {
            die($message);
        }
    }

    public static function create()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
    }
}
Todebug::create();

<?php

declare(strict_types = 1);

namespace todebug;

class WordPressLogger extends Logger
{
    /**
     * All the messages of the current execution.
     *
     * @var array
     */
    protected static $executionMessages = [];

    /** @var \todebug\WordPressLogger */
    protected static $instance = null;

    /** @var string */
    protected $pluginFile = '';

    /** @var string */
    protected $version = '';

    /** @var bool */
    protected $haveAdminBar = false;

    public static function saveMessage(string $message)
    {
        static::$executionMessages[] = $message;
    }

    public static function clearExecutionLogs()
    {
        static::$executionMessages = [];
    }

    public static function log(string $message, string $toFile)
    {
        // Save messages here to not handle the functions tostring(),
        // tostrings() and tostringx() and don't push their messages to
        // execution log
        static::saveMessage($message);

        $isAjax = (defined('DOING_AJAX') && DOING_AJAX);

        $isSilent = (bool)get_option('silent_todebug', true);
        $isSilent = apply_filters('silent_todebug', $isSilent);

        $isNoAjax = (bool)get_option('todebug_noajax', true);
        $isNoAjax = apply_filters('todebug_noajax', $isNoAjax);

        if (!$isAjax) {
            if (!$isSilent) {
                parent::log($message, $toFile);
            }
        } else {
            if (!$isNoAjax) {
                parent::log($message, $toFile);
            }
        }
    }

    public static function suggestFile()
    {
        $file = get_option('todebug_log_file', null);

        if (!empty($file)) {
            return $file;
        } else {
            return parent::suggestFile();
        }
    }

    protected function __construct()
    {
        // Don't load unnecessary login on AJAX calls
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        $this->pluginFile = $this->pluginFile();
        $this->version    = $this->readVersion();

        $this->addActions();
    }

    protected function pluginFile(): string
    {
        $pluginRoot = real_path(__DIR__ . '/../');
        // Add trailing slash
        $pluginRoot = rtrim($pluginRoot, '\/') . '/';
        return $pluginRoot . 'todebug.php';
    }

    protected function readVersion()
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $pluginData = get_plugin_data($this->pluginFile, false, false);

        return isset($pluginData['Version']) ? $pluginData['Version'] : '';
    }

    protected function addActions()
    {
        /**
         * Fires as an admin screen or script is being initialized.
         *
         * @requires WordPress 2.5.0
         * @see https://developer.wordpress.org/reference/hooks/admin_init/
         */
        add_action('admin_init', [$this, 'addSettings']);

        /**
         * Enqueue scripts for all admin pages.
         *
         * @requires WordPress 2.8.0
         * @see https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
         */
        add_action('admin_enqueue_scripts', [$this, 'loadScripts']);

        /**
         * Fires when scripts and styles are enqueued.
         *
         * @requires WordPress 2.8.0
         * @see https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/
         */
        add_action('wp_enqueue_scripts', [$this, 'loadScripts']);

        /**
         * Load all necessary admin bar items.
         *
         * @requires WordPress 3.1.0
         * @see https://developer.wordpress.org/reference/hooks/admin_bar_menu/
         */
        add_action('admin_bar_menu', [$this, 'extendAdminBar'], 100, 1);

        /**
         * Fires just before PHP shuts down execution.
         *
         * @requires WordPress 1.2.0
         * @see https://developer.wordpress.org/reference/hooks/shutdown/
         */
        add_action('shutdown', [$this, 'renderLogs'], 999); // Bigger priority to also handle logs on "shutdown" hook
    }

    public function addSettings()
    {
        add_settings_section('todebug', 'Todebug', function () {}, 'general');

        add_settings_field('todebug_log_file', 'Output file', [$this, 'renderLogFileSetting'], 'general', 'todebug');
        add_settings_field('silent_todebug', 'Silent debugging', [$this, 'renderSilentSetting'], 'general', 'todebug');
        add_settings_field('todebug_noajax', 'Skip AJAX logs', [$this, 'renderNoAjaxSetting'], 'general', 'todebug');

        register_setting('general', 'todebug_log_file', ['type' => 'string', 'default' => '']);
        register_setting('general', 'silent_todebug', ['type' => 'boolean', 'default' => true]);
        register_setting('general', 'todebug_noajax', ['type' => 'boolean', 'default' => true]);
    }

    public function renderLogFileSetting()
    {
        $currentFile = get_option('todebug_log_file', '');
        $defaultFile = parent::outputFile();

        echo '<input id="todebug_log_file" class="regular-text" name="todebug_log_file" type="text" value="'
            . esc_attr($currentFile) . '" />';
        echo '<p id="todebug_log_file-description" class="description">'
            . sprintf('By default it will be the file <code>%s</code>.', $defaultFile) . '</p>';
    }

    public function renderSilentSetting()
    {
        $isSilent = (bool)get_option('silent_todebug', true);

        echo '<label>';
            echo '<input id="silent_todebug" name="silent_todebug" type="checkbox" value="1" '
                . checked($isSilent, true, false) . ' />';
            echo 'Enable silent debugging';
        echo '</label>';
        echo '<p id="silent_todebug-description" class="description">'
            . "Don't push messages to log file on non-AJAX calls and save messages only for rendering." . '</p>';
    }

    public function renderNoAjaxSetting()
    {
        $isNoAjax = (bool)get_option('todebug_noajax', true);

        echo '<label>';
            echo '<input id="todebug_noajax" name="todebug_noajax" type="checkbox" value="1" '
                . checked($isNoAjax, true, false) . ' />';
            echo 'Skip AJAX logs';
        echo '</label>';
        echo '<p id="todebug_noajax-description" class="description">'
            . "Don't push any message to log file on AJAX calls." . '</p>';
    }

    public function loadScripts()
    {
        $pluginUrl = plugin_dir_url($this->pluginFile);

        wp_enqueue_style('todebug-css', $pluginUrl . 'assets/todebug-styles.css', [], $this->version);
        wp_enqueue_script('todebug-js', $pluginUrl . 'assets/todebug-scripts.js', ['jquery'], $this->version, true);
    }

    /**
     * @param \WP_Admin_Bar $adminBar
     */
    public function extendAdminBar($adminBar)
    {
        // Don't show execution logs for every user
        if (!current_user_can('manage_options')) {
            return;
        }

        // If something will go wrong, then admin button will refer to current page
        $pageUrl = regex_match('/[^\/]+$/', $_SERVER['REQUEST_URI'], '');

        $adminBar->add_node([
            'id'    => 'todebug',
            'title' => 'Todebug',
            'href'  => admin_url($pageUrl), // See real usage of the link in assets/todebug-scripts.js
            'meta'  => ['title' => 'Display execution logs']
        ]);

        $this->haveAdminBar = true;
    }

    public function renderLogs()
    {
        // Don't print execution logs for every user
        if (!current_user_can('manage_options')) {
            return;
        }

        // Don't print logs when there is no admin bar where to show them
        if (!$this->haveAdminBar) {
            return;
        }

        $messages = static::$executionMessages;

        if (empty($messages)) {
            $messages[] = 'No logs in the current execution.';
        }

        // Render messages of the current execution
        echo '<div id="todebug-execution-logs" style="display: none;">';
            echo '<div class="inner-wrapper">';
                foreach ($messages as $message) {
                    if ($message == PHP_EOL) {
                        // Replace empty lines with <hr> element
                        echo '<hr />';

                    } else {
                        // Handle PHP_EOL elements of the message as horizontal line
                        $parts = explode(' "' . PHP_EOL . '" ', $message);

                        for ($i = 0, $lastIndex = count($parts) - 1; $i <= $lastIndex; $i++) {
                            $part = $parts[$i];
                            // Ensure new line character in the end of string
                            $part = rtrim($part, PHP_EOL) . PHP_EOL;
                            // Translate all "&" into "&amp;" before esc_html()
                            $part = str_replace('&', '&amp;', $part);
                            echo '<pre>' . esc_html($part) . '</pre>';
                            // Add horizontal line between message parts
                            if ($i != $lastIndex) {
                                echo '<hr />';
                            }
                        }

                    } // If not horizontal line
                } // For each message
            echo '</div>'; // .inner-wrapper
        echo '</div>'; // #todebug-execution-logs
    }

    /**
     * Cloning of the object.
     */
    public function __clone() {
        // Cloning instances of the class is forbidden
        $this->terminate(__FUNCTION__, 'Do not clone the \todebug\WordPressLogger class.', '1.0');
    }

    /**
     * Unserializing of the class.
     */
    public function __wakeup() {
        // Unserializing instances of the class is forbidden
        $this->terminate(__FUNCTION__, 'Do not clone the \todebug\WordPressLogger class.', '1.0');
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

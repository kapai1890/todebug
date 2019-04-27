<?php

namespace todebug;

use WordPress\Settings\SettingsRegistry;
use tostr\AsIs;
use tostr\MessageBuilder;
use tostr\Reflector;

class Plugin
{
    /** @var \tostr\MessageBuilder */
    protected $messageBuilder;

    /** @var \todebug\User */
    protected $user;

    /** @var \todebug\Settings */
    protected $settings;

    /** @var \WordPress\Settings\SettingsSection */
    protected $settingsSection;

    /** @var \todebug\AssetsLoader */
    protected $assetsLoader;

    /** @var \todebug\AdminBar */
    protected $adminBar;

    /** @var \todebug\LogsPrinter */
    protected $logsPrinter;

    public function __construct()
    {
        $reflector   = new Reflector();
        $stringifier = new Stringifier($reflector);
        $msgBuilder  = new MessageBuilder($reflector, $stringifier);

        $this->messageBuilder  = $msgBuilder;
        $this->user            = new User();
        $this->settings        = new Settings();
        $this->settingsSection = SettingsRegistry::registerSettings('writing', $this->pluginSettings());
        $this->assetsLoader    = new AssetsLoader($this->user);
        $this->adminBar        = new AdminBar($this->user);
        $this->logsPrinter     = new LogsPrinter($this->user);

        $this->addActions();
    }

    protected function addActions()
    {
        add_action('init', [$this, 'loadTranslations']);
        add_action('init', [$this, 'checkOutputFile']);
    }

    public function loadTranslations()
    {
        $pluginDir = plugin_basename(PLUGIN_DIR); // "todebug" or renamed folder
        load_plugin_textdomain('todebug', false, $pluginDir . '/languages');
    }

    public function checkOutputFile()
    {
        $outputFile = $this->settings->getOutputFile();

        if (!is_writable($outputFile)) {
            // Notice: push-method will change the order of messages
            $this->logsPrinter->pushMessage(PHP_EOL); // <hr />
            $this->logsPrinter->pushMessage(sprintf(esc_html__('Warning: the output file "%s" is not writable.', 'todebug'), $outputFile));
        }
    }

    /**
     * @param mixed[] $vars
     * @return string The result of conversion.
     */
    public function logMessage(array $vars)
    {
        // Convert root strings into `messages` (output them as is, without "")
        $vars = array_map(function ($var) {
            if (is_string($var) && !is_numeric($var)) {
                $trimmed = trim($var);

                if (!empty($trimmed)) {
                    // Show without ""
                    return new AsIs($trimmed);
                }
            }

            // Otherwise don't change the output method
            return $var;
        }, $vars);

        // Now all values are ready
        return $this->logStrict($vars);
    }

    /**
     * @param mixed[] $vars
     * @return string The result of conversion.
     */
    public function logStrict(array $vars)
    {
        $message = $this->messageBuilder->buildMessage($vars, $this->settings->getMaxDepth());
        $this->saveMessage($message);

        return $message;
    }

    /**
     * @param mixed[] $vars
     * @param string $type
     * @param int $maxDepth Optional. -1 by default (use value from settings).
     * @return string The result of conversion.
     */
    public function logAs($var, $type, $maxDepth = -1)
    {
        if ($maxDepth < 1) {
            $maxDepth = $this->settings->getMaxDepth();
        }

        $message = $this->messageBuilder->buildAs($var, $type, $maxDepth);
        $message .= PHP_EOL;

        $this->saveMessage($message);

        return $message;
    }

    /**
     * @param string $message
     */
    protected function saveMessage($message)
    {
        $this->logsPrinter->addMessage($message);

        if (wp_doing_ajax()) {
            if ($this->settings->isLoggingOnAjaxEnabled()) {
                $this->writeMessage($message);
            }
        } else if (wp_doing_cron()) {
            if ($this->settings->isLoggingOnCronEnabled()) {
                $this->writeMessage($message);
            }
        } else {
            if ($this->settings->isLoggingInGeneralEnabled()) {
                $this->writeMessage($message);
            }
        }
    }

    /**
     * @param string $message
     */
    protected function writeMessage($message)
    {
        // Message type 3 - append text tot the specified file
        @error_log($message, 3, $this->settings->getOutputFile());
    }

    public function clearLogs()
    {
        $this->logsPrinter->clear();
    }

    protected function pluginSettings()
    {
        return [
            'todebug' => [
                0 => esc_html__('Todebug', 'todebug'),
                'todebug_enabled_requests' => [
                    'type'        => 'string',
                    'default'     => '',
                    'title'       => esc_html__('Enabled Requests', 'todebug'),
                    'input_type'  => 'multicheck',
                    'values'      => [
                        'general'   => esc_html__('Log general requests', 'todebug'),
                        'ajax'      => esc_html__('Log AJAX requests', 'todebug'),
                        'cron'      => esc_html__('Log cron requests', 'todebug')
                    ]
                ],
                'todebug_preferred_output' => [
                    'type'        => 'string',
                    'default'     => 'wp-debug',
                    'title'       => esc_html__('Preferred Output', 'todebug'),
                    'input_type'  => 'radio',
                    'options'     => [
                        'wp-debug'  => wp_kses(__("WordPress' <code>debug.log</code> file", 'todebug'), ['code' => []]),
                        'todebug'   => esc_html__('Custom log file of the plugin', 'todebug')
                    ]
                ],
                'todebug_custom_file' => [
                    'type'        => 'string',
                    'default'     => '',
                    'title'       => esc_html__('Custom File', 'todebug'),
                    'input_type'  => 'text',
                    'placeholder' => $this->settings->getDefaultFile(),
                    'size'        => 'large'
                ],
                'todebug_max_depth' => [
                    'type'        => 'integer',
                    'default'     => 5,
                    'title'       => esc_html__('Max Depth', 'todebug'),
                    'input_type'  => 'number',
                    'min'         => 1,
                    'step'        => 1
                ]
            ]
        ];
    }
}

<?php

namespace todebug;

use WordPress\Settings\SettingsRegistry;
use tostr\AsIs;
use tostr\Reflector;

class Plugin
{
    /** @var \todebug\MessageBuilder */
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
        $this->settingsSection = SettingsRegistry::registerSettings('writing', $this->settings->getPluginSettings());
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
     * @param int $maxDepth Optional. "auto" by default (use value from settings).
     * @return string The result of conversion.
     */
    public function logAs($var, $type, $maxDepth = 'auto')
    {
        if ($maxDepth === 'auto') {
            $maxDepth = $this->settings->getMaxDepth();
        }

        $message = $this->messageBuilder->buildAs($var, $type, $maxDepth);
        $message .= PHP_EOL;

        $this->saveMessage($message);

        return $message;
    }

    /**
     * @param mixed $var Any object.
     * @param int $maxDepth Optional. "auto" by default (use value from settings).
     * @return string The result of conversion.
     */
    public function logObjectsHierarchy($var, $maxDepth = 'auto')
    {
        if ($maxDepth === 'auto') {
            $maxDepth = $this->settings->getMaxDepth();
        }

        $message = $this->messageBuilder->buildObjectsHierarchy($var, $maxDepth);
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
                $this->writeMessage($message, false);
            }
        } else if (wp_doing_cron()) {
            if ($this->settings->isLoggingOnCronEnabled()) {
                $this->writeMessage($message, false);
            }
        } else {
            if ($this->settings->isLoggingInGeneralEnabled()) {
                $this->writeMessage($message, true);
            }
        }
    }

    /**
     * @param string $message
     */
    protected function writeMessage($message, $suppressErrors)
    {
        // Message type 3 - append text tot the specified file
        if ($suppressErrors) {
            @error_log($message, 3, $this->settings->getOutputFile());
        } else {
            error_log($message, 3, $this->settings->getOutputFile());
        }
    }

    public function clearLogs()
    {
        $this->logsPrinter->clear();
    }
}

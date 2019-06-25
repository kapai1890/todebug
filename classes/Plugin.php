<?php

namespace todebug;

use NSCL\WordPress\Settings\SettingsRegistry;

class Plugin
{
    /** @var \todebug\User */
    protected $user;

    /** @var \todebug\Settings */
    protected $settings;

    /** @var \NSCL\WordPress\Settings\SettingsSection */
    protected $settingsSection;

    /** @var \todebug\AssetsLoader */
    protected $assetsLoader;

    /** @var \todebug\AdminBar */
    protected $adminBar;

    /** @var \todebug\LogsPrinter */
    protected $logsPrinter;

    public function __construct()
    {
        $this->changeDefaultStringBuilder();

        $this->user            = new User();
        $this->settings        = new Settings();
        $this->settingsSection = SettingsRegistry::registerSettings('writing', $this->settings->getPluginSettings());
        $this->assetsLoader    = new AssetsLoader($this->user);
        $this->adminBar        = new AdminBar($this->user);
        $this->logsPrinter     = new LogsPrinter($this->user);

        $this->addActions();
    }

    protected function changeDefaultStringBuilder()
    {
        global $tostr;

        $stringifier = new Stringifier();
        $tostr = new StringBuilder($stringifier);
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
     * @param string $message
     */
    public function logMessage($message)
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

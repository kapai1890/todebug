<?php

namespace todebug;

class Settings
{
    protected function isRequestsEnabled($type)
    {
        $enabledRequests = get_option('todebug_enabled_requests', array());

        if (empty($enabledRequests)) {
            return false;
        } else {
            return in_array($type, $enabledRequests);
        }
    }

    public function isLoggingInGeneralEnabled()
    {
        $isEnabled = $this->isRequestsEnabled('general');
        $isEnabled = apply_filters('todebug/enable-channel/general', $isEnabled);

        return $isEnabled;
    }

    public function isLoggingOnAjaxEnabled()
    {
        $isEnabled = $this->isRequestsEnabled('ajax');
        $isEnabled = apply_filters('todebug/enable-channel/ajax', $isEnabled);

        return $isEnabled;
    }

    public function isLoggingOnCronEnabled()
    {
        $isEnabled = $this->isRequestsEnabled('cron');
        $isEnabled = apply_filters('todebug/enable-channel/cron', $isEnabled);

        return $isEnabled;
    }

    /**
     * @return string wp-debug|todebug
     */
    public function getPreferredOutput()
    {
        return get_option('todebug_preferred_output', 'wp-debug');
    }

    /**
     * The path to desired output file.
     *
     * @return string wp-content/debug.log or custom file.
     */
    public function getOutputFile()
    {
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG && $this->getPreferredOutput() == 'wp-debug') {
            return WP_CONTENT_DIR . '/debug.log';
        } else {
            return $this->getCustomFile();
        }
    }

    /**
     * The path to custom output file.
     *
     * @return string Custom file or todebug/debug.log.
     */
    public function getCustomFile()
    {
        $customFile = get_option('todebug_custom_file', '');

        if (!empty($customFile)) {
            return $customFile;
        } else {
            return $this->getDefaultFile();
        }
    }

    /**
     * Path to default output file.
     *
     * @return string todebug/debug.log
     */
    public function getDefaultFile()
    {
        return PLUGIN_DIR . 'debug.log';
    }

    public function getMaxDepth()
    {
        $defaultDepth = $this->getDefaultDepth();

        $depth = get_option('todebug_max_depth', $defaultDepth);

        if ($depth !== '') {
            return (int)$depth;
        } else {
            return $defaultDepth;
        }
    }

    public function getDefaultDepth()
    {
        return 5;
    }
}

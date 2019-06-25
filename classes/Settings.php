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

    public function getPluginSettings()
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
                    'placeholder' => $this->getDefaultFile(),
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

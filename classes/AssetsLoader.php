<?php

namespace todebug;

class AssetsLoader
{
    /** @var \todebug\User */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->addActions();
    }

    protected function addActions()
    {
        add_action('admin_enqueue_scripts', [$this, 'loadAssets']);
        add_action('wp_enqueue_scripts', [$this, 'loadAssets']);
    }

    public function loadAssets()
    {
        // If current user can't see logs, then there is nothing to style or handle
        if ($this->user->canSeeLogs()) {
            wp_enqueue_style('todebug-css', PLUGIN_URL . 'assets/styles.css', [], PLUGIN_VERSION);
            wp_enqueue_script('todebug-js', PLUGIN_URL . 'assets/scripts.js', ['jquery'], PLUGIN_VERSION, true);
        }
    }
}

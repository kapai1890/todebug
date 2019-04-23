<?php

namespace todebug;

class AdminBar
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
        // Use big priority just to make the button most right in the top bar
        add_action('admin_bar_menu', [$this, 'addAdminBar'], 10002, 1);
    }

    /**
     * @param \WP_Admin_Bar $adminBar
     */
    public function addAdminBar(\WP_Admin_Bar $adminBar)
    {
        // Don't show logs for every user
        if (!$this->user->canSeeLogs()) {
            return;
        }

        // If something will go wrong, then admin button will refer to current
        // page. URL example: "edit.php?post_type=page"
        $pageUrl = regex_match('/[^\/]+$/', $_SERVER['REQUEST_URI'], '');

        $adminBar->add_node([
            'id'    => 'todebug',
            'title' => esc_html__('Todebug', 'todebug'),
            'href'  => admin_url($pageUrl),
            'meta'  => [
                'title' => esc_html__('Display execution logs', 'todebug')
            ]
        ]);
    }
}

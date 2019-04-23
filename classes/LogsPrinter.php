<?php

namespace todebug;

class LogsPrinter
{
    /** @var \todebug\User */
    protected $user;

    /**
     * All messages of the current execution.
     *
     * @var string[]
     */
    protected $loggedMessages = [];

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->addActions();
    }

    protected function addActions()
    {
        // Use bigger priority to also handle logs on "shutdown" hook
        add_action('shutdown', [$this, 'displayLogs'], 10002);
    }

    /**
     * @param string $message
     */
    public function pushMessage($message)
    {
        $this->loggedMessages = $message;
    }

    public function clear()
    {
        $this->loggedMessages = [];
    }

    public function displayLogs()
    {
        // Don't print execution logs for every user
        if (!$this->user->canSeeLogs()) {
            return;
        }

        // Render messages of the current execution
        echo '<div id="todebug-logs" style="display: none;">';
            echo '<div class="inner-wrapper">';
                echo $this->renderLogs();
            echo '</div>';
        echo '</div>';
    }

    protected function renderLogs()
    {
        if (!empty($this->loggedMessages)) {
            $messages = $this->loggedMessages;
        } else {
            $messages[] = __('No logs in the current execution.', 'todebug');
        }

        $render = '';

        foreach ($messages as $message) {
            if ($message == PHP_EOL) {
                // Replace empty lines as <hr /> element
                $render .= '<hr />';

            } else {
                // Replace with <hr /> all message parts, that only consist of
                // PHP_EOL
                $blocks = explode(' "' . PHP_EOL . '" ', $message);
                $blocks = array_map(function ($block) {
                    return '<pre>' . $this->renderString($block) . '</pre>';
                }, $blocks);

                $render .= implode('<hr />', $blocks);
            }
        }

        return $render;
    }

    protected function renderString($string)
    {
        // End the string (block) with a newline character
        $string = rtrim($string, PHP_EOL) . PHP_EOL;

        // Translate all "&" into "&amp;" before doing any escapes
        $string = str_replace('&', '&amp;', $string);

        // Escape HTML tags
        $string = str_replace(['<', '>'], ['&lt;', '&gt;'], $string);

        return $string;
    }
}

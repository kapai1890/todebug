<?php

namespace todebug;

class User
{
    public function canSeeLogs()
    {
        return current_user_can('manage_options');
    }
}

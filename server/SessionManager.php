<?php

namespace SessionManager;

class SessionManager {
    public function set($index, $data)
    {
        $_SESSION[$index] = $data;
    }

    public function get($index = null, $default = null)
    {
        if (empty($index)) {
            return $_SESSION;
        }
        return $_SESSION[$index] ?? $default;
    }

    public function delete($index)
    {
        if (isset($_SESSION[$index])) {
            unset($_SESSION[$index]);
        }
    }

    public function destroy()
    {
        session_destroy();
    }
}
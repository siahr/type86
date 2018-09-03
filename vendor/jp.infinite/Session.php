<?php

class Session {

    public function set($key, $value) {
        $primitive = true;
        if (is_array($value) || is_object($value)) {
            $value = NetUtils::safeSerialize($value);
            $primitive = false;
        }
        $_SESSION[$key]["primitive"] = $primitive;
        $_SESSION[$key]["value"] = $value;
    }

    public function get($key) {
        if (!isset($_SESSION[$key])) return null;
        if (!$_SESSION[$key]["primitive"]) {
            return NetUtils::safeUnserialize($_SESSION[$key]["value"]);
        }
        return $_SESSION[$key]["value"];
    }

    public function delete($key) {
        unset($_SESSION[$key]);
    }

    public function exists($key) {
        return isset($_SESSION[$key]);
    }

    public function destroy() {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }
        session_destroy();
    }

}
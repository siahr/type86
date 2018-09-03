<?php

/**
 * Class View
 *
 */
class View {
    private $_vars = array();

    private $_request;

    public function __isset($name) {
        if ($name == "lang") return true;
        if (StringUtils::startsWith($name, "_")) return false;
        if (isset($this->_vars[$name])) {
            return true;
        }
        return false;
    }

    public function __get($name) {
        if ($name == "lang") return $this->lang();
        if (StringUtils::startsWith($name, "_")) return null;
        if (isset($this->_vars[$name])) {
            return $this->_vars[$name];
        }
        return null;
    }

    public function anchor($anchor, $url, $attributes=array()) {
        if (empty($url)) return $anchor;
        $html = "<a ";
        $attrs = array();
        foreach($attributes as $name => $attr) {
            $attrs[] = '"' . $name . "=" . $attr . '"';
        }
        $html .= implode(" ", $attrs);
        $html .= "href=\"";
        $html .= $url;
        $html .= "\">";
        $html .= $anchor;
        $html .= "</a>";
        return $html;
    }

    public function incl($filename, array $dirs=array()) {
        if (empty($dirs)) {
            $backtraces = debug_backtrace();
            $tpl = dirname($backtraces[0]["file"]) . DS . $filename;
        } else {
            $tpl = ROOT_DIR . "view/" . implode("/", $dirs) . DS . $filename;
        }
        if (is_file($tpl)) {
            /** @noinspection PhpIncludeInspection */
            require $tpl;
        }
    }

    public function url($location=null) {
        if ($location === null) return WEB_ROOT . DS;
        return WEB_ROOT . DS . $location;
    }

    public function __construct(Request $request) {
        $this->_request = $request;
    }

    public function set($name, $value) {
        if (StringUtils::startsWith($name, "_")) {
            throw new Exception("Variable names beginning with an underscore are invalid");
        }
        $this->_vars[$name] = $value;
    }

    public function render($tpl=null) {
        if (empty($tpl)) {
            $tpl = ROOT_DIR . "view/" . $this->_request->params["controller"] . DS . $this->_request->params["action"] . ".tpl";
        } else {
            if (strpos($tpl, "/") === false) {
                $tpl = ROOT_DIR . "view/" . $this->_request->params["controller"] . DS . $tpl;
            } else {
                $tpl = ROOT_DIR . "view/" . $tpl;
            }
        }
        if (!is_file($tpl)) {
            if (DEBUG_MODE) {
                echo "<html><header></header><body style=\"margin:20px auto auto 20px \">";
                echo "<h1>Missing view.</h1>";
                echo "<h2>". $tpl . " is not exists.</h2>";
                echo "</body></html>";
            } else {
                HttpResponseCode::send(500);
            }
            die;
        }
        $_tpl = $tpl;
        foreach($this->_vars as $_key=> $_val) {
            if (isset(${$_key})) throw new Exception("Duplicate variable name assigned");
            ${$_key} = $_val;
        }
        unset($_key, $_val, $tpl);

        /** @noinspection PhpIncludeInspection */
        include($_tpl);
    }

}

<?php

/**
 * Class Request
 *
 * @property array $params
 * @property array $data
 * @property array $query
 * @property array $queryString
 * @property string $method
 *
 */
class Request {
	private $params = array(
		'controller' => null,
		'action' => null,
		'path' => array(),
	);

    private $data;

    private $query;

    private $queryString;

    private $method;

    public function __get($name) {
        if (property_exists($this, $name)) return $this->{$name};
        return null;
    }

    public function __construct() {
        $security = new Security();

		$this->data = $_POST;
		$this->query = $_GET;
		$this->queryString = env("QUERY_STRING");

		foreach($this->data as &$val) $val = $security->xss_clean($val);
        foreach($this->query as &$val) $val = $security->xss_clean($val);

		$this->method = $_SERVER['REQUEST_METHOD'];

		$pathString = env("REQUEST_URI");
		$pos = strpos($pathString, "?");
		if ($pos !== false) {
			$pathString = substr($pathString, 0, $pos);
		}
		$pathString = trim($pathString);
		$pathString = trim($pathString, "/");
		$path = explode("/", $pathString);

		if (strtolower($path[1]) == "index.html" || strtolower($path[1]) == "index.php") {
			$path = array();
		}

		$idx = 0 + Settings::PATH_OFFSET;
        $idx++;
		if (!isset($path[$idx])) {
			$this->params['controller'] = Settings::DEFAULT_CONTROLLER;
		} else {
			$this->params['controller'] = $path[$idx];
		}
        $idx++;
		if (!isset($path[$idx])) {
			$this->params['action'] = Settings::DEFAULT_ACTION;
		} else {
    		$this->params['action'] = $path[$idx];
		}
        $idx++;
		for($i = $idx; $i < count($path); $i++) {
			$this->params['path'][] = $path[$i];
		}

		define('APP_URL', WEB_ROOT . $this->params['site'] . DS);
	}

}

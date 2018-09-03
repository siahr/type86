<?php

class Dispatcher {

	var $request;

	public function __construct(Request $request) {
		$this->request = $request;
	}

	public function dispatch() {
		session_start();

        $controller = $this->getController($this->request);

		if (!($controller instanceof Controller)) {
			if (DEBUG_MODE) {
				echo "<html><header></header><body style=\"margin:20px auto auto 20px \">";
				echo "<h1>Missing ".Inflector::camelize($this->request->params['controller'])."Controller</h1>";
				echo "</body></html>";
			} else {
                HttpResponseCode::send(404);
			}
			die;
		}

		try {
			$this->invoke($controller);
		} catch (Exception $e) {
            Logger::error("lastSQL", array("SQL" => PHP_EOL . Reticent::$lastQuery));
			Logger::error($e->getMessage());
            if (DEBUG_MODE) {
                Logger::error($e->getTraceAsString());
                echo "<html><header></header><body style=\"margin:20px auto auto 20px \">";
				echo "<div><h1>".$e->getMessage()."</h1></div>";
                echo "<div><h3>".nl2br(Reticent::$lastQuery)."</h3></div>";
				echo "<div style=\"color:red;\">".nl2br($e->getTraceAsString())."</div>";
				echo "</body></html>";
			} else {
                HttpResponseCode::send(500);
			}
			die;
		}
	}

	private function invoke(Controller $controller) {
		$controller->invokeAction();
	}

	private function getController($request) {
		$ctrlClass = $this->loadController($request);
		if (!$ctrlClass) {
			return false;
		}
		$reflection = new ReflectionClass($ctrlClass);
		if ($reflection->isAbstract() || $reflection->isInterface()) {
			return false;
		}
		return $reflection->newInstance($request);
	}

	private function loadController($request) {
        $controller = null;
		if (!empty($request->params['controller'])) {
			$controller = Inflector::camelize($request->params['controller']);
		}
		if ($controller) {
            $site = $request->params["site"];
			$class = $controller . 'Controller';

			$classFile = ROOT_DIR . "controller/" . $class . ".php";
			if (is_file($classFile)) {
                /** @noinspection PhpIncludeInspection */
				require_once $classFile;
			}
			if (class_exists($class)) {
				return $class;
			}
		}
		return false;
	}

}

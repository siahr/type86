<?php

class Controller {

    protected $request;

    protected $view;

    protected $session;

    protected $autoRender = true;

    protected $uses = array();

    public function __construct(Request $request) {
		$this->request = $request;
		$this->view = new View($request);
        $this->session = new Session();
	}

	public function invokeAction() {
		try {
			$method = new ReflectionMethod($this, Inflector::camelize($this->request->params['action']));

		} catch (Exception $e) {
            if (DEBUG_MODE) {
                echo "<html><header></header><body style=\"margin:20px auto auto 20px \">";
                echo "<h1>Missing action method.</h1>";
                echo "<h2>" . get_class($this) . "::" . $this->request->params['action'] . "(...) is undefined.</h2>";
                echo "</body></html>";
                die;
            } else {
                HttpResponseCode::send(500);
            }
		}

		foreach($this->uses as $name) {
			$modelName = Inflector::camelize($name);
			$this->{$modelName} = Model::createInstance(Inflector::camelize($modelName));
		}
		$this->beforeInvoke();
		$method->invokeArgs($this, $this->request->params['path']);

		if (NetUtils::isAjaxRequest()) exit;

		$this->beforeRender();
		if ($this->autoRender) {
			$this->view->render();
		}
	}

	public function set($name, $value) {
		$this->view->set($name, $value);
	}

	public function render($tpl) {
		$this->beforeRender();
		$this->view->render($tpl);
	}

    public function redirect($uri = '', $method = 'auto', $code = null) {
		NetUtils::redirect($uri, $method, $code);
    }

	protected function beforeInvoke() {
	}

	protected function beforeRender() {
	}

}

<?php

abstract class N2ApplicationType {

    public $identifier;

    /**
     * @var N2Application
     */
    public $app;

    /**
     * @var String App wide identifier for the current app type
     */
    public $type;

    public $path;

    /**
     * @var The current controller name from $_REQUEST
     */
    public $controllerName;

    public $router;

    private $controller;

    /**
     * Setting up application platform, fixing magic quotes gpc, initializing autoloader and N2Request
     *
     * @param $app N2Application
     * @param $appTypePath
     */
    public function __construct($app, $appTypePath) {

        $this->identifier = $app->name . '.' . $this->type;

        N2Loader::addPath($this->identifier, $appTypePath);

        $this->app    = $app;
        $this->router = $app->router;

        $this->path = $appTypePath;

        $this->autoload();

    }

    protected function autoload() {

    }

    public function setCurrent() {
        N2Base::$currentApplicationType = $this;

        return $this;
    }

    public function run($parameters = array(), $arguments = array()) {
        $useRequest = true;
        if (isset($parameters['useRequest'])) {
            $useRequest = $parameters['useRequest'];
        }
        if (isset($parameters["controller"])) {
            $this->getController($parameters["controller"], $useRequest);
        } else {
            $this->getController();
        }


        $path = N2Loader::$paths[$this->identifier] . '/controllers/' . $this->controllerName . '/';

        if (isset($parameters['prerender']) && $parameters['prerender']) {
            $path .= 'prerender/';
            $class = 'N2' . $this->app->name . $this->type . $this->controllerName . 'PrerenderController';
        } else {
            $class = 'N2' . $this->app->name . $this->type . $this->controllerName . 'Controller';
        }

        if ($this->isAjaxCall()) {
            $class = $class . 'Ajax';
            require_once $path . 'ajax/' . $this->controllerName . '.php';
        } else {
            require_once $path . $this->controllerName . '.php';
        }

        if (!class_exists($class, false)) {
            throw new Exception('Controller not found: ' . $class);
        }

        $method = 'action';

        if (isset($parameters["action"])) {
            $method .= $this->getAction($parameters["action"], $useRequest);
        } else {
            $method .= $this->getAction();
        }

        if (!method_exists($class, $method)) {
            throw new Exception('Action not found: ' . $method . ' in ' . $class);
        }

        /**
         * @var $controller N2Controller
         */
        $this->controller = new $class($path, $this, array(
            "controller" => $class,
            "action"     => $method
        ));

        $this->onControllerReady();

        $callable = array(
            $this->controller,
            $method
        );

        if (is_callable($callable)) {
            call_user_func_array($callable, $arguments);
        } else {
            $callable[1] = 'magicMethod';
            if (is_callable($callable)) {
                array_unshift($arguments, $method);
                call_user_func_array($callable, $arguments);
            } else {
                throw new Exception("Method not exists: " . get_class($callable[0]) . '->' . $method);
            }
        }

    }

    final public function setRequest($controller, $action) {
        N2Request::set("nextendcontroller", $controller);
        N2Request::set("nextendaction", $action);
    }

    final protected function getController($controller = false, $useRequest = true) {
        if ($useRequest) {
            $desiredController = N2Request::getCmd("nextendcontroller");
            if (!empty($desiredController)) {
                $controller = $desiredController;
            }
        }
        $controller = trim(strtolower($controller));
        if (empty($controller)) {
            throw new Exception("Controller is not specified!");
        }

        $this->controllerName = $controller;

        return $this->controllerName;
    }

    /**
     * Get current action by $_REQUEST
     *
     */
    final protected function getAction($action = false, $useRequest = true) {
        if ($useRequest) {
            $desiredAction = N2Request::getCmd("nextendaction");
            if (!empty($desiredAction)) {
                $action = $desiredAction;
            }
        }
        $action = trim($action);
        if (empty($action)) {
            $action = 'index';
        }

        $this->actionName = strtolower($action);

        return $this->actionName;
    }

    /**
     * @return N2Layout
     */
    public function getLayout() {
        return $this->controller->layout;
    }

    /**
     * @return bool
     */
    public function isAjaxCall() {
        return N2Request::getInt('nextendajax');
    }

    public function render($parameters, $arguments = array()) {
        try {
            ob_start();
            $this->run($parameters, $arguments);
            echo ob_get_clean();
        } catch (Exception $e) {
            n2_ob_end_clean_all();
			if(isset($_GET['debug'])){
				echo '<pre>';
				print_r($e);
				echo '</pre>';
			} else {
				$params = array_merge($_GET, array("debug" => "1"));
				$new_query_string = http_build_query($params);
				$redirect_url = (empty($_SERVER['HTTPS'])?"http://":"https://") . (empty($_SERVER['HTTP_HOST'])?$defaultHost:$_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'] . "?" . $new_query_string;				
				echo "<div style='position:fixed;background:white;left:25%;top:25%;width:46%;height:46%;z-index:100000;padding:3%;'>" . $e->getMessage() . " <br><br> <a href=\"" . $redirect_url ."\">debug error</a></div>";
			}
        }
    }
	
    protected function onControllerReady() {
    }

} 
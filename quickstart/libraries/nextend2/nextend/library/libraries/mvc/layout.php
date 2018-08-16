<?php

N2Loader::import("libraries.mvc.view");

class N2Layout extends N2View {

    /** @var N2ControllerAbstract */
    protected $controller;

    protected $path = '';

    private $layoutFragments = array();

    private $viewObject = null;

    protected $breadcrumbs = array();


    /**
     * N2Layout constructor.
     *
     * @param N2ControllerAbstract $controller
     * @param N2ApplicationType    $appType
     */
    public function __construct($controller, $appType) {

        $this->controller = $controller;
        parent::__construct($appType);

        $this->path = $this->controller->getPath() . 'views/';
    }

    public function addView($fileName, $position, $viewParameters = array(), $path = null) {
        if (is_null($path)) {
            $path = $this->path;
        }

        if (!file_exists($path . $fileName . ".phtml")) {
            throw new N2ViewException("View file ({$fileName}.phtml) not found in " . $path . $fileName);
        }
        $this->layoutFragments["nextend_" . $position][] = array(
            'params' => $viewParameters,
            'file'   => $path . $fileName . ".phtml"
        );
    }

    /**
     * Render page layout
     *
     * @param string      $fileName
     * @param null|string $path
     * @param array       $params
     *
     * @throws N2ViewException
     */
    protected function renderLayout($fileName, $params = array(), $path = null) {
        if (is_null($path)) {
            $path = $this->appType->path . NDS . "layouts" . NDS;
        } else {
            if (strpos(".", $path) !== false) {
                $path = N2Filesystem::dirFormat($path);
            }
        }

        if (!N2Filesystem::existsFile($path . $fileName . ".phtml")) {
            throw new N2ViewException("Layout file ({$fileName}.phtml) not found in '{$path}'");
        }

        extract($params);

        ob_start();
        /** @noinspection PhpIncludeInspection */
        include $path . $fileName . ".phtml";

        $content = ob_get_clean();

        if (!empty($this->breadcrumbs)) {
            $html = '';
            foreach ($this->breadcrumbs AS $i => $breadcrumb) {
                if ($i) {
                    $html .= N2Html::tag('span', array(), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-breadcrumbarrow'), ''));
                }
                $html .= $breadcrumb;
            }
            $content = str_replace('<!--breadcrumb-->', N2Html::tag('div', array(
                'class' => 'n2-header-breadcrumbs n2-header-right'
            ), $html), $content);
        }

        echo $content;
    }

    public function render($params = array(), $layoutName = false) {
        $controller = strtolower($this->appType->controllerName);
        if (N2Filesystem::existsFile($this->path . NDS . "_view.php")) {
            require_once $this->path . NDS . "_view.php";

            $call             = array(
                "class"  => "N2{$this->appType->app->name}{$this->appType->type}{$controller}View",
                "method" => $this->appType->actionName
            );
            $this->viewObject = $this->preCall($call, $this->appType);
        }

        if ($layoutName) {
            $this->renderLayout($layoutName, $params);
        }
    }

    public function renderFragmentBlock($block, $fallback = false) {
        if (isset($this->layoutFragments[$block])) {
            foreach ($this->layoutFragments[$block] as $key => $view) {

                $view["params"]["_class"] = $this->viewObject;
                $this->renderInline($view["file"], $view["params"], null, true);
            }
        } else if ($fallback) {
            $this->renderInline($fallback, array());
        }
    }

    public function getFragmentValue($key, $default = null) {
        if (isset($this->layoutFragments[$key])) {
            return $this->layoutFragments[$key];
        }

        return $default;
    }

    private function renderInline($fileName, $params = array(), $path = null, $absolutePathInFilename = false) {
        if ($absolutePathInFilename) {
            $path = "";
        } elseif (is_null($path)) {
            $path = $this->appType->path . NDS . "inline" . NDS;
        }

        if (strpos($fileName, ".phtml") === false) {
            $fileName = $fileName . ".phtml";
        }

        if (!N2Filesystem::existsFile($path . $fileName)) {
            throw new N2ViewException("View file ({$fileName}) not found in {$path}");
        }

        extract($params);

        /** @noinspection PhpIncludeInspection */
        include $path . $fileName;
    }

    public function addBreadcrumb($html) {
        $this->breadcrumbs[] = $html;
    }

}

class N2LayoutAjax extends N2Layout {

    protected function renderLayout($fileName, $params = array(), $path = null) {
        $this->renderFragmentBlock('nextend_content');
    }
}
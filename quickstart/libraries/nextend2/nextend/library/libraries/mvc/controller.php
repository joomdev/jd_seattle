<?php

/**
 * @property mixed moduleParams
 * @property mixed module
 */
class N2ControllerAbstract {

    protected $path = '';

    /** @var  N2Layout */
    public $layout;
    public $layoutName = 'default';

    /**
     * @var N2ApplicationType
     */
    public $appType;

    /**
     * @var N2AssetsManager
     */
    public $assets;

    public function __construct($path, $appType, $defaultParams) {
        $this->path = $path;

        $this->appType = $appType;

        $this->initLayout();
        N2AssetsManager::getInstance();

        $this->initialize();
    }

    public function getPath() {
        return $this->path;
    }

    protected function initLayout() {
        $this->layout = new N2Layout($this, $this->appType);
    }

    public function initialize() {
        N2AssetsPredefined::frontend();
        $this->appType->app->info->assetsFrontend();
    }

    /**
     * Check ACL permissions
     *
     * @param      $action
     *
     * @return bool
     */
    public function canDo($action) {
        return N2Acl::canDo($action, $this->appType->app->info);
    }

    /**
     * Add view file to current layout & page
     *
     * @param string $viewName
     * @param array  $params
     * @param string $position - position on layout. default is 'content'
     */
    public function addView($viewName = null, $params = array(), $position = 'content') {
        if (is_null($viewName)) {
            $viewName = "index";
        }
        $this->layout->addView($viewName, $position, $params);
    }

    public function addViewFile($path, $viewName, $params = array(), $position = 'content') {
        if (is_null($viewName)) {
            $viewName = "index";
        }
        $this->layout->addView($viewName, $position, $params, $path);
    }

    public function render($params = array()) {
        $this->layout->render($params, $this->layoutName);
    }

    /**
     * This method display no access screen
     */
    public function noAccess() {
        $this->addViewFile(N2LIBRARY . '/libraries/', "noaccess");
        $this->render();
    }

    public function redirect($url, $statusCode = 302, $terminate = true) {
        N2Request::redirect($this->appType->router->createUrl($url), $statusCode, $terminate);
    }

    /**
     * @param int  $statusCode
     * @param bool $terminate
     */
    public function refresh($statusCode = 302, $terminate = true) {
        $this->redirect(N2Request::getRequestUri(), $statusCode, $terminate);
    }

    protected function validatePermission($permission) {

        if (!$this->canDo($permission)) {
            $this->noAccess();

            return false;
        }

        return true;
    }

    protected function validateVariable($condition, $property) {

        if (!$condition) {
            $this->noAccess();

            return false;
        }

        return true;
    }

    protected function validateDatabase($condition) {
        if (!$condition) {
            $this->noAccess();

            return false;
        }

        return true;
    }

    protected function validateToken() {
        if (!N2Form::checkToken()) {
            N2Message::error(n2_('Security token mismatch'));

            return false;
        }

        return true;
    }

}

N2Loader::import("libraries.mvc.controller", 'platform');

if (!class_exists('N2Controller', false)) {
    class N2Controller extends N2ControllerAbstract {

    }
}

N2Loader::import('libraries.ajax.response');

class N2ControllerAjax extends N2Controller {

    /** @var N2AjaxResponse */
    protected $response;

    public function __construct($path, $appType, $defaultParams) {
        n2_ob_end_clean_all();

        $this->response = new N2AjaxResponse($appType);
        parent::__construct($path, $appType, $defaultParams);
    }

    protected function initLayout() {
        $this->layout = new N2LayoutAjax($this, $this->appType);
    }

    public function render($params = array()) {
        $this->layout->render($params, $this->layoutName);
    }

    protected function validateToken() {

        if (!N2Form::checkToken()) {
            N2Message::error(n2_('Security token mismatch. Please refresh the page!'));
            $this->response->error();
        }
    }

    protected function validatePermission($permission) {

        if (!$this->canDo($permission)) {
            N2Message::error(sprintf(n2_('No permission: %s'), $permission));
            $this->response->error();
        }
    }

    protected function validateVariable($condition, $property) {

        if (!$condition) {
            N2Message::error(sprintf(n2_('Missing parameter: %s'), $property));
            $this->response->error();
        }
    }

    protected function validateDatabase($condition) {
        if (!$condition) {
            N2Message::error(n2_('Database error'));
            $this->response->error();
        }
    }
}
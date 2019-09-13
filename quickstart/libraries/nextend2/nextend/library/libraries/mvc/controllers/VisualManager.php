<?php

class N2SystemBackendVisualManagerController extends N2BackendController {

    public $layoutName = "lightbox";

    protected $type = '';

    protected $app = null;

    protected $logoText = '';

    public function getModel() {
    }

    public function initialize() {
        $this->app = N2Base::getApplication('system');
        parent::initialize();

        N2Loader::import(array(
            'models.visual'
        ), 'system');
        $this->loadModel();

        N2Localization::addJS(array(
            'visual',
            'visuals',
            'Static',
            'Empty',

            'Save as new',
            'Overwrite current',
            '%s changed - %s',
            'Save as',

            'Sets',
            'Add new',
            '%s sets',
            'Create set',
            'Add',
            'Name',
            'Please fill the name field!',
            'Set added',
            'Rename set',
            'Rename',
            'Delete',
            'Set renamed',
            'Delete set',
            'Cancel',
            'Yes',
            'Do you really want to delete the set and all associated %s?',
            'Unable to delete the set',
            'Current tab',
            'Live'
        ));
    }

    protected function loadModel() {
        N2Loader::import(array(
            'models.' . $this->type
        ), 'system');
    }

    public function actionIndex() {

        $model = $this->getModel();
        $this->app->set($this->type . 'setModel', $model);

        $this->addViewFile($this->path, 'sidebar-' . $this->type, array(
            "model" => $model
        ), "sidebar");

        $this->addView("topbar", array(), 'content_top_bar');

        $this->addView("index", array(
            "model" => $model
        ));

        $this->render(array(
            'lightboxId' => 'n2-lightbox-' . $this->type,
            'logo'       => $this->logoText
        ));
    }

}
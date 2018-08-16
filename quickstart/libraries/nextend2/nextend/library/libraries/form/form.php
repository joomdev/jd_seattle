<?php

N2Loader::import('libraries.form.tabcontainer');
N2Loader::import('libraries.form.elementcontainer');
N2Loader::import('libraries.form.tab');
N2Loader::import('libraries.form.element');

class N2FormAbstract extends N2Data implements N2FormTabContainer {

    public $appType;

    //public static $importPaths = array();

    /**
     * @var N2Data
     */
    protected $context;

    /** @var N2Tab[] */
    protected $tabs = array();


    /**
     *
     * App type must be declared if you need to route in the parameters. Route is needed for example for subform!!!
     *
     * @param $appType N2ApplicationType|bool
     */
    public function __construct($appType = false) {

        self::initialize();

        $this->appType = $appType;

        $this->context = new N2Data();
        parent::__construct();

    }

    public function getForm() {
        return $this;
    }

    /**
     * @return N2Data
     */
    public function getContext() {
        return $this->context;
    }

    public function addTab($tab) {
        $this->tabs[$tab->getName()] = $tab;
    }

    public function getTab($tab) {
        return $this->tabs[$tab];
    }

    public function render($control_name) {
        //$this->initTabs(false);
        $this->decorateFormStart();
        foreach ($this->tabs AS $tabName => $tab) {
            $tab->render($control_name);
        }
        $this->decorateFormEnd();

    }

    protected function decorateFormStart() {
        echo N2Html::openTag("div", array("class" => "n2-form"));
    }

    protected function decorateFormEnd() {
        echo N2Html::closeTag("div");
    }

    private static $isInitialized = false;
    private static $paths = array();

    private static function initialize() {
        if (!self::$isInitialized) {
            array_unshift(self::$paths, dirname(__FILE__) . '/elements');
            array_unshift(self::$paths, dirname(__FILE__) . '/tabs');

            for ($i = 0; $i < count(self::$paths); $i++) {
                self::doImport(self::$paths[$i]);
            }
            self::$isInitialized = true;
        }
    }

    public static function import($path) {
        if (self::$isInitialized) {
            self::doImport($path);
        } else {
            self::$paths[] = $path;
        }
    }

    private static function doImport($path) {
        N2Loader::importPathAll($path);
    }
}

N2Loader::import('libraries.form.form', 'platform');


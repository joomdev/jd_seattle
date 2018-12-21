<?php

class N2SmartsliderApplicationTypeBackend extends N2ApplicationType {

    public $type = "backend";

    protected function autoload() {


        N2Loader::import(array(
            'libraries.image.color',
            'libraries.parse.parse'
        ));

        /**
         * We do not need the method exists check here, but upgrading from 3.2.14 -> 3.3 caused an error in WordPress free
         */
        if(method_exists('N2Form', 'import')) {
            N2Form::import(dirname(__FILE__) . '/elements');
        }

        N2Loader::import(array(
            'libraries.settings.settings'
        ), 'smartslider');

        require_once dirname(__FILE__) . '/SmartSliderController.php';

        require_once dirname(__FILE__) . '/fragments.php';
    }

    protected function onControllerReady() {
        $this->getLayout()
             ->addBreadcrumb(N2Html::tag('a', array(
                 'href'  => $this->router->createUrl("sliders/index"),
                 'class' => 'n2-h4'
             ), n2_('Dashboard')));

        N2JS::addGlobalInline("window.N2SS3VERSION='" . N2SS3::$version . "';");
    }

}
<?php

class N2SmartSliderFrontendSliderPreRenderController extends N2Controller {

    public function initialize() {
        N2JS::jQuery(true, true);

        parent::initialize();

        N2Loader::import(array(
            'models.Sliders',
            'models.Slides'
        ), 'smartslider');

    }

    public function actionIframe() {

        $sliderIDorAlias = isset($_GET['sliderid']) ? $_GET['sliderid'] : false;
        if (empty($sliderIDorAlias)) throw new Exception('Slider ID or alias is not valid.');
        N2CSS::addStaticGroup(N2LIBRARYASSETS . '/normalize.min.css', 'normalize');
    


        $locale = setlocale(LC_NUMERIC, 0);
        setlocale(LC_NUMERIC, "C");

        $sliderManager = new N2SmartSliderManager($sliderIDorAlias);
        $slider        = $sliderManager->render(true);

        setlocale(LC_NUMERIC, $locale);

        $this->addView("iframe", array(
            "slider" => $slider
        ), "content");

        $this->render();
    }

} 
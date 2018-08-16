<?php

class N2SmartSliderTypeGroup extends N2SmartSliderType {

    private $earlier = 2145916800;

    public function render($css) {

        ob_start();
        $this->renderType($css);


        return ob_get_clean();
    }

    protected function renderType($css) {

        $xref = new N2SmartsliderSlidersXrefModel();
        $rows = $xref->getSliders($this->slider->data->get('id'));
        foreach ($rows AS $row) {
            $slider     = new N2SmartSliderManager($row['slider_id']);
            $sliderHTML = $slider->render();
            echo $sliderHTML;
            if (!empty($sliderHTML)) {
                $this->earlier = min($slider->slider->getNextCacheRefresh(), $this->earlier);
            }
        }
    }

    public function getNextCacheRefresh() {
        return $this->earlier;
    }

}

class N2SmartSliderCSSGroup extends N2SmartSliderCSSAbstract {

    public function render() {

    }

    protected function renderType(&$context) {

    }

}
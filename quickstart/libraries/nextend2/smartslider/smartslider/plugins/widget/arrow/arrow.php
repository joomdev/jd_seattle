<?php

class N2SSPluginWidgetArrow extends N2SSPluginSliderWidget {

    protected $name = 'arrow';

    public function getLabel() {
        return n2_('Arrows');
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'widgetsarrow');

        $url = N2Base::getApplication('smartslider')
                     ->getApplicationType('backend')->router->createAjaxUrl(array("slider/renderwidgetarrow"));

        new N2ElementWidgetPluginMatrix($settings, 'widgetarrow', false, 'imageEmpty', $url, array(
            'widget' => $this
        ));

        new N2ElementOnOff($settings, 'widget-arrow-display-hover', n2_('Shows on hover'), 0);


        new N2TabPlaceholder($form, 'widget-arrow-placeholder', false, array(
            'id' => 'nextend-widgetarrow-panel'
        ));
    }
}

N2SmartSliderWidgets::addGroup(new N2SSPluginWidgetArrow);
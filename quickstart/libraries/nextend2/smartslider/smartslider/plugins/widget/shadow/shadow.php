<?php

class N2SSPluginWidgetShadow extends N2SSPluginSliderWidget {

    public $ordering = 7;

    protected $name = 'shadow';

    public function getLabel() {
        return n2_('Shadows');
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'widgetsshadow');

        $url = N2Base::getApplication('smartslider')
                     ->getApplicationType('backend')->router->createAjaxUrl(array("slider/renderwidgetshadow"));

        new N2ElementWidgetPluginMatrix($settings, 'widgetshadow', false, '', $url, array(
            'widget' => $this
        ));

        new N2TabPlaceholder($form, 'widget-shadow-placeholder', false, array(
            'id' => 'nextend-widgetshadow-panel'
        ));

    }
}

N2SmartSliderWidgets::addGroup(new N2SSPluginWidgetShadow);
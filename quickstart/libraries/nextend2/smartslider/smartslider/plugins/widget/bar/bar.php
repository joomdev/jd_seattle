<?php

class N2SSPluginWidgetBar extends N2SSPluginSliderWidget {

    public $ordering = 5;

    protected $name = 'bar';

    public function getLabel() {
        return n2_('Text Bar');
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'widgetsbar');

        $url = N2Base::getApplication('smartslider')
                     ->getApplicationType('backend')->router->createAjaxUrl(array("slider/renderwidgetbar"));

        new N2ElementWidgetPluginMatrix($settings, 'widgetbar', false, '', $url, array(
            'widget' => $this
        ));

        new N2ElementOnOff($settings, 'widget-bar-display-hover', n2_('Shows on hover'), 0);


        new N2TabPlaceholder($form, 'widget-bar-placeholder', false, array(
            'id' => 'nextend-widgetbar-panel'
        ));

    }
}

N2SmartSliderWidgets::addGroup(new N2SSPluginWidgetBar);
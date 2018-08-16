<?php

class N2SSPluginWidgetAutoplay extends N2SSPluginSliderWidget {

    public $ordering = 3;

    protected $name = 'autoplay';

    public function getLabel() {
        return n2_('Autoplay');
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'widgetsautoplay');

        $url = N2Base::getApplication('smartslider')
                     ->getApplicationType('backend')->router->createAjaxUrl(array("slider/renderwidgetautoplay"));

        new N2ElementWidgetPluginMatrix($settings, 'widgetautoplay', false, 'disabled', $url, array(
            'widget' => $this
        ));

        new N2ElementOnOff($settings, 'widget-autoplay-display-hover', n2_('Shows on hover'), 0);


        new N2TabPlaceholder($form, 'widget-autoplay-placeholder', false, array(
            'id' => 'nextend-widgetautoplay-panel'
        ));
    }
}

N2SmartSliderWidgets::addGroup(new N2SSPluginWidgetAutoplay);
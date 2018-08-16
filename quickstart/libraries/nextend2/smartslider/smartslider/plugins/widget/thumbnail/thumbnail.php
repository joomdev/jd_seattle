<?php

class N2SSPluginWidgetThumbnail extends N2SSPluginSliderWidget {

    public $ordering = 6;

    protected $name = 'thumbnail';

    public function getLabel() {
        return n2_('Thumbnails');
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'widgetsthumbnail');

        $url = N2Base::getApplication('smartslider')
                     ->getApplicationType('backend')->router->createAjaxUrl(array("slider/renderwidgetthumbnail"));

        new N2ElementWidgetPluginMatrix($settings, 'widgetthumbnail', false, '', $url, array(
            'widget' => $this
        ));

        new N2ElementOnOff($settings, 'widget-thumbnail-display-hover', n2_('Shows on hover'), 0);


        $thumbnail = new N2elementGroup($settings, 'thumbnail-thumbnail', n2_('Thumbnail'));

        new N2ElementNumberAutocomplete($thumbnail, 'widget-thumbnail-width', n2_('Width'), 100, array(
            'unit'   => 'px',
            'values' => array(
                60,
                100,
                150,
                200
            ),
            'style'  => 'width:30px'
        ));

        new N2ElementNumberAutocomplete($thumbnail, 'widget-thumbnail-height', n2_('Height'), 60, array(
            'unit'   => 'px',
            'values' => array(
                60,
                100,
                150,
                200
            ),
            'style'  => 'width:30px'
        ));


        new N2TabPlaceholder($form, 'widget-thumbnail-placeholder', false, array(
            'id' => 'nextend-widgetthumbnail-panel'
        ));

    }
}

N2SmartSliderWidgets::addGroup(new N2SSPluginWidgetThumbnail);
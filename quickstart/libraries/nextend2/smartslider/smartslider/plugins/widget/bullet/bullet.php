<?php

class N2SSPluginWidgetBullet extends N2SSPluginSliderWidget {

    public $ordering = 2;

    protected $name = 'bullet';

    public function getLabel() {
        return n2_('Bullets');
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'widgetsbullet');

        $url = N2Base::getApplication('smartslider')
                     ->getApplicationType('backend')->router->createAjaxUrl(array("slider/renderwidgetbullet"));

        new N2ElementWidgetPluginMatrix($settings, 'widgetbullet', false, '', $url, array(
            'widget' => $this
        ));

        new N2ElementOnOff($settings, 'widget-bullet-display-hover', n2_('Shows on hover'), 0);


        $thumbnail = new N2elementGroup($settings, 'bullet-thumbnail', n2_('Thumbnail'));
        new N2ElementOnOff($thumbnail, 'widget-bullet-thumbnail-show-image', n2_('Enable'), 0, array(
            'relatedFields' => array(
                'sliderwidget-bullet-thumbnail-width',
                'sliderwidget-bullet-thumbnail-height',
                'sliderwidget-bullet-thumbnail-style',
                'sliderwidget-bullet-thumbnail-side'
            )
        ));

        new N2ElementNumberAutocomplete($thumbnail, 'widget-bullet-thumbnail-width', n2_('Width'), 100, array(
            'unit'   => 'px',
            'values' => array(
                60,
                100,
                150,
                200
            ),
            'style'  => 'width:30px'
        ));

        new N2ElementNumberAutocomplete($thumbnail, 'widget-bullet-thumbnail-height', n2_('Height'), 60, array(
            'unit'   => 'px',
            'values' => array(
                60,
                100,
                150,
                200
            ),
            'style'  => 'width:30px'
        ));

        new N2ElementStyle($thumbnail, 'widget-bullet-thumbnail-style', n2_('Style'), 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwODAiLCJwYWRkaW5nIjoiM3wqfDN8KnwzfCp8M3wqfHB4IiwiYm94c2hhZG93IjoiMHwqfDB8KnwwfCp8MHwqfDAwMDAwMGZmIiwiYm9yZGVyIjoiMHwqfHNvbGlkfCp8MDAwMDAwZmYiLCJib3JkZXJyYWRpdXMiOiIzIiwiZXh0cmEiOiJtYXJnaW46IDVweDtiYWNrZ3JvdW5kLXNpemU6Y292ZXI7In1dfQ==', array(
            'previewMode' => 'simple',
            'preview'     => '<div class="{styleClassName}" style="display: inline-block;"><div style="width:{' . '$(\'#sliderwidget-bullet-thumbnail-width\').val()}px; height: {' . '$(\'#sliderwidget-bullet-thumbnail-height\').val()}px; overflow: hidden; background: url(\'$system$/images/placeholder/image.png\');background-size: cover;"></div></div>'
        ));

        new N2ElementSwitcher($thumbnail, 'widget-bullet-thumbnail-side', n2_('Side'), 'before', array(
            'options' => array(
                'before' => n2_('Before'),
                'after'  => n2_('After')
            )
        ));


        new N2TabPlaceholder($form, 'widget-bullet-placeholder', false, array(
            'id' => 'nextend-widgetbullet-panel'
        ));

    }
}

N2SmartSliderWidgets::addGroup(new N2SSPluginWidgetBullet);
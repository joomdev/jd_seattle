<?php

abstract class N2SSPluginSliderWidget {

    public $ordering = 1;

    protected $name = '';

    /** @var N2SSPluginWidgetAbstract[] */
    private $widgets = array();

    /**
     * @param N2SSPluginWidgetAbstract $widget
     */
    public function addWidget($widget) {
        $this->widgets[$widget->getName()] = $widget;
    }

    /**
     * @return N2SSPluginWidgetAbstract[]
     */
    public function getWidgets() {
        return $this->widgets;
    }

    /**
     * @param $name
     *
     * @return N2SSPluginWidgetAbstract
     */
    public function getWidget($name) {
        return $this->widgets[$name];
    }

    public abstract function getLabel();

    public abstract function getPath();

    /**
     * @param N2Form $form
     */
    public abstract function renderFields($form);

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    public function getSubFormImagePath() {
        return $this->getPath() . 'subformimage.png';
    }


}
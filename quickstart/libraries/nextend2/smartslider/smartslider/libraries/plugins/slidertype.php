<?php

abstract class N2SSPluginSliderType {

    public $ordering = 1;

    protected $name = '';

    /** @var N2SSPluginSliderType[] */
    private static $types = array();

    /**
     * @param N2SSPluginSliderType $sliderType
     */
    public static function addSliderType($sliderType) {
        self::$types[$sliderType->getName()] = $sliderType;
    }

    /**
     * @return N2SSPluginSliderType[]
     */
    public static function getSliderTypes() {
        uasort(self::$types, 'N2SSPluginSliderType::sortTypes');

        return self::$types;
    }

    /**
     * @param $name
     *
     * @return N2SSPluginSliderType
     */
    public static function getSliderType($name) {
        return self::$types[$name];
    }


    public function onList(&$types) {
        $types[$this->name] = $this;
    }

    public abstract function getLabel();

    public abstract function getPath();

    /**
     * @param N2FormElementContainer $form
     */
    public abstract function renderFields($form);


    /**
     * @param N2FormElementContainer $form
     */
    public function renderSlideFields($form) {

    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    public function getSubFormImagePath() {
        return $this->getPath() . 'subformimage.png';
    }


    public static function sortTypes($a, $b) {
        return $a->ordering - $b->ordering;
    }

    /**
     * @param N2SmartSliderExport      $export
     * @param                          $slider
     */
    public function export($export, $slider) {
    }

    /**
     * @param N2SmartSliderImport      $import
     * @param                          $slider
     */
    public function import($import, $slider) {

    }

}
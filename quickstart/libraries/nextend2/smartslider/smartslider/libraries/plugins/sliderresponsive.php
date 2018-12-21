<?php

abstract class N2SSPluginSliderResponsive {

    public $ordering = 1;

    protected $name = '';

    /** @var N2SSPluginSliderResponsive[] */
    private static $types = array();

    /**
     * @param N2SSPluginSliderResponsive $type
     */
    public static function addType($type) {
        self::$types[$type->getName()] = $type;
    }

    /**
     * @param $type
     *
     * @return N2SSPluginSliderResponsive
     */
    public static function getType($type) {
        if (isset(self::$types[$type])) {
            return self::$types[$type];
        }

        return self::$types['auto'];
    }

    /**
     * @return N2SSPluginSliderResponsive[]
     */
    public static function getTypes() {
        return self::$types;
    }

    public function onResponsiveList(&$types) {
        $types[$this->name] = $this;
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


    public function parse($params, $responsive, $features) {

    }

}
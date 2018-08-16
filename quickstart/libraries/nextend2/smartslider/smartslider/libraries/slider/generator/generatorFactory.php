<?php

class N2SSGeneratorFactory {

    /** @var N2SliderGeneratorPluginAbstract[] */
    private static $generators = array();

    /**
     * @param N2SliderGeneratorPluginAbstract $generator
     */
    public static function addGenerator($generator) {
        self::$generators[$generator->getName()] = $generator;
    }

    public static function getGenerators() {
        foreach (self::$generators AS $generator) {
            $generator->load();
        }

        return self::$generators;
    }

    /**
     * @param $name
     *
     * @return N2SliderGeneratorPluginAbstract|false
     */
    public static function getGenerator($name) {
        if (!isset(self::$generators[$name])) {
            return false;
        }

        return self::$generators[$name]->load();
    }
}
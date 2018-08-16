<?php

class N2View {

    /**
     * @var N2ApplicationType
     */
    public $appType;

    public function __construct($appType) {
        $this->appType = $appType;
    }

    public function __get($name) {
        return $this->$name;
    }

    public function __set($name, $value) {
        $this->$name = $value;
    }

    protected function preCall($preCall, $applicationType = false) {
        if (is_array($preCall)) {
            $class    = $preCall["class"];
            $callable = array(
                null,
                $preCall["method"]
            );

            if (class_exists($class)) {
                $callable[0] = new $class($applicationType);

                if (is_callable($callable)) {
                    call_user_func($callable, $preCall["viewName"]);
                }

                return $callable[0];
            }
        }

        return false;

    }

}

class N2ViewException extends Exception {

}
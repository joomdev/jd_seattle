<?php

abstract class N2GeneratorAbstract {

    protected $name = '';

    protected $label = '';


    protected $layout = '';

    /** @var  N2SliderGeneratorPluginAbstract */
    protected $group;
    protected $data;

    /**
     * N2GeneratorAbstract constructor.
     *
     * @param N2SliderGeneratorPluginAbstract $group
     * @param string                          $name
     * @param string                          $label
     */
    public function __construct($group, $name, $label) {
        $this->group = $group;
        $this->name  = $name;
        $this->label = $label;

        $this->group->addSource($name, $this);
    }

    /**
     *
     * @param N2Form $form
     */
    public function renderFields($form) {
        $this->group->loadElements();
    }

    public function setData($data) {
        $this->data = $data;
    }

    public final function getData($slides, $startIndex, $group) {
        $data       = array();
        $linearData = $this->_getData($slides * $group, $startIndex - 1);
        $keys       = array();
        for ($i = 0; $i < count($linearData); $i++) {
            $keys = array_merge($keys, array_keys($linearData[$i]));
        }

        $columns = array_fill_keys($keys, '');

        for ($i = 0; $i < count($linearData); $i++) {
            $firstIndex = intval($i / $group);
            if (!isset($data[$firstIndex])) {
                $data[$firstIndex] = array();
            }
            $data[$firstIndex][$i % $group] = array_merge($columns, $linearData[$i]);
        }

        if (count($data) && count($data[count($data) - 1]) != $group) {
            if (count($data) - 1 == 0 && count($data[count($data) - 1]) > 0) {
                for ($i = 0; count($data[0]) < $group; $i++) {
                    $data[0][] = $data[0][$i];
                }
            } else {
                array_pop($data);
            }
        }

        return $data;
    }

    protected abstract function _getData($count, $startIndex);

    function makeClickableLinks($s) {
        return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $s);
    }

    protected function getIDs($field = 'ids') {
        return array_map('intval', explode("\n", str_replace(array(
            "\r\n",
            "\n\r",
            "\r"
        ), "\n", $this->data->get($field))));
    }

    public function filterName($name) {
        return $name;
    }

    public function hash($key) {
        return md5($key);
    }

    public static function cacheKey($params) {
        return '';
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getLayout() {
        return $this->layout;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return N2SliderGeneratorPluginAbstract
     */
    public function getGroup() {
        return $this->group;
    }

}
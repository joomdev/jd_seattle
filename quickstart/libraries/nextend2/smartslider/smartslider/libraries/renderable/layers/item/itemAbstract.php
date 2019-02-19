<?php

abstract class N2SSItemAbstract {

    /** @var N2SSPluginItemFactoryAbstract */
    protected $factory;

    protected $id;

    /** @var N2SSSlideComponentLayer */
    protected $layer;

    /** @var N2Data */
    protected $data;

    protected $type = '';

    protected $isEditor = false;

    /**
     * N2SSItemAbstract constructor.
     *
     * @param N2SSPluginItemFactoryAbstract $factory
     * @param string                        $id
     * @param array                         $itemData
     * @param N2SSSlideComponentLayer       $layer
     */
    public function __construct($factory, $id, $itemData, $layer) {
        $this->factory = $factory;
        $this->id      = $id;
        $this->data    = new N2Data($itemData);
        $this->layer   = $layer;

        $this->fillDefault($factory->getValues());
        $factory->upgradeData($this->data);
    }

    public function fillDefault($defaults) {
        $this->data->fillDefault($defaults);
    }

    public abstract function render();

    public function renderAdmin() {
        $this->isEditor = true;

        $rendered = $this->_renderAdmin();

        $json = $this->data->toJson();

        return N2Html::tag("div", array(
            "class"           => "n2-ss-item n2-ss-item-" . $this->type,
            "data-item"       => $this->type,
            "data-itemvalues" => $json
        ), $rendered);
    }

    protected abstract function _renderAdmin();

    public function needSize() {
        return false;
    }

    protected function hasLink() {
        $link = $this->data->get('href', '#');
        if (($link != '#' && !empty($link))) {
            return true;
        }

        return false;
    }

    protected function getLink($content, $attributes = array(), $renderEmpty = false) {

        N2Loader::import('libraries.link.link');

        $link   = $this->data->get('href', '#');
        $target = $this->data->get('href-target', '#');
        $rel    = $this->data->get('href-rel', '#');
        $class  = $this->data->get('href-class', '');

        if (($link != '#' && !empty($link)) || $renderEmpty === true) {

            $link = N2LinkParser::parse($this->layer->getOwner()
                                                    ->fill($link), $attributes, $this->isEditor);
            if (!empty($target) && $target != '_self') {
                $attributes['target'] = $target;
            }
            if (!empty($rel)) {
                $attributes['rel'] = $rel;
            }
            if (!empty($class)) {
                $attributes['class'] = $class;
            }

            return N2Html::link($content, $link, $attributes);
        }

        return $content;
    }
}
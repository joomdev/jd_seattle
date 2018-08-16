<?php

abstract class N2SSItemAbstract {

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
     * @param string                  $id
     * @param array                   $itemData
     * @param N2SSSlideComponentLayer $layer
     */
    public function __construct($id, $itemData, $layer) {
        $this->id    = $id;
        $this->data  = new N2Data($itemData);
        $this->layer = $layer;
    }

    public function fillDefault($defaults) {
        $this->data->fillDefault($defaults);
    }

    public abstract function render();

    public function renderAdmin() {
        $this->isEditor = true;

        $json = $this->data->toJson();

        return N2Html::tag("div", array(
            "class"           => "n2-ss-item n2-ss-item-" . $this->type,
            "data-item"       => $this->type,
            "data-itemvalues" => $json
        ), $this->_renderAdmin());
    }

    protected abstract function _renderAdmin();

    public function needSize() {
        return false;
    }

    protected function getLink($content, $attributes = array(), $renderEmpty = false) {

        N2Loader::import('libraries.link.link');

        list($link, $target, $rel) = array_pad((array)N2Parse::parse($this->data->get('link', '#|*||*|')), 3, '');

        if (($link != '#' && !empty($link)) || $renderEmpty === true) {

            $link = N2LinkParser::parse($this->layer->getOwner()
                                                    ->fill($link), $attributes, $this->isEditor);
            if (!empty($target) && $target != '_self') {
                $attributes['target'] = $target;
            }
            if (!empty($rel)) {
                $attributes['rel'] = $rel;
            }

            return N2Html::link($content, $link, $attributes);
        }

        return $content;
    }
}
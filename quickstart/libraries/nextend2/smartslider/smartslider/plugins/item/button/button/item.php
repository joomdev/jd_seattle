<?php

N2Loader::import('libraries.renderable.layers.itemFactory', 'smartslider');

class N2SSItemButton extends N2SSItemAbstract {

    protected $type = 'button';

    public function render() {
        return $this->getHtml();
    }

    public function _renderAdmin() {
        return $this->getHtml();
    }

    private function getHtml() {
        $owner = $this->layer->getOwner();

        $this->loadResources($owner);

        $font = $owner->addFont($this->data->get('font'), 'link');

        $html = N2Html::openTag("div", array(
            "class" => "n2-ss-button-container n2-ow " . $font . ($this->data->get('fullwidth', 0) ? ' n2-ss-fullwidth' : '') . ($this->data->get('nowrap', 1) ? ' n2-ss-nowrap' : '')
        ));

        $content = '<div>' . $owner->fill($this->data->get("content")) . '</div>';

        $attrs = array();

        $style = $owner->addStyle($this->data->get('style'), 'heading');

        $html .= $this->getLink('<div>' . $content . '</div>', $attrs + array(
                "class" => "{$style} n2-ow " . $owner->fill($this->data->get('class', ''))
            ), true);

        $html .= N2Html::closeTag("div");

        return $html;
    }

    /**
     * @param N2SmartSliderComponentOwnerAbstract $owner
     */
    public function loadResources($owner) {
        $owner->addLess(dirname(__FILE__) . "/button.n2less", array(
            "sliderid" => $owner->getElementID()
        ));
    }
}
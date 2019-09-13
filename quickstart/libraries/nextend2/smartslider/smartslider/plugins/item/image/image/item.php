<?php

N2Loader::import('libraries.renderable.layers.itemFactory', 'smartslider');

class N2SSItemImage extends N2SSItemAbstract {

    protected $type = 'image';

    public function render() {
        return $this->getHtml();
    }

    public function _renderAdmin() {
        return $this->getHtml();
    }

    private function getHtml() {
        $owner = $this->layer->getOwner();

        $size = (array)N2Parse::parse($this->data->get('size', ''));
        for ($i = 0; $i < 2; $i++) {
            if (is_numeric($size[$i])) {
                $size[$i] = $size[$i] . 'px';
            }
        }
        if (empty($size[0])) $size[0] = 'auto';
        if (empty($size[1])) $size[1] = 'auto';

        $imageUrl = $this->data->get('image', '');
        if (!empty($imageUrl)) {
            $imageAttributes = $owner->optimizeImage($imageUrl) + array(
                    "id"    => $this->id,
                    "alt"   => htmlspecialchars($owner->fill($this->data->get('alt', ''))),
                    "style" => "display: inline-block; max-width: 100%; width: {$size[0]};height: {$size[1]};",
                    "class" => $owner->fill($this->data->get('cssclass', '')) . ' n2-ow'
                );

            $title = htmlspecialchars($owner->fill($this->data->get('title', '')));
            if (!empty($title)) {
                $imageAttributes['title'] = $title;
            }

            $html = N2Html::tag('img', N2HTML::addExcludeLazyLoadAttributes($imageAttributes), false);

            $style = $owner->addStyle($this->data->get('style'), 'heading');

            return N2Html::tag("div", array(
                "class" => $style . ' n2-ss-img-wrapper n2-ss-item-content n2-ow',
                'style' => 'overflow:hidden;'
            ), $this->getLink($html, array( 'class' => 'n2-ow' )));
        } else {
            return null;
        }
    }
}
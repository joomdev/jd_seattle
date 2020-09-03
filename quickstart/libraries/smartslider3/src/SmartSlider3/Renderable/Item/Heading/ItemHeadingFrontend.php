<?php


namespace Nextend\SmartSlider3\Renderable\Item\Heading;


use Nextend\Framework\Misc\Base64;
use Nextend\Framework\View\Html;
use Nextend\SmartSlider3\Renderable\Item\AbstractItemFrontend;

class ItemHeadingFrontend extends AbstractItemFrontend {

    public function render() {
        return $this->getHtml();
    }

    private function getHtml() {
        $owner = $this->layer->getOwner();

        $attributes = array();
        $font = $owner->addFont($this->data->get('font'), 'hover');

        $style = $owner->addStyle($this->data->get('style'), 'heading');

        $linkAttributes = array(
            'class' => 'n2-ow'
        );
        if ($this->isEditor) {
            $linkAttributes['onclick'] = 'return false;';
        }

        $title = $this->data->get('title', '');
        if (!empty($title)) {
            $attributes['title'] = $title;
        }

        $href = $this->data->get('href', '');
        if (!empty($href) && $href != '#') {
            $linkAttributes['class'] .= ' ' . $font . $style;

            $font  = '';
            $style = '';
        }

        $linkAttributes['style'] = "display:" . ($this->data->get('fullwidth', 1) ? 'block' : 'inline-block') . ";";

        return $this->heading($this->data->get('priority', 'div'), $attributes + array(
                "id"    => $this->id,
                "class" => $font . $style . " " . $owner->fill($this->data->get('class', '')) . ' n2-ss-item-content n2-ow',
                "style" => "display:" . ($this->data->get('fullwidth', 1) ? 'block' : 'inline-block') . ";" . ($this->data->get('nowrap', 0) ? 'white-space:nowrap;' : '')
            ), $this->getLink(str_replace("\n", '<br />', strip_tags($owner->fill($this->data->get('heading', '')))), $linkAttributes));
    }

    private function heading($type, $attributes, $content) {
        if ($type > 0) {
            return Html::tag("h{$type}", $attributes, $content);
        }

        return Html::tag("div", $attributes, $content);
    }

    public function renderAdminTemplate() {
        return $this->getHtml();
    }
}
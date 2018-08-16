<?php

N2Loader::import('libraries.form.elements.text');
N2Loader::import('libraries.browse.browse');

N2ImageHelper::init();

N2Loader::import('libraries.image.aviary');

class N2ElementImage extends N2ElementText {

    protected $attributes = array();

    protected $fixed = false;
    protected $relatedAlt = '';

    protected $class = 'n2-form-element-img ';

    protected function fetchElement() {

        if ($this->fixed) {
            $this->class .= 'n2-form-element-img-fixed ';
        }

        $html = parent::fetchElement();

        $params = array();

        N2ImageHelper::initLightbox();

        $params['alt'] = $this->relatedAlt;

        N2JS::addInline("new N2Classes.FormElementImage('" . $this->fieldID . "', " . json_encode($params) . " );");
        $this->renderRelatedFields();

        if ($this->fixed) {

            $aviary = '';
            $html .= '<div id="' . $this->fieldID . '_preview" class="n2-form-element-preview n2-form-element-preview-fixed n2-border-radius" style="' . $this->getImageStyle() . '">
                ' . $aviary . '
            </div><div></div>';
        } else {
        }

        return $html;
    }

    protected function pre() {
        if (!$this->fixed) {
            return '<div id="' . $this->fieldID . '_preview" class="n2-form-element-preview n2-border-radius" style="' . $this->getImageStyle() . '"></div>';
        }

        return '';
    }

    protected function getImageStyle() {
        $image = $this->getValue();
        if (empty($image) || $image[0] == '{') {
            return '';
        }

        return 'background-image:URL(' . N2ImageHelper::fixed($image) . ');';
    }

    protected function post() {
        return N2Html::tag('a', array(
                'href'  => '#',
                'class' => 'n2-form-element-clear'
            ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-empty n2-i-grey-opacity'), '')) . '<a id="' . $this->fieldID . '_button" class="n2-form-element-button n2-icon-button n2-h5 n2-uc" href="#"><i class="n2-i n2-it  n2-i-layer-image"></i></a>';
    }

    /**
     * @param bool $fixed
     */
    public function setFixed($fixed) {
        $this->fixed = $fixed;
    }

    /**
     * @param string $relatedAlt
     */
    public function setRelatedAlt($relatedAlt) {
        $this->relatedAlt = $relatedAlt;
    }

}

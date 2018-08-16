<?php
N2Loader::import('libraries.form.elements.hidden');
N2Loader::import('libraries.stylemanager.stylemanager');

class N2ElementStyle extends N2ElementHidden {

    protected $set = '';

    protected $previewMode = '';

    protected $font = '';

    protected $font2 = '';

    protected $style2 = '';

    protected $preview = '';

    protected $css = '';

    public $hasTooltip = true;

    protected function fetchElement() {

        $preview = preg_replace_callback('/url\(\'(.*?)\'\)/', 'N2ElementStyle::fixPreviewImages', $this->preview);

        N2JS::addInline('new N2Classes.FormElementStyle("' . $this->fieldID . '", {
            previewmode: "' . $this->previewMode . '",
            font: "' . $this->font . '",
            font2: "' . $this->font2 . '",
            style2: "' . $this->style2 . '",
            preview: ' . json_encode($preview) . ',
            set: "' . $this->set . '",
            label: "' . $this->label . '"
        });');

        return N2Html::tag('div', array(
            'class' => 'n2-form-element-option-chooser n2-form-element-style n2-border-radius'
        ), parent::fetchElement() . N2Html::tag('input', array(
                'type'          => 'text',
                'class'         => 'n2-h5',
                'style'         => 'width: 130px;' . $this->css,
                'data-disabled' => 'disabled'
            ), false) . N2Html::tag('a', array(
                'href'  => '#',
                'class' => 'n2-form-element-clear'
            ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-empty n2-i-grey-opacity'), '')) . N2Html::tag('a', array(
                'href'  => '#',
                'class' => 'n2-form-element-button n2-h5 n2-uc'
            ), n2_('Style')));
    }

    public static function fixPreviewImages($matches) {
        return "url(" . N2ImageHelper::fixed($matches[1]) . ")";
    }

    /**
     * @param string $set
     */
    public function setSet($set) {
        $this->set = $set;
    }

    /**
     * @param string $previewMode
     */
    public function setPreviewMode($previewMode) {
        $this->previewMode = $previewMode;
    }

    /**
     * @param string $font
     */
    public function setFont($font) {
        $this->font = $font;
    }

    /**
     * @param string $font2
     */
    public function setFont2($font2) {
        $this->font2 = $font2;
    }

    /**
     * @param string $style2
     */
    public function setStyle2($style2) {
        $this->style2 = $style2;
    }

    /**
     * @param string $preview
     */
    public function setPreview($preview) {
        $this->preview = $preview;
    }

    /**
     * @param string $css
     */
    public function setCss($css) {
        $this->css = $css;
    }


}

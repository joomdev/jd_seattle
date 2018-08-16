<?php
N2Loader::import('libraries.form.elements.hidden');
N2Loader::import('libraries.fonts.fontmanager');

class N2ElementFont extends N2ElementHidden {


    protected $set = '';

    protected $previewMode = '';

    protected $css = '';

    protected $style = '';

    protected $style2 = '';

    protected $preview = '';

    public $hasTooltip = true;

    protected function fetchElement() {

        N2JS::addInline('new N2Classes.FormElementFont("' . $this->fieldID . '", {
            previewmode: "' . $this->previewMode . '",
            style: "' . $this->style . '",
            style2: "' . $this->style2 . '",
            preview: ' . json_encode($this->preview) . ',
            set: "' . $this->set . '",
            label: "' . $this->label . '"
        });');

        return N2Html::tag('div', array(
            'class' => 'n2-form-element-option-chooser n2-form-element-font n2-border-radius'
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
            ), n2_('Font')));
    }

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
     * @param string $css
     */
    public function setCss($css) {
        $this->css = $css;
    }

    /**
     * @param string $style
     */
    public function setStyle($style) {
        $this->style = $style;
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


}

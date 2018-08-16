<?php
N2Loader::import('libraries.form.elements.text');

class N2ElementNumber extends N2ElementText {

    protected $class = 'n2-form-element-number ';

    protected $min = '';
    protected $max = '';
    protected $sublabel = '';

    protected $units = false;

    protected function fetchElement() {

        if ($this->min == '') {
            $this->min = '-Number.MAX_VALUE';
        }

        if ($this->max == '') {
            $this->max = 'Number.MAX_VALUE';
        }

        N2JS::addInline('new N2Classes.FormElementNumber("' . $this->fieldID . '", ' . $this->min . ', ' . $this->max . ', ' . json_encode($this->units) . ');');

        $html = N2Html::openTag('div', array(
            'class' => 'n2-form-element-text ' . $this->getClass() . ($this->unit ? ' n2-text-has-unit ' : '') . ' n2-border-radius',
            'style' => ($this->fieldType == 'hidden' ? 'display: none;' : '')
        ));

        if (!empty($this->sublabel)) {
            $html .= N2Html::tag('div', array(
                'class' => 'n2-text-sub-label n2-h5 n2-uc'
            ), $this->sublabel);
        }

        $html .= $this->pre();

        $html .= N2Html::tag('input', array(
            'type'         => $this->fieldType,
            'id'           => $this->fieldID,
            'name'         => $this->getFieldName(),
            'value'        => $this->getValue(),
            'class'        => 'n2-h5',
            'style'        => $this->getStyle(),
            'autocomplete' => 'off'
        ), false);

        $html .= $this->post();

        if ($this->unit) {
            $html .= N2Html::tag('div', array(
                'class' => 'n2-text-unit n2-h5 n2-uc'
            ), $this->unit);
        }
        $html .= "</div>";

        return $html;
    }

    public function setMin($min) {
        $this->min = $min;
    }

    /**
     * @param int $max
     */
    public function setMax($max) {
        $this->max = $max;
    }

    /**
     * @param string $sublabel
     */
    public function setSublabel($sublabel) {
        $this->sublabel = $sublabel;
    }

    /**
     * @param bool|array $units
     */
    public function setUnits($units) {
        $this->units = $units;
    }

    public function setWide($wide) {
        switch ($wide) {
            case 2:
                $this->style .= 'width:14px;';
                break;
            case 3:
                $this->style .= 'width:22px;';
                break;
            case 4:
                $this->style .= 'width:32px;';
                break;
            case 5:
                $this->style .= 'width:40px;';
                break;
            case 6:
                $this->style .= 'width:60px;';
                break;
        }
    }


}
<?php
N2Loader::import('libraries.form.elements.number');

class N2ElementNumberSlider extends N2ElementNumber {

    protected $step = 1;
    protected $sliderMax;

    protected $class = 'n2-form-element-number n2-form-element-autocomplete';

    protected function fetchElement() {
        $html = parent::fetchElement();

        N2JS::addInline('new N2Classes.FormElementNumberSlider("' . $this->fieldID . '", ' . json_encode(array(
                'min'   => floatval($this->min),
                'max'   => floatval($this->sliderMax),
                'step'  => floatval($this->step),
                'units' => $this->units
            )) . ');');

        return $html;
    }

    /**
     * @param int $step
     */
    public function setStep($step) {
        $this->step = $step;
    }

    /**
     * @param int $sliderMax
     */
    public function setSliderMax($sliderMax) {
        $this->sliderMax = $sliderMax;
    }

    /**
     * @param int $max
     */
    public function setMax($max) {
        parent::setMax($max);

        if ($this->sliderMax === null) {
            $this->sliderMax = $max;
        }
    }


}
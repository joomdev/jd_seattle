<?php


namespace Nextend\SmartSlider3\Form\Element;

use Nextend\Framework\Asset\Js\Js;
use Nextend\Framework\Form\AbstractField;
use Nextend\Framework\Form\ContainerInterface;
use Nextend\Framework\Form\Element\AbstractFieldHidden;
use Nextend\Framework\Form\Element\OnOff;
use Nextend\Framework\Form\Element\Select;
use Nextend\Framework\Form\Element\Text\Number;
use Nextend\Framework\Form\Element\Text\NumberSlider;
use Nextend\Framework\Form\TraitFieldset;

class DatePicker extends AbstractFieldHidden implements ContainerInterface {

    use TraitFieldset;

    protected $rowClass = 'n2_field_mixed ';

    protected $onOffLabel = '';

    public function __construct($insertAt, $name = '', $label = false, $default = '', $parameters = array()) {

        $this->onOffLabel = $label;

        parent::__construct($insertAt, $name, false, $default, $parameters);
    }

    protected function fetchElement() {
        $this->addDatePicker();

        $html = '';

        $subElements = array();
        $i           = 0;

        $element = $this->first;
        while ($element) {

            $element->setExposeName(false);

            $html            .= $this->decorateElement($element);
            $subElements[$i] = $element->getID();
            $i++;

            $element = $element->getNext();
        }

        $html .= parent::fetchElement();

        Js::addInline('new N2Classes.FormElementDatePicker("' . $this->fieldID . '", ' . json_encode($subElements) . ');');

        return $html;
    }

    /**
     * @param AbstractField $element
     *
     * @return string
     */
    public function decorateElement($element) {

        return $this->parent->decorateElement($element);
    }

    protected function addDatePicker() {

        $defaultParts     = explode(' ', $this->defaultValue);
        $defaultDateParts = explode('-', $defaultParts[0]);
        $defaultTimeParts = explode(':', $defaultParts[1]);
        $defaultArray     = array_merge($defaultDateParts, $defaultTimeParts);

        $valueParts     = explode(' ', $this->getValue());
        $valueDateParts = explode('-', $valueParts[0]);
        $valueTimeParts = explode(':', $valueParts[1]);
        $valueArray     = array_merge($valueDateParts, $valueTimeParts);

        $valueArray = $valueArray + $defaultArray;

        $controlName = $this->getControlName();
        new OnOff($this, $this->name . '-enable', $this->onOffLabel, 0, array(
            'relatedFieldsOn' => array(
                $controlName . $this->name . '-year',
                $controlName . $this->name . '-month',
                $controlName . $this->name . '-day',
                $controlName . $this->name . '-hour',
                $controlName . $this->name . '-minute'
            )
        ));


        //YEAR
        new Number($this, $this->name . '-year', n2_('Year'), $valueArray[0], array(
            'wide' => 4,
            'min'  => 1970,
            'max'  => 9999
        ));

        //MONTH
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $formattedValue          = sprintf("%02d", $i);
            $months[$formattedValue] = $formattedValue;
        }
        new Select($this, $this->name . '-month', n2_('Month'), $valueArray[1], array(
            'options' => $months
        ));

        //DAY
        $days = array();
        for ($i = 1; $i <= 31; $i++) {
            $formattedValue        = sprintf("%02d", $i);
            $days[$formattedValue] = $formattedValue;
        }

        new Select($this, $this->name . '-day', n2_('Day'), $valueArray[2], array(
            'options' => $days,
        ));

        //HOUR
        $hours = array();
        for ($i = 0; $i < 24; $i++) {
            $formattedValue         = sprintf("%02d", $i);
            $hours[$formattedValue] = $formattedValue;
        }
        new Select($this, $this->name . '-hour', n2_('Hour'), $valueArray[3], array(
            'options' => $hours
        ));

        //MINUTE
        new NumberSlider($this, $this->name . '-minute', n2_('Minute'), $valueArray[4], array(
            'wide' => 2,
            'min'  => 0,
            'max'  => 59
        ));
    }
}
<?php
N2Loader::import('libraries.form.elements.hidden');

class N2ElementSliderWidgetArea extends N2ElementHidden {

    protected function fetchElement() {
        $areas = '';
        for ($i = 1; $i <= 12; $i++) {
            $areas .= N2Html::tag('div', array(
                'class'     => 'n2-area n2-area-' . $i . $this->isSelected($i),
                'data-area' => $i
            ));
        }

        $html = N2Html::tag('div', array(
            'id'    => $this->fieldID . '_area',
            'class' => 'n2-widget-area'
        ), N2Html::tag('div', array(
                'class' => 'n2-widget-area-inner'
            )) . $areas);
        $html .= parent::fetchElement();

        N2JS::addInline('new N2Classes.FormElementSliderWidgetArea("' . $this->fieldID . '");');

        return $html;
    }

    function isSelected($i) {
        if ($i == $this->getValue()) {
            return ' n2-active';
        }

        return '';
    }
}

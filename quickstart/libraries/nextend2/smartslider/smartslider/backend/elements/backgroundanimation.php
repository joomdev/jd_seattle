<?php
N2Loader::import('libraries.backgroundanimation.manager', 'smartslider');

class N2ElementBackgroundAnimation extends N2ElementHidden {

    public $hasTooltip = true;

    protected function fetchElement() {

        N2Localization::addJS(array(
            'Disabled',
            'Multiple animations',
            'Single animation'
        ));
        N2JS::addStaticGroup(NEXTEND_SMARTSLIDER_ASSETS . '/plugins/type/simple/simple/dist/smartslider-backgroundanimation.min.js', 'smartslider-backgroundanimation');
    

        N2JS::addInline('new N2Classes.FormElementAnimationManager("' . $this->fieldID . '", "backgroundanimationManager");');

        $this->renderRelatedFields();

        return N2Html::tag('div', array(
            'class' => 'n2-form-element-option-chooser n2-border-radius'
        ), parent::fetchElement() . N2Html::tag('input', array(
                'type'     => 'text',
                'class'    => 'n2-h5',
                'style'    => 'width: 130px;',
                'disabled' => 'disabled'
            ), false) . N2Html::tag('a', array(
                'href'  => '#',
                'class' => 'n2-form-element-clear'
            ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-empty n2-i-grey-opacity'), '')) . N2Html::tag('a', array(
                'href'  => '#',
                'class' => 'n2-form-element-button n2-h5 n2-uc'
            ), n2_('Animations')));
    }
}

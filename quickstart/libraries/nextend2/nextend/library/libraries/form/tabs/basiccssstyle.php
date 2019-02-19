<?php

class N2TabBasicCSSStyle extends N2Tab {

    protected function renderTitle() {
        echo N2Html::tag('div', array(
            'class' => 'n2-h2'
        ), N2Html::tag('span', array(
                'class' => 'n2-css-name'
            ), N2Html::tag('span', array(
                    'class' => 'n2-css-name-label n2-uc'
                ), '') . N2Html::tag('span', array(
                    'class' => 'n2-css-name-list'
                ), '')) . N2Html::tag('div', array('class' => 'n2-css-right'), N2Html::tag('div', array(
                    'class'      => 'n2-css-tab-reset n2-button n2-button-icon n2-button-s n2-radius-s n2-button-grey',
                    'data-n2tip' => n2_('Reset to normal state'),
                ), '<i class="n2-i n2-i-reset2"></i>') . N2Html::tag('div', array(
                    'class' => 'n2-css-tab'
                ))));
    }

    protected function decorateGroupStart() {
    }

    protected function decorateGroupEnd() {
        echo N2Html::link(n2_('More'), '#', array(
            'class' => 'n2-basiccss-more n2-button n2-button-normal n2-button-s n2-button-grey n2-radius-s n2-h5 n2-uc'
        ));
        echo N2Html::clear();
        echo "</div>";
    }

    protected function decorateElement($el, $renderedElement) {
        echo N2Html::tag('div', array(
            'class' => 'n2-inline-block ' . $el->getRowClass()
        ), N2Html::tag('div', array(
            'class' => 'n2-form-element-mixed'
        ), N2Html::tag('div', array(
                'class' => 'n2-mixed-label'
            ), $renderedElement[0]) . N2Html::tag('div', array(
                'class' => 'n2-mixed-element'
            ), $renderedElement[1])));


    }
}
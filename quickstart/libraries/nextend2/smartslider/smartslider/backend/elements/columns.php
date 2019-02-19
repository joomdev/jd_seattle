<?php

class N2ElementColumns extends N2ElementHidden {

    protected function fetchElement() {

        N2JS::addInline('new N2Classes.FormElementColumns("' . $this->fieldID . '");');

        return N2Html::tag('div', array(
            'class' => 'n2-ss-columns-element'
        ), N2Html::tag('div', array(
                'class' => 'n2-ss-columns-element-container n2-unselectable'
            ), '') . N2Html::tag('div', array(
                'class' => 'n2-ss-columns-element-add-col'
            ), '<div class="n2-i n2-i-addlayer2"></div>') . parent::fetchElement());
    }
}